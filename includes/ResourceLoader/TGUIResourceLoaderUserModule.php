<?php

namespace MediaWiki\Skins\TGUI\ResourceLoader;

use MediaWiki\ResourceLoader as RL;
use MediaWiki\Skins\TGUI\Constants;

class TGUIResourceLoaderUserModule extends RL\UserModule {
	/**
	 * @inheritDoc
	 */
	protected function getPages( RL\Context $context ) {
		$skin = $context->getSkin();
		$config = $this->getConfig();
		$user = $context->getUserObj();
		$pages = [];
		if ( $config->get( 'AllowUserCss' ) && !$user->isAnon() && ( $skin === Constants::SKIN_NAME ) ) {
			$userPage = $user->getUserPage()->getPrefixedDBkey();
			$pages["$userPage/tgui.js"] = [ 'type' => 'script' ];
		}
		return $pages;
	}
}
