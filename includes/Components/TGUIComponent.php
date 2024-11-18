<?php

declare( strict_types=1 );

namespace MediaWiki\Skins\TGUI\Components;

/**
 * Component interface for managing TGUI-modified components
 *
 * @internal
 */
interface TGUIComponent {
	/**
	 * @return array of Mustache compatible data
	 */
	public function getTemplateData(): array;
}
