<?php
/**
 * Settings fields dependency save action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings fields dependency save action class.
 */
class Settings_FieldsDependency_Save_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordId = null;
		if (!$request->isEmpty('record')) {
			$recordId = $request->getInteger('record');
		}
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		if (!empty($recordId)) {
			$recordModel = Settings_FieldsDependency_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_FieldsDependency_Record_Model::getCleanInstance();
		}
		\App\Cache::delete('FieldsDependency', $recordModel->get('tabid'));
		$recordModel->set('name', $request->getByType('name', 'Text'));
		$recordModel->set('tabid', $request->getInteger('tabid'));
		$recordModel->set('status', $request->getBoolean('status'));
		$recordModel->set('mandatory', $request->getBoolean('mandatory'));
		$recordModel->set('gui', $request->getBoolean('gui'));
		$recordModel->set('conditions', \App\Condition::getConditionsFromRequest($request->getArray('conditions', 'Text')));
		$recordModel->set('views', $request->getArray('views', 'Standard'));
		$recordModel->set('fields', $request->getArray('fields', 'AlnumExtended'));
		$recordModel->save();
		header('location: ' . $moduleModel->getDefaultUrl());
	}
}
