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
	var $filesToDelete = [

	];

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
		$this->changeActivity();
	}

	function postupdate()
	{
		return true;
	}
	
	public function changeActivity()
	{
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::changeActivity() method ...");
		$result = $adb->query("UPDATE `vtiger_activity_reminder_popup` SET `status` = '1' WHERE recordid IN (SELECT activityid FROM `vtiger_activity` WHERE `status` IN ('PLL_CANCELLED','PLL_COMPLETED','PLL_OVERDUE'))");
		$result = $adb->query("UPDATE `vtiger_activity_reminder_popup` SET `status` = '0' WHERE recordid IN (SELECT activityid FROM `vtiger_activity` WHERE `status` IN ('PLL_IN_REALIZATION','PLL_POSTPONED','PLL_PLANNED'))");
		$log->debug("Exiting YetiForceUpdate::changeActivity() method ...");
	}

}
