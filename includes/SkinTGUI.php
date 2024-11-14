<?php
namespace MediaWiki\Skins\TGUI;

use MediaWiki\Skins\TGUI\Partials\Metadata;
use MediaWiki\Skins\TGUI\Partials\Theme;
use ExtensionRegistry;
use Html;
use Linker;
use MediaWiki\MediaWikiServices;
use SkinMustache;
use SkinTemplate;
use SpecialPage;
use Title;
use User;

class SkinTGUI extends SkinMustache {
	use GetConfigTrait;

	/** @var null|array for caching purposes */
	private $languages;

	/** @var int */
	private const MENU_TYPE_DEFAULT = 0;
	/** @var int */
	private const MENU_TYPE_TABS = 1;
	/** @var int */
	private const MENU_TYPE_DROPDOWN = 2;
	private const MENU_TYPE_PORTAL = 3;
	private const SEARCH_SHOW_THUMBNAIL_CLASS = 'tgui-search-box-show-thumbnail';
	private const SEARCH_AUTO_EXPAND_WIDTH_CLASS = 'tgui-search-box-auto-expand-width';

	/**
	 * Overrides template, styles and scripts module
	 *
	 * @inheritDoc
	 */
	public function __construct( $options = [] ) {
		if ( !isset( $options['name'] ) ) {
			$options['name'] = 'tgui';
		}

		// Add skin-specific features
		$this->buildSkinFeatures( $options );
		parent::__construct( $options );
	}

	/**
	 * Ensure onSkinTemplateNavigation runs after all SkinTemplateNavigation hooks
	 *
	 * @param SkinTemplate $skin The skin template object.
	 * @param array &$content_navigation The content navigation array.
	 */
	protected function runOnSkinTemplateNavigationHooks( SkinTemplate $skin, &$content_navigation ) {
		parent::runOnSkinTemplateNavigationHooks( $skin, $content_navigation );
		Hooks\SkinHooks::onSkinTemplateNavigation( $skin, $content_navigation );
	}

	/**
	 * Returns HTML for the create account link inside the anon user links
	 * @param string[] $returnto array of query strings used to build the login link
	 * @param bool $isDropdownItem Set true for create account link inside the user menu dropdown
	 *  which includes icon classes and is not styled like a button
	 * @return string
	 */
	private function getCreateAccountHTML( $returnto, $isDropdownItem ) {
		$createAccountData = $this->buildCreateAccountData( $returnto );
		$createAccountData = array_merge( $createAccountData, [
			'class' => $isDropdownItem ? [
				'tgui-menu-content-item',
			] : '',
			'collapsible' => true,
			'icon' => $isDropdownItem ? $createAccountData['icon'] : null,
			'button' => !$isDropdownItem,
		] );
		$createAccountData = Hooks\SkinHooks::updateLinkData( $createAccountData );
		return $this->makeLink( 'create-account', $createAccountData );
	}

	/**
	 * Returns HTML for the create account button, login button and learn more link inside the anon user menu
	 * @param string[] $returnto array of query strings used to build the login link
	 * @param bool $useCombinedLoginLink if a combined login/signup link will be used
	 * @param bool $isTempUser
	 * @param bool $includeLearnMoreLink Pass `true` to include the learn more
	 * link in the menu for anon users. This param will be inert for temp users.
	 * @return string
	 */
	private function getAnonMenuBeforePortletHTML(
		$returnto,
		$useCombinedLoginLink,
		$isTempUser,
		$includeLearnMoreLink
	) {
		$templateParser = $this->getTemplateParser();
		$loginLinkData = array_merge( $this->buildLoginData( $returnto, $useCombinedLoginLink ), [
			'class' => [ 'tgui-menu-content-item', 'tgui-menu-content-item-login' ],
		] );
		$loginLinkData = Hooks\SkinHooks::updateLinkData( $loginLinkData );
		$templateData = [
			'htmlCreateAccount' => $this->getCreateAccountHTML( $returnto, true ),
			'htmlLogin' => $this->makeLink( 'login', $loginLinkData ),
			'data-anon-editor' => []
		];

		$templateName = $isTempUser ? 'UserLinks__templogin' : 'UserLinks__login';

		if ( !$isTempUser && $includeLearnMoreLink ) {
			try {
				$learnMoreLinkData = [
					'text' => $this->msg( 'tgui-anon-user-menu-pages-learn' )->text(),
					'href' => Title::newFromText( $this->msg( 'tgui-intro-page' )->text() )->getLocalURL(),
					'aria-label' => $this->msg( 'tgui-anon-user-menu-pages-label' )->text(),
				];

				$templateData['data-anon-editor'] = [
					'htmlLearnMoreLink' => $this->makeLink( '', $learnMoreLinkData ),
					'msgLearnMore' => $this->msg( 'tgui-anon-user-menu-pages' )
				];
			} catch ( MalformedTitleException $e ) {
				// ignore (T340220)
			}
		}

		return $templateParser->processTemplate( $templateName, $templateData );
	}

