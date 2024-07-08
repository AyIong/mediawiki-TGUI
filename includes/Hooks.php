<?php

namespace MediaWiki\Skins\TGUI;

use Config;
use Html;
use IContextSource;
use MediaWiki\Hook\MakeGlobalVariablesScriptHook;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader as RL;
use MediaWiki\Skins\TGUI\GetConfigTrait;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderSiteModulePagesHook;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderSiteStylesModulePagesHook;
use MediaWiki\Skins\Hook\SkinPageReadyConfigHook;
use OutputPage;
use RuntimeException;
use Skin;
use SkinTemplate;
use Title;
use User;

/**
 * Presentation hook handlers for TGUI skin.
 *
 * Hook handler method names should be in the form of:
 *	on<HookName>()
 * @package TGUI
 * @internal
 */
class Hooks implements
	BeforePageDisplayHook,
	MakeGlobalVariablesScriptHook,
	ResourceLoaderSiteModulePagesHook,
	ResourceLoaderSiteStylesModulePagesHook,
	SkinPageReadyConfigHook
{
	use GetConfigTrait;

	/**
	 * Adds inline scripts to the page
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( $skin->getSkinName() !== 'tgui' ) {
			return;
		}

		$out->addHeadItem('floating-ui-core', '<script src="https://cdn.jsdelivr.net/npm/@floating-ui/core@1.6.4"></script>');
		$out->addHeadItem('floating-ui-dom', '<script src="https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.7"></script>');

		$scriptPaths = json_decode(file_get_contents(MW_INSTALL_PATH . '/skins/TGUI/resources/skins.tgui.scripts/scripts.json'), true);

		if (isset($scriptPaths['scripts']) && is_array($scriptPaths['scripts'])) {
			foreach ($scriptPaths['scripts'] as $scriptPath) {
				if ($scriptPath === '/skins/TGUI/resources/skins.tgui.scripts/inline.js' && !$this->getConfigValue('TGUIEnablePreferences', $out)) {
					continue;
				}

				$script = file_get_contents(MW_INSTALL_PATH . $scriptPath);
				$script = Html::inlineScript($script);
				$script = RL\ResourceLoader::filter('minify-js', $script);
				$out->addHeadItem('skin.tgui.' . basename($scriptPath, '.js'), $script);
			}
		}
	}

	/**
	 * Checks if the current skin is a variant of TGUI
	 *
	 * @param string $skinName
	 * @return bool
	 */
	private static function isTGUISkin( string $skinName ): bool {
		return ($skinName === Constants::SKIN_NAME);
	}

		/**
	 * Generates config variables for skins.tgui.search Resource Loader module (defined in
	 * skin.json).
	 *
	 * @param RL\Context $context
	 * @param Config $config
	 * @return array<string,mixed>
	 */
	public static function getTGUISearchResourceLoaderConfig(
		RL\Context $context,
		Config $config
	): array {
		$result = $config->get( 'TGUIWvuiSearchOptions' );

		return $result;
	}

	/**
	 * SkinPageReadyConfig hook handler
	 *
	 * Replace searchModule provided by skin.
	 *
	 * @since 1.35
	 * @param RL\Context $context
	 * @param mixed[] &$config Associative array of configurable options
	 * @return void This hook must not abort, it must return no value
	 */
	public function onSkinPageReadyConfig(
		RL\Context $context,
		array &$config
	): void {
		// It's better to exit before any additional check
		if ( !self::isTGUISkin( $context->getSkin() ) ) {
			return;
		}

		// Tell the `mediawiki.page.ready` module not to wire up search.
		// This allows us to use the new Vue implementation.
		// Context has no knowledge of legacy / modern TGUI
		// and from its point of view they are the same thing.
		// Please see the modules `skins.tgui.js` and `skins.tgui.legacy.js`
		// for the wire up of search.
		// The related method self::getTGUIResourceLoaderConfig handles which
		// search to load.
		$config['search'] = false;
	}

	/**
	 * Transforms watch item inside the action navigation menu
	 *
	 * @param array &$content_navigation
	 */
	private static function updateActionsMenu( &$content_navigation ) {
		$key = null;
		if ( isset( $content_navigation['actions']['watch'] ) ) {
			$key = 'watch';
		}
		if ( isset( $content_navigation['actions']['unwatch'] ) ) {
			$key = 'unwatch';
		}

		// Promote watch link from actions to views and add an icon
		if ( $key !== null ) {
			self::appendClassToItem(
				$content_navigation['actions'][$key]['class'],
				[ 'icon' ]
			);
			$content_navigation['views'][$key] = $content_navigation['actions'][$key];
			unset( $content_navigation['actions'][$key] );
		}
	}

	/**
	 * Adds class to a property
	 *
	 * @param array &$item to update
	 * @param array|string $classes to add to the item
	 */
	private static function appendClassToItem( &$item, $classes ) {
		$existingClasses = $item;

		if ( is_array( $existingClasses ) ) {
			// Treat as array
			$newArrayClasses = is_array( $classes ) ? $classes : [ trim( $classes ) ];
			$item = array_merge( $existingClasses, $newArrayClasses );
		} elseif ( is_string( $existingClasses ) ) {
			// Treat as string
			$newStrClasses = is_string( $classes ) ? trim( $classes ) : implode( ' ', $classes );
			$item .= ' ' . $newStrClasses;
		} else {
			// Treat as whatever $classes is
			$item = $classes;
		}

		if ( is_string( $item ) ) {
			$item = trim( $item );
		}
	}

	/**
	 * Updates personal navigation menu (user links) dropdown for modern TGUI:
	 *  - Adds icons
	 *  - Makes user page and watchlist collapsible
	 *
	 * @param SkinTemplate $sk
	 * @param array &$content_navigation
	 */
	private static function updateUserLinksDropdownItems( $sk, &$content_navigation ) {
		// For logged-in users in modern TGUI, rearrange some links in the personal toolbar.
		$user = $sk->getUser();
		$isTemp = $user->isTemp();
		$isRegistered = $user->isRegistered();
		if ( $isTemp ) {
			if ( isset( $content_navigation['user-page']['tmpuserpage'] ) ) {
				$content_navigation['user-page']['tmpuserpage']['collapsible'] = true;
				$content_navigation['user-page']['tmpuserpage'] =
					self::updateMenuItemData( $content_navigation['user-page']['tmpuserpage'] );
			}
			if ( isset( $content_navigation['user-menu']['tmpuserpage'] ) ) {
				$content_navigation['user-menu']['tmpuserpage']['collapsible'] = true;
				$content_navigation['user-menu']['tmpuserpage'] =
					self::updateMenuItemData( $content_navigation['user-menu']['tmpuserpage'] );
			}
		} elseif ( $isRegistered ) {
			// Remove user page from personal menu dropdown for logged in use
			$content_navigation['user-menu']['userpage']['collapsible'] = true;
			// watchlist may be disabled if $wgGroupPermissions['*']['viewmywatchlist'] = false;
			// See [[phab:T299671]]
			if ( isset( $content_navigation['user-menu']['watchlist'] ) ) {
				$content_navigation['user-menu']['watchlist']['collapsible'] = true;
			}
			// Remove logout link from user-menu and recreate it in SkinTGUI,
			unset( $content_navigation['user-menu']['logout'] );
		}

		if ( $isRegistered ) {
			// Prefix user link items with associated icon.
			// Don't show icons for anon menu items (besides login and create account).
			// Loop through each menu to check/append its link classes.
			self::updateMenuItems( $content_navigation, 'user-menu' );
		} else {
			// Remove "Not logged in" from personal menu dropdown for anon users.
			unset( $content_navigation['user-menu']['anonuserpage'] );
		}

		if ( !$isRegistered || $isTemp ) {
			// "Create account" link is handled manually by TGUI
			unset( $content_navigation['user-menu']['createaccount'] );
			// "Login" link is handled manually by TGUI
			unset( $content_navigation['user-menu']['login'] );
			// Remove duplicate "Login" link added by SkinTemplate::buildPersonalUrls if group read permissions
			// are set to false.
			unset( $content_navigation['user-menu']['login-private'] );
		}
	}

	/**
	 * Populates 'tgui-user-menu-overflow' bucket for modern TGUI with modified personal navigation (user links)
	 * menu items, including 'notification', 'user-interface-preferences', 'user-page', 'tgui-user-menu-overflow'
	 *
	 * @param SkinTemplate $sk
	 * @param array &$content_navigation
	 */
	private static function updateUserLinksOverflowItems( $sk, &$content_navigation ) {
		$overflow = 'tgui-user-menu-overflow';
		$content_navigation[$overflow] = [];

		// Logged in and logged out overflow items
		if ( isset( $content_navigation['user-interface-preferences']['uls'] ) ) {
			$content_navigation[$overflow]['uls'] = array_merge(
				$content_navigation['user-interface-preferences']['uls'], [
				'collapsible' => true,
			] );
		}

		// Logged in overflow items
		if ( isset( $content_navigation['user-page']['userpage'] ) ) {
			$content_navigation[$overflow]['userpage'] = array_merge(
				$content_navigation['user-page']['userpage'], [
				// T312157: Style the userpage link as a blue link rather than a quiet button.
				'button' => false,
				'collapsible' => true,
				// Remove icon
				'icon' => '',
			] );
		}
		if ( isset( $content_navigation['notifications'] ) ) {
			foreach ( $content_navigation['notifications'] as $key => $data ) {
				$content_navigation[$overflow][$key] = $data;
			}
		}
		if ( isset( $content_navigation['user-menu']['watchlist'] ) ) {
			$content_navigation[$overflow]['watchlist'] = array_merge(
				$content_navigation['user-menu']['watchlist'], [
				'id' => 'pt-watchlist-2',
				'button' => true,
				'collapsible' => true,
				'text-hidden' => true,
			] );
		}

		// Anon/temp overflow items
		$user = $sk->getUser();
		$isTemp = $user->isTemp();
		$isRegistered = $user->isRegistered();
		$isCreateAccountAllowed = ( !$isRegistered || $isTemp );
		if ( isset( $content_navigation['user-menu']['createaccount'] ) && $isCreateAccountAllowed ) {
			$content_navigation[$overflow]['createaccount'] = array_merge(
				$content_navigation['user-menu']['createaccount'], [
				'id' => 'pt-createaccount-2',
				// T312157: Style the userpage link as a blue link rather than a quiet button.
				'button' => false,
				'collapsible' => true,
				// Remove icon
				'icon' => '',
			] );
		}

		self::updateMenuItems( $content_navigation, $overflow );
	}

	/**
	 * Updates personal navigation menu (user links) for modern TGUI wherein user page, create account and login links
	 * are removed from the dropdown to be handled separately. In legacy TGUI, the custom "user-page" bucket is
	 * removed to preserve existing behavior.
	 *
	 * @param SkinTemplate $sk
	 * @param array &$content_navigation
	 */
	private static function updateUserLinksItems( $sk, &$content_navigation ) {
		$skinName = $sk->getSkinName();
		self::updateUserLinksOverflowItems( $sk, $content_navigation );
		self::updateUserLinksDropdownItems( $sk, $content_navigation );
	}

	/**
	 * Modifies list item to make it collapsible.
	 *
	 * @param array &$item
	 * @param string $prefix defaults to user-links-
	 */
	private static function makeMenuItemCollapsible( array &$item, string $prefix = 'user-links-' ) {
		$COLLAPSE_MENU_ITEM_CLASS = $prefix . 'collapsible-item';
		self::appendClassToItem( $item[ 'class' ], $COLLAPSE_MENU_ITEM_CLASS );
	}

	/**
	 * Make an icon
	 *
	 * @internal for use inside TGUI skin.
	 * @param string $name
	 * @return string of HTML
	 */
	public static function makeIcon( $name ) {
		// Html::makeLink will pass this through rawElement
		return '<span class="mw-ui-icon mw-ui-icon-' . $name . ' mw-ui-icon-wikimedia-' . $name . '"></span>';
	}

	/**
	 * Update template data to include classes and html that handle buttons, icons, and collapsible items.
	 *
	 * @internal for use inside TGUI skin.
	 * @param array $item data to update
	 * @param string $buttonClassProp property to append button classes
	 * @param string $iconHtmlProp property to set icon HTML
	 * @return array $item Updated data
	 */
	private static function updateItemData( $item, $buttonClassProp, $iconHtmlProp ) {
		$hasButton = $item['button'] ?? false;
		$hideText = $item['text-hidden'] ?? false;
		$isCollapsible = $item['collapsible'] ?? false;
		$icon = $item['icon'] ?? '';
		unset( $item['button'] );
		unset( $item['icon'] );
		unset( $item['text-hidden'] );
		unset( $item['collapsible'] );

		if ( $isCollapsible ) {
			self::makeMenuItemCollapsible( $item );
		}
		if ( $hasButton ) {
			self::appendClassToItem( $item[ $buttonClassProp ], [ 'mw-ui-button', 'mw-ui-quiet' ] );
		}
		if ( $icon ) {
			if ( $hideText ) {
				$iconElementClasses = [ 'mw-ui-icon', 'mw-ui-icon-element',
					// Some extensions declare icons without the wikimedia- prefix. e.g. Echo
					'mw-ui-icon-' . $icon,
					// FIXME: Some icon names are prefixed with `wikimedia-`.
					// We should seek to remove all these instances.
					'mw-ui-icon-wikimedia-' . $icon
				];
				self::appendClassToItem( $item[ $buttonClassProp ], $iconElementClasses );
			} else {
				$item[ $iconHtmlProp ] = self::makeIcon( $icon );
			}
		}
		return $item;
	}

	/**
	 * Updates template data for TGUI dropdown menus.
	 *
	 * @param array $item Menu data to update
	 * @return array $item Updated menu data
	 */
	public static function updateDropdownMenuData( $item ) {
		$buttonClassProp = 'heading-class';
		$iconHtmlProp = 'html-tgui-heading-icon';
		return self::updateItemData( $item, $buttonClassProp, $iconHtmlProp );
	}

	/**
	 * Updates template data for TGUI link items.
	 *
	 * @param array $item link data to update
	 * @return array $item Updated link data
	 */
	public static function updateLinkData( $item ) {
		$buttonClassProp = 'class';
		$iconHtmlProp = 'link-html';
		return self::updateItemData( $item, $buttonClassProp, $iconHtmlProp );
	}

	/**
	 * Updates template data for TGUI menu items.
	 *
	 * @param array $item menu item data to update
	 * @return array $item Updated menu item data
	 */
	public static function updateMenuItemData( $item ) {
		$buttonClassProp = 'link-class';
		$iconHtmlProp = 'link-html';
		return self::updateItemData( $item, $buttonClassProp, $iconHtmlProp );
	}

	/**
	 * Updates user interface preferences for modern TGUI to upgrade icon/button menu items.
	 *
	 * @param array &$content_navigation
	 * @param string $menu identifier
	 */
	private static function updateMenuItems( &$content_navigation, $menu ) {
		foreach ( $content_navigation[$menu] as $key => $item ) {
			$content_navigation[$menu][$key] = self::updateMenuItemData( $item );
		}
	}

	/**
	 * TGUI only:
	 * Creates an additional menu that will be injected inside the more (cactions)
	 * dropdown menu. This menu is a clone of `views` and this menu will only be
	 * shown at low resolutions (when the `views` menu is hidden).
	 *
	 * An additional menu is used instead of adding to the existing cactions menu
	 * so that the emptyPortlet logic for that menu is preserved and the cactions menu
	 * is not shown at large resolutions when empty (e.g. all items including collapsed
	 * items are hidden).
	 *
	 * @param array &$content_navigation
	 */
	private static function createMoreOverflowMenu( &$content_navigation ) {
		$clonedViews = [];
		foreach ( array_keys( $content_navigation['views'] ?? [] ) as $key ) {
			$newItem = $content_navigation['views'][$key];
			self::makeMenuItemCollapsible(
				$newItem,
				'tgui-more-'
			);
			$clonedViews['more-' . $key] = $newItem;
		}
		// Inject collapsible menu items ahead of existing actions.
		$content_navigation['views-overflow'] = $clonedViews;
	}

	/**
	 * Upgrades TGUI's watch action to a watchstar.
	 * This is invoked inside SkinTGUI, not via skin registration, as skin hooks
	 * are not guaranteed to run last.
	 * This can possibly be revised based on the outcome of T287622.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateNavigation
	 * @param SkinTemplate $sk
	 * @param array &$content_navigation
	 */
	public static function onSkinTemplateNavigation( $sk, &$content_navigation ) {
		$title = $sk->getRelevantTitle();
		if (
			$sk->getConfig()->get( 'TGUIUseIconWatch' ) &&
			$title && $title->canExist()
		) {
			self::updateActionsMenu( $content_navigation );
		}

		self::updateUserLinksItems( $sk, $content_navigation );
		self::createMoreOverflowMenu( $content_navigation );
	}

	/**
	 * Adds MediaWiki:TGUI.css as the skin style that controls classic TGUI.
	 *
	 * @param string $skin
	 * @param array &$pages
	 */
	public function onResourceLoaderSiteStylesModulePages( $skin, &$pages ): void {
		if ( $skin === Constants::SKIN_NAME ) {
			$pages['MediaWiki:TGUI.css'] = [ 'type' => 'style' ];
		}
	}

	/**
	 * Adds MediaWiki:TGUI.css as the skin style that controls classic TGUI.
	 *
	 * @param string $skin
	 * @param array &$pages
	 */
	public function onResourceLoaderSiteModulePages( $skin, &$pages ): void {
		if ( $skin === Constants::SKIN_NAME ) {
			$pages['MediaWiki:TGUI.js'] = [ 'type' => 'script' ];
		}
	}

	/**
	 * NOTE: Please use ResourceLoaderGetConfigVars hook instead if possible
	 * for adding config to the page.
	 * Adds config variables to JS that depend on current page/request.
	 *
	 * Adds a config flag that can disable saving the TGUISidebarVisible
	 * user preference when the sidebar menu icon is clicked.
	 *
	 * @param array &$vars Array of variables to be added into the output.
	 * @param OutputPage $out OutputPage instance calling the hook
	 */
	public function onMakeGlobalVariablesScript( &$vars, $out ): void {
		$skin = $out->getSkin();
		$skinName = $skin->getSkinName();
		if ( !self::isTGUISkin( $skinName ) ) {
			return;
		}
		$config = $out->getConfig();
		$user = $out->getUser();
	}
}
