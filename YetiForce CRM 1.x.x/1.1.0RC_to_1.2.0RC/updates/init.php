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
class YetiForceUpdate{
	var $modulenode;
	var $filesToDelete = array(
		'layouts\vlayout\modules\Settings\MenuEditor\Index.tpl',
		'modules\Settings\MenuEditor\actions\Save.php',
		'modules\Settings\MenuEditor\views\Index.php',
		'languages\de_de\Proposal.php',
		'languages\en_us\Proposal.php',
		'languages\pl_pl\Proposal.php',
		'languages\pt_br\Proposal.php',
		'languages\ru_ru\Proposal.php',
		'modules\Settings\ModuleManager\models\Extension.php',
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
