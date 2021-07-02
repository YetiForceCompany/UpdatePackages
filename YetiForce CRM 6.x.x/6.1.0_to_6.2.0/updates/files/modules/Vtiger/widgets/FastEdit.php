<?php

/**
 * Vtiger FastEdit widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_FastEdit_Widget extends Vtiger_Basic_Widget
{
	public function getConfigTplName()
	{
		return 'FastEditConfig';
	}

	public function getWidget()
	{
		$this->Config['tpl'] = 'FastEdit.tpl';

		return $this->Config;
	}
}
