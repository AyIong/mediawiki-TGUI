<?php
namespace MediaWiki\Skins\TGUI;

use MediaWiki\MediaWikiServices;
use MediaWiki\Skins\TGUI\Components\TGUIComponentPageHeading;
use MediaWiki\Skins\TGUI\Components\TGUIComponentPageSidebar;
use MediaWiki\Skins\TGUI\Components\TGUIComponentPageTools;
use MediaWiki\Skins\TGUI\Components\TGUIComponentUserInfo;
use MediaWiki\Skins\TGUI\Partials\BodyContent;
use MediaWiki\Skins\TGUI\Partials\Metadata;
use MediaWiki\Skins\TGUI\Partials\Theme;
use SkinMustache;
use SkinTemplate;

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
	public function __construct($options = []) {
		if (!isset($options['name'])) {
			$options['name'] = 'tgui';
		}

		// Add skin-specific features
		$this->buildSkinFeatures($options);
		parent::__construct($options);
	}

	/**
	 * Ensure onSkinTemplateNavigation runs after all SkinTemplateNavigation hooks
	 *
	 * @param SkinTemplate $skin The skin template object.
	 * @param array &$content_navigation The content navigation array.
	 */
	protected function runOnSkinTemplateNavigationHooks(SkinTemplate $skin, &$content_navigation) {
		parent::runOnSkinTemplateNavigationHooks($skin, $content_navigation);
		Hooks\SkinHooks::onSkinTemplateNavigation($skin, $content_navigation);
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$parentData = parent::getTemplateData();

		$config = $this->getConfig();
		$localizer = $this->getContext();
		$out = $this->getOutput();
		$title = $this->getTitle();
		$user = $this->getUser();
		$pageLang = $title->getPageLanguage();
		$services = MediaWikiServices::getInstance();

		$isRegistered = $user->isRegistered();
		$isTemp = $user->isTemp();

		$bodycontent = new BodyContent($this);

		$components = [
			'data-main-menu' => new TGUIComponentPageSidebar($parentData['data-portlets-sidebar']),
			'data-page-heading' => new TGUIComponentPageHeading(
				$services,
				$localizer,
				$out,
				$pageLang,
				$title,
				$parentData['html-title-heading']
			),
			'data-page-tools' => new TGUIComponentPageTools($config, $localizer, $title, $user),
			'data-user-info' => new TGUIComponentUserInfo(
				$isRegistered,
				$isTemp,
				$services,
				$localizer,
				$title,
				$user,
				$parentData['data-portlets']['data-user-page']
			),
		];

		foreach ($components as $key => $component) {
			// Array of components or null values.
			if ($component) {
				$parentData[$key] = $component->getTemplateData();
			}
		}

		// HACK: So that we can use Icon.mustache in Header__logo.mustache
		$parentData['data-logos']['icon-home'] = 'home';

		// TGUI Background logo
		$parentData['wgTGUIBackground'] = $GLOBALS['wgTGUIBackground'] ?? '/skins/TGUI/resources/skins.tgui.styles/images/nanotrasen.svg';

		return array_merge($parentData, [
			// Booleans
			'toc-enabled' => !empty($parentData['data-toc']),
			'html-body-content--formatted' => $bodycontent->decorateBodyContent($parentData['html-body-content']),
		]);
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

		if ($isCollapsible) {
			$searchClass .= ' tgui-search-box-collapses ';
		}

		if ($this->doesSearchHaveThumbnails()) {
			$searchClass .= ' ' . self::SEARCH_SHOW_THUMBNAIL_CLASS . ($autoExpandWidth ? ' ' . self::SEARCH_AUTO_EXPAND_WIDTH_CLASS : '');
		}

		// Annotate search box with a component class.
		$searchBoxData['class'] = trim($searchClass);
		$searchBoxData['is-collapsible'] = $isCollapsible;
		$searchBoxData['is-primary'] = $isPrimary;
		$searchBoxData['form-id'] = $formId;

		// At lower resolutions the search input is hidden search and only the submit button is shown.
		// It should behave like a form submit link (e.g. submit the form with no input value).
		// We'll wire this up in a later task T284242.
		$collapseIconAttrs = Linker::tooltipAndAccesskeyAttribs('search');
		$searchBoxData['data-collapse-icon'] = array_merge(
			[
				'href' => Title::newFromText($searchBoxData['page-title'])->getLocalUrl(),
				'label' => $this->msg('search'),
				'icon' => 'wikimedia-search',
				'is-quiet' => true,
				'class' => 'search-toggle',
			],
			$collapseIconAttrs
		);

		return $searchBoxData;
	}

	/**
	 * Returns `true` if Vue search is enabled to show thumbnails and `false` otherwise.
	 * Note this is only relevant for Vue search experience (not legacy search).
	 *
	 * @return bool
	 */
	private function doesSearchHaveThumbnails(): bool {
		return $this->getConfig()->get('TGUIWvuiSearchOptions')['showThumbnail'];
	}

	/**
	 * Add client preferences features
	 * Did not add the tgui-feature- prefix because there might be features from core MW or extensions
	 *
	 * @param string $feature
	 * @param string $value
	 */
	private function addClientPrefFeature(string $feature, string $value = 'standard') {
		$this->getOutput()->addHtmlClasses($feature . '-clientpref-' . $value);
	}

	/**
	 * Set up optional skin features
	 *
	 * @param array &$options
	 */
	private function buildSkinFeatures(array &$options) {
		$config = $this->getConfig();
		$title = $this->getOutput()->getTitle();

		$metadata = new Metadata($this);
		$skinTheme = new Theme($this);

		// Add metadata
		$metadata->addMetadata();

		// Add theme handler
		$skinTheme->setSkinTheme($options);

		// Clientprefs feature handling
		$this->addClientPrefFeature('tgui-feature-blur', 'enabled');
		$this->addClientPrefFeature('tgui-feature-font-size', 'medium');
		$this->addClientPrefFeature('tgui-feature-sticky-header', 'disabled');
		$this->addClientPrefFeature('tgui-feature-reduced-motion', 'disabled');
		$this->addClientPrefFeature('tgui-feature-holidays', 'enabled');
		$this->addClientPrefFeature('tgui-feature-darkened-images', 'light');
		$this->addClientPrefFeature('tgui-feature-primary-hue-slider', '210');

		if ($title !== null) {
			// Collapsible sections
			if ($config->get('TGUIEnableCollapsibleSections') === true && $title->isContentPage()) {
				$options['bodyClasses'][] = 'tgui-sections-enabled';
			}
		}
	}
}
