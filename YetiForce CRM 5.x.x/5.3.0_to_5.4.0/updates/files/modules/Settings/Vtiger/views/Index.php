<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_Vtiger_Index_View extends \App\Controller\View\Page
{
	use \App\Controller\ExposeMethod;

	/**
	 * Page title.
	 *
	 * @var type
	 */
	protected $pageTitle = 'LBL_SYSTEM_SETTINGS';

	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::addBasic('view');
		parent::__construct();
	}

	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$this->preProcessSettings($request);
	}

	public function postProcess(App\Request $request, $display = true)
	{
		$this->postProcessSettings($request);
		parent::postProcess($request);
	}

	/**
	 * Pre process settings.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessSettings(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$view = $request->getByType('view', \App\Purifier::STANDARD, '');
		$qualifiedModuleName = $request->getModule(false);
		$selected = null;
		$viewer->assign('MENUS', Settings_Vtiger_Menu_Model::getMenu($moduleName, $view, $request->getMode(), $selected));
		$viewer->assign('SELECTED_PAGE', $selected);
		$viewer->view('SettingsMenuStart.tpl', $qualifiedModuleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$warnings = \App\SystemWarnings::getWarnings('all');
		$viewer->assign('WARNINGS', !App\Session::has('SystemWarnings') ? $warnings : []);
		$viewer->assign('SYSTEM_MONITORING', [
			'WARNINGS_COUNT' => [
				'LABEL' => 'PLU_SYSTEM_WARNINGS',
				'VALUE' => \count($warnings),
				'HREF' => 'index.php?module=Logs&parent=Settings&view=SystemWarnings',
				'ICON' => 'yfi yfi-system-warnings-2'
			],
			'SECURITY_COUNT' => [
				'LABEL' => 'PLU_SECURITY',
				'VALUE' => $this->getSecurityCount(),
				'HREF' => 'index.php?module=Log&parent=Settings&view=Index',
				'ICON' => 'yfi yfi-security-errors-2'
			],
			'USERS_COUNT' => [
				'LABEL' => 'PLU_USERS',
				'VALUE' => Users_Record_Model::getCount(true),
				'HREF' => 'index.php?module=Users&parent=Settings&view=List',
				'ICON' => 'yfi yfi-users-2'
			],
			'ACTIVE_MODULES' => [
				'LABEL' => 'PLU_MODULES',
				'VALUE' => Settings_ModuleManager_Module_Model::getModulesCount(true),
				'HREF' => 'index.php?module=ModuleManager&parent=Settings&view=List',
				'ICON' => 'yfi yfi-modules-2'
			],
			'ALL_WORKFLOWS' => [
				'LABEL' => 'PLU_WORKFLOWS_ACTIVE',
				'VALUE' => Settings_Workflows_Record_Model::getAllAmountWorkflowsAmount(),
				'HREF' => 'index.php?module=Workflows&parent=Settings&view=List',
				'ICON' => 'yfi yfi-workflows-2'
			],
		]);
		$viewer->assign('SETTINGS_SHORTCUTS', Settings_Vtiger_MenuItem_Model::getPinnedItems());
		$viewer->view('Index.tpl', $request->getModule(false));
	}

	/**
	 * Post process settings.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function postProcessSettings(App\Request $request)
	{
		$this->getViewer($request)->view('SettingsMenuEnd.tpl', $request->getModule(false));
	}

	/**
	 * Get security alerts count.
	 *
	 * @return int
	 */
	protected function getSecurityCount()
	{
		$count = App\Log::getLogs('access_for_admin', 'oneDay', true);
		$count += App\Log::getLogs('access_to_record', 'oneDay', true);
		$count += App\Log::getLogs('access_for_api', 'oneDay', true);
		return $count + App\Log::getLogs('access_for_user', 'oneDay', true);
	}

	protected function getMenu()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$type = \App\Process::$processName;
		return array_merge(
			parent::getFooterScripts($request),
			$this->checkAndConvertJsScripts([
				'modules.Vtiger.resources.Vtiger',
				'~vendor/ckeditor/ckeditor/ckeditor.js',
				'~vendor/ckeditor/ckeditor/adapters/jquery.js',
				'~libraries/jstree/dist/jstree.js',
				'~libraries/datatables.net/js/jquery.dataTables.js',
				'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
				'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
				'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js',
				'modules.Settings.Vtiger.resources.Vtiger',
				'modules.Settings.Vtiger.resources.Edit',
				'modules.Settings.Vtiger.resources.Index',
				'modules.Vtiger.resources.List',
				'modules.Settings.Vtiger.resources.List',
				'modules.Settings.YetiForce.resources.Shop',
				"modules.Settings.$moduleName.resources.Index",
				"modules.Settings.$moduleName.resources.$type",
				"modules.Settings.$moduleName.resources.$moduleName"
			])
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
			'~libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css'
		]), parent::getHeaderCss($request));
	}

	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}
}