	/**
	 * Returns HTML for the logout button that should be placed in the user (personal) menu
	 * after the menu itself.
	 * @return string
	 */
	private function getLogoutHTML() {
		$logoutLinkData = array_merge( $this->buildLogoutLinkData(), [
			'class' => [ 'tgui-menu-content-item', 'tgui-menu-content-item-logout' ],
		] );
		$logoutLinkData = Hooks\SkinHooks::updateLinkData( $logoutLinkData );

		$templateParser = $this->getTemplateParser();
		return $templateParser->processTemplate( 'UserLinks__logout', [
			'htmlLogout' => $this->makeLink( 'logout', $logoutLinkData )
		] );
	}

	/**
	 * Returns template data for UserLinks.mustache
	 * @param array $menuData existing menu template data to be transformed and copied for UserLinks
	 * @param User $user the context user
	 * @return array
	 */
	private function getUserLinksTemplateData( $menuData, $user ): array {
		$isAnon = !$user->isRegistered();
		$isTempUser = $user->isTemp();
		$returnto = $this->getReturnToParam();
		$useCombinedLoginLink = $this->useCombinedLoginLink();
		$userMenuData = $menuData[ 'data-user-menu' ];
		if ( $isAnon || $isTempUser ) {
			$userMenuData[ 'html-before-portal' ] .= $this->getAnonMenuBeforePortletHTML(
				$returnto,
				$useCombinedLoginLink,
				$isTempUser,
				// T317789: The `anontalk` and `anoncontribs` links will not be added to
				// the menu if `$wgGroupPermissions['*']['edit']` === false which can
				// leave the menu empty due to our removal of other user menu items in
				// `Hooks\SkinHooks::updateUserLinksDropdownItems`. In this case, we do not want
				// to render the anon "learn more" link.
				!$userMenuData['is-empty']
			);
		} else {
			// Appending as to not override data potentially set by the onSkinAfterPortlet hook.
			$userMenuData[ 'html-after-portal' ] .= $this->getLogoutHTML();
		}

		return [
			'data-user-menu' => $userMenuData
		];
	}

	/**
	 * Annotates search box with TGUI-specific information
	 *
	 * @param array $searchBoxData
	 * @param bool $isCollapsible
	 * @param bool $isPrimary
	 * @param string $formId
	 * @param bool $autoExpandWidth
	 * @return array modified version of $searchBoxData
	 */
	final protected function getSearchData(
		array $searchBoxData,
		bool $isCollapsible,
		bool $isPrimary,
		string $formId,
		bool $autoExpandWidth
	) {
		$searchClass = 'tgui-search-box-vue ';

		if ( $isCollapsible ) {
			$searchClass .= ' tgui-search-box-collapses ';
		}

		if ( $this->doesSearchHaveThumbnails() ) {
			$searchClass .= ' ' . self::SEARCH_SHOW_THUMBNAIL_CLASS .
				( $autoExpandWidth ? ' ' . self::SEARCH_AUTO_EXPAND_WIDTH_CLASS : '' );
		}

		// Annotate search box with a component class.
		$searchBoxData['class'] = trim( $searchClass );
		$searchBoxData['is-collapsible'] = $isCollapsible;
		$searchBoxData['is-primary'] = $isPrimary;
		$searchBoxData['form-id'] = $formId;

		// At lower resolutions the search input is hidden search and only the submit button is shown.
		// It should behave like a form submit link (e.g. submit the form with no input value).
		// We'll wire this up in a later task T284242.
		$collapseIconAttrs = Linker::tooltipAndAccesskeyAttribs( 'search' );
		$searchBoxData['data-collapse-icon'] = array_merge( [
			'href' => Title::newFromText( $searchBoxData['page-title'] )->getLocalUrl(),
			'label' => $this->msg( 'search' ),
			'icon' => 'wikimedia-search',
			'is-quiet' => true,
			'class' => 'search-toggle',
		], $collapseIconAttrs );

		return $searchBoxData;
	}

