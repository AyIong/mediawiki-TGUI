<?php
namespace MediaWiki\Skins\TGUI;

use FatalError;

/**
 * A namespace for TGUI constants for internal TGUI usage only. **Do not rely on this file as an
 * API as it may change without warning at any time.**
 * @package TGUI
 * @internal
 */
final class Constants {
	/**
	 * This is tightly coupled to the ValidSkinNames field in skin.json.
	 * @var string
	 */
	public const SKIN_NAME = 'tgui';

	/**
	 * @var string
	 */
	public const SERVICE_FEATURE_MANAGER = 'TGUI.FeatureManager';

	/**
	 * @var string
	 */
	public const CONFIG_KEY_DEFAULT_SIDEBAR_VISIBLE_FOR_AUTHORISED_USER =
		'TGUIDefaultSidebarVisibleForAuthorisedUser';

	/**
	 * @var string
	 */
	public const CONFIG_KEY_DEFAULT_SIDEBAR_VISIBLE_FOR_ANONYMOUS_USER =
		'TGUIDefaultSidebarVisibleForAnonymousUser';

	/**
	 * @var string
	 */
	public const PREF_KEY_SKIN = 'skin';

	/**
	 * @var string
	 */
	public const PREF_KEY_SIDEBAR_VISIBLE = 'TGUISidebarVisible';

	// These are used in the Feature Management System.
	/**
	 * Also known as `$wgFullyInitialised`. Set to true in core/includes/Setup.php.
	 * @var string
	 */
	public const CONFIG_KEY_FULLY_INITIALISED = 'FullyInitialised';

	/**
	 * @var string
	 */
	public const REQUIREMENT_FULLY_INITIALISED = 'FullyInitialised';

	/**
	 * @var string
	 */
	public const FEATURE_LANGUAGE_IN_HEADER = 'LanguageInHeader';

	/**
	 * @var string
	 */
	public const CONFIG_KEY_DISABLE_SIDEBAR_PERSISTENCE = 'TGUIDisableSidebarPersistence';

	/**
	 * @var string
	 */
	public const CONFIG_KEY_LANGUAGE_IN_HEADER = 'TGUILanguageInHeader';

	/**
	 * @var string
	 */
	public const REQUIREMENT_LANGUAGE_IN_HEADER = 'LanguageInHeader';

	/**
	 * Defines whether or not the Language in header A/B test is running. See
	 * https://phabricator.wikimedia.org/T280825 for additional detail about the test.
	 *
	 * Note well that if the associated config value is falsy, then we fall back to choosing the
	 * language treatment based on the `TGUILanguageInHeader` config variable.
	 *
	 * @var string
	 */
	public const CONFIG_LANGUAGE_IN_HEADER_TREATMENT_AB_TEST = 'TGUILanguageInHeaderTreatmentABTest';

	/**
	 * The `mediawiki.searchSuggest` protocol piece of the SearchSatisfaction instrumention reads
	 * the value of an element with the "data-search-loc" attribute and set the event's
	 * `inputLocation` property accordingly.
	 *
	 * When the search widget is moved as part of the "Search 1: Search widget move" feature, the
	 * "data-search-loc" attribute is set to this value.
	 *
	 * See also:
	 * - https://www.mediawiki.org/wiki/Reading/Web/Desktop_Improvements/Features#Search_1:_Search_widget_move
	 * - https://phabricator.wikimedia.org/T261636 and https://phabricator.wikimedia.org/T256100
	 * - https://gerrit.wikimedia.org/g/mediawiki/core/+/61d36def2d7adc15c88929c824b444f434a0511a/resources/src/mediawiki.searchSuggest/searchSuggest.js#106
	 *
	 * @var string
	 */
	public const SEARCH_BOX_INPUT_LOCATION_MOVED = 'header-moved';

	/**
	 * Similar to `Constants::SEARCH_BOX_INPUT_LOCATION_MOVED`, when the search widget hasn't been
	 * moved, the "data-search-loc" attribute is set to this value.
	 *
	 * @var string
	 */
	public const SEARCH_BOX_INPUT_LOCATION_DEFAULT = 'header-navigation';

	/**
	 * @var string
	 */
	public const REQUIREMENT_IS_MAIN_PAGE = 'IsMainPage';

	/**
	 * @var string
	 */
	public const REQUIREMENT_LANGUAGE_IN_MAIN_PAGE_HEADER = 'LanguageInMainPageHeader';

	/**
	 * @var string
	 */
	public const CONFIG_LANGUAGE_IN_MAIN_PAGE_HEADER = 'TGUILanguageInMainPageHeader';

	/**
	 * @var string
	 */
	public const FEATURE_LANGUAGE_IN_MAIN_PAGE_HEADER = 'LanguageInMainPageHeader';

	/**
	 * @var string
	 */
	public const REQUIREMENT_LANGUAGE_ALERT_IN_SIDEBAR = 'LanguageAlertInSidebar';

	/**
	 * @var string
	 */
	public const CONFIG_LANGUAGE_ALERT_IN_SIDEBAR = 'TGUILanguageAlertInSidebar';

	/**
	 * @var string
	 */
	public const FEATURE_LANGUAGE_ALERT_IN_SIDEBAR = 'LanguageAlertInSidebar';

	/**
	 * @var string
	 */
	public const FEATURE_TABLE_OF_CONTENTS = 'TableOfContents';

	/**
	 * @var string
	 */
	public const REQUIREMENT_TABLE_OF_CONTENTS = 'TableOfContents';

	/**
	 * @var string
	 */
	public const WEB_AB_TEST_ARTICLE_ID_FACTORY_SERVICE = 'WikimediaEvents.WebABTestArticleIdFactory';

	/**
	 * @var string
	 */
	public const FEATURE_VISUAL_ENHANCEMENTS = 'VisualEnhancementNext';

	/**
	 * @var string
	 */
	public const REQUIREMENT_VISUAL_ENHANCEMENTS = 'VisualEnhancementNext';

	/**
	 * @var string
	 */
	public const CONFIG_KEY_VISUAL_ENHANCEMENTS = 'TGUIVisualEnhancementNext';

	/**
	 * This class is for namespacing constants only. Forbid construction.
	 * @throws FatalError
	 * @return never
	 */
	private function __construct() {
		throw new FatalError( "Cannot construct a utility class." );
	}
}
