<?php

namespace MediaWiki\Skins\TGUI;

/**
 * @ingroup Skins
 * @package TGUI
 * @internal
 */
class SkinTGUILegacy extends SkinTGUI {
	/**
	 * Whether or not the legacy version of the skin is being used.
	 *
	 * @return bool
	 */
	protected function isLegacy(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function isLanguagesInContentAt( $location ) {
		return false;
	}
}
