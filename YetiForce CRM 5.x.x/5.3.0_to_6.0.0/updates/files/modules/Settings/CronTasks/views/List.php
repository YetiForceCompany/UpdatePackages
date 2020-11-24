<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_CronTasks_List_View extends Settings_Vtiger_List_View
{
	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($request->getModule(false));
		$listViewModel->set('orderby', 'sequence');

		$pagingModel = new Vtiger_Paging_Model();
		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}

		$module = $listViewModel->getModule();

		$viewer->assign('MODULE_MODEL', $module);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('LAST_CRON', $module->getLastCronIteration());
	}
}
