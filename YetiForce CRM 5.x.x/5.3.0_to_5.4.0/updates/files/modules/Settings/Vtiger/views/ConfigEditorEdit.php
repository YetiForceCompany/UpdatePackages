<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_ConfigEditorEdit_View extends Settings_Vtiger_Index_View
{
	/**
	 * Page title.
	 *
	 * @var type
	 */
	protected $pageTitle = 'LBL_CONFIG_EDITOR';

	public function process(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_ConfigModule_Model::getInstance();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODEL', $moduleModel);
		$viewer->view('ConfigEditorEdit.tpl', $qualifiedName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.Settings.{$request->getModule()}.resources.ConfigEditor",
		]));
	}
}
