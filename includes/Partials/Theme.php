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

namespace MediaWiki\Skins\TGUI\Partials;

const CLIENTPREFS_THEME_MAP = [
	'auto' => 'os',
	'light' => 'day',
	'dark' => 'night'
];

/**
 * Theme switcher partial of Skin TGUI
 */
final class Theme extends Partial {

	/**
	 * Sets the corresponding theme and theme style classes on the <html> element
	 * If the theme is set to auto, the theme switcher script will be added
	 *
	 * @param array &$options
	 */
	public function setSkinTheme( array &$options ) {
		$out = $this->out;

		// Set theme to site theme
		$theme = $this->getConfigValue( 'TGUIThemeDefault' ) ?? 'auto';

		// Add HTML class based on theme set
		if ( CLIENTPREFS_THEME_MAP[ $theme ] ) {
			$out->addHtmlClasses( 'skin-theme-clientpref-' . CLIENTPREFS_THEME_MAP[ $theme ] );
		}

		// Set theme style
		$themeStyle = $this->getConfigValue( 'TGUIThemeStyleDefault' ) ?? 'default';

		// Add HTML class based on setted theme style
		$out->addHtmlClasses( 'tgui-feature-theme-style-clientpref-' . $themeStyle );
	}
}
