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

use MediaWiki\Skins\TGUI\GetConfigTrait;
use MediaWiki\Skins\TGUI\SkinTGUI;
use MediaWiki\Title\Title;
use OutputPage;

/**
 * The base class for all skin partials
 * TODO: Use SkinComponentRegistryContext
 */
abstract class Partial {

	use GetConfigTrait;

	/** @var SkinTGUI */
	protected $skin;

	/** @var OutputPage */
	protected $out;

	/** @var Title */
	protected $title;

	/** @var User */
	protected $user;

	/**
	 * Constructor
	 * @param SkinTGUI $skin
	 */
	public function __construct( SkinTGUI $skin ) {
		$this->skin = $skin;
		$this->out = $skin->getOutput();
		$this->title = $this->out->getTitle();
		$this->user = $this->out->getUser();
	}
}
