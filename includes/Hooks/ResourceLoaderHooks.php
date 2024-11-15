<?php
/**
 * Citizen - A responsive skin developed for the Star Citizen Wiki
 *
 * This file is part of Citizen. Ported/Copypasted for TGUI
 *
 * Citizen is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Citizen is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Citizen.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup Skins
 */

declare( strict_types=1 );

namespace MediaWiki\Skins\TGUI\Hooks;

use Config;
use ExtensionRegistry;
use MediaWiki\MainConfigNames;
use MediaWiki\ResourceLoader as RL;

/**
 * Hooks to run relating to the resource loader
 */
class ResourceLoaderHooks {

	/**
	 * Passes config variables to skins.tgui.scripts ResourceLoader module.
	 * @param RL\Context $context
	 * @param Config $config
	 * @return array
	 */
	public static function getTGUIResourceLoaderConfig(
		RL\Context $context,
		Config $config
	) {
		return [
			'wgTGUIEnablePreferences' => $config->get( 'TGUIEnablePreferences' ),
			'wgTGUITooltips' => $config->get( 'TGUITooltips' ),
			'wgTGUIReplaceTitleTooltips' => $config->get( 'TGUIReplaceTitleTooltips' ),
			'wgTGUIEnableHolidays' => $config->get( 'TGUIEnableHolidays' )
		];
	}

	/**
	 * Passes config variables to skins.tgui.preferences ResourceLoader module.
	 * @param RL\Context $context
	 * @param Config $config
	 * @return array
	 */
	public static function getTGUIPreferencesResourceLoaderConfig(
		RL\Context $context,
		Config $config
	) {
		return [
			'wgTGUIThemeDefault' => $config->get( 'TGUIThemeDefault' ),
		];
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
		$tguiSearchConfig = [
			'highlightQuery' =>
				TGUIServices::getLanguageService()->canWordsBeSplitSafely( $context->getLanguage() )
		];

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'TGUISearchResourceLoaderConfig',
			[ &$tguiSearchConfig ]
		);

		return array_merge( $config->get( 'TGUIWvuiSearchOptions' ), $tguiSearchConfig );
	}
}
