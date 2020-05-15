<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o
 * *********************************************************************************** */

class Vtiger_BasicAjax_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getByType('search_module', 'Alnum')) || !$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param App\Request $request
	 */
	public function process(App\Request $request)
	{
		$searchModuleModel = \Vtiger_Module_Model::getInstance($request->getByType('search_module', 'Alnum'));
		$records = $searchModuleModel->searchRecord($request->getByType('search_value', 'Text'), $request->getModule());
		$result = [];
		if (\is_array($records)) {
			foreach ($records as $recordModels) {
				foreach ($recordModels as $recordModel) {
					$result[] = [
						'label' => App\Purifier::decodeHtml($recordModel->getSearchName()),
						'value' => App\Purifier::decodeHtml($recordModel->getName()),
						'id' => $recordModel->getId(),
					];
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
