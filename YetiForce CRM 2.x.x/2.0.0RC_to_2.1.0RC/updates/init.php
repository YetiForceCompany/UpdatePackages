<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php'); 

class YetiForceUpdate{
	var $package;
	var $modulenode;
	var $return = true;
	var $dropTablePicklist = array();
	var $filesToDelete = array();
	var $filesToDeleteNew = array();

	function YetiForceUpdate($modulenode) {
		$this->modulenode = $modulenode;
	}
	function preupdate() {
		//$this->package->_errorText = 'Errot';
		
		return true;
	}
	
	function update() {
		
	}
	
	function postupdate() {
		global $log,$adb;
		if ($this->filesToDeleteNew) {
			foreach ($this->filesToDeleteNew as $path) {
				$this->recurseDelete($path);
			}
		}
		$result = true;
		self::recurseCopy('cache/updates/files_new','', true);
		$this->recurseDelete('cache/updates');
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		$adb->query('SET FOREIGN_KEY_CHECKS = 1;');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$adb->query("INSERT INTO `yetiforce_updates` (`user`, `name`, `from_version`, `to_version`, `result`) VALUES ('" . $currentUser->get('user_name') . "', '" . $this->modulenode->label . "', '" . $this->modulenode->from_version . "', '" . $this->modulenode->to_version . "','" . $result . "');", true);

		if ($result) {
			$adb->query("UPDATE vtiger_version SET current_version = '" . $this->modulenode->to_version . "';");
		}
		$dirName = 'cache/updates';
		$this->recurseDelete($dirName . '/files');
		$this->recurseDelete($dirName . '/init.php');
		$this->recurseDelete('cache/templates_c');
		header('Location: '.vglobal('site_URL'));
		exit;
		return true;
	}
	
	public function recurseDelete($src) {
		$rootDir = vglobal('root_directory');
		if (!file_exists($rootDir . $src))
			return;
		$dirs = [];
		@chmod($root_dir . $src, 0777);
		if(is_dir($src)) {
			foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
				if ($item->isDir()) {
					$dirs[] = $rootDir . $src . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
				} else {
					unlink($rootDir . $src . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
				}
			}
			$dirs[] =$src;
			arsort($dirs);
			foreach ($dirs as $dir) {
				@rmdir($dir);
			}
		} else {
			unlink($rootDir . $src);
		}
	}

	public function recurseCopy($src, $dest, $delete = false) {
		$rootDir = vglobal('root_directory');
		if (!file_exists($rootDir . $src))
			return;

		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isDir() && !file_exists($rootDir . $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
				mkdir($rootDir . $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
			} elseif(!$item->isDir())  {
				copy($item, $rootDir . $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
			}
		}
	}
}
