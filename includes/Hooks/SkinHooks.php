<?php

declare( strict_types=1 );

namespace MediaWiki\Skins\TGUI\Hooks;

use Config;
use Html;
use DateTime;
use IContextSource;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader as RL;
use MediaWiki\Skins\TGUI\GetConfigTrait;
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
class SkinHooks implements
	BeforePageDisplayHook,
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

		// FontAwesome
		$out->addLink([
            'rel' => 'stylesheet',
            'href' => '/skins/TGUI/resources/skins.tgui.styles/font-awesome/css/all.min.css'
        ]);

		// HeadScripts
		$scriptPaths = json_decode(file_get_contents(MW_INSTALL_PATH . '/skins/TGUI/resources/skins.tgui.scripts/scripts.json'), true);
		if (isset($scriptPaths['scripts']) && is_array($scriptPaths['scripts'])) {
			$allScripts = '';

			foreach ($scriptPaths['scripts'] as $scriptPath) {
				if ($scriptPath === '/skins/TGUI/resources/skins.tgui.scripts/inline.js' && !$this->getConfigValue('TGUIEnablePreferences', $out)) {
					continue;
				}

				$script = file_get_contents(MW_INSTALL_PATH . $scriptPath);
				$script = RL\ResourceLoader::filter('minify-js', $script);
				$allScripts .= $script;
			}

			$out->addHeadItem('skin.tgui.scripts', Html::inlineScript($allScripts));
		}

		// Holidays system
		if ($this->getConfigValue('TGUIEnableHolidays', $out)) {
			self::checkHolidays($out);
		}
	}

	public function checkHolidays($out) {
		$currentDate = new DateTime();
		$holidaysPath = MW_INSTALL_PATH . '/skins/TGUI/resources/skins.tgui.holidays/holidays.json';
		$holidays = json_decode(file_get_contents($holidaysPath), true);

		foreach ($holidays as $holiday) {
			$start = DateTime::createFromFormat('j-n', $holiday['start']['day'] . '-' . $holiday['start']['month']);
			$end = DateTime::createFromFormat('j-n', $holiday['end']['day'] . '-' . $holiday['end']['month']);

			if (($start <= $currentDate && $currentDate <= $end) || ($start->format('m') > $end->format('m') && ($currentDate >= $start || $currentDate <= $end))) {
				$cssPath = "/skins/TGUI/resources/skins.tgui.holidays/styles/{$holiday['name']}.css";
				$jsPath = "/skins/TGUI/resources/skins.tgui.holidays/scripts/{$holiday['name']}.js";

				if (file_exists(MW_INSTALL_PATH . $cssPath)) {
					$style = file_get_contents(MW_INSTALL_PATH . $cssPath);
					$style = Html::inlineStyle($style);
					$style = RL\ResourceLoader::filter('minify-css', $style);
					$out->addHeadItem('skin.tgui.holiday.' . $holiday['name'], $style);
				}

				if (file_exists(MW_INSTALL_PATH . $jsPath)) {
					$script = file_get_contents(MW_INSTALL_PATH . $jsPath);
					$script = Html::inlineScript($script);
					$script = RL\ResourceLoader::filter('minify-js', $script);
					$out->addHeadItem('skin.tgui.holiday.' . $holiday['name'], $script);
				}
			}
		}
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
	 * @since 1.36
	 * @param RL\Context $context
	 * @param mixed[] &$config Associative array of configurable options
	 * @return void This hook must not abort, it must return no value
	 */
	public function onSkinPageReadyConfig( $context, array &$config ): void {
		// It's better to exit before any additional check
		if ( $context->getSkin() !== 'tgui' ) {
			return;
		}

		// Tell the `mediawiki.page.ready` module not to wire up search.
		$config['search'] = false;
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
	 * Modify navigation links
	 *
	 * TODO: Update to a proper hook when T287622 is resolved
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateNavigation::Universal
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 */
	public static function onSkinTemplateNavigation( $sktemplate, &$links ) {
		// Be extra safe because it might be active on other skins with caching
		if ( $sktemplate->getSkinName() !== 'tgui' ) {
			return;
		}

		if ( isset( $links['actions'] ) ) {
			self::updateActionsMenu( $links );
		}

		if ( isset( $links['user-menu'] ) ) {
			self::updateUserMenu( $sktemplate, $links );
		}
	}

		/**
	 * Update actions menu items
	 *
	 * @internal used inside Hooks\SkinHooks::onSkinTemplateNavigation
	 * @param array &$links
	 */
	private static function updateActionsMenu( &$links ) {
		// Most icons are not mapped yet in the actions menu
		$iconMap = [
			'delete' => 'trash',
			'move' => 'move',
			'protect' => 'lock',
			'unprotect' => 'unLock',
			// Extension:Purge
			// Extension:SemanticMediaWiki
			'purge' => 'reload',
			// Extension:Cargo
			'cargo-purge'  => 'reload',
			// Extension:DiscussionTools
			'dt-page-subscribe' => 'bell'
		];

		self::mapIconsToMenuItems( $links, 'actions', $iconMap );
		self::addIconsToMenuItems( $links, 'actions' );
	}

	/**
	 * Update user menu
	 *
	 * @internal used inside Hooks\SkinHooks::onSkinTemplateNavigation
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 */
	private static function updateUserMenu( $sktemplate, &$links ) {
		$user = $sktemplate->getUser();
		$isRegistered = $user->isRegistered();
		$isTemp = $user->isTemp();

		if ( $isTemp ) {
			// Remove temporary user page text from user menu and recreate it in user info
			unset( $links['user-menu']['tmpuserpage'] );
			// Remove links as they are added to the bottom of user menu later
			// unset( $links['user-menu']['logout'] );
		} elseif ( $isRegistered ) {
			// Remove user page link from user menu and recreate it in user info
			unset( $links['user-menu']['userpage'] );
		} else {
			// Remove anon user page text from user menu and recreate it in user info
			unset( $links['user-menu']['anonuserpage'] );
		}

		self::addIconsToMenuItems( $links, 'user-menu' );
	}

	/**
	 * Set the icon parameter of the menu item based on the mapping
	 *
	 * @param array &$links
	 * @param string $menu identifier
	 * @param array $map icon mapping
	 */
	private static function mapIconsToMenuItems( &$links, $menu, $map ) {
		foreach ( $map as $key => $icon ) {
			if ( isset( $links[$menu][$key] ) ) {
				$links[$menu][$key]['icon'] ??= $icon;
			}
		}
	}

	/**
	 * Add the HTML needed for icons to menu items
	 *
	 * @param array &$links
	 * @param string $menu identifier
	 */
	private static function addIconsToMenuItems( &$links, $menu ) {
		// Loop through each menu to check/append its link classes.
		foreach ( $links[$menu] as $key => $item ) {
			$icon = $item['icon'] ?? '';

			if ( $icon ) {
				// Html::makeLink will pass this through rawElement
				// Avoid using mw-ui-icon in case its styles get loaded
				// Sometimes extension includes the "wikimedia-" part in the icon key (e.g. ULS),
				// so we apply both classes just to be safe
				$links[$menu][$key]['link-html'] = '<span class="tgui-icon tgui-icon-' . $icon .'"></span>';
			}
		}
	}
}
