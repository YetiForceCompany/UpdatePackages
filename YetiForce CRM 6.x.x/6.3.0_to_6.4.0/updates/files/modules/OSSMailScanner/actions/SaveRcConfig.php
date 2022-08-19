<?php

/**
 * OSSMailScanner SaveRcConfig action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_SaveRcConfig_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(App\Request $request)
	{
		$conf_type = $request->get('ct');
		$type = $request->get('type');
		$vale = $request->get('vale');
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$result = ['success' => true, 'data' => $recordModel->setConfigWidget($conf_type, $type, $vale)];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
