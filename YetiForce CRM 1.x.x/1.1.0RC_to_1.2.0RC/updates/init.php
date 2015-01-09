<?php
class YetiForceUpdate{
	var $modulenode;
	var $filesToDelete = array(

	);

	function YetiForceUpdate($modulenode) {
		$this->modulenode = $modulenode;
	}

	function preupdate() {

	}
	
	function update() {

	}
	
	function postupdate() {
		global $adb;
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		return true;
	}
	public function updateDatabase(){
		global $log,$adb;

	}
}