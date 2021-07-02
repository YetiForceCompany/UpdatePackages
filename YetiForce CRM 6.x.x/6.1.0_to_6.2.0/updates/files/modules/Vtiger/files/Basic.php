<?php
/**
 * Basic class to handle files.
 *
 * @package Files
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Basic class to handle files.
 */
abstract class Vtiger_Basic_File
{
	/**
	 * Storage name.
	 *
	 * @var string
	 */
	public $storageName = '';
	/**
	 * File type.
	 *
	 * @var string
	 */
	public $fileType = '';

	/**
	 * Checking permission in get method.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function getCheckPermission(App\Request $request)
	{
		if (!$request->isEmpty('record')) {
			$moduleName = $request->getModule();
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record')) || !\App\Field::getFieldPermission($moduleName, $request->getByType('field', 2))) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		} else {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		return true;
	}

	/**
	 * Checking permission in post method.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function postCheckPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$field = $request->getByType('field', 'Alnum');
		if (!$request->isEmpty('record', true)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			if (!$recordModel->isEditable() || !\App\Field::getFieldPermission($moduleName, $field, false)) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		} else {
			if (!\App\Field::getFieldPermission($moduleName, $field, false) || !\App\Privilege::isPermitted($moduleName, 'CreateView')) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
		return true;
	}

	/**
	 * Get and save files.
	 *
	 * @param \App\Request $request
	 */
	public function post(App\Request $request)
	{
		$attach = \App\Fields\File::uploadAndSave($request, $_FILES, $this->fileType, $this->storageName);
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setResult([
				'field' => $request->getByType('field', 'Alnum'),
				'module' => $request->getModule(),
				'attach' => $attach,
			]);
			$response->emit();
		}
	}
}
