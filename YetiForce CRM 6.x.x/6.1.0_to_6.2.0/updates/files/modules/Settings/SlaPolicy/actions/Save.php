<?php
/**
 * Settings SlaPolicy Save Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SlaPolicy_Save_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordId = null;
		if (!$request->isEmpty('record')) {
			$recordId = $request->getInteger('record');
		}
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		if (!empty($recordId)) {
			$recordModel = Settings_SlaPolicy_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_SlaPolicy_Record_Model::getCleanInstance();
		}
		$recordModel->set('name', $request->getByType('name', 'Text'));
		$recordModel->set('operational_hours', $request->getInteger('operational_hours'));
		$recordModel->set('tabid', \App\Module::getModuleId($request->getByType('source_module', 2)));
		$conditions = \App\Condition::getConditionsFromRequest($request->getArray('conditions', 'Text'));
		$recordModel->set('conditions', \App\Json::encode($conditions));
		$recordModel->set('reaction_time', $request->getByType('reaction_time', 'TimePeriod'));
		$recordModel->set('idle_time', $request->getByType('idle_time', 'TimePeriod'));
		$recordModel->set('resolve_time', $request->getByType('resolve_time', 'TimePeriod'));
		$recordModel->set('business_hours', $request->getByType('business_hours', 'Text'));
		$recordModel->save();
		header('location: ' . $moduleModel->getDefaultUrl());
	}
}
