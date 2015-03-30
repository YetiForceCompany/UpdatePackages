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

class YetiForceUpdate{
	var $package;
	var $modulenode;
	var $return = true;
	var $dropTablePicklist = array();
	var $filesToDelete = array();
	var $filesToDeleteNew = array(
		'logs',
		'includes',
		'session',
		'test',
		'storage/Files',
		//'modules/OSSMail/roundcube/program/steps/mail/autocomplete_org.inc',
		'include/events/runtime/cache',
		'include/events/runtime/Cache.php',
		'languages/pt_br/Migration.php',
		'languages/pt_br/EmailTemplates.php'
	);
	
	function YetiForceUpdate($modulenode) {
		$this->modulenode = $modulenode;
	}
	function preupdate() {
		//$this->package->_errorText = 'Errot';
		
		return true;
	}
	
	function update() {
		$this->roundcubeConfig();
		$this->databaseStructureExceptDeletedTables();
		$this->databaseData();
	}
	
	function postupdate() {
		if ($this->filesToDeleteNew) {
			foreach ($this->filesToDeleteNew as $path) {
				$this->recurseDelete($path);
			}
		}
		self::recurseCopy('cache/updates/files_new','', true);
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		return true;
	}
	
	public function databaseStructureExceptDeletedTables(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::databaseStructureExceptDeletedTables() method ...");
		
		$adb->query("CREATE TABLE IF NOT EXISTS `roundcube_users_autologin` (
				  `rcuser_id` int(10) unsigned NOT NULL,
				  `crmuser_id` int(19) NOT NULL,
				  KEY `rcuser_id` (`rcuser_id`),
				  CONSTRAINT `roundcube_users_autologin_ibfk_1` FOREIGN KEY (`rcuser_id`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_mail_config` (
				  `type` varchar(50) DEFAULT NULL,
				  `name` varchar(50) DEFAULT NULL,
				  `value` varchar(50) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_mail_quantities` (
				  `userid` int(10) unsigned NOT NULL,
				  `num` int(10) unsigned DEFAULT '0',
				  `status` tinyint(1) DEFAULT '0',
				  PRIMARY KEY (`userid`),
				  CONSTRAINT `yetiforce_mail_quantities_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
				
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_users` LIKE 'emailoptout';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_users` ADD COLUMN `emailoptout` varchar(3) NOT NULL DEFAULT '1' after `is_owner` ;");
		}
		$log->debug("Exiting YetiForceUpdate::databaseStructureExceptDeletedTables() method ...");
	}
	function settingsReplace() {
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::settingsReplace() method ...");

		//add new record
		$settings_field = array();
		$settings_field[] = array('LBL_MAIL','LBL_AUTOLOGIN',NULL,'LBL_AUTOLOGIN_DESCRIPTION','index.php?parent=Settings&module=Mail&view=Autologin','2','0','0');
		$settings_field[] = array('LBL_MAIL','LBL_MAIL_GENERAL_CONFIGURATION',NULL,'LBL_MAIL_GENERAL_CONFIGURATION_DESCRIPTION','index.php?parent=Settings&module=Mail&view=Config','1','0','0');
		foreach ($settings_field AS $field){
			if(!self::checkFieldExists( $field, 'Settings' )){
				$field[0] = self::getBlockId($field[0]);
				$count = self::countRow('vtiger_settings_field', 'fieldid');
				array_unshift($field, ++$count);
				$adb->pquery('insert into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values (?, ?, ?, ?, ?, ?, ?, ?, ?)', $field);
			}
		}
		$adb->pquery('UPDATE `vtiger_settings_field_seq` SET id = ?;', array(self::countRow('vtiger_settings_field', 'fieldid')));

		$log->debug("Exiting YetiForceUpdate::settingsReplace() method ...");
	}
	public function getBlockId($label){
		global $adb;
		$result = $adb->pquery("SELECT blockid FROM vtiger_settings_blocks WHERE label = ? ;",array($label), true);
		return $adb->query_result($result, 0, 'blockid');
	}
	public function checkFieldExists($field, $moduleName){
		global $adb;
		if($moduleName == 'Settings')
			$result = $adb->pquery("SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;", array($field[1],$field[4]));
		else
			$result = $adb->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? AND tabid = ?;", array($field['name'],$field['table'], getTabid($moduleName)));
		if(!$adb->num_rows($result)) {
			return false;
		}
		return true;
	}
	public function countRow($table, $field){
		global $adb;
		$result = $adb->query("SELECT MAX(".$field.") AS max_seq  FROM ".$table." ;");
		return $adb->query_result($result, 0, 'max_seq');
	}
	public function databaseData(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::databaseData() method ...");
		$this->addFields();
		$result = $adb->pquery("SELECT * FROM `vtiger_eventhandlers` WHERE event_name = ? AND handler_class = ?;", array('vtiger.entity.link.after', 'HelpDeskHandler'));
		if($adb->num_rows($result) == 0){
			$addHandler = array();
			$addHandler[] = array('vtiger.entity.link.after','modules/HelpDesk/handlers/HelpDeskHandler.php','HelpDeskHandler','','1','[]');
			$em = new VTEventsManager($adb);
			foreach($addHandler as $handler){
				$em->registerHandler($handler[0], $handler[1], $handler[2], $handler[3], $handler[5]);
			}
		}
		$template[] = array('Notify Owner On Ticket Change','HelpDesk','PLL_RECORD');
		$template[] = array('Notify Account On Ticket Change','HelpDesk','PLL_RECORD');
		$template[] = array('Notify Contact On Ticket Closed','HelpDesk','PLL_RECORD');
		$template[] = array('Notify Account On Ticket Closed','HelpDesk','PLL_RECORD');
		$template[] = array('Notify Contact On Ticket Create','HelpDesk','PLL_RECORD');
		$template[] = array('Notify Account On Ticket Create','HelpDesk','PLL_RECORD');
		$template[] = array('Notify Contact On Ticket Change','HelpDesk','PLL_RECORD');
		$template[] = array('Notify Owner On Ticket Closed','HelpDesk','PLL_RECORD');
		$template[] = array('Notify Owner On Ticket Create','HelpDesk','PLL_RECORD');
		$template[] = array('Customer Portal Login Details','Contacts','PLL_RECORD');
		$template[] = array('Send invitations','Events','PLL_RECORD');
		$template[] = array('Send Notification Email to Record Owner','Calendar','PLL_RECORD');
		$template[] = array('Activity Reminder Notification','Calendar','PLL_RECORD');
		$template[] = array('Activity Reminder Notification','Events','PLL_RECORD');
		$template[] = array('Test mail about the mail server configuration.','Users','PLL_RECORD');
		$template[] = array('ForgotPassword','Users','PLL_RECORD');
		$template[] = array('Customer Portal - ForgotPassword','Contacts','PLL_RECORD');
		$template[] = array('New comment added to ticket from portal','ModComments','PLL_RECORD');
		$template[] = array('New comment added to ticket','ModComments','PLL_RECORD');
		$template[] = array('Security risk has been detected - Brute Force','Contacts','PLL_MODULE');
		$template[] = array('Backup has been made','Contacts','PLL_MODULE');
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_ossmailtemplates` LIKE 'ossmailtemplates_type';");
		if($adb->num_rows($result) == 1){
			foreach($template as $temp){
				$adb->pquery( "UPDATE `vtiger_ossmailtemplates` set ossmailtemplates_type = ? WHERE `name` = ? AND oss_module_list = ? ", array($temp[2],$temp[0],$temp[1]));
			}
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(getTabid('OSSMailTemplates'),getTabid('Documents'),'get_attachments','Documents'));
		if($adb->num_rows($result) == 0){
			$moduleInstance = Vtiger_Module::getInstance('Documents');
			$target_Module = Vtiger_Module::getInstance('OSSMailTemplates');
			$target_Module->setRelatedList($moduleInstance, 'Documents', array('add,select'),'get_attachments');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ? AND name = ? AND label = ?;", array(getTabid('HelpDesk'),getTabid('Contacts'),'get_related_list','Contacts'));
		if($adb->num_rows($result) == 0){
			$moduleInstance = Vtiger_Module::getInstance('Contacts');
			$target_Module = Vtiger_Module::getInstance('HelpDesk');
			$target_Module->setRelatedList($moduleInstance, 'Contacts', array('SELECT'),'get_related_list');
		}
		$adb->pquery( "UPDATE `vtiger_field` set displaytype = ? WHERE `columnname` = ?;", array(2,'was_read'));
		
		$adb->pquery( "UPDATE `vtiger_field` set uitype = ? WHERE `columnname` = ? AND tablename = ?;", array(15,'industry', 'vtiger_leaddetails'));
		$adb->pquery( "UPDATE `vtiger_calendar_default_activitytypes` set active = ? ;", array(1));
		$adb->pquery("UPDATE `com_vtiger_workflows` SET `type` = ? WHERE `summary` = ? AND module_name = ? ;", array('[]', 'Ticket Creation: Send Email to Record Contact', 'HelpDesk'));
		
		$result = $adb->pquery("SELECT workflow_id FROM `com_vtiger_workflows` WHERE summary = ? AND module_name =? ", array('Send Customer Login Details','Contacts'));
		if($adb->num_rows($result) == 1){
			$workflow_id = $adb->query_result_raw($result, 0, 'workflow_id');
			$workflowTaskAdd = array(128,53,'Mark portal users password as sent.','O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"53";s:7:"summary";s:35:"Mark portal users password as sent.";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:16:"MarkPasswordSent";s:2:"id";i:128;}');
			$result = $adb->pquery("SELECT * FROM `com_vtiger_workflowtasks` WHERE summary = ? AND workflow_id =? ", array($workflowTaskAdd[2],$workflow_id));
			if($adb->num_rows($result) == 0){
				$taskManager = new VTTaskManager($adb);
				$task = $taskManager->unserializeTask($workflowTaskAdd[3]);
				$task->id = '';
				$task->workflowId = $workflow_id;
				$taskManager->saveTask($task);
			}
		}
		
		$adb->pquery('UPDATE com_vtiger_workflows SET defaultworkflow = "0" WHERE `summary` = ? AND module_name = ?;', array('Ticket Creation: Send Email to Record Contact','HelpDesk'));
		$result = $adb->pquery('SELECT * FROM com_vtiger_workflows WHERE `summary` = ? AND module_name = ?;', array('Ticket Creation: Send Email to Record Contact','HelpDesk'));
		for($i=0;$i<$adb->num_rows($result);$i++){
			$recordId = $adb->query_result($result, $i, 'workflow_id');
			$adb->pquery("DELETE FROM com_vtiger_workflowtasks WHERE workflow_id IN
							(SELECT workflow_id FROM com_vtiger_workflows WHERE workflow_id=? AND (defaultworkflow IS NULL OR defaultworkflow != 1))",
						array($recordId));
			$adb->pquery("DELETE FROM com_vtiger_workflows WHERE workflow_id=? AND (defaultworkflow IS NULL OR defaultworkflow != 1)", array($recordId));
		}
		$result = $adb->pquery('SELECT * FROM com_vtiger_workflowtasks WHERE `summary` IN (?,?);', array('Notify Contact On Ticket Closed','Notify Contact On Ticket Change'));
		for($i=0;$i<$adb->num_rows($result);$i++){
			$recordId = $adb->query_result($result, $i, 'task_id');
			$adb->pquery("delete from com_vtiger_workflowtasks where task_id=?", array($recordId));
		}
		$task_entity_method[] = array('HelpDesk','HeldDeskChangeNotifyContacts','modules/HelpDesk/workflows/HelpDeskWorkflow.php','HeldDeskChangeNotifyContacts');
		$task_entity_method[] = array('HelpDesk','HeldDeskClosedNotifyContacts','modules/HelpDesk/workflows/HelpDeskWorkflow.php','HeldDeskClosedNotifyContacts');
		$emm = new VTEntityMethodManager($adb);
		foreach($task_entity_method as $method){
			$result = $adb->pquery("SELECT * FROM `com_vtiger_workflowtasks_entitymethod` WHERE method_name = ? ", array($method[1]));
			if($adb->num_rows($result) == 0){
				$emm->addEntityMethod($method[0], $method[1], $method[2], $method[3]);
			}
		}
		
		$adb->pquery('UPDATE com_vtiger_workflows SET test = ? WHERE `summary` = ? AND module_name = ?;', array('[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]','Ticket Closed: Send Email to Record Contact','HelpDesk'));
		$adb->pquery('UPDATE com_vtiger_workflows SET test = ? WHERE `summary` = ? AND module_name = ?;', array('[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]','Ticket change: Send Email to Record Contact','HelpDesk'));
		
		$result = $adb->pquery("SELECT workflow_id FROM `com_vtiger_workflows` WHERE summary = ? AND module_name =? ", array('Ticket change: Send Email to Record Contact','HelpDesk'));
		if($adb->num_rows($result) == 1){
			$workflow_id = $adb->query_result_raw($result, 0, 'workflow_id');
			$workflowTaskAdd = array(133,26,'Notify Contact On Ticket Change','O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"26";s:7:"summary";s:31:"Notify Contact On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:28:"HeldDeskChangeNotifyContacts";s:2:"id";i:133;}');
			$result = $adb->pquery("SELECT * FROM `com_vtiger_workflowtasks` WHERE summary = ? AND workflow_id =? ", array($workflowTaskAdd[2],$workflow_id));
			if($adb->num_rows($result) == 0){
				$taskManager = new VTTaskManager($adb);
				$task = $taskManager->unserializeTask($workflowTaskAdd[3]);
				$task->id = '';
				$task->workflowId = $workflow_id;
				$taskManager->saveTask($task);
			}
		}
		$result = $adb->pquery("SELECT workflow_id FROM `com_vtiger_workflows` WHERE summary = ? AND module_name =? ", array('Ticket Closed: Send Email to Record Contact','HelpDesk'));
		if($adb->num_rows($result) == 1){
			$workflow_id = $adb->query_result_raw($result, 0, 'workflow_id');
			$workflowTaskAdd = array(134,29,'Notify contacts about closing of ticket.','O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"29";s:7:"summary";s:40:"Notify contacts about closing of ticket.";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:28:"HeldDeskClosedNotifyContacts";s:2:"id";i:134;}');
			$result = $adb->pquery("SELECT * FROM `com_vtiger_workflowtasks` WHERE summary = ? AND workflow_id =? ", array($workflowTaskAdd[2],$workflow_id));
			if($adb->num_rows($result) == 0){
				$taskManager = new VTTaskManager($adb);
				$task = $taskManager->unserializeTask($workflowTaskAdd[3]);
				$task->id = '';
				$task->workflowId = $workflow_id;
				$taskManager->saveTask($task);
			}
		}
		$this->settingsReplace();
		$this->picklists();
		$result = $adb->pquery("SELECT * FROM `yetiforce_mail_config` WHERE name = ? ", array('showMailAccounts'));
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `yetiforce_mail_config`(`type`,`name`,`value`) values ('mailIcon','showMailAccounts','false');");
		}
		$result = $adb->pquery("SELECT * FROM `yetiforce_mail_config` WHERE name = ? ", array('showNumberUnreadEmails'));
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `yetiforce_mail_config`(`type`,`name`,`value`) values ('mailIcon','showNumberUnreadEmails','false');");
		}
		$result = $adb->pquery("SELECT * FROM `yetiforce_mail_config` WHERE name = ? ", array('showMailIcon'));
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `yetiforce_mail_config`(`type`,`name`,`value`) values ('mailIcon','showMailIcon','true');");
		}
		$result = $adb->pquery("SELECT * FROM `yetiforce_mail_config` WHERE name = ? ", array('timeCheckingMail'));
		if($adb->num_rows($result) == 0){
			$result = $adb->pquery("SELECT * FROM `vtiger_ossmailscanner_config` WHERE conf_type = ? AND `parameter` = ? ;", array('email_list','time_checking_mail'));
			$value = $adb->query_result($result, 0, 'value');
			if(!$value){
				$value = 30;
			}
			$adb->pquery("insert  into `yetiforce_mail_config`(`type`,`name`,`value`) values ('mailIcon','timeCheckingMail',?);", array($value));
		}
		$result = $adb->pquery("SELECT * FROM `yetiforce_mail_config` WHERE name = ? ", array('autologinActive'));
		if($adb->num_rows($result) == 0){
			$result = $adb->pquery("SELECT * FROM `vtiger_ossmailscanner_config` WHERE conf_type = ? AND `parameter` = ? ;", array('email_list','autologon'));
			$value = $adb->query_result($result, 0, 'value');
			if(!$value){
				$value = 'false';
			}
			$adb->pquery("insert  into `yetiforce_mail_config`(`type`,`name`,`value`) values ('autologin','autologinActive',?);", array($value));
		}
		
		$adb->pquery("DELETE FROM vtiger_ossmailscanner_config WHERE conf_type = ? AND `parameter` = ? ;", array('email_list','autologon'));
		$adb->pquery("DELETE FROM vtiger_ossmailscanner_config WHERE conf_type = ? AND `parameter` = ? ;", array('email_list','time_checking_mail'));
		$adb->pquery("UPDATE `com_vtiger_workflows` SET `test` = ? WHERE `summary` = ? ;", array('[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(assigned_user_id : (Users) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 'Ticket change: Send Email to Record Owner'));
		$adb->pquery("UPDATE `com_vtiger_workflows` SET `test` = ? WHERE `summary` = ? ;", array('[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(assigned_user_id : (Users) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 'Ticket Closed: Send Email to Record Owner'));
		
		$result = $adb->pquery("SELECT * FROM `com_vtiger_workflowtasks` WHERE summary = ? ", array('Update Closed Time'));
		if($adb->num_rows($result) == 1){
			$task_id = $adb->query_result($result, 0, 'task_id');
			$taskRecordModel = Settings_Workflows_TaskRecord_Model::getInstance($task_id);
			$taskObject = $taskRecordModel->getTaskObject();
			$taskObject->active = false;
			$taskRecordModel->save();
		}
		$adb->pquery('UPDATE `vtiger_field` SET uitype = ? WHERE tabid = ? AND columnname = ? ;', array(19,getTabid('Emails'), 'description'));
		$log->debug("Exiting YetiForceUpdate::databaseData() method ...");
	}
	
	public function roundcubeConfig(){
		global $log,$adb,$root_directory;
		$log->debug("Entering YetiForceUpdate::roundcubeConfig() method ...");
		if(!$root_directory)
			$root_directory = getcwd();
		$fileName = $root_directory.'/modules/OSSMail/roundcube/config/config.inc.php';
		$completeData = file_get_contents($fileName);
		if(strpos($completeData,"yt_new_user") !== FALSE){
			return;
		}
		$completeData = str_replace("'autologon'", "'yt_new_user','autologon'", $completeData); 	
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $completeData);
		fclose($filePointer);
		$log->debug("Exiting YetiForceUpdate::roundcubeConfig() method ...");
	}
	public function picklists(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::picklists() method ...");
		$addPicklists = [];
		$addPicklists['ProjectMilestone'][] = array('name'=>'projectmilestonetype','uitype'=>'15','add_values'=>array('PLL_INTERNAL','PLL_EXTERNAL','PLL_SHARED'),'remove_values'=>array('administrative','operative','other'));
		
		$roleRecordList = Settings_Roles_Record_Model::getAll();
		$rolesSelected = array();
		foreach($roleRecordList as $roleRecord) {
			$rolesSelected[] = $roleRecord->getId();
		}
		foreach($addPicklists as $moduleName=>$piscklists){
			$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
			if(!$moduleModel)
				continue;
			foreach($piscklists as $piscklist){
				$fieldModel = Settings_Picklist_Field_Model::getInstance($piscklist['name'], $moduleModel);
				if(!$fieldModel)
					continue;
				$pickListValues = Vtiger_Util_Helper::getPickListValues($piscklist['name']);
				foreach($piscklist['add_values'] as $newValue){
					if(!in_array($newValue, $pickListValues)){
						$moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
					}
				}
				foreach($piscklist['remove_values'] as $newValue){
					if(!in_array($newValue, $pickListValues))
						continue;
					if($piscklist['uitype'] == '15'){
						$deletePicklistValueId = self::getPicklistId($piscklist['name'], $newValue);
						if($deletePicklistValueId)
							$adb->pquery("DELETE FROM `vtiger_role2picklist` WHERE picklistvalueid = ? ", array($deletePicklistValueId));
					}
					$adb->pquery("DELETE FROM `vtiger_".$piscklist['name']."` WHERE ".$piscklist['name']." = ? ", array($newValue));
					if($piscklist['name'] == 'administrative' && $moduleName == 'ProjectMilestone'){
						$adb->pquery("UPDATE `vtiger_projectmilestone` SET `projectmilestonetype` = ? WHERE `projectmilestonetype` = ? ;", array($piscklist['name'], 'PLL_INTERNAL'));
					}if($piscklist['name'] == 'operative' && $moduleName == 'ProjectMilestone'){
						$adb->pquery("UPDATE `vtiger_projectmilestone` SET `projectmilestonetype` = ? WHERE `projectmilestonetype` = ? ;", array($piscklist['name'], 'PLL_EXTERNAL'));
					}if($piscklist['name'] == 'other' && $moduleName == 'ProjectMilestone'){
						$adb->pquery("UPDATE `vtiger_projectmilestone` SET `projectmilestonetype` = ? WHERE `projectmilestonetype` = ? ;", array($piscklist['name'], 'PLL_SHARED'));
					}
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::picklists() method ...");
	}
	public function getPicklistId($fieldName, $value){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::getPicklistId(".$fieldName.','.$value.") method ...");
		if(Vtiger_Utils::CheckTable('vtiger_' .$fieldName)) {
			$sql = 'SELECT * FROM vtiger_' .$fieldName. ' WHERE ' .$fieldName. ' = ? ;';
			$result = $adb->pquery($sql, array($value));
			if($adb->num_rows($result) > 0){
				$log->debug("Exiting YetiForceUpdate::getPicklistId() method ...");
				return $adb->query_result($result, 0, 'picklist_valueid');
			}
		}
		$log->debug("Exiting YetiForceUpdate::getPicklistId() method ...");
		return false;
		
	}
	
	public function addFields(){
		global $log, $adb;
		$log->debug("Entering YetiForceUpdate::addFields() method ...");
		include_once('vtlib/Vtiger/Module.php'); 
		$columnName = array("tabid","id","column","table","generatedtype","uitype","name","label","readonly","presence","defaultvalue","maximumlength","sequence","block","displaytype","typeofdata","quickcreate","quicksequence","info_type","masseditable","helpinfo","summaryfield","fieldparams","columntype","blocklabel","setpicklistvalues","setrelatedmodules");

		$OSSMailTemplates = array(
		array('49','1739','ossmailtemplates_type','vtiger_ossmailtemplates','1','16','ossmailtemplates_type','LBL_TYPE','1','2','','100','8','126','1','V~M','1',NULL,'BAS','1','','0','',"varchar(255)","LBL_OSSMAILTEMPLATES_INFORMATION",array('PLL_MODULE','PLL_RECORD'),array())
		);
		$Users = array(
		array('29','1740','emailoptout','vtiger_users','1','56','emailoptout','Approval for email','1','0','','50','22','79','1','V~O','1',NULL,'BAS','1','','0','',"varchar(3)","LBL_MORE_INFORMATION",array(),array())
		);
		
		$setToCRM = array('OSSMailTemplates'=>$OSSMailTemplates,'Users'=>$Users);

		$setToCRMAfter = array();
		foreach($setToCRM as $nameModule=>$module){
			if(!$module)
				continue;
			foreach($module as $key=>$fieldValues){
				for($i=0;$i<count($fieldValues);$i++){
					$setToCRMAfter[$nameModule][$key][$columnName[$i]] = $fieldValues[$i];
				}
			}
		}
		foreach($setToCRMAfter as $moduleName=>$fields){
			foreach($fields as $field){
				if(self::checkFieldExists($field, $moduleName)){
					continue;
				}
					$moduleInstance = Vtiger_Module::getInstance($moduleName);
					$blockInstance = Vtiger_Block::getInstance($field['blocklabel'],$moduleInstance);
					$fieldInstance = new Vtiger_Field();
					$fieldInstance->column = $field['column'];
					$fieldInstance->name = $field['name'];
					$fieldInstance->label = $field['label'];
					$fieldInstance->table = $field['table'];
					$fieldInstance->uitype = $field['uitype'];
					$fieldInstance->typeofdata = $field['typeofdata'];
					$fieldInstance->readonly = $field['readonly'];
					$fieldInstance->displaytype = $field['displaytype'];
					$fieldInstance->masseditable = $field['masseditable'];
					$fieldInstance->quickcreate = $field['quickcreate'];
					$fieldInstance->columntype = $field['columntype'];
					$fieldInstance->presence = $field['presence'];
					$fieldInstance->maximumlength = $field['maximumlength'];
					$fieldInstance->info_type = $field['info_type'];
					$fieldInstance->helpinfo = $field['helpinfo'];
					$fieldInstance->summaryfield = $field['summaryfield'];
					$fieldInstance->generatedtype = $field['generatedtype'];
					$fieldInstance->defaultvalue = $field['defaultvalue'];
					$blockInstance->addField($fieldInstance);
					if($field['setpicklistvalues'] && ($field['uitype'] == 15 || $field['uitype'] == 16 || $field['uitype'] == 33 ))
						$fieldInstance->setPicklistValues($field['setpicklistvalues']);
					if($field['setrelatedmodules'] && $field['uitype'] == 10){
						$fieldInstance->setRelatedModules($field['setrelatedmodules']);
					}
			}
		}
		$result = $adb->query("SHOW TABLES LIKE 'vtiger_ossmailtemplates_type';");
		if($adb->num_rows($result) == 1){
			$adb->pquery("UPDATE `vtiger_ossmailtemplates_type` SET `presence` = ? WHERE `ossmailtemplates_type` = ?;", array(0, 'PLL_MODULE'));
		}
		
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
			arsort($dirs);
			foreach ($dirs as $dir) {
				rmdir($dir);
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