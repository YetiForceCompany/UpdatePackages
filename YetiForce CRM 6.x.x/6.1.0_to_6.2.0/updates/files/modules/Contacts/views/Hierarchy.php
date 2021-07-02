<?php

/**
 * Class to show hierarchy.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Contacts_Hierarchy_View extends \App\Controller\View\Page
{
	use App\Controller\ClearProcess;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$hierarchy = $recordModel->getHierarchy();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('HIERARCHY', $hierarchy);
		$viewer->view('Hierarchy.tpl', $moduleName);
	}
}