	/**
	 * Returns `true` if Vue search is enabled to show thumbnails and `false` otherwise.
	 * Note this is only relevant for Vue search experience (not legacy search).
	 *
	 * @return bool
	 */
	private function doesSearchHaveThumbnails(): bool {
		return $this->getConfig()->get( 'TGUIWvuiSearchOptions' )['showThumbnail'];
	}

	/**
	 * Creates portlet data for the user menu dropdown
	 *
	 * @param array $portletData
	 * @return array
	 */
	private function getUserMenuPortletData( $portletData ) {
		// T317789: Core can undesirably add an 'emptyPortlet' class that hides the
		// user menu. This is a result of us manually removing items from the menu
		// in Hooks\SkinHooks::updateUserLinksDropdownItems which can make
		// SkinTemplate::getPortletData apply the `emptyPortlet` class if there are
		// no menu items. Since we subsequently add menu items in
		// SkinTGUI::getUserLinksTemplateData, the `emptyPortlet` class is
		// innaccurate. This is why we add the desired classes, `mw-portlet` and
		// `mw-portlet-personal` here instead. This can potentially be removed upon
		// completion of T319356.
		//
		// Also, add target class to apply different icon to personal menu dropdown for logged in users.
		$portletData['class'] = 'mw-portlet mw-portlet-personal tgui-user-menu';
		$portletData['class'] .= $this->loggedin ?
			' tgui-user-menu-logged-in' :
			' tgui-user-menu-logged-out';
		if ( $this->getUser()->isTemp() ) {
			$icon = 'userAnonymous';
		} elseif ( $this->loggedin ) {
			$icon = 'userAvatar';
		} else {
			$icon = 'ellipsis';
			// T287494 We use tooltip messages to provide title attributes on hover over certain menu icons.
			// For modern TGUI, the "tooltip-p-personal" key is set to "User menu" which is appropriate for
			// the user icon (dropdown indicator for user links menu) for logged-in users.
			// This overrides the tooltip for the user links menu icon which is an ellipsis for anonymous users.
			$portletData['html-tooltip'] = Linker::tooltip( 'tgui-anon-user-menu-title' );
		}
		$portletData['icon'] = $icon;
		$portletData['button'] = true;
		$portletData['text-hidden'] = true;
		return $portletData;
	}

	/**
	 * Helper for applying TGUI menu classes to portlets
	 *
	 * @param array $portletData returned by SkinMustache to decorate
	 * @param int $type representing one of the menu types (see MENU_TYPE_* constants)
	 * @return array modified version of portletData input
	 */
	private function updatePortletClasses(
		array $portletData,
		int $type = self::MENU_TYPE_DEFAULT
	) {
		$extraClasses = [
			self::MENU_TYPE_DROPDOWN => 'tgui-menu-dropdown',
			self::MENU_TYPE_TABS => 'tgui-menu-tabs',
			self::MENU_TYPE_PORTAL => 'tgui-menu-portal portal',
			self::MENU_TYPE_DEFAULT => '',
		];
		$portletData['class'] .= ' ' . $extraClasses[$type];

		if ( !isset( $portletData['heading-class'] ) ) {
			$portletData['heading-class'] = '';
		}
		if ( $type === self::MENU_TYPE_DROPDOWN ) {
			$portletData = Hooks\SkinHooks::updateDropdownMenuData( $portletData );
		}

		$portletData['class'] = trim( $portletData['class'] );
		$portletData['heading-class'] = trim( $portletData['heading-class'] );
		return $portletData;
	}

