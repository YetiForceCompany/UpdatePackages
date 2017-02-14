<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @license https://yetiforce.com/en/implementer/license.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php');
include_once('modules/ModTracker/ModTracker.php');

class YetiForceUpdate
{

	public $package;
	public $modulenode;
	public $return = true;
	private $cron = [];
	public $filesToDelete = [
		
	];

	public function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	public function preupdate()
	{
		return true;
	}

	public function update()
	{
		
	}

	public function postupdate()
	{
		return true;
	}
}
