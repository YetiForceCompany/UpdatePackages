<?php
/**
 * OSSMailScanner SaveCRMuser action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * OSSMailScanner SaveCRMuser action class.
 */
class OSSMailScanner_SaveCRMuser_Action extends \App\Controller\Action
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

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$userId = $request->getInteger('userid');
		$value = $request->getInteger('value');
		if ($userId) {
			\App\Db::getInstance()->createCommand()->update('roundcube_users', ['crm_user_id' => $value], ['user_id' => $userId])->execute();
			$success = true;
			$data = \App\Language::translate('JS_saveuser_info', $moduleName);
		} else {
			$success = false;
			$data = \App\Language::translate('LBL_NO_DATA', $moduleName);
		}
		$result = ['success' => $success, 'data' => $data];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
