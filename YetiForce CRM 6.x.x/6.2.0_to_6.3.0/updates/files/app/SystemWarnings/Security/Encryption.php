<?php

/**
 * Encryption warnings file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\Security;

/**
 * Encryption warnings class.
 */
class Encryption extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_MECHANISM_ENCRYPTION';

	/** {@inheritdoc} */
	protected $priority = 9;

	/**
	 * Checks if encryption is active.
	 *
	 * @return void
	 */
	public function process(): void
	{
		if (\App\Encryption::getInstance()->isActive()) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
		if (0 === $this->status) {
			$this->link = 'index.php?module=Password&parent=Settings&view=Encryption';
			$this->linkTitle = \App\Language::translate('BTN_CONFIGURE_ENCRYPTION', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate('LBL_CONFIGURE_ENCRYPTION_DESCRIPTION', 'Settings:SystemWarnings');
		}
	}
}
