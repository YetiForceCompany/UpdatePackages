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
	var $dropTablePicklist = array();
	
	
	var $filesToDelete = array(
		'forgotPassword.php',
		'layouts/vlayout/modules/Documents/AddFolder.tpl',
		'modules/Documents/actions/Folder.php',
		'modules/Documents/models/Folder.php',
		'modules/Documents/views/AddFolder.php',
		'modules/Documents/views/List.php',
		'modules/Documents/views/ListAjax.php',
		'licenses/License_linux.txt',
		'licenses/License_windows.txt',
		'test/migration/bootstrap/css/bootstrap.min.css',
		'test/migration/bootstrap/js/bootstrap.min.js',
		'test/migration/css/check_radio_sprite.png',
		'test/migration/css/mkCheckbox.css',
		'test/migration/css/style.css',
		'test/migration/images/help40.png',
		'test/migration/images/migration_screen.png',
		'test/migration/images/no.png',
		'test/migration/images/vt1.png',
		'test/migration/images/wizard_screen.png',
		'test/migration/images/yes.png',
		'test/migration/js/jquery-min.js',
		//'modules/OSSCosts/copy',
		'data/CRMEntity.php',
		'data/Tracker.php',
		'data/VTEntityDelta.php',
		'data/Ideas.xml',
		'data/init.php',
		'layouts/vlayout/modules/Vtiger/Popup2.tpl',
		'layouts/vlayout/modules/Vtiger/Popup2Contents.tpl',
		'layouts/vlayout/modules/Vtiger/Popup2Search.tpl',
		'modules/Ideas/schema.xml',
	);
	function YetiForceUpdate($modulenode) {
		$this->modulenode = $modulenode;
	}
	function preupdate() {
		$this->recurseCopy('cache/updates/files','');
	}
	
	function update() {
		$this->addFields();
		$this->addModules();
		$this->databaseStructureExceptDeletedTables();
		$this->databaseData();
		$this->addRecords();
		$this->dropTable();
		$this->updateFiles();
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
			$adb->query("ALTER TABLE `vtiger_field` ADD COLUMN `fieldparams` varchar(255) DEFAULT '' after `summaryfield` ;");
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
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_module_dashboard_widgets` LIKE 'owners';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` ADD COLUMN `owners` varchar(100) NULL after `active`;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_module_dashboard` LIKE 'owners';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_module_dashboard` ADD COLUMN `owners` varchar(100)  COLLATE utf8_general_ci NULL after `isdefault`");
		}
		$adb->query("ALTER TABLE `vtiger_notes` CHANGE `folderid` `folderid` varchar(255)  COLLATE utf8_general_ci NOT NULL after `notecontent`;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_trees_templates` (
					`templateid` int(19) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) DEFAULT NULL,
					`module` int(19) DEFAULT NULL,
					`access` int(1) DEFAULT '1',
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
		// apiAddress			
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_apiaddress` LIKE 'id';");
		if($adb->num_rows($result) == 0){
			$result = $adb->query("SELECT * FROM `vtiger_apiaddress`;");
			if($adb->num_rows($result) != 0){
				$apiAddress = $adb->query_result_rowdata($result, 0);
				$adb->query("ALTER TABLE `vtiger_apiaddress` 
						ADD COLUMN `id` int(19)   NOT NULL first , 
						ADD COLUMN `name` varchar(255) NOT NULL after `id` , 
						ADD COLUMN `val` varchar(255) NOT NULL after `name` , 
						ADD COLUMN `type` varchar(255) NOT NULL after `val` , 
						DROP COLUMN `nominatim` , 
						DROP COLUMN `key` , 
						DROP COLUMN `min_lenght` , 
						DROP COLUMN `source` , 
						ADD PRIMARY KEY(`id`) ;");
	
				$adb->pquery("insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (?,?,?,?);", array(1,'min_lenght',$apiAddress["min_lenght"],'global'));
				$adb->pquery("insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (?,?,?,?);", array(2,'key','','google_map_api'));
				$adb->pquery("insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (?,?,?,?);", array(3,'nominatim','0','google_map_api'));
				$adb->pquery("insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (?,?,?,?);", array(4,'source','https://maps.googleapis.com/maps/api/geocode/json','google_map_api'));
				$adb->pquery("insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (?,?,?,?);", array(5,'key',$apiAddress["key"],'opencage_data'));
				$adb->pquery("insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (?,?,?,?);", array(6,'source','https://api.opencagedata.com/geocode/v1/','opencage_data'));
				$adb->pquery("insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (?,?,?,?);", array(7,'nominatim',$apiAddress["nominatim"],'opencage_data'));
				$adb->pquery("insert  into `vtiger_apiaddress`(`id`,`name`,`val`,`type`) values (?,?,?,?);", array(8,'result_num','10','global'));
				$result = $adb->query("SELECT * FROM `vtiger_apiaddress` WHERE `id` = 0;");
				if($adb->num_rows($result) == 1){
					$adb->query("delete from vtiger_apiaddress where `id` = 0");
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::databaseStructureExceptDeletedTables() method ...");
	}
	public function addModules(){
		try {
			if(file_exists('cache/updates/Ideas.xml') && !Vtiger_Module::getInstance('Ideas')){
				$importInstance = new Vtiger_PackageImport();
				$importInstance->_modulexml = simplexml_load_file('cache/updates/Ideas.xml');
				$importInstance->import_Module();
				self::addModuleToMenu('Ideas', (string)$importInstance->_modulexml->parent);
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	public function addModuleToMenu($moduleName, $parent){
		global $log;
		$log->debug("Entering YetiForceUpdate::addModuleToMenu($moduleName, $parent) method ...");
		$adb = PearDatabase::getInstance();
		if(!$parent)
			return false;
		$sql = "SELECT `profileid` FROM `vtiger_profile` WHERE 1;";
        $result = $adb->query( $sql, true );
        $num = $adb->num_rows( $result );
        
        $profiles = array();
        for ( $i=0; $i<$num; $i++ ) {
            $profiles[] = $adb->query_result( $result, $i, 'profileid' );
        }
        
        $profilePermissions = implode( ' |##| ', $profiles );
		$profilePermissions = ' ' . $profilePermissions . ' ';
		$tabID = getTabid($moduleName);
		
		//$blocksModule = array('My Home Page','Companies','Human resources','Sales','Projects','Support','Databases');
		$sql = "SELECT `id` FROM `vtiger_ossmenumanager` WHERE label = ? AND tabid = ? AND parent_id = ?;";
		$result = $adb->pquery( $sql, array($parent, $tabID, 0) );
		$num = $adb->num_rows( $result );
		if($num == 0){
			$subParams = array(
				'parent_id'     => 0,
				'tabid'         => $tabID,
				'label'         => 'Group Card',
				'sequence'      => -1,
				'visible'       => '1',
				'type'          => 0,
				'url'           => '',
				'new_window'    => 0,
				'permission'    => $profilePermissions,
				'locationicon'  => '',
				'sizeicon'      => '',
				'langfield'     => 'en_us*Group Card#pl_pl*Karta grupowa'
				
			);
			$id = OSSMenuManager_Record_Model::addMenu( $subParams ); 
		}
		$sql = "SELECT `id` FROM `vtiger_ossmenumanager` WHERE label = ? AND tabid = ? AND parent_id = ?;";
		$result = $adb->pquery( $sql, array($parent, $tabID, 0) );
		$num = $adb->num_rows( $result );
		if($num == 1){
			$subParams = array(
				'parent_id'     => $adb->query_result( $result, 0, 'id' ),
				'tabid'         => $tabID,
				'label'         => $moduleName,
				'sequence'      => -1,
				'visible'       => '1',
				'type'          => 0,
				'url'           => '',
				'new_window'    => 0,
				'permission'    => $profilePermissions,
				'locationicon'  => '',
				'sizeicon'      => '',
				'langfield'     => ''
				
			);
			$id = OSSMenuManager_Record_Model::addMenu( $subParams ); 
		}
		$log->debug("Exiting YetiForceUpdate::addModuleToMenu() method ...");
	}
	public function addRecords(){
		global $log,$adb,$current_user;
		$log->debug("Entering YetiForceUpdate::addRecords() method ...");
		$assigned_user_id = $current_user->id;
		$moduleName = 'OSSMailTemplates';
		vimport('~~modules/' . $moduleName . '/' . $moduleName . '.php');
		$records = array();
		$records[] = array('ForgotPassword','Users','Request: ForgotPassword','Dear user,<br /><br />\r\nYou recently requested a password reset for your YetiForce CRM.<br />\r\nTo create a new password, click on the link #s#LinkToForgotPassword#sEnd#.<br /><br />\r\nThis request was made on #s#CurrentDateTime#sEnd# and will expire in next 24 hours.<br /><br />\r\nRegards,<br />\r\nYetiForce CRM Support Team.');
		$records[] = array('Customer Portal - ForgotPassword','Contacts','Request: ForgotPassword','Dear #a#67#aEnd# #a#67#aEnd#,<br /><br />You recently requested a reminder of your access data for the YetiForce Portal.<br /><br />You can login by entering the following data:<br /><br />Your username: #a#80#aEnd#<br />Your password: #s#ContactsPortalPass#sEnd#<br /><br /><br />Regards,<br />YetiForce CRM Support Team.');
		foreach($records as $record){
			$result = $adb->query("SELECT * FROM `vtiger_ossmailtemplates` WHERE `name` = '".$record[0]."'");
			if($adb->num_rows($result) == 0){
				$instance = new $moduleName();
				$instance->column_fields['assigned_user_id'] = $assigned_user_id;
				$instance->column_fields['name'] = $record[0];
				$instance->column_fields['oss_module_list'] = $record[1];
				$instance->column_fields['subject'] = $record[2];
				$instance->column_fields['content'] = $record[3];
				$save = $instance->save($moduleName);
				if($record[0] == 'ForgotPassword')
					self::updateForgotPassword($instance->id);
				if($record[0] == 'Customer Portal - ForgotPassword')
					self::updateCPForgotPassword($instance->id);
			}
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
	public function updateCPForgotPassword($id){
		global $log,$adb,$root_directory;
		$log->debug("Entering YetiForceUpdate::updateForgotPassword(".$id.") method ...");
		if(!$root_directory)
			$root_directory = getcwd();
		$fileName = $root_directory.'/api/yetiportal.php';
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
		foreach($this->dropTablePicklist AS $name=>$none){
			$adb->query("DROP TABLE IF EXISTS `vtiger_".$name."`;");
			$adb->query("DROP TABLE IF EXISTS `vtiger_".$name."_seq`;");
		}
		$log->debug("Exiting YetiForceUpdate::dropTable() method ...");
	}
	
	public function databaseData(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::databaseData() method ...");
		$adb->query('UPDATE vtiger_eventhandlers SET handler_path = "include/events/VTEntityDelta.php" WHERE handler_path = "data/VTEntityDelta.php";');
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
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,2,'LBL_TREES_MANAGER',NULL,'LBL_TREES_MANAGER_DESCRIPTION','index.php?module=TreesManager&parent=Settings&view=List',15,0,0);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_MODTRACKER_SETTINGS'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,2,'LBL_MODTRACKER_SETTINGS',NULL,'LBL_MODTRACKER_SETTINGS_DESCRIPTION','index.php?module=ModTracker&parent=Settings&view=List','16','0','0');");
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
		$moduleInstance = Vtiger_Module::getInstance('CallHistory');
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ?;", array(getTabid('Contacts'), getTabid('CallHistory')));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('Contacts');
			$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ?;", array(getTabid('Accounts'), getTabid('CallHistory')));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('Accounts');
			$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ?;", array(getTabid('Leads'), getTabid('CallHistory')));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('Leads');
			$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ?;", array(getTabid('Vendors'), getTabid('CallHistory')));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('Vendors');
			$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ?;", array(getTabid('OSSEmployees'), getTabid('CallHistory')));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('OSSEmployees');
			$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ?;", array(getTabid('Potentials'), getTabid('CallHistory')));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('Potentials');
			$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_relatedlists` WHERE tabid = ? AND related_tabid = ?;", array(getTabid('HelpDesk'), getTabid('CallHistory')));
		if($adb->num_rows($result) == 0){
			$targetModule = Vtiger_Module::getInstance('HelpDesk');
			$targetModule->setRelatedList($moduleInstance, 'CallHistory', array(),'get_dependents_list');
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_links` WHERE linklabel = ?;", array('Employees Time Control'));
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_links");
			$adb->query("insert  into `vtiger_links`(`linkid`,`tabid`,`linktype`,`linklabel`,`linkurl`,`linkicon`,`sequence`,`handler_path`,`handler_class`,`handler`) values (".$lastId.",".getTabid('Home').",'DASHBOARDWIDGET','Employees Time Control','index.php?module=OSSEmployees&view=ShowWidget&name=TimeControl','',12,NULL,NULL,NULL);");
			$lastId = $adb->getUniqueID("vtiger_links");
			$adb->query("insert  into `vtiger_links`(`linkid`,`tabid`,`linktype`,`linklabel`,`linkurl`,`linkicon`,`sequence`,`handler_path`,`handler_class`,`handler`) values (".$lastId.",".getTabid('OSSEmployees').",'DASHBOARDWIDGET','Employees Time Control','index.php?module=OSSEmployees&view=ShowWidget&name=TimeControl','',1,NULL,NULL,NULL);");			
		}
		/*
		$dbconfig = vglobal('dbconfig');
		$result = $adb->pquery("SELECT CONSTRAINT_NAME AS keyname FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'vtiger_module_dashboard_widgets' AND constraint_type = 'FOREIGN KEY' AND TABLE_SCHEMA = ?;",array($dbconfig['db_name']));
		if($adb->num_rows($result) > 0){
			$adb->pquery("ALTER TABLE `vtiger_module_dashboard_widgets` DROP FOREIGN KEY ?;",array($adb->query_result_raw($result, 0, 'keyname')));
		}
		$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` ADD CONSTRAINT `vtiger_module_dashboard_widgets_ibfk_1` FOREIGN KEY (`templateid`) REFERENCES `vtiger_module_dashboard`(`id`) ON DELETE CASCADE;");
		*/
		$adb->query("ALTER TABLE `vtiger_crmentity` CHANGE `was_read` `was_read` tinyint(1)   NULL DEFAULT 0 after `inheritsharing` ;");
		
		$adb->query("UPDATE vtiger_projecttype SET `projecttype` = 'PLL_INTERNAL' WHERE `projecttype` = 'administrative';");
		$adb->query("UPDATE vtiger_projecttype SET `projecttype` = 'PLL_EXTERNAL' WHERE `projecttype` = 'operative';");
		$adb->query("UPDATE vtiger_projecttype SET `projecttype` = 'PLL_COMMON' WHERE `projecttype` = 'other';");
		self::changeFieldOnTree();
		$log->debug("Exiting YetiForceUpdate::databaseData() method ...");
	}
	
	public function foldersToTree(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::foldersToTree() method ...");
		
		$sql = 'INSERT INTO vtiger_trees_templates(`name`, `module`, `access`) VALUES (?,?,?)';
		$params = array('System', getTabid('Documents'), 0);
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
	function updateFiles() {
		$config = '
	
//Pop-up window type with record list  1 - Normal , 2 - Expanded search
$popupType = 1;

//Minimum cron frequency [min]
$MINIMUM_CRON_FREQUENCY = 1;';
		file_put_contents( 'config/config.inc.php', $config, FILE_APPEND );
	}

	public function changeFieldOnTree(){
		global $log,$adb;
			$log->debug("Entering YetiForceUpdate::changeFieldOnTree() method ...");
		$tab = array('vtiger_products'=>'pscategory',
					'vtiger_service'=>'pscategory',	
					'vtiger_ossoutsourcedservices'=>'pscategory',	
					'vtiger_osssoldservices'=>'pscategory',	
					'vtiger_outsourcedproducts'=>'pscategory'	
					);
		$templateNames = array('pscategory'=>'Category');
		$this->dropTablePicklist = $templateNames;
		foreach($tab as $tablename=>$columnname){
			$result = $adb->pquery("SELECT * FROM `vtiger_field` WHERE `columnname` = ? AND `tablename` = ?;", array($columnname, $tablename));
			$log->debug("Entering YetiForceUpdate::changeFieldOnTree result(".$adb->num_rows($result).") method ...");
			if($adb->num_rows($result) == 1){
				$uitype = $adb->query_result_raw($result, 0, 'uitype');
				$moduleId = $adb->query_result_raw($result, 0, 'tabid');
				if($uitype != 302){
					$dependency = self::getPicklistDependency($columnname, $moduleId);
					$stem = self::getPicklistValues($columnname);
					$k=1;$c=1;
					$log->debug("Entering YetiForceUpdate::changeFieldOnTree kk(".$k.") method ...");
					$tree = array();
					foreach($stem AS $storey){
						$children = array();
						$log->debug("Entering YetiForceUpdateeee::storey(".$storey.") method ...");
						if(array_key_exists($storey,$dependency)){
							$branches = Zend_Json::decode(decode_html($dependency[$storey]['targetvalues']));
							$c = $k+1;
							foreach($branches as $branch){
								$log->debug("Entering YetiForceUpdateeee::branch(".$branch.") method ...");
								$children[] = array('data'=>$branch, 'attr'=>array('id'=>$c));
								$c++;
							}
						}
						$tree[] = array('data'=>$storey, 'attr'=>array('id'=>$k), 'children'=>$children);
						$k = ($c==$k)?$k+1:$c+1;
						$c = $k;
					}
					$templateId = self::createTree($moduleId, $templateNames[$columnname], $tree);
					if($templateId){
						self::updateRecords($tablename, $columnname, $tree);
						$adb->pquery("UPDATE `vtiger_field` SET `fieldparams` = ?, `uitype` = ? WHERE `columnname` = ? AND `tablename` = ?;", array($templateId, 302, $columnname, $tablename));
					}
				}
			}
		}
		$fieldsToDelete = array(
		'Products'=>array('pssubcategory'),
		'Services'=>array('pssubcategory'),
		'OSSOutsourcedServices'=>array('pssubcategory'),
		'OSSSoldServices'=>array('pssubcategory'),
		'OutsourcedProducts'=>array('pssubcategory'),
		);
		self::deleteFields($fieldsToDelete);
	}
	public function getPicklistValues($columnName){
		global $log,$adb;
			$log->debug("Entering YetiForceUpdate::getPicklistValues(".$columnName.") method ...");
		$sequence = 'sequence';
		if($columnName == 'pscategory')
			$sequence = 'sortorderid';
		$result = $adb->query("SELECT * FROM `vtiger_".$columnName."` ORDER BY `".$sequence."`;");
		$fieldsResult = array();
		for($i=0;$i<$adb->num_rows($result);$i++){
			$fieldsResult[] = $adb->query_result($result, $i, $columnName);
		}
		$log->debug("Exiting YetiForceUpdate::getPicklistValues() method ...");
		return $fieldsResult;
	}
	public function getPicklistDependency($columnname, $moduleId){
		global $log,$adb;
			$log->debug("Entering YetiForceUpdate::getPicklistDependency(".$columnname.", ".$moduleId.") method ...");
		$dependencyResult = $adb->pquery("SELECT * FROM `vtiger_picklist_dependency` WHERE `sourcefield` = ? AND `tabid` = ?;", array($columnname, $moduleId));
		$fieldsResult = array();
		for($i=0;$i<$adb->num_rows($dependencyResult);$i++){
			$name = $adb->query_result($dependencyResult, $i, 'sourcevalue');
			$fieldsResult[$name] = $adb->query_result_rowdata($dependencyResult, $i);
		}
		$log->debug("Exiting YetiForceUpdate::getPicklistDependency() method ...");
		return $fieldsResult;
	}
	public function updateRecords($tablename, $columnName, $tree ){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::updateRecords(".$tablename.", ".$columnName.", ".$tree.") method ...");
		foreach($tree AS $treeElement){
			$query = 'UPDATE '.$tablename.' SET '.$columnName.' = ? WHERE '.$columnName.' = ?';
			$params = array('T'.$treeElement['attr']['id'], $treeElement['data']);
			$adb->pquery($query, $params);
			if(($treeElement['children'])){
				$this->updateRecords( $tablename, $columnName, $treeElement['children']);
			}
		}
		$log->debug("Exiting YetiForceUpdate::updateRecords() method ...");
	}
	public function createTree($moduleId, $nameTemplate, $tree){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::createTree(".$moduleId.", ".$nameTemplate.", ".$tree.") method ...");
		vimport('~~modules/Settings/TreesManager/models/Record.php');
		
		$sql = 'INSERT INTO vtiger_trees_templates(`name`, `module`, `access`) VALUES (?,?,?)';
		$params = array($nameTemplate, $moduleId, 0);
		$adb->pquery($sql, $params);
		$templateId = $adb->getLastInsertID();
		
		$recordModel = new Settings_TreesManager_Record_Model();
		$recordModel->set('name', $nameTemplate);
		$recordModel->set('module', $moduleId);
		$recordModel->set('tree', $tree);
		$recordModel->set('templateid', $templateId);
		$recordModel->save();

		$log->debug("Exiting YetiForceUpdate::createTree() method ...");
		return $templateId;
	}
	public function deleteFields($fieldsToDelete){
		global $log;
		$log->debug("Entering YetiForceUpdate::deleteFields() method ...");
		require_once('includes/main/WebUI.php');
		$adb = PearDatabase::getInstance();
		foreach($fieldsToDelete AS $fld_module=>$columnnames){
			$moduleId = getTabid($fld_module);
			foreach($columnnames AS $columnname){
				$fieldquery = 'select * from vtiger_field where tabid = ? AND columnname = ?';
				$res = $adb->pquery($fieldquery,array($moduleId,$columnname));
				$id = $adb->query_result($res,0,'fieldid');
				if(empty($id))
					continue;
				$typeofdata = $adb->query_result($res,0,'typeofdata');
				$fieldname = $adb->query_result($res,0,'fieldname');
				$oldfieldlabel = $adb->query_result($res,0,'fieldlabel');
				$tablename = $adb->query_result($res,0,'tablename');
				$uitype = $adb->query_result($res,0,'uitype');
				$colName = $adb->query_result($res,0,'columnname');
				$tablica = $adb->query_result($res,0,'tablename');
				$fieldtype =  explode("~",$typeofdata);

				//Deleting the CustomField from the Custom Field Table
				$query='delete from vtiger_field where fieldid = ? and vtiger_field.presence in (0,2)';
				$adb->pquery($query, array($id));

				//Deleting from vtiger_profile2field table
				$query='delete from vtiger_profile2field where fieldid=?';
				$adb->pquery($query, array($id));

				//Deleting from vtiger_def_org_field table
				$query='delete from vtiger_def_org_field where fieldid=?';
				$adb->pquery($query, array($id));

				$focus = CRMEntity::getInstance($fld_module);

				$deletecolumnname =$tablename .":". $columnname .":".$fieldname.":".$fld_module. "_" .str_replace(" ","_",$oldfieldlabel).":".$fieldtype[0];
				$column_cvstdfilter = 	$tablename .":". $columnname .":".$fieldname.":".$fld_module. "_" .str_replace(" ","_",$oldfieldlabel);
				$select_columnname = $tablename.":".$columnname .":".$fld_module. "_" . str_replace(" ","_",$oldfieldlabel).":".$fieldname.":".$fieldtype[0];
				$reportsummary_column = $tablename.":".$columnname.":".str_replace(" ","_",$oldfieldlabel);

				$dbquery = 'alter table '. $adb->sql_escape_string($tablica).' drop column '. $adb->sql_escape_string($colName);
				$adb->pquery($dbquery, array());

				//To remove customfield entry from vtiger_field table
				$dbquery = 'delete from vtiger_field where columnname= ? and fieldid=? and vtiger_field.presence in (0,2)';
				$adb->pquery($dbquery, array($colName, $id));
				//we have to remove the entries in customview and report related tables which have this field ($colName)
				$adb->pquery("delete from vtiger_cvcolumnlist where columnname = ? ", array($deletecolumnname));
				$adb->pquery("delete from vtiger_cvstdfilter where columnname = ?", array($column_cvstdfilter));
				$adb->pquery("delete from vtiger_cvadvfilter where columnname = ?", array($deletecolumnname));
				$adb->pquery("delete from vtiger_selectcolumn where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_relcriteria where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_reportsortcol where columnname = ?", array($select_columnname));
				$adb->pquery("delete from vtiger_reportdatefilter where datecolumnname = ?", array($column_cvstdfilter));
				$adb->pquery("delete from vtiger_reportsummary where columnname like ?", array('%'.$reportsummary_column.'%'));
				$adb->pquery("delete from vtiger_fieldmodulerel where fieldid = ?", array($id));

				//Deleting from convert lead mapping vtiger_table- Jaguar
				if($fld_module=="Leads") {
					$deletequery = 'delete from vtiger_convertleadmapping where leadfid=?';
					$adb->pquery($deletequery, array($id));
				}elseif($fld_module=="Accounts" || $fld_module=="Contacts" || $fld_module=="Potentials") {
					$map_del_id = array("Accounts"=>"accountfid","Contacts"=>"contactfid","Potentials"=>"potentialfid");
					$map_del_q = "update vtiger_convertleadmapping set ".$map_del_id[$fld_module]."=0 where ".$map_del_id[$fld_module]."=?";
					$adb->pquery($map_del_q, array($id));
				}

				//HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
				if($uitype == 15) {
					$deltablequery = 'drop table IF EXISTS vtiger_'.$adb->sql_escape_string($colName);
					$adb->pquery($deltablequery, array());
					$deltablequeryseq = 'drop table IF EXISTS vtiger_'.$adb->sql_escape_string($colName).'_seq';
					$adb->pquery($deltablequeryseq, array());		
					$adb->pquery("delete from  vtiger_picklist_dependency where sourcefield=? or targetfield=?", array($colName,$colName));
					
					$fieldquery = 'select * from vtiger_picklist where name = ?';
					$res = $adb->pquery($fieldquery,array($columnname));
					$picklistid = $adb->query_result($res,0,'picklistid');
					$adb->pquery("delete from vtiger_picklist where name = ?", array($columnname));
					$adb->pquery("delete from vtiger_role2picklist where picklistid = ?", array($picklistid));
				}
			}
			
		}
		$log->debug("Exiting YetiForceUpdate::deleteFields() method ...");
	}
	public function addFields(){
		$adb = PearDatabase::getInstance();
		include_once('vtlib/Vtiger/Module.php'); 
		$sql = "SELECT tabid,name FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name not in ('SMSNotifier','ModComments','PBXManager','Events','Emails','');";
		$result = $adb->query($sql,true);
		$Num = $adb->num_rows($result);
		for($i = 0; $i < $Num; $i++){
			$name = $adb->query_result($result, $i, 'name');
			$tabid = $adb->query_result($result, $i, 'tabid');
			$row = $adb->query_result_rowdata($result, $i); 
			$result2 = $adb->pquery('SELECT * FROM vtiger_field WHERE tabid = ? AND block <> ? ORDER BY block, sequence ASC',array($tabid,0));
			$block = $adb->query_result_raw($result2, 0, 'block');

			$moduleInstance = Vtiger_Module::getInstance($name);
			$blockInstance = Vtiger_Block::getInstance($block,$moduleInstance);
			$fieldInstance = new Vtiger_Field(); 
			$fieldInstance->name = 'was_read'; 
			$fieldInstance->table = 'vtiger_crmentity'; 
			$fieldInstance->label = 'Was read'; 
			$fieldInstance->column = 'was_read'; 
			$fieldInstance->columntype = 'tinyint(1)'; 
			$fieldInstance->uitype = 56;
			$fieldInstance->typeofdata = 'C~O'; 
			$blockInstance->addField($fieldInstance); 
		}
	}
	function recurseCopy($src,$dst) {
		global $root_directory;
		if(!file_exists( $src ) )
			return;
		$dir = opendir($src); 
		@mkdir($root_directory.$dst); 
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					$this->recurseCopy($src . '/' . $file,$dst . '/' . $file); 
				} else {
					copy($root_directory.$src . '/' . $file,$root_directory.$dst . '/' . $file);
					unlink($root_directory.$src . '/' . $file);
				}
			} 
		} 
		closedir($dir); 
		rmdir($src);
	}
}