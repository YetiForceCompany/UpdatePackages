<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Services_Detail_View extends Products_Detail_View
{
	/**
	 * * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$modulePopUpFile = 'modules.' . $moduleName . '.resources.Edit';
		$moduleRelatedListFile = 'modules.' . $moduleName . '.resources.RelatedList';
		unset($headerScriptInstances[$modulePopUpFile], $headerScriptInstances[$moduleRelatedListFile]);
		return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts([
			'modules.Products.resources.Edit',
			'modules.Products.resources.RelatedList',
			$modulePopUpFile,
			$moduleRelatedListFile
		]));
	}
}