	/**
	 * Performs updates to all portlets.
	 *
	 * @param array $data
	 * @return array
	 */
	private function decoratePortletsData( array $data ) {
		foreach ( $data['data-portlets'] as $key => $pData ) {
			$data['data-portlets'][$key] = $this->decoratePortletData(
				$key,
				$pData
			);
		}
		$sidebar = $data['data-portlets-sidebar'];
		$sidebar['data-portlets-first'] = $this->decoratePortletData(
			'navigation', $sidebar['data-portlets-first']
		);
		$rest = $sidebar['array-portlets-rest'];
		foreach ( $rest as $key => $pData ) {
			$rest[$key] = $this->decoratePortletData(
				$pData['id'], $pData
			);
		}
		$sidebar['array-portlets-rest'] = $rest;
		$data['data-portlets-sidebar'] = $sidebar;
		return $data;
	}

	/**
	 * Performs the following updates to portlet data:
	 * - Adds concept of menu types
	 * - Marks the selected variant in the variant portlet
	 * - modifies tooltips of personal and user-menu portlets
	 * @param string $key
	 * @param array $portletData
	 * @return array
	 */
	private function decoratePortletData(
		string $key,
		array $portletData
	): array {
		switch ( $key ) {
			case 'data-user-menu':
			case 'data-actions':
			case 'data-variants':
			case 'data-sticky-header-toc':
				$type = self::MENU_TYPE_DROPDOWN;
				break;
			case 'data-views':
			case 'data-associated-pages':
			case 'data-namespaces':
				$type = self::MENU_TYPE_TABS;
				break;
			case 'data-notifications':
			case 'data-personal':
			case 'data-user-page':
			default:
				$type = self::MENU_TYPE_PORTAL;
				break;
		}

		if ( $key === 'data-user-menu' ) {
			$portletData = $this->getUserMenuPortletData( $portletData );
		}

		// Special casing for Variant to change label to selected.
		// Hopefully we can revisit and possibly remove this code when the language switcher is moved.
		if ( $key === 'data-variants' ) {
			$languageConverterFactory = MediaWikiServices::getInstance()->getLanguageConverterFactory();
			$pageLang = $this->getTitle()->getPageLanguage();
			$converter = $languageConverterFactory->getLanguageConverter( $pageLang );
			$portletData['label'] = $pageLang->getVariantname(
				$converter->getPreferredVariant()
			);
			// T289523 Add aria-label data to the language variant switcher.
			$portletData['aria-label'] = $this->msg( 'tgui-language-variant-switcher-label' );
		}

		$portletData = $this->updatePortletClasses(
			$portletData,
			$type
		);

		return $portletData + [
			'is-dropdown' => $type === self::MENU_TYPE_DROPDOWN,
			'is-portal' => $type === self::MENU_TYPE_PORTAL,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$skin = $this;

		$parentData = $this->decoratePortletsData( parent::getTemplateData() );
		$commonSkinData = array_merge( $parentData, [
			'data-search-box' => $this->getSearchData(
				$parentData['data-search-box'],
				false,
				false,
				'tgui-sticky-search-form',
				false
			)
		] );

		$user = $skin->getUser();
		$commonSkinData['data-tgui-user-links'] = $this->getUserLinksTemplateData(
			$commonSkinData['data-portlets'],
			$user
		);

		return $commonSkinData;
	}

	/**
	 * Add client preferences features
	 * Did not add the tgui-feature- prefix because there might be features from core MW or extensions
	 *
	 * @param string $feature
	 * @param string $value
	 */
	private function addClientPrefFeature( string $feature, string $value = 'standard' ) {
		$this->getOutput()->addHtmlClasses( $feature . '-clientpref-' . $value );
	}

	/**
	 * Set up optional skin features
	 *
	 * @param array &$options
	 */
	private function buildSkinFeatures( array &$options ) {
		$title = $this->getOutput()->getTitle();

		$metadata = new Metadata( $this );
		$skinTheme = new Theme( $this );

		// Add metadata
		$metadata->addMetadata();

		// Add theme handler
		$skinTheme->setSkinTheme( $options );

		// Clientprefs feature handling
		$this->addClientPrefFeature( 'tgui-feature-blur', 'enabled' );
		$this->addClientPrefFeature( 'tgui-feature-reduced-motion', 'disabled' );
		$this->addClientPrefFeature( 'tgui-feature-darkened-images', 'disabled' );
		$this->addClientPrefFeature( 'tgui-feature-primary-hue-slider', '210' );
	}
}
