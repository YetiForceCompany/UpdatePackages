<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php');

class YetiForceUpdate
{

	var $package;
	var $modulenode;
	var $return = true;
	var $filesToDelete = [];

	function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	function preupdate()
	{
		//$this->package->_errorText = 'Errot';
		return true;
	}

	function update()
	{
		$this->databaseData();
	}

	function postupdate()
	{
		return true;
	}

	function databaseData()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::databaseData() method ...");

		$result = $adb->pquery("SELECT * FROM `vtiger_blocks` WHERE `blocklabel` = ? AND `tabid` = ?;", ['Oryginalna wiadomość', getTabid('OSSMailView')]);
		if ($adb->num_rows($result)) {
			$adb->pquery("UPDATE `vtiger_blocks` SET `blocklabel` = ? WHERE `blocklabel` = ? AND `tabid` = ?;", ['LBL_ORIGN_MESSAGE', 'Oryginalna wiadomość', getTabid('OSSMailView')]);
		}
		$result = $adb->query("SELECT * FROM `vtiger_field` WHERE `fieldlabel` = 'Treść' AND `columnname` = 'orginal_mail';");
		if ($adb->num_rows($result)) {
			$adb->pquery("UPDATE `vtiger_field` SET `fieldlabel` = ? WHERE `fieldlabel` = ? AND `columnname` = ?;", ['Content', 'Treść', 'orginal_mail']);
		}

		$log->debug("Exiting YetiForceUpdate::databaseData() method ...");
	}
}
