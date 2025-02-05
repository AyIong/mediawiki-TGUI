<?php

declare( strict_types=1 );

namespace MediaWiki\Skins\TGUI\Components;

/**
 * TGUIComponentPageSidebar component
 */
class TGUIComponentPageSidebar implements TGUIComponent {
	/** @var array */
	private $sidebarData;

	/**
	 * @param array $sidebarData
	 */
	public function __construct( array $sidebarData ) {
		$this->sidebarData = $sidebarData;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		$portletsRest = [];
		foreach ( $this->sidebarData[ 'array-portlets-rest' ] as $data ) {
			$portletsRest[] = ( new TGUIComponentMenu( $data ) )->getTemplateData();
		}
		$firstPortlet = new TGUIComponentMenu( $this->sidebarData['data-portlets-first'] );

		return [
			'data-portlets-first' => $firstPortlet->getTemplateData(),
			'array-portlets-rest' => $portletsRest
		];
	}
}
