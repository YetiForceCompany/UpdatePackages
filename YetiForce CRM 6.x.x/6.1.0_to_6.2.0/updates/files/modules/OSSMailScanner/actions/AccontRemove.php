<?php

/**
 * OSSMailScanner AccontRemove action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_AccontRemove_Action extends \App\Controller\Action
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
		$id = $request->getInteger('id');
		$recordModelOSSMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$recordModelOSSMailScanner->accontDelete($id);
		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'data' => \App\Language::translate('AccontDeleteOK', 'OSSMailScanner')]);
		$response->emit();
	}
}
