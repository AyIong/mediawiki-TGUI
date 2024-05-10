<?php
namespace MediaWiki\Skins\TGUI;

use ExtensionRegistry;
use Html;
use Linker;
use MediaWiki\MediaWikiServices;
use RuntimeException;
use SkinMustache;
use SkinTemplate;
use SpecialPage;
use Title;
use User;

abstract class SkinTGUI extends SkinMustache {
	/** @var null|array for caching purposes */
	private $languages;
	/** @var int */
	private const MENU_TYPE_DEFAULT = 0;
	/** @var int */
	private const MENU_TYPE_TABS = 1;
	/** @var int */
	private const MENU_TYPE_DROPDOWN = 2;
	private const MENU_TYPE_PORTAL = 3;
	private const TALK_ICON = [
		'href' => '#',
		'id' => 'ca-talk-sticky-header',
		'event' => 'talk-sticky-header',
		'icon' => 'wikimedia-speechBubbles',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const SUBJECT_ICON = [
		'href' => '#',
		'id' => 'ca-subject-sticky-header',
		'event' => 'subject-sticky-header',
		'icon' => 'wikimedia-article',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const HISTORY_ICON = [
		'href' => '#',
		'id' => 'ca-history-sticky-header',
		'event' => 'history-sticky-header',
		'icon' => 'wikimedia-history',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	// Event and icon will be updated depending on watchstar state
	private const WATCHSTAR_ICON = [
		'href' => '#',
		'id' => 'ca-watchstar-sticky-header',
		'event' => 'watch-sticky-header',
		'icon' => 'wikimedia-star',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon mw-watchlink'
	];
	private const EDIT_VE_ICON = [
		'href' => '#',
		'id' => 'ca-ve-edit-sticky-header',
		'event' => 've-edit-sticky-header',
		'icon' => 'wikimedia-edit',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const EDIT_WIKITEXT_ICON = [
		'href' => '#',
		'id' => 'ca-edit-sticky-header',
		'event' => 'wikitext-edit-sticky-header',
		'icon' => 'wikimedia-wikiText',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const EDIT_PROTECTED_ICON = [
		'href' => '#',
		'id' => 'ca-viewsource-sticky-header',
		'event' => 've-edit-protected-sticky-header',
		'icon' => 'wikimedia-editLock',
		'is-quiet' => true,
		'tabindex' => '-1',
		'class' => 'sticky-header-icon'
	];
	private const SEARCH_SHOW_THUMBNAIL_CLASS = 'tgui-search-box-show-thumbnail';
	private const SEARCH_AUTO_EXPAND_WIDTH_CLASS = 'tgui-search-box-auto-expand-width';
	private const CLASS_PROGRESSIVE = 'mw-ui-progressive';

	/**
	 * T243281: Code used to track clicks to opt-out link.
	 *
	 * The "vct" substring is used to describe the newest "TGUI" (non-legacy)
	 * feature. The "w" describes the web platform. The "1" describes the version
	 * of the feature.
	 *
	 * @see https://wikitech.wikimedia.org/wiki/Provenance
	 * @var string
	 */
	private const OPT_OUT_LINK_TRACKING_CODE = 'vctw1';

	/**
	 * Calls getLanguages with caching.
	 * @return array
	 */
	protected function getLanguagesCached(): array {
		if ( $this->languages === null ) {
			$this->languages = $this->getLanguages();
		}
		return $this->languages;
	}

	/**
	 * This should be upstreamed to the Skin class in core once the logic is finalized.
	 * Returns false if the page is a special page without any languages, or if an action
	 * other than view is being used.
	 * @return bool
	 */
	private function canHaveLanguages(): bool {
		if ( $this->getContext()->getActionName() !== 'view' ) {
			return false;
		}
		$title = $this->getTitle();
		// Defensive programming - if a special page has added languages explicitly, best to show it.
		if ( $title && $title->isSpecialPage() && empty( $this->getLanguagesCached() ) ) {
			return false;
		}
		return true;
	}

	/**
	 * @param string $location Either 'top' or 'bottom' is accepted.
	 * @return bool
	 */
	protected function isLanguagesInContentAt( $location ) {
		if ( !$this->canHaveLanguages() ) {
			return false;
		}
		$featureManager = TGUIServices::getFeatureManager();
		$inContent = $featureManager->isFeatureEnabled(
			Constants::FEATURE_LANGUAGE_IN_HEADER
		);
		$isMainPage = $this->getTitle() ? $this->getTitle()->isMainPage() : false;

		switch ( $location ) {
			case 'top':
				return $isMainPage ? $inContent && $featureManager->isFeatureEnabled(
					Constants::FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER
				) : $inContent;
			case 'bottom':
				return $inContent && $isMainPage && !$featureManager->isFeatureEnabled(
					Constants::FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER
				);
			default:
				throw new RuntimeException( 'unknown language button location' );
		}
	}

	/**
	 * Whether or not the languages are out of the sidebar and in the content either at
	 * the top or the bottom.
	 * @return bool
	 */
	private function isLanguagesInContent() {
		return $this->isLanguagesInContentAt( 'top' ) || $this->isLanguagesInContentAt( 'bottom' );
	}

	/**
	 * Show the ULS button if it's modern TGUI, languages in header is enabled,
	 * and the ULS extension is enabled. Hide it otherwise.
	 * There is no point in showing the language button if ULS extension is unavailable
	 * as there is no ways to add languages without it.
	 * @return bool
	 */
	protected function shouldHideLanguages() {
		return !$this->isLanguagesInContent() || !$this->isULSExtensionEnabled();
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
		$createAccountData = Hooks::updateLinkData( $createAccountData );
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
		$loginLinkData = Hooks::updateLinkData( $loginLinkData );
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
		$logoutLinkData = Hooks::updateLinkData( $logoutLinkData );

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
				// `Hooks::updateUserLinksDropdownItems`. In this case, we do not want
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
	 * @inheritDoc
	 */
	protected function runOnSkinTemplateNavigationHooks( SkinTemplate $skin, &$content_navigation ) {
		parent::runOnSkinTemplateNavigationHooks( $skin, $content_navigation );
		Hooks::onSkinTemplateNavigation( $skin, $content_navigation );
	}

	/**
	 * Check whether ULS is enabled
	 *
	 * @return bool
	 */
	private function isULSExtensionEnabled(): bool {
		return ExtensionRegistry::getInstance()->isLoaded( 'UniversalLanguageSelector' );
	}

	/**
	 * Generate data needed to create SidebarAction item.
	 * @param array $htmlData data to make a link or raw html
	 * @param array $headingOptions optional heading for the html
	 * @return array keyed data for the SidebarAction template
	 */
	private function makeSidebarActionData( array $htmlData = [], array $headingOptions = [] ): array {
		$htmlContent = '';
		// Populates the sidebar as a standalone link or custom html.
		if ( array_key_exists( 'link', $htmlData ) ) {
			$htmlContent = $this->makeLink( 'link', $htmlData['link'] );
		} elseif ( array_key_exists( 'html-content', $htmlData ) ) {
			$htmlContent = $htmlData['html-content'];
		}

		return $headingOptions + [
			'html-content' => $htmlContent,
		];
	}

	/**
	 * Determines if the language switching alert box should be in the sidebar.
	 *
	 * @return bool
	 */
	private function shouldLanguageAlertBeInSidebar(): bool {
		$featureManager = TGUIServices::getFeatureManager();
		$isMainPage = $this->getTitle() ? $this->getTitle()->isMainPage() : false;
		$shouldShowOnMainPage = $isMainPage && !empty( $this->getLanguagesCached() ) &&
			$featureManager->isFeatureEnabled( Constants::FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER );
		return ( $this->isLanguagesInContentAt( 'top' ) && !$isMainPage && !$this->shouldHideLanguages() &&
			$featureManager->isFeatureEnabled( Constants::FEATURE_LANGUAGE_ALERT_IN_SIDEBAR ) ) ||
			$shouldShowOnMainPage;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$skin = $this;

		$parentData = $this->decoratePortletsData( parent::getTemplateData() );

		//
		// Naming conventions for Mustache parameters.
		//
		// Value type (first segment):
		// - Prefix "is" or "has" for boolean values.
		// - Prefix "msg-" for interface message text.
		// - Prefix "html-" for raw HTML.
		// - Prefix "data-" for an array of template parameters that should be passed directly
		//   to a template partial.
		// - Prefix "array-" for lists of any values.
		//
		// Source of value (first or second segment)
		// - Segment "page-" for data relating to the current page (e.g. Title, WikiPage, or OutputPage).
		// - Segment "hook-" for any thing generated from a hook.
		//   It should be followed by the name of the hook in hyphenated lowercase.
		//
		// Conditionally used values must use null to indicate absence (not false or '').
		$commonSkinData = array_merge( $parentData, [
			'input-location' => $this->getSearchBoxInputLocation(),
			'sidebar-visible' => $this->isSidebarVisible(),
			'is-language-in-content' => $this->isLanguagesInContent(),
			'is-language-in-content-top' => $this->isLanguagesInContentAt( 'top' ),
			'is-language-in-content-bottom' => $this->isLanguagesInContentAt( 'bottom' ),
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

		// T295555 Add language switch alert message temporarily (to be removed).
		if ( $this->shouldLanguageAlertBeInSidebar() ) {
			$languageSwitchAlert = [
				'html-content' => Html::noticeBox(
					$this->msg( 'tgui-language-redirect-to-top' )->parse(),
					'tgui-language-sidebar-alert'
				),
			];
			$headingOptions = [
				'heading' => $this->msg( 'tgui-languages' )->plain(),
			];
			$commonSkinData['data-tgui-language-switch-alert'][] = $this->makeSidebarActionData(
				$languageSwitchAlert,
				$headingOptions
			);
		}

		return $commonSkinData;
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
	 * Gets the value of the "input-location" parameter for the SearchBox Mustache template.
	 *
	 * @return string Either `Constants::SEARCH_BOX_INPUT_LOCATION_DEFAULT` or
	 *  `Constants::SEARCH_BOX_INPUT_LOCATION_MOVED`
	 */
	private function getSearchBoxInputLocation(): string {
		return Constants::SEARCH_BOX_INPUT_LOCATION_MOVED;
	}

	/**
	 * @inheritDoc
	 */
	public function isResponsive() {
		// Check it's enabled by user preference and configuration
		$responsive = parent::isResponsive() && $this->getConfig()->get( 'TGUIResponsive' );
		// For historic reasons, the viewport is added when TGUI is loaded on the mobile
		// domain. This is only possible for 3rd parties or by useskin parameter as there is
		// no preference for changing mobile skin. Only need to check if $responsive is falsey.
		if ( !$responsive && ExtensionRegistry::getInstance()->isLoaded( 'MobileFrontend' ) ) {
			$mobFrontContext = MediaWikiServices::getInstance()->getService( 'MobileFrontend.Context' );
			if ( $mobFrontContext->shouldDisplayMobileView() ) {
				return true;
			}
		}
		return $responsive;
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
	 * Determines wheather the initial state of sidebar is visible on not
	 *
	 * @return bool
	 */
	private function isSidebarVisible() {
		$skin = $this->getSkin();
		if ( $skin->getUser()->isRegistered() ) {
			$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
			$userPrefSidebarState = $userOptionsLookup->getOption(
				$skin->getUser(),
				Constants::PREF_KEY_SIDEBAR_VISIBLE
			);

			$defaultLoggedinSidebarState = $this->getConfig()->get(
				Constants::CONFIG_KEY_DEFAULT_SIDEBAR_VISIBLE_FOR_AUTHORISED_USER
			);

			// If the sidebar user preference has been set, return that value,
			// if not, then the default sidebar state for logged-in users.
			return ( $userPrefSidebarState !== null )
				? (bool)$userPrefSidebarState
				: $defaultLoggedinSidebarState;
		}
		return $this->getConfig()->get(
			Constants::CONFIG_KEY_DEFAULT_SIDEBAR_VISIBLE_FOR_ANONYMOUS_USER
		);
	}

	/**
	 * Get the ULS button label, accounting for the number of available
	 * languages.
	 *
	 * @return array
	 */
	private function getULSLabels(): array {
		$numLanguages = count( $this->getLanguagesCached() );

		if ( $numLanguages === 0 ) {
			return [
				'label' => $this->msg( 'tgui-no-language-button-label' )->text(),
				'aria-label' => $this->msg( 'tgui-no-language-button-aria-label' )->text()
			];
		} else {
			return [
				'label' => $this->msg( 'tgui-language-button-label' )->numParams( $numLanguages )->escaped(),
				'aria-label' => $this->msg( 'tgui-language-button-aria-label' )->numParams( $numLanguages )->escaped()
			];
		}
	}

	/**
	 * Creates button data for the "Add section" button in the sticky header
	 *
	 * @return array
	 */
	private function getAddSectionButtonData() {
		return [
			'href' => '#',
			'id' => 'ca-addsection-sticky-header',
			'event' => 'addsection-sticky-header',
			'html-tgui-button-icon' => Hooks::makeIcon( 'wikimedia-speechBubbleAdd-progressive' ),
			'label' => $this->msg( [ 'tgui-action-addsection', 'skin-action-addsection' ] ),
			'is-quiet' => true,
			'tabindex' => '-1',
			'class' => 'sticky-header-icon mw-ui-primary mw-ui-progressive'
		];
	}

	/**
	 * Creates button data for the ULS button in the sticky header
	 *
	 * @return array
	 */
	private function getULSButtonData() {
		$numLanguages = count( $this->getLanguagesCached() );

		return [
			'id' => 'p-lang-btn-sticky-header',
			'class' => 'mw-interlanguage-selector',
			'is-quiet' => true,
			'tabindex' => '-1',
			'label' => $this->getULSLabels()[ 'label' ],
			'html-tgui-button-icon' => Hooks::makeIcon( 'wikimedia-language' ),
			'event' => 'ui.dropdown-p-lang-btn-sticky-header'
		];
	}

	/**
	 * Creates portlet data for the ULS button in the header
	 *
	 * @return array
	 */
	private function getULSPortletData() {
		$numLanguages = count( $this->getLanguagesCached() );

		$languageButtonData = [
			'id' => 'p-lang-btn',
			'label' => $this->getULSLabels()['label'],
			'aria-label' => $this->getULSLabels()['aria-label'],
			// ext.uls.interface attaches click handler to this selector.
			'checkbox-class' => ' mw-interlanguage-selector ',
			'icon' => 'language-progressive',
			'button' => true,
			'heading-class' => self::CLASS_PROGRESSIVE . ' mw-portlet-lang-heading-' . strval( $numLanguages ),
		];

		// Adds class to hide language button
		// Temporary solution to T287206, can be removed when ULS dialog includes interwiki links
		if ( $this->shouldHideLanguages() ) {
			$languageButtonData['class'] = ' mw-portlet-empty';
		}

		return $languageButtonData;
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
		// in Hooks::updateUserLinksDropdownItems which can make
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
			$portletData = Hooks::updateDropdownMenuData( $portletData );
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
			case 'data-languages':
				$type = $this->isLanguagesInContent() ?
					self::MENU_TYPE_DROPDOWN : self::MENU_TYPE_PORTAL;
				break;
			default:
				$type = self::MENU_TYPE_PORTAL;
				break;
		}

		if ( $key === 'data-languages' && $this->isLanguagesInContent() ) {
			$portletData = array_merge( $portletData, $this->getULSPortletData() );
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
}
