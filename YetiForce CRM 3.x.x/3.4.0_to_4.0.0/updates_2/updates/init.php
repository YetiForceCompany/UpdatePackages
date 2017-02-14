<?php

/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @license https://yetiforce.com/en/implementer/license.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class YetiForceUpdate
{

	public $package;
	public $modulenode;
	public $return = true;
	public $userEntity = [];
	private $cronAction = [];
	public $filesToDelete = [];

	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	public function preupdate()
	{
		$modulenode = $this->modulenode;
		$result = copy('cache/updates/files/vtlib/Vtiger/Utils.php', 'vtlib/Vtiger/Utils.php');
		$result2 = copy('cache/updates/files/vtlib/Vtiger/PackageImport.php', 'vtlib/Vtiger/PackageImport.php');
		$result3 = copy('cache/updates/files/modules/Vtiger/models/Inventory.php', 'modules/Vtiger/models/Inventory.php');
		if ($result && $result2 && $result3) {
			exit(header('Location: ' . AppConfig::main('site_URL') . 'cache/updates/init2.php?from_version=' . $modulenode->from_version . '&to_version=' . $modulenode->to_version));
		} else {
			\App\Log::error('ERROR' . __METHOD__ . ': The file cannot be copied, necessary permissions are missing.');
			die('<div class="well pushDown">Update unsuccessful.<br>The file cannot be copied, necessary permissions are missing.<a class="btn btn-success" href="' . AppConfig::main('site_URL') . '">Return to the homepage</a></div>');
		}
	}

	public function update()
	{
		
	}

	public function postupdate()
	{
		
	}
}
