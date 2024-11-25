<?php

declare( strict_types=1 );

namespace MediaWiki\Skins\TGUI\Components;

use Config;
use Exception;
use MediaWiki\MediaWikiServices;
use Title;
use MediaWiki\User\UserIdentity;
use MessageLocalizer;

/**
 * TGUIComponentPageTools component
 */
class TGUIComponentPageTools implements TGUIComponent {
	/** @var Config */
	private $config;

	/** @var MessageLocalizer */
	private $localizer;

	/** @var Title */
	private $title;

	/** @var UserIdentity */
	private $user;

	/**
	 * @param Config $config
	 * @param MessageLocalizer $localizer
	 * @param Title $title
	 * @param UserIdentity $user
	 */
	public function __construct(
		Config $config,
		MessageLocalizer $localizer,
		Title $title,
		UserIdentity $user,
	) {
		$this->config = $config;
		$this->localizer = $localizer;
		$this->title = $title;
		$this->user = $user;
	}

	/**
	 * Check if views and actions should show
	 *
	 * Possible visibility conditions:
	 * * true: always visible (bool)
	 * * false: never visible (bool)
	 * * 'login': only visible if logged in (string)
	 * * 'permission-*': only visible if user has permission
	 *   e.g. permission-edit = only visible if user can edit pages
	 *
	 * @return bool
	 */
	private function shouldShowPageTools(): bool {
		$condition = $this->config->get( 'TGUIShowPageTools' );
		$user = $this->user;

		// Login-based condition, return true if condition is met
		if ( $condition === 'login' ) {
			$condition = $user->isRegistered();
		}

		// Permission-based condition, return true if condition is met
		if ( is_string( $condition ) && strpos( $condition, 'permission' ) === 0 ) {
			$permission = substr( $condition, 11 );
			try {
				$title = $this->title;
				$condition = MediaWikiServices::getInstance()->getPermissionManager()->userCan(
					$permission, $user, $title );
			} catch ( Exception $e ) {
				$condition = false;
			}
		}

		return (bool)$condition;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData(): array {
		return [
			'is-visible' => $this->shouldShowPageTools(),
		];
	}
}
