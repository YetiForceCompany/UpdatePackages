<?php

namespace App\SystemWarnings\Security;

/**
 * Https system warnings class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServerHttps extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_SERVER_HTTPS';
	protected $priority = 7;

	/**
	 * Checking whether there is a https connection.
	 */
	public function process()
	{
		if ('WebUI' !== \App\Process::$requestMode) {
			$this->status = 1;

			return;
		}
		if (\App\RequestUtil::isHttps()) {
			$this->status = 1;
		} else {
			$this->status = 0;
		}
		if (0 === $this->status) {
			$this->link = 'https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirements';
			$this->linkTitle = \App\Language::translate('BTN_CONFIGURE_HTTPS', 'Settings:SystemWarnings');
			$this->description = \App\Language::translateArgs('LBL_MISSING_HTTPS', 'Settings:SystemWarnings', '<a target="_blank" rel="noreferrer noopener" href="https://yetiforce.com/en/knowledge-base/documentation/implementer-documentation/item/web-server-requirement"><u>' . \App\Language::translate('LBL_CONFIG_DOC_URL_LABEL', 'Settings:SystemWarnings') . '</u></a>');
		}
	}
}
