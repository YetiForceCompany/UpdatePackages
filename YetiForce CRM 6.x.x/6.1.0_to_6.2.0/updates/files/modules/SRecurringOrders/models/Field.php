<?php

/**
 * SRecurringOrders field model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class SRecurringOrders_Field_Model extends Vtiger_Field_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getModulesListValues()
	{
		if ('target_module' !== $this->getFieldName()) {
			return parent::getModulesListValues();
		}
		$moduleName = 'SSingleOrders';
		return [App\Module::getModuleId($moduleName) => ['name' => $moduleName, 'label' => \App\Language::translate($moduleName, $moduleName)]];
	}
}
