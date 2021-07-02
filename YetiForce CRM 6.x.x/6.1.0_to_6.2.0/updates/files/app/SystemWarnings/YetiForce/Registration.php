<?php

/**
 * Registration system warning class file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace App\SystemWarnings\YetiForce;

/**
 * Registration warning class.
 */
class Registration extends \App\SystemWarnings\Template
{
	/**
	 * @var string Warning title
	 */
	protected $title = 'LBL_REGISTRATION';
	/**
	 * @var int Warning priority
	 */
	protected $priority = 8;
	/**
	 * @var bool Template flag
	 */
	protected $tpl = true;

	/**
	 * Checking if registration is correct and display modal with info if not.
	 */
	public function process()
	{
		if (\App\YetiForce\Register::verify(true) || 'demo' === \App\Config::main('systemMode')) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
	}
}
