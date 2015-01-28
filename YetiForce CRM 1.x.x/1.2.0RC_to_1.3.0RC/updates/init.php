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
	var $return = true;
	
	var $filesToDelete = array(

	);
	function YetiForceUpdate($modulenode) {
		$this->modulenode = $modulenode;
	}
	function preupdate() {

	}
	
	function update() {
		$this->databaseStructureExceptDeletedTables();
		$this->databaseData();
		$this->addRecords();
		$this->dropTable();
	}
	function postupdate() {
		global $adb;
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		return true;
	}
	public function databaseStructureExceptDeletedTables(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::databaseStructureExceptDeletedTables() method ...");
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_field` LIKE 'fieldparams';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_field` ADD COLUMN `fieldparams` varchar(255) COLLATE utf8_general_ci NULL after `summaryfield` ;");
			$adb->query("UPDATE vtiger_field SET `fieldparams` = '1', `uitype` = '302', `defaultvalue` = 'T1' WHERE `columnname` = 'folderid' AND `tablename` = 'vtiger_notes';");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_module_dashboard_widgets` LIKE 'size';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` ADD COLUMN `size` varchar(50)  COLLATE utf8_general_ci NULL after `data`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_module_dashboard_widgets` LIKE 'limit';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` ADD COLUMN `limit` int(10) NULL after `size`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_module_dashboard` LIKE 'size';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_module_dashboard` ADD COLUMN `size` varchar(50) COLLATE utf8_general_ci NULL after `data`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_module_dashboard` LIKE 'limit';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_module_dashboard` ADD COLUMN `limit` int(10) NULL after `size`;");
		}
		$adb->query("ALTER TABLE `vtiger_notes` CHANGE `folderid` `folderid` varchar(255)  COLLATE utf8_general_ci NOT NULL after `notecontent`;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_trees_templates` (
					`templateid` int(19) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) DEFAULT NULL,
					`module` int(19) DEFAULT NULL,
					PRIMARY KEY (`templateid`),
					KEY `module` (`module`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_trees_templates_data` (
					`templateid` int(19) NOT NULL,
					`name` varchar(255) DEFAULT NULL,
					`tree` varchar(255) DEFAULT NULL,
					`parenttrre` varchar(255) DEFAULT NULL,
					`depth` int(10) DEFAULT NULL,
					`label` varchar(255) DEFAULT NULL,
					KEY `id` (`templateid`),
					KEY `parenttrre` (`parenttrre`,`templateid`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_relatedlists_fields` (
					`relation_id` int(19) DEFAULT NULL,
					`fieldid` int(19) DEFAULT NULL,
					`fieldname` varchar(30) DEFAULT NULL,
					`sequence` int(10) DEFAULT NULL,
					KEY `relation_id` (`relation_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$log->debug("Exiting YetiForceUpdate::databaseStructureExceptDeletedTables() method ...");
	}
	public function addRecords(){
		global $log,$adb,$current_user;
		$log->debug("Entering YetiForceUpdate::addRecords() method ...");
		$assigned_user_id = $current_user->id;
		$moduleName = 'OSSMailTemplates';
		vimport('~~modules/' . $moduleName . '/' . $moduleName . '.php');
		$records = array();
		$records[] = array('ForgotPassword','Users','Request: ForgotPassword','Dear user,<br /><br />\r\nYou recently requested a password reset for your YetiForce CRM.<br />\r\nTo create a new password, click on the link #s#LinkToForgotPassword#sEnd#.<br /><br />\r\nThis request was made on #s#CurrentDateTime#sEnd# and will expire in next 24 hours.<br /><br />\r\nRegards,<br />\r\nYetiForce CRM Support Team.');
		foreach($records as $record){
				$instance = new $moduleName();
				$instance->column_fields['assigned_user_id'] = $assigned_user_id;
				$instance->column_fields['name'] = $record[0];
				$instance->column_fields['oss_module_list'] = $record[1];
				$instance->column_fields['subject'] = $record[2];
				$instance->column_fields['content'] = $record[3];
				$save = $instance->save($moduleName);
				if($record[0] == 'ForgotPassword')
					self::updateForgotPassword($instance->id);
		}
		$log->debug("Exiting YetiForceUpdate::addRecords() method ...");
	}
	public function updateForgotPassword($id){
		global $log,$adb,$root_directory;
		$log->debug("Entering YetiForceUpdate::updateForgotPassword(".$id.") method ...");
		if(!$root_directory)
			$root_directory = getcwd();
		$fileName = $root_directory.'/modules/Users/actions/ForgotPassword.php';
		$completeData = file_get_contents($fileName);
		$updatedFields = "'id'";
		$patternString = "%s => %s,";
		$pattern = '/' . $updatedFields . '[\s]+=([^,]+),/';
		$replacement = sprintf($patternString, $updatedFields, ltrim($id, '0'));
		$fileContent = preg_replace($pattern, $replacement, $completeData);
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		
		$log->debug("Exiting YetiForceUpdate::updateForgotPassword() method ...");
	}
	public function dropTable(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::dropTable() method ...");
		$adb->query("DROP TABLE IF EXISTS `vtiger_attachmentsfolder`;");
		$adb->query("DROP TABLE IF EXISTS `vtiger_attachmentsfolder_seq`;");
		$log->debug("Exiting YetiForceUpdate::dropTable() method ...");
	}
	
	public function databaseData(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::databaseData() method ...");
		$result = $adb->query("SELECT * FROM `vtiger_ws_fieldtype` WHERE `uitype` = '120'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_ws_fieldtype`(`uitype`,`fieldtype`) values ('120','sharedOwner');");
		}
		$result = $adb->query("SELECT * FROM `vtiger_ws_fieldtype` WHERE `uitype` = '301'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_ws_fieldtype`(`uitype`,`fieldtype`) values ('301','modules');");
		}
		$result = $adb->query("SELECT * FROM `vtiger_ws_fieldtype` WHERE `uitype` = '302'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_ws_fieldtype`(`uitype`,`fieldtype`) values ('302','tree');");
		}
		$result = $adb->query("SELECT `cvid` FROM `vtiger_customview` WHERE `viewname` = 'All' AND `entitytype` = 'Documents'; ");
		if($adb->num_rows($result) == 1){
			$cvId = $adb->query_result_raw($result, 0, 'cvid');
			$result = $adb->query("SELECT * FROM `vtiger_cvcolumnlist` WHERE `cvid` = '$cvId' AND `columnname` = 'vtiger_crmentity:modifiedtime:modifiedtime:Notes_Modified_Time:DT';");
			if($adb->num_rows($result) == 1){
				$adb->query("UPDATE vtiger_cvcolumnlist SET `columnname` = 'vtiger_notes:folderid:folderid:Documents_Folder_Name:V' WHERE `cvid` = '$cvId' AND `columnname` = 'vtiger_crmentity:modifiedtime:modifiedtime:Notes_Modified_Time:DT';");
			}
		}
		
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_TREES_MANAGER'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,2,'LBL_MOBILE_KEYS',NULL,'LBL_TREES_MANAGER_DESCRIPTION','index.php?module=TreesManager&parent=Settings&view=List',15,0,0);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_ACTIVITY_TYPES'");
		if($adb->num_rows($result) == 1){
			$id = $adb->query_result_raw($result, 0, 'fieldid');
			$adb->query("UPDATE vtiger_settings_field SET `sequence` = '14' WHERE `fieldid` = $id;");
		}
		
		$result = $adb->query("SELECT * FROM `vtiger_trees_templates`;");
		if($adb->num_rows($result) == 0){
			self::foldersToTree();
		}
		
		$result1 = $adb->query("SELECT workflow_id FROM `com_vtiger_workflows` WHERE summary = 'Workflow for Events when Send Notification is True'");
		$result2 = $adb->query("SELECT ossmailtemplatesid FROM `vtiger_ossmailtemplates` WHERE name = 'Send invitations'");
		$result3 = $adb->query("SELECT * FROM `com_vtiger_workflowtasks` WHERE `summary` = 'Send invitations'");
		if($adb->num_rows($result1) == 1 && $adb->num_rows($result2) == 1 && $adb->num_rows($result3) == 1){
			$workflow_id = $adb->query_result_raw($result1, 0, 'workflow_id');
			$ossmailtemplatesid = $adb->query_result_raw($result2, 0, 'ossmailtemplatesid');
			$task_id = $adb->query_result_raw($result3, 0, 'task_id');
			$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($task_id);
			$taskObject = $taskRecordModel->getTaskObject();
			if($taskObject->template != $ossmailtemplatesid){
				$taskObject->template = $ossmailtemplatesid;
				$taskRecordModel->save();
			}
		}
		
		
		$log->debug("Exiting YetiForceUpdate::databaseData() method ...");
	}
	
	public function foldersToTree(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::foldersToTree() method ...");
		
		$sql = 'INSERT INTO vtiger_trees_templates(name, module) VALUES (?,?)';
		$params = array('System', getTabid('Documents'));
		$adb->pquery($sql, $params);
		$templateId = $adb->getLastInsertID();
		
		$sql = 'INSERT INTO vtiger_trees_templates_data(`templateid`, `name`, `tree`, `parenttrre`, `depth`, `label`) VALUES (?,?,?,?,?,?)';
		$params = array($templateId, 'Default', 'T1', 'T1', 0, 'Default');
		$adb->pquery($sql, $params);
		
		$result = $adb->query("SELECT * FROM `vtiger_attachmentsfolder` ORDER BY `sequence`;");
		
		$fieldsResult = array();
		for($i=0;$i<$adb->num_rows($result);$i++){
			$folderid = $adb->query_result($result, $i, 'folderid');
			$name = $adb->query_result($result, $i, 'foldername');
			if($folderid != 1){
				$sql = 'INSERT INTO vtiger_trees_templates_data(templateid, name, tree, parenttrre, depth, label) VALUES (?,?,?,?,?,?)';
				$params = array($templateId, $name, 'T'.$folderid, 'T'.$folderid, 0, $name);
				$adb->pquery($sql, $params);
			}
			$query = "UPDATE `vtiger_notes` SET `folderid` = ? WHERE `folderid` = ? ; ";
			$adb->pquery($query, array('T'.$folderid, $folderid));
		}
		$log->debug("Exiting YetiForceUpdate::foldersToTree() method ...");
	}
}