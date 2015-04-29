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
	var $filesToDeleteNew = array(
		'logs',
		'includes',
		'session',
		'test',
		//'modules/OSSMail/roundcube/program/steps/mail/autocomplete_org.inc',
		'include/events/runtime/cache',
		'include/events/runtime/Cache.php',
		'languages/pt_br/Migration.php',
		'languages/pt_br/EmailTemplates.php',
		'include/events/runtime/cache/Connector.php',
		'include/events/runtime/cache/Connectors.php',
		'modules/Calendar/activityTypes',
		'layouts/vlayout/modules/Events/uitypes/Multireference.tpl',
		'layouts/vlayout/modules/Events/uitypes/MultireferenceDetailView.tpl',
		'modules/Calendar/uitypes/Multireference.php',
		'modules/Events/uitypes/Multireference.php',
		'layouts/vlayout/skins/images/Accounts256.png',
		'layouts/vlayout/skins/images/Assets256.png',
		'layouts/vlayout/skins/images/Calculations256.png',
		'layouts/vlayout/skins/images/Calendar256.png',
		'layouts/vlayout/skins/images/CallHistory256.png',
		'layouts/vlayout/skins/images/Campaigns256.png',
		'layouts/vlayout/skins/images/Contacts256.png',
		'layouts/vlayout/skins/images/Documents256.png',
		'layouts/vlayout/skins/images/Emails256.png',
		'layouts/vlayout/skins/images/Faq256.png',
		'layouts/vlayout/skins/images/Google256.png',
		'layouts/vlayout/skins/images/HelpDesk256.png',
		'layouts/vlayout/skins/images/Invoice256.png',
		'layouts/vlayout/skins/images/Leads256.png',
		'layouts/vlayout/skins/images/ModComments256.png',
		'layouts/vlayout/skins/images/OSSCosts256.png',
		'layouts/vlayout/skins/images/OSSDocumentControl256.png',
		'layouts/vlayout/skins/images/OSSEmployees256.png',
		'layouts/vlayout/skins/images/OSSMail256.png',
		'layouts/vlayout/skins/images/OSSMailScanner256.png',
		'layouts/vlayout/skins/images/OSSMailTemplates256.png',
		'layouts/vlayout/skins/images/OSSMailView256.png',
		'layouts/vlayout/skins/images/OSSMenuManager256.png',
		'layouts/vlayout/skins/images/OSSOutsourcedServices256.png',
		'layouts/vlayout/skins/images/OSSPasswords256.png',
		'layouts/vlayout/skins/images/OSSPdf256.png',
		'layouts/vlayout/skins/images/OSSProjectTemplates256.png',
		'layouts/vlayout/skins/images/OSSSoldServices256.png',
		'layouts/vlayout/skins/images/OSSTimeControl256.png',
		'layouts/vlayout/skins/images/OutsourcedProducts256.png',
		'layouts/vlayout/skins/images/Password256.png',
		'layouts/vlayout/skins/images/PBXManager256.png',
		'layouts/vlayout/skins/images/Potentials256.png',
		'layouts/vlayout/skins/images/PriceBooks256.png',
		'layouts/vlayout/skins/images/Products256.png',
		'layouts/vlayout/skins/images/Project256.png',
		'layouts/vlayout/skins/images/ProjectMilestone256.png',
		'layouts/vlayout/skins/images/ProjectTask256.png',
		'layouts/vlayout/skins/images/PurchaseOrder256.png',
		'layouts/vlayout/skins/images/Quotes256.png',
		'layouts/vlayout/skins/images/RecycleBin256.png',
		'layouts/vlayout/skins/images/Reports256.png',
		'layouts/vlayout/skins/images/Rss256.png',
		'layouts/vlayout/skins/images/SalesOrder256.png',
		'layouts/vlayout/skins/images/ServiceContracts256.png',
		'layouts/vlayout/skins/images/Services256.png',
		'layouts/vlayout/skins/images/SMSNotifier256.png',
		'layouts/vlayout/skins/images/Vendors256.png',
		'layouts/vlayout/modules/Vtiger/browsercompatibility/hourGlass.png',
		'layouts/vlayout/modules/Vtiger/browsercompatibility/vtiger_logo.png',
		'modules/Settings/Leads/views/ConvertToAccount.php',
		'layouts/vlayout/modules/Settings/Leads/ConvertToAccount.tpl',
		'libraries/jquery/jstree/jquery.hotkeys.js',
		'modules/Vtiger/models/MenuStructure.php',
		'layouts/vlayout/modules/Settings/Leads/resources/ConvertToAccount.js',
		'modules/CallHistory/schema.xml',
		'modules/Users/views/Colors.php',
		'libraries/html5shim/html5.js',
		'layouts/vlayout/skins/images/OSSMenuManager128.png',
		'layouts/vlayout/skins/images/OSSMenuManager48.png',
		'layouts/vlayout/skins/images/OSSMenuManager64.png',
		'libraries/jquery/jstree/themes/default/style.min.css',
		'libraries/jquery/jstree3/themes/default/style.css',
		'libraries/jquery/jstree3/themes/default/style.min.css',
		'libraries/jquery/jstree3/themes/default/throbber.gif',
		'libraries/jquery/jstree/jquery.jstree.js',
		'libraries/jquery/jstree/themes/apple/bg.jpg',
		'libraries/jquery/jstree/themes/apple/d.png',
		'libraries/jquery/jstree/themes/apple/dot_for_ie.gif',
		'libraries/jquery/jstree/themes/apple/style.css',
		'libraries/jquery/jstree/themes/apple/throbber.gif',
		'libraries/jquery/jstree/themes/classic/d.gif',
		'libraries/jquery/jstree/themes/classic/d.png',
		'libraries/jquery/jstree/themes/classic/dot_for_ie.gif',
		'libraries/jquery/jstree/themes/classic/style.css',
		'libraries/jquery/jstree/themes/classic/throbber.gif',
		'libraries/jquery/jstree/themes/default-rtl/d.gif',
		'libraries/jquery/jstree/themes/default-rtl/d.png',
		'libraries/jquery/jstree/themes/default-rtl/dots.gif',
		'libraries/jquery/jstree/themes/default-rtl/style.css',
		'libraries/jquery/jstree/themes/default-rtl/throbber.gif',
		'libraries/jquery/jstree/themes/default/d.gif',
		'libraries/jquery/jstree/themes/default/d.png',
		'libraries/jquery/jstree3',
		'languages/de_de/Settings/MenuEditor.php',
		'languages/en_us/Settings/MenuEditor.php',
		'languages/pt_br/Settings/MenuEditor.php',
		'languages/ru_ru/Settings/MenuEditor.php',
		'languages/pl_pl/Settings/MenuEditor.php',
		'layouts/vlayout/modules/Settings/MenuEditor/Color.tpl',
		'layouts/vlayout/modules/Settings/MenuEditor/resources/MenuEditor.js',
		'modules/Settings/MenuEditor/actions/SaveAjax.php',
		'modules/Settings/MenuEditor/models/Module.php',
		'modules/Settings/MenuEditor/views/Colors.php',
		'modules/Settings/MenuEditor/',
		'modules/Settings/OSSMenuManager/',
		'layouts/vlayout/modules/Settings/MenuEditor/',
		'modules/Settings/Leads/models/ConvertToAccount.php',
		'modules/Settings/MarketingProcesses/actions/Save.php',
		'layouts/vlayout/modules/Settings/SalesProcesses/Configuration.tpl',
		'layouts/vlayout/modules/Settings/SalesProcesses/resources/SalesProcesses.js',
		'modules/Settings/SalesProcesses/actions/SaveProcess.php',
		'modules/Settings/Leads/actions/ConvertToAccountSave.php',
		'modules/Settings/SalesProcesses/views/Configuration.php',
		'layouts/vlayout/modules/HelpDesk/dashboards/OpenTicketsContents.tpl',
		'libraries/Smarty/',
		'modules/Potentials/actions/SaveAjax.php',
		'modules/Potentials/actions/Save.php',
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
		$this->updateConfig();
		$this->databaseStructureExceptDeletedTables();
		$this->databaseData();
		$this->specialScripta();
		$this->changeCalendarRelationships();
		$this->addRelatedModules();
		$fieldsToDelete = [
			'Users'=>['signature','hidecompletedevents'],
			'Potentials'=>['contact_id'],
		];
		$this->deleteFields($fieldsToDelete);
		$this->removeModules();
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
				  `value` text,
				  UNIQUE KEY `type` (`type`,`name`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$result = $adb->query("SHOW INDEX  FROM `yetiforce_mail_config` WHERE COLUMN_NAME = 'type';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE yetiforce_mail_config CHANGE `value` `value` TEXT NULL, ADD UNIQUE INDEX (`type`, `name`);");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_mail_quantities` (
				  `userid` int(10) unsigned NOT NULL,
				  `num` int(10) unsigned DEFAULT '0',
				  `status` tinyint(1) DEFAULT '0',
				  PRIMARY KEY (`userid`),
				  CONSTRAINT `yetiforce_mail_quantities_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `roundcube_users` (`user_id`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_support_processes` (
					`id` int(11) NOT NULL,
					`ticket_status_indicate_closing` varchar(255) NOT NULL
				  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
				
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_users` LIKE 'emailoptout';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_users` ADD COLUMN `emailoptout` varchar(3) NOT NULL DEFAULT '1' after `is_owner` ;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_groups` LIKE 'color';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_groups` ADD COLUMN `color` varchar(25) NULL DEFAULT '#E6FAD8' after `description` ;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_ticketpriorities` LIKE 'color';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_ticketpriorities` ADD COLUMN `color` varchar(25) NULL DEFAULT '	#E6FAD8' after `sortorderid` ;");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_realization_process`(
					`module_id` int(11) NOT NULL  , 
					`status_indicate_closing` varchar(255) COLLATE utf8_general_ci NULL  
				) ENGINE=InnoDB DEFAULT CHARSET='utf8';");
				
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_sales_stage` LIKE 'color';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_sales_stage` ADD COLUMN `color` varchar(25) DEFAULT '#E6FAD8' ;");
		}	
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_leadstatus` LIKE 'color';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_leadstatus` ADD COLUMN `color` varchar(25) DEFAULT '#E6FAD8' ;");
		}	
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_ticketstatus` LIKE 'color';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_ticketstatus` ADD COLUMN `color` varchar(25) DEFAULT '#E6FAD8' ;");
		}	
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_projectstatus` LIKE 'color';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_projectstatus` ADD COLUMN `color` varchar(25) DEFAULT '#E6FAD8' ;");
		}	

		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_menu` (
					  `id` int(19) unsigned NOT NULL AUTO_INCREMENT,
					  `role` int(19) DEFAULT NULL,
					  `parentid` int(19) DEFAULT '0',
					  `type` tinyint(1) DEFAULT NULL,
					  `sequence` int(3) DEFAULT NULL,
					  `module` int(19) DEFAULT NULL,
					  `label` varchar(100) DEFAULT NULL,
					  `newwindow` tinyint(1) DEFAULT '0',
					  `dataurl` text,
					  `showicon` tinyint(1) DEFAULT '0',
					  `icon` varchar(255) DEFAULT NULL,
					  `sizeicon` varchar(255) DEFAULT NULL,
					  `hotkey` varchar(30) DEFAULT NULL,
					  PRIMARY KEY (`id`),
					  KEY `parent_id` (`parentid`),
					  KEY `role` (`role`),
					  KEY `module` (`module`),
					  CONSTRAINT `yetiforce_menu_ibfk_1` FOREIGN KEY (`module`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_marketing_processes` (
					  `module_id` int(11) NOT NULL,
					  `data` varchar(255) DEFAULT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_auth` (
					  `type` varchar(20) DEFAULT NULL,
					  `param` varchar(20) DEFAULT NULL,
					  `value` text,
					  UNIQUE KEY `type` (`type`,`param`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
					
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_publicholiday` LIKE 'holidaytype';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_publicholiday` ADD COLUMN `holidaytype` varchar(25) DEFAULT NULL COMMENT 'type of holiday' ;");
		}	
		$adb->query("ALTER TABLE `vtiger_users` CHANGE `user_name` `user_name` varchar(32) NULL after `id`;");
		
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_group2modules` (
				  `groupid` int(19) NOT NULL,
				  `tabid` int(19) NOT NULL,
				  KEY `groupid` (`groupid`),
				  KEY `tabid` (`tabid`),
				  CONSTRAINT `vtiger_group2modules_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `vtiger_groups` (`groupid`) ON DELETE CASCADE,
				  CONSTRAINT `vtiger_group2modules_ibfk_2` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
				
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_groups` LIKE 'modules';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_groups` ADD COLUMN `modules` varchar(255) NULL after `color`;");
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
		$settings_field[] = array('LBL_PROCESSES','LBL_SUPPORT_PROCESSES',NULL,'LBL_SUPPORT_PROCESSES_DESCRIPTION','index.php?module=SupportProcesses&view=Index&parent=Settings','3','0','0');
		$settings_field[] = array('LBL_STUDIO','LBL_COLORS',NULL,'LBL_COLORS_DESCRIPTION','index.php?module=Users&parent=Settings&view=Colors',19,0,0);
		$settings_field[] = array('LBL_PROCESSES','LBL_REALIZATION_PROCESSES','','LBL_REALIZATION_PROCESSES_DESCRIPTION','index.php?module=RealizationProcesses&view=Index&parent=Settings',4,0,0);
		$settings_field[] = array('LBL_PROCESSES','LBL_MARKETING_PROCESSES','','LBL_MARKETING_PROCESSES_DESCRIPTION','index.php?module=MarketingProcesses&view=Index&parent=Settings',4,0,0);
		$settings_field[] = array('LBL_PROCESSES','LBL_FINANCIAL_PROCESSES','','LBL_FINANCIAL_PROCESSES_DESCRIPTION','index.php?module=FinancialProcesses&view=Index&parent=Settings',4,0,0);
		$settings_field[] = array('LBL_USER_MANAGEMENT','LBL_AUTHORIZATION',NULL,'LBL_AUTHORIZATION_DESCRIPTION','index.php?module=Users&view=Auth&parent=Settings',8,0,0);
		
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
	function specialScripta() {
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::specialScripta() method ...");
		$actions = [17=>'MassEdit',18=>'MassDelete',19=>'MassAddComment',20=>'MassComposeEmail',21=>'MassSendSMS',22=>'MassTransferOwnership',23=>'MassMoveDocuments'];
		foreach ($actions as $key =>$action) {
			$result = $adb->pquery('SELECT actionid FROM vtiger_actionmapping WHERE actionname=?;',[$action]);
			if($adb->num_rows($result) == 0){
				$adb->pquery("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (?, ?,'0');",[$key,$action]);
			}
			$sql = "SELECT tabid, name  FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name not in ('SMSNotifier','ModComments','PBXManager','Events','Emails','');";
			$result = $adb->query($sql);

			$resultP = $adb->query("SELECT profileid FROM vtiger_profile;");
			for($i = 0; $i < $adb->num_rows($resultP); $i++){
				$profileId = $adb->query_result_raw($resultP, $i, 'profileid');
				for($k = 0; $k < $adb->num_rows($result); $k++){
					$insert = false;
					$row = $adb->query_result_rowdata($result, $k);
					$tabid = $row['tabid'];
					if( $key == 23 && $row['name'] == 'Documents'){
						$insert = true;
					}
					if( ($key == 20 || $key == 21 || $key == 22) && in_array($row['name'] , ['Accounts','Contacts','Leads','Vendors']) ){
						$insert = true;
					}
					if( !($key == 22 && $row['name'] == 'PriceBooks') && $key != 23 && $key != 20 && $key != 21){
						$insert = true;
					}
					$resultC = $adb->pquery("SELECT activityid FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=? ;",[$profileId, $tabid, $key]);
					if($insert && $adb->num_rows($resultC) == 0){
						$adb->pquery("INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)", array($profileId, $tabid, $key, 0));
					}
				}
			}
		}
		$actions = [24=>'ReadRecord',25=>'WorkflowTrigger'];
		foreach ($actions as $key =>$action) {
			$result = $adb->pquery('SELECT actionid FROM vtiger_actionmapping WHERE actionname=?;',[$action]);
			if($adb->num_rows($result) == 0){
				$adb->pquery("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (?, ?,'0');",[$key,$action]);
			}
			$sql = "SELECT tabid, name  FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name not in ('SMSNotifier','ModComments','PBXManager','Events','Emails','CallHistory','OSSMailView','');";
			$result = $adb->query($sql);

			$resultP = $adb->query("SELECT profileid FROM vtiger_profile;");
			for($i = 0; $i < $adb->num_rows($resultP); $i++){
				$profileId = $adb->query_result_raw($resultP, $i, 'profileid');
				for($k = 0; $k < $adb->num_rows($result); $k++){
					$row = $adb->query_result_rowdata($result, $k);
					$tabid = $row['tabid'];
					$resultC = $adb->pquery("SELECT activityid FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=? ;",[$profileId, $tabid, $key]);
					if($adb->num_rows($resultC) == 0){
						$adb->pquery("INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)", array($profileId, $tabid, $key, 0));
					}
				}
			}
		}
		$log->debug("Exiting YetiForceUpdate::specialScripta() method ...");
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
		$adb->query("UPDATE vtiger_eventhandlers_seq SET `id` = (SELECT MAX(eventhandler_id) FROM `vtiger_eventhandlers`);");
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
			$adb->pquery("insert  into `yetiforce_mail_config`(`type`,`name`,`value`) values ('autologin','autologinActive',?);", array('false'));
		}
		$result = $adb->pquery('SELECT * FROM `yetiforce_mail_config` WHERE type = ? AND name = ?;', array('signature','signature'));
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `yetiforce_mail_config`(`type`,`name`,`value`) values ('signature','signature','');");
		}
		$result = $adb->pquery('SELECT * FROM `yetiforce_mail_config` WHERE type = ? AND name = ?;', array('signature','addSignature'));
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `yetiforce_mail_config`(`type`,`name`,`value`) values ('signature','addSignature','false');");
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
		$adb->pquery('UPDATE `vtiger_field` SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?  AND tabid =?;', 
				[3,'vtiger_activity','date_start',getTabid('Calendar')]);
		$adb->pquery('UPDATE `vtiger_field` SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?  AND tabid =?;', 
				[10,'vtiger_activity','date_start',getTabid('Events')]);
		
		$adb->pquery('UPDATE `vtiger_field` SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid =?;', 
				[4,'vtiger_activity','due_date',getTabid('Calendar')]);
		$adb->pquery('UPDATE `vtiger_field` SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid =?;', 
				[11,'vtiger_activity','due_date',getTabid('Events')]);
		$adb->pquery('UPDATE `vtiger_field` SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[1,'vtiger_contactdetails','firstname']);
		$adb->pquery('UPDATE `vtiger_field` SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[5,'vtiger_contactdetails','parentid']);
		$adb->pquery('UPDATE `vtiger_field` SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[3,'vtiger_contactdetails','email']);
		$adb->pquery('UPDATE `vtiger_field` SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[6,'vtiger_contactdetails','smownerid']);
		$result = $adb->pquery("SELECT * FROM `vtiger_support_processes`;");
		if($adb->num_rows($result) == 0){		
			$adb->query("insert  into `vtiger_support_processes`(`id`,`ticket_status_indicate_closing`) values (1,'');");
		}
		$result = $adb->pquery('SELECT quickcreatesequence FROM `vtiger_field` WHERE tablename = ? AND columnname = ? AND tabid =?;', array('vtiger_seactivityrel','crmid',getTabid('Calendar')));
		$result2 = $adb->pquery('SELECT quickcreatesequence FROM `vtiger_field` WHERE tablename = ? AND columnname = ? AND tabid =?;', array('vtiger_seactivityrel','crmid',getTabid('Events')));
		
		if($adb->num_rows($result) == 1){
			$quickcreatesequence = $adb->query_result($result, 0, 'quickcreatesequence');
			$adb->pquery('UPDATE `vtiger_field` SET columnname=?,tablename=?,fieldname=?,fieldlabel=?, quickcreate=? WHERE tablename = ? AND columnname = ? ;', 
					['process','vtiger_activity','process','Process','1','vtiger_seactivityrel','crmid']);
			$adb->pquery('UPDATE `vtiger_field` SET columnname=?,tablename=?,fieldname=?,fieldlabel=?, quickcreate=?, uitype=?, quickcreatesequence=?, summaryfield=? WHERE tablename = ? AND columnname = ? AND tabid = ?;', 
					['link','vtiger_activity','link','Relation','2','67',$quickcreatesequence,'1','vtiger_cntactivityrel','contactid',getTabid('Calendar')]);
			$quickcreatesequence = $adb->query_result($result2, 0, 'quickcreatesequence');
			$adb->pquery('UPDATE `vtiger_field` SET columnname=?,tablename=?,fieldname=?,fieldlabel=?, quickcreate=?, uitype=?, quickcreatesequence=?, summaryfield=? WHERE tablename = ? AND columnname = ? AND tabid = ?;', 
				['link','vtiger_activity','link','Relation','2','67',$quickcreatesequence,'1','vtiger_cntactivityrel','contactid',getTabid('Events')]);
		}
		
		$result = $adb->pquery("SELECT * FROM `vtiger_realization_process`;");
		if($adb->num_rows($result) == 0){
			$adb->pquery("INSERT INTO vtiger_realization_process(module_id, status_indicate_closing) VALUES(?,?)", array(getTabid('Project'), ''));
		}
		$adb->pquery("DELETE FROM vtiger_settings_field WHERE name = ?;", array('LBL_CONVERSION_TO_ACCOUNT'));
		$adb->pquery('UPDATE `vtiger_settings_field` SET `name` = ?, `description` = ?, `linkto` = ? WHERE `name` = ? AND `description` = ? ;', ['LBL_MENU_BUILDER','LBL_MENU_BUILDER_DESCRIPTION','index.php?module=Menu&view=Index&parent=Settings','Menu Manager','LBL_MENU_DESC']);
		
		$result = $adb->query("SELECT * FROM `yetiforce_auth`;");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `yetiforce_auth`(`type`,`param`,`value`) values ('ldap','active','false');");
			$adb->query("insert  into `yetiforce_auth`(`type`,`param`,`value`) values ('ldap','server','testlab.local');");
			$adb->query("insert  into `yetiforce_auth`(`type`,`param`,`value`) values ('ldap','port','389');");
			$adb->pquery("insert  into `yetiforce_auth`(`type`,`param`,`value`) values (?,?,?);", ['ldap','users',NULL]);
		}
		$result = $adb->pquery("SELECT * FROM `yetiforce_auth` WHERE `param` = ?;", ['domain']);
		if($adb->num_rows($result) == 0){
			$adb->pquery("insert  into `yetiforce_auth`(`type`,`param`,`value`) values (?,?,?);",['ldap','domain',NULL]);
		}
		
		$result = $adb->pquery("SELECT * FROM `yetiforce_menu`;");
		if($adb->num_rows($result) == 0){
			$menu[] = array(44,0,0,2,1,NULL,'MEN_VIRTUAL_DESK',0,NULL,0,NULL,NULL,"");
			$menu[] = array(45,0,44,0,0,getTabid('Home'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(46,0,44,0,1,getTabid('Calendar'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(47,0,0,2,2,NULL,'MEN_LEADS',0,NULL,0,NULL,NULL,"");
			$menu[] = array(48,0,47,0,0,getTabid('Leads'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(49,0,47,0,1,getTabid('Contacts'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(50,0,47,0,2,getTabid('Vendors'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(51,0,47,0,3,getTabid('Accounts'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(52,0,0,2,3,NULL,'MEN_SALES',0,NULL,0,NULL,NULL,"");
			$menu[] = array(54,0,52,0,0,getTabid('Campaigns'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(55,0,52,0,1,getTabid('Potentials'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(56,0,52,0,2,getTabid('QuotesEnquires'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(57,0,52,0,3,getTabid('RequirementCards'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(58,0,52,0,4,getTabid('Calculations'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(59,0,52,0,5,getTabid('Quotes'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(60,0,52,0,6,getTabid('SalesOrder'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(61,0,52,0,7,getTabid('PurchaseOrder'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(62,0,52,0,8,getTabid('PriceBooks'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(63,0,0,2,5,NULL,'MEN_SUPPORT',0,NULL,0,NULL,NULL,"");
			$menu[] = array(64,0,63,0,0,getTabid('HelpDesk'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(65,0,63,0,1,getTabid('ServiceContracts'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(66,0,63,0,2,getTabid('Faq'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(67,0,0,2,4,NULL,'MEN_PROJECTS',0,NULL,0,NULL,NULL,"");
			$menu[] = array(68,0,67,0,0,getTabid('Project'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(69,0,67,0,1,getTabid('ProjectMilestone'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(70,0,67,0,2,getTabid('ProjectTask'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(71,0,0,2,6,NULL,'MEN_BOOKKEEPING',0,NULL,0,NULL,NULL,"");
			$menu[] = array(72,0,71,0,3,getTabid('PaymentsIn'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(73,0,71,0,2,getTabid('PaymentsOut'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(74,0,71,0,1,getTabid('Invoice'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(75,0,71,0,0,getTabid('OSSCosts'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(76,0,0,2,7,NULL,'MEN_HUMAN_RESOURCES',0,NULL,0,NULL,NULL,"");
			$menu[] = array(77,0,76,0,0,getTabid('OSSEmployees'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(78,0,76,0,1,getTabid('OSSTimeControl'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(79,0,76,0,2,getTabid('HolidaysEntitlement'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(80,0,0,2,8,NULL,'MEN_SECRETARY',0,NULL,0,NULL,NULL,"");
			$menu[] = array(81,0,80,0,0,getTabid('LettersIn'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(82,0,80,0,1,getTabid('LettersOut'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(83,0,80,0,2,getTabid('Reservations'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(84,0,0,2,9,NULL,'MEN_DATABESES',0,NULL,0,NULL,NULL,"");
			$menu[] = array(85,0,84,2,0,NULL,'MEN_PRODUCTBASE',0,NULL,0,NULL,NULL,"");
			$menu[] = array(86,0,84,0,1,getTabid('Products'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(87,0,84,0,2,getTabid('OutsourcedProducts'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(88,0,84,0,3,getTabid('Assets'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(89,0,84,3,4,NULL,NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(90,0,84,2,5,NULL,'MEN_SERVICESBASE',0,NULL,0,NULL,NULL,"");
			$menu[] = array(91,0,84,0,6,getTabid('Services'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(92,0,84,0,7,getTabid('OSSOutsourcedServices'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(93,0,84,0,8,getTabid('OSSSoldServices'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(94,0,84,3,9,NULL,NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(95,0,84,2,10,NULL,'MEN_LISTS',0,NULL,0,NULL,NULL,"");
			$menu[] = array(96,0,84,0,11,getTabid('OSSMailView'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(97,0,84,0,12,getTabid('SMSNotifier'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(98,0,84,0,13,getTabid('PBXManager'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(99,0,84,0,14,getTabid('OSSMailTemplates'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(100,0,84,0,15,getTabid('Documents'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(102,0,84,0,16,getTabid('OSSPdf'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(106,0,84,0,18,getTabid('CallHistory'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(107,0,84,3,19,NULL,NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(108,0,84,0,21,getTabid('NewOrders'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(109,0,84,0,17,getTabid('OSSPasswords'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(110,0,0,2,10,NULL,'MEN_TEAMWORK',0,NULL,0,NULL,NULL,"");
			$menu[] = array(111,0,110,0,0,getTabid('Ideas'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(112,0,0,6,0,getTabid('Home'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(113,0,44,0,2,getTabid('OSSMail'),NULL,0,NULL,0,NULL,NULL,"");
			$menu[] = array(114,0,84,0,20,getTabid('Reports'),NULL,0,NULL,0,NULL,NULL,"");
			foreach($menu AS $m){
				$adb->pquery("insert  into `yetiforce_menu`(`id`,`role`,`parentid`,`type`,`sequence`,`module`,`label`,`newwindow`,`dataurl`,`showicon`,`icon`,`sizeicon`,`hotkey`) values (". generateQuestionMarks($m) .");",array($m));
			}
		}
		$result = $adb->pquery("SELECT * FROM `vtiger_links` WHERE linktype = ? AND linklabel = ? AND tabid = ? ;", ['DASHBOARDWIDGET','Calendar',getTabid('Home')]);
		if($adb->num_rows($result) == 0){
			$instanceModule = Vtiger_Module::getInstance('Home');
			$instanceModule->addLink('DASHBOARDWIDGET', 'Calendar', 'index.php?module=Home&view=ShowWidget&name=Calendar');
		}
		
		$adb->query('DROP TABLE IF EXISTS vtiger_converttoaccount_settings;');
		$adb->query('DROP TABLE IF EXISTS vtiger_marketing_processes;');
		$adb->query('DROP TABLE IF EXISTS vtiger_proc_marketing;');
		$adb->query('DROP TABLE IF EXISTS vtiger_salesprocesses_settings;');
		$adb->pquery('DELETE FROM vtiger_settings_field WHERE name = ?;', array('LBL_MDULES_COLOR_EDITOR'));
		
		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_proc_marketing` (
  `type` varchar(30) DEFAULT NULL,
  `param` varchar(30) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  KEY `type` (`type`,`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_proc_sales` (
  `type` varchar(30) DEFAULT NULL,
  `param` varchar(30) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  KEY `type` (`type`,`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$result = $adb->pquery("SELECT * FROM `yetiforce_proc_marketing` WHERE type = ? AND param = ?;", ['conversion','change_owner']);
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `yetiforce_proc_marketing`(`type`,`param`,`value`) values ('conversion','change_owner','false');");
			$adb->query("insert  into `yetiforce_proc_marketing`(`type`,`param`,`value`) values ('lead','groups','');");
			$adb->query("insert  into `yetiforce_proc_marketing`(`type`,`param`,`value`) values ('lead','status','');");
			$adb->query("insert  into `yetiforce_proc_marketing`(`type`,`param`,`value`) values ('lead','currentuser_status','false');");
		}
		$result = $adb->pquery("SELECT * FROM `yetiforce_proc_sales` WHERE type = ? AND param = ?;", ['popup','limit_product_service']);
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `yetiforce_proc_sales`(`type`,`param`,`value`) values ('popup','limit_product_service','false');");
			$adb->query("insert  into `yetiforce_proc_sales`(`type`,`param`,`value`) values ('popup','update_shared_permissions','false');");
		}
		$adb->pquery('UPDATE `vtiger_settings_field` SET linkto = ? WHERE name = ?;', ['index.php?module=SalesProcesses&view=Index&parent=Settings','LBL_SALES_PROCESSES']);
		$result = $adb->pquery("SELECT * FROM `vtiger_eventhandlers` WHERE event_name = ? AND handler_class = ?;", ['vtiger.entity.link.after','Vtiger_SharingPrivileges_Handler']);
		if($adb->num_rows($result) == 0){
			$em = new VTEventsManager($adb);
			$em->registerHandler('vtiger.entity.link.after', 'modules/Vtiger/handlers/SharingPrivileges.php', 'Vtiger_SharingPrivileges_Handler');
		}
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[2,'vtiger_projecttask','projectid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[4,'vtiger_projecttask','startdate']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tabid = ? AND columnname = ? ;', 
				[6,getTabid('ProjectTask'),'smownerid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[7,'vtiger_projecttask','projecttaskstatus']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[3,'vtiger_projecttask','projectmilestoneid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[5,'vtiger_projecttask','targetenddate']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[1,'vtiger_requirementcards','subject']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[4,'vtiger_requirementcards','requirementcards_status']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[3,'vtiger_requirementcards','potentialid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tabid = ? AND columnname = ? ;', 
				[2,getTabid('RequirementCards'),'smownerid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[5,'vtiger_requirementcards','quotesenquiresid']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[9,'vtiger_leadaddress','phone']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[4,'vtiger_leaddetails','lastname']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[1,'vtiger_leaddetails','company']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[8,'vtiger_leaddetails','email']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[3,'vtiger_leaddetails','leadstatus']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tabid = ? AND columnname = ? ;', 
				[2,getTabid('Leads'),'smownerid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[7,'vtiger_leaddetails','vat_id']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[5,'vtiger_leaddetails','leads_relation']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[6,'vtiger_leaddetails','legal_form']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[3,'vtiger_reservations','reservations_status']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tabid = ? AND columnname = ? ;', 
				[4,getTabid('Reservations'),'smownerid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[6,'vtiger_reservations','date_start']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[5,'vtiger_reservations','time_start']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[7,'vtiger_reservations','time_end']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[8,'vtiger_reservations','due_date']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[2,'vtiger_reservations','type']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tabid = ? AND columnname = ? ;', 
				[9,getTabid('Reservations'),'description']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[1,'vtiger_osspdf','title']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tabid = ? AND columnname = ? ;', 
				[2,getTabid('OSSPdf'),'smownerid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[10,'vtiger_osspdf','osspdf_pdf_format']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[11,'vtiger_osspdf','osspdf_pdf_orientation']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[8,'vtiger_osspdf','osspdf_enable_footer']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[3,'vtiger_osspdf','osspdf_enable_header']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[5,'vtiger_osspdf','height_header']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[9,'vtiger_osspdf','height_footer']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[4,'vtiger_osspdf','selected']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[7,'vtiger_osspdf','osspdf_view']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', 
				[6,'vtiger_osspdf','moduleid']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tabid = ? AND columnname = ? ;', 
				[6,getTabid('Contacts'),'smownerid']);
				
		$result = $adb->pquery("SELECT * FROM `vtiger_eventhandlers` WHERE handler_class = ?;", array('ProjectTaskHandler'));
		if($adb->num_rows($result) == 0){
			$em = new VTEventsManager($adb);
			$em->registerHandler('vtiger.entity.aftersave.final', 'modules/ProjectTask/handlers/ProjectTaskHandler.php', 'ProjectTaskHandler');
			$em->registerHandler('vtiger.entity.afterdelete', 'modules/ProjectTask/handlers/ProjectTaskHandler.php', 'ProjectTaskHandler');
			$em->registerHandler('vtiger.entity.afterrestore', 'modules/ProjectTask/handlers/ProjectTaskHandler.php', 'ProjectTaskHandler');
		}
		$adb->pquery('UPDATE vtiger_relatedlists SET label = ? WHERE label = ?;', ['Activities','Upcoming Activities']);
		$adb->pquery('DELETE FROM vtiger_relatedlists WHERE `tabid` IN (?,?,?,?,?,?,?,?) AND `label` = ?;', [getTabid('Accounts'),getTabid('Leads'),getTabid('Contacts'),getTabid('Potentials'),getTabid('HelpDesk'),getTabid('Campaigns'),getTabid('ServiceContracts'),getTabid('Project'),'Activity History']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', [4,'vtiger_potential','related_to']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', [5,'vtiger_potential','closingdate']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? ;', [3,'vtiger_potential','sales_stage']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_crmentity','smownerid',2]);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [7,'vtiger_osssoldservices','ssservicesstatus']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [9,'vtiger_osssoldservices','pscategory']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [5,'vtiger_osssoldservices','datesold']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_crmentity','smownerid',getTabid('OSSSoldServices')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [3,'vtiger_osssoldservices','invoiceid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [6,'vtiger_osssoldservices','parent_id']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [4,'vtiger_osssoldservices','serviceid']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [3,'vtiger_paymentsin','paymentsname']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_crmentity','smownerid',getTabid('PaymentsIn')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [8,'vtiger_paymentsin','paymentsin_status']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [7,'vtiger_paymentsin','relatedid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [6,'vtiger_paymentsin','salesid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [1,'vtiger_paymentsin','paymentsvalue']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [5,'vtiger_paymentsin','paymentstitle']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [4,'vtiger_paymentsin','bank_account']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [1,'vtiger_lettersin','title']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_crmentity','smownerid',getTabid('LettersIn')]);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [1,'vtiger_lettersout','title']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_crmentity','smownerid',getTabid('LettersOut')]);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [1,'vtiger_quotesenquires','subject']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [4,'vtiger_quotesenquires','potentialid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_crmentity','smownerid',getTabid('QuotesEnquires')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [3,'vtiger_quotesenquires','quotesenquires_stage']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [5,'vtiger_account','phone']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [4,'vtiger_account','website']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_crmentity','smownerid',getTabid('Accounts')]);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [3,'vtiger_vendor','phone']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [4,'vtiger_vendor','email']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_crmentity','smownerid',getTabid('Vendors')]);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [2,'vtiger_osstimecontrol','osstimecontrol_status']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [6,'vtiger_osstimecontrol','date_start']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [5,'vtiger_osstimecontrol','time_start']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [7,'vtiger_osstimecontrol','time_end']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [8,'vtiger_osstimecontrol','due_date']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [3,'vtiger_osstimecontrol','timecontrol_type']);
		
		$adb->pquery('UPDATE vtiger_field SET sequence = ? WHERE tablename = ? AND columnname = ?;', [3,'vtiger_ossemployees','private_phone']);
		$adb->pquery('UPDATE vtiger_field SET sequence = ? WHERE tablename = ? AND columnname = ?;', [2,'vtiger_ossemployees','business_mail']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [9,'vtiger_crmentity','description',getTabid('OSSTimeControl')]);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [2,'vtiger_holidaysentitlement','holidaysentitlement_year']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ?, quickcreate = ?, masseditable = ? WHERE tablename = ? AND columnname = ?;', [3,2,2,'vtiger_holidaysentitlement','days']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [1,'vtiger_holidaysentitlement','ossemployeesid']);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [4,'vtiger_crmentity','smownerid',getTabid('HolidaysEntitlement')]);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ?;', [3,'vtiger_account','legal_form']);
		
		$adb->pquery('UPDATE vtiger_field SET sequence = ? WHERE tablename = ? AND columnname = ?;', [5,'vtiger_leaddetails','industry']);
		$adb->pquery('UPDATE vtiger_field SET sequence = ? WHERE tablename = ? AND columnname = ?;', [8,'vtiger_leaddetails','subindustry']);
		$adb->pquery('UPDATE vtiger_field SET sequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [9,'vtiger_crmentity','was_read',getTabid('Leads')]);
		$adb->pquery('UPDATE vtiger_field SET sequence = ? WHERE tablename = ? AND columnname = ?;', [10,'vtiger_leaddetails','leads_relation']);
		
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [8,'vtiger_crmentity','smownerid',getTabid('Calendar')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [5,'vtiger_activity','due_date',getTabid('Calendar')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ?, quickcreate = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [6,2,'vtiger_activity','process',getTabid('Calendar')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [4,'vtiger_activity','link',getTabid('Calendar')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [1,'vtiger_activity','subject',getTabid('Events')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [9,'vtiger_crmentity','smownerid',getTabid('Events')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [3,'vtiger_activity','date_start',getTabid('Events')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [5,'vtiger_activity','due_date',getTabid('Events')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ?, quickcreate = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [6,2,'vtiger_activity','process',getTabid('Events')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [4,'vtiger_activity','eventstatus',getTabid('Events')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [2,'vtiger_activity','activitytype',getTabid('Events')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [8,'vtiger_activity','link',getTabid('Events')]);
		$adb->pquery('UPDATE vtiger_field SET quickcreatesequence = ? WHERE tablename = ? AND columnname = ? AND tabid = ? ;', [7,'vtiger_activity','allday',getTabid('Events')]);
		
		$adb->pquery('UPDATE vtiger_homestuff SET `visible` = ? WHERE `stufftype` = ? ;', [1,'Tag Cloud']);
		
		$adb->pquery('UPDATE vtiger_field SET typeofdata = ? WHERE tablename = ? AND columnname = ?;', 	['D~M','vtiger_projectmilestone','projectmilestonedate']);
		
		$log->debug("Exiting YetiForceUpdate::databaseData() method ...");
	}
	public function changeCalendarRelationships(){
		global $log,$adb;
		$log->debug("Entering YetiForceUpdate::changeCalendarRelationships() method ...");
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_activity` LIKE 'link';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE vtiger_activity ADD COLUMN `link` INT(19) NULL AFTER `state`, ADD COLUMN `process` INT(19) NULL AFTER `link`, ADD INDEX (`link`), ADD INDEX (`process`);");
		}
		$adb->pquery('UPDATE vtiger_ws_fieldtype SET uitype = ? WHERE fieldtypeid = ?;', 
				[67,35]);
		$adb->pquery('DELETE FROM vtiger_ws_referencetype WHERE `fieldtypeid` = ? AND `type` = ?;', 
				[34,'Accounts']);
		$adb->pquery('DELETE FROM vtiger_ws_referencetype WHERE `fieldtypeid` = ? AND `type` = ?;', 
				[34,'Leads']);
		$adb->pquery('DELETE FROM vtiger_ws_referencetype WHERE `fieldtypeid` = ? AND `type` = ?;', 
				[35,'Users']);
		$result = $adb->pquery('SELECT * FROM vtiger_ws_referencetype WHERE `fieldtypeid` = ? AND type = ?;',[35,'Accounts']);
		if($adb->num_rows($result) == 0){
			$adb->pquery("INSERT INTO vtiger_ws_referencetype (`fieldtypeid`, `type`) VALUES ('35', 'Accounts');");
			$adb->pquery("INSERT INTO vtiger_ws_referencetype (`fieldtypeid`, `type`) VALUES ('35', 'Contacts');");
			$adb->pquery("INSERT INTO vtiger_ws_referencetype (`fieldtypeid`, `type`) VALUES ('35', 'Leads');");
			$adb->pquery("INSERT INTO vtiger_ws_referencetype (`fieldtypeid`, `type`) VALUES ('35', 'OSSEmployees');");
			$adb->pquery("INSERT INTO vtiger_ws_referencetype (`fieldtypeid`, `type`) VALUES ('35', 'Vendors');");
		}
		$adb->query("DELETE FROM vtiger_relatedlists WHERE `tabid` IN (".getTabid('Quotes').",".getTabid('PurchaseOrder').",".getTabid('SalesOrder').",".getTabid('Invoice').") AND `name` IN ('get_activities','get_history') ;");

		$result = $adb->query("SHOW TABLES LIKE 'vtiger_cntactivityrel';");
		if($adb->num_rows($result) > 0){
			$result = $adb->query("SELECT * FROM vtiger_cntactivityrel;");
			for($i = 0; $i < $adb->num_rows($result); $i++){
				$contactid = $adb->query_result_raw($result, $i, 'contactid');
				$activityid = $adb->query_result_raw($result, $i, 'activityid');
				$adb->pquery('UPDATE vtiger_activity SET link = ? WHERE activityid = ?;', [$contactid,$activityid]);
				//$adb->pquery('DELETE FROM vtiger_cntactivityrel WHERE contactid = ? AND activityid = ?;',[$contactid,$activityid]);
			}
			$result = $adb->query("SELECT vtiger_seactivityrel.*, vtiger_crmentity.setype FROM vtiger_seactivityrel INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seactivityrel.crmid;");
			for($i = 0; $i < $adb->num_rows($result); $i++){
				$crmid = $adb->query_result_raw($result, $i, 'crmid');
				$activityid = $adb->query_result_raw($result, $i, 'activityid');
				$setype = $adb->query_result_raw($result, $i, 'setype');
				if(in_array($setype, ['Accounts','Leads'])){
					$adb->pquery('UPDATE vtiger_activity SET link = ? WHERE activityid = ?;', [$crmid,$activityid]);
				}
				if(in_array($setype, ['Campaigns','HelpDesk','Potentials','Project','ServiceContracts'])){
					$adb->pquery('UPDATE vtiger_activity SET process = ? WHERE activityid = ?;', [$crmid,$activityid]);
				}
				//$adb->pquery('DELETE FROM vtiger_seactivityrel WHERE crmid = ? AND activityid = ?;',[$crmid,$activityid]);
			}

			$adb->query('DROP TABLE vtiger_cntactivityrel;');
			$adb->query('DROP TABLE vtiger_seactivityrel;');
			$adb->query('DROP TABLE vtiger_seactivityrel_seq;');
		}
		$log->debug("Exiting YetiForceUpdate::changeCalendarRelationships() method ...");
	}
	
	public function roundcubeConfig(){
		global $log, $adb, $root_directory;
		$log->debug("Entering YetiForceUpdate::roundcubeConfig() method ...");
		if (!$root_directory)
			$root_directory = getcwd();
		$fileName = $root_directory . '/modules/OSSMail/roundcube/config/config.inc.php';

		$configContent = file($fileName);
		foreach($configContent as $key => $line){
			if(	strpos($line, '$config[\'plugins\']') !== FALSE){
				if (strpos($line, 'yt_new_user') === FALSE) {
					$configContent[$key] = str_replace(");", ",'yt_new_user');", $configContent[$key]);
				}
				if (strpos($line, 'yt_signature') === FALSE) {
					$configContent[$key] = str_replace(");", ",'yt_signature');", $configContent[$key]);
				}
			}
			if (strpos($line, '$GEBUG_CONFIG') !== FALSE) {
				$configContent[$key] = str_replace('$GEBUG_CONFIG','$DEBUG_CONFIG', $configContent[$key]);
			}
		}
		$content = implode("", $configContent);
		$file = fopen($fileName,"w+");
		fwrite($file,$content);
		fclose($file);
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
		$ProjectMilestone = array(
		array('41','1741','projectmilestone_priority','vtiger_projectmilestone','1','15','projectmilestone_priority','LBL_PRIORITY','1','2','','100','10','101','1','V~O','1',NULL,'BAS','1','','0','',"varchar(255)","LBL_PROJECT_MILESTONE_INFORMATION",array('PLL_LOW','PLL_NORMAL','PLL_HIGH'),array()),
		array('41','1743','projectmilestone_progress','vtiger_projectmilestone','1','1','projectmilestone_progress','LBL_PROGRESS','1','2','','100','11','101','10','V~O','1',NULL,'BAS','1','','0','',"varchar(10)","LBL_PROJECT_MILESTONE_INFORMATION",array(),array())
		);
		$ProjectTask = array(
		array('42','1742','estimated_work_time','vtiger_projecttask','1','7','estimated_work_time','LBL_ESTIMATED_WORK_TIME','1','2','','100','9','105','1','NN~M','1',NULL,'BAS','1','','0','',"decimal(8,2)","LBL_CUSTOM_INFORMATION",array(),array())
		);
		$Contacts = array(
		array('4','1744','jobtitle','vtiger_contactdetails','1','1','jobtitle','Job title','1','2','','100','31','4','1','V~O','1',NULL,'BAS','1','','0','',"varchar(100)","LBL_CONTACT_INFORMATION",array(),array())
		);
		$setToCRM = array('OSSMailTemplates'=>$OSSMailTemplates,'Users'=>$Users,'ProjectMilestone'=>$ProjectMilestone,'ProjectTask'=>$ProjectTask,'Contacts'=>$Contacts);

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
	public function deleteFields($fieldsToDelete){
		global $log;
		$log->debug("Entering YetiForceUpdate::deleteFields() method ...");
		$adb = PearDatabase::getInstance();
		foreach($fieldsToDelete AS $fld_module=>$columnnames){
			$moduleId = getTabid($fld_module);
			foreach($columnnames AS $columnname){
				$fieldquery = 'select * from vtiger_field where tabid = ? AND fieldname = ?';
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
				if($uitype == 15 || $uitype == 16 ) {
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
	function updateConfig() {
		$config = 'config/config.inc.php';
		$configContent = file($config);
		foreach($configContent as $key => $line){
			if(	strpos($line, 'encode passwords for Customer Portal') !== FALSE ||
				strpos($line, 'encode_customer_portal_passwords') !== FALSE
			){
				unset($configContent[$key]);
			}
		}
		$content = implode("", $configContent);
		$file = fopen($config,"w+");
		fwrite($file,$content);
		fclose($file);
		if(strpos(file_get_contents( $config ),'lifetime of session') !== FALSE){
				return;
			}
		$configC = "
// lifetime of session
ini_set('session.gc_maxlifetime','1800'); //30 min
";
		file_put_contents( $config, $configC, FILE_APPEND );
	}
	public function checkModuleExists($moduleName){
		global $log;
		$log->debug("Entering YetiForceUpdate::checkModuleExists(".$moduleName.") method ...");
		global $adb;
		$result = $adb->pquery('SELECT * FROM vtiger_tab WHERE name = ?', array($moduleName));
		if(!$adb->num_rows($result)) {
			$log->debug("Exiting YetiForceUpdate::checkModuleExists() method ...");
			return false;
		}
		$log->debug("Exiting YetiForceUpdate::checkModuleExists() method ...");
		return true;
	}
	function removeModules(){
		global $log;
		$log->debug("Entering YetiForceUpdate::removeModules() method ...");
		$removeModules = ['OSSMenuManager'=>['table_list'=>['vtiger_ossmenumanager'],'supported_languages'=>['en_us','pl_pl','de_de','pt_br','ru_ru'],'tabid'=>getTabid('OSSMenuManager')]];
		foreach($removeModules as $moduleName=>$removeModule){
			if(!self::checkModuleExists($moduleName))
				continue;
			$moduleInstance = Vtiger_Module::getInstance($moduleName);
			$moduleInstance->delete();
			$obiekt = new RemoveModule( $moduleName );
			foreach($removeModule AS $key=>$value){
				$obiekt->$key = $value;
			}
			$obiekt->DeleteAll();
		}
		$log->debug("Exiting YetiForceUpdate::removeModules() method ...");
	}
	
    function addRelatedModules() {
		$docelowy_Module = Vtiger_Module::getInstance('OutsourcedProducts');
		$moduleInstance = Vtiger_Module::getInstance('Documents');
		$docelowy_Module->setRelatedList($moduleInstance, 'Documents', array('add','select'),'get_attachments');

		$docelowy_Module = Vtiger_Module::getInstance('OSSOutsourcedServices');
		$moduleInstance = Vtiger_Module::getInstance('Documents');
		$docelowy_Module->setRelatedList($moduleInstance, 'Documents', array('add','select'),'get_attachments');

		$docelowy_Module = Vtiger_Module::getInstance('OSSSoldServices');
		$moduleInstance = Vtiger_Module::getInstance('Documents');
		$docelowy_Module->setRelatedList($moduleInstance, 'Documents', array('add','select'),'get_attachments');
		
		$moduleInstance = Vtiger_Module::getInstance('ProjectTask');
		$docelowy_Module = Vtiger_Module::getInstance('ProjectMilestone');
		$docelowy_Module->setRelatedList($moduleInstance, 'ProjectTask', array('ADD'),'get_dependents_list');
    }
}

class RemoveModule {

	var $module_name;
	var $tabid;
	var $cvid;
	var $table_list = array( );
	var $added_links = array();
	var $added_fields = array();
	var $added_handlers = array();
	// supported languages
	var $supported_languages = array();
	// setting links in "crm settings"
	var $settings_links = array();

	function __construct( $module_name ) 
	{
		global $adb;
		
		$this->module_name = $module_name;
		$take_tabid = $adb->query( "select tabid from vtiger_tab where name = '".$this->module_name."'");
		if( $adb->num_rows( $take_tabid ) > 0 )
		{
			$this->tabid = $adb->query_result( $take_tabid,0,"tabid" );
		}
		$take_cvid = $adb->query( "select cvid from vtiger_customview where entitytype = '".$this->module_name."'");
		if( $adb->num_rows( $take_cvid ) > 0 )
		{
			$this->cvid = $take_cvid->GetArray();
			
		}

		$take_info = $adb->query( "select * from vtiger_field where tabid = '$field_tabid' and uitype = '15'");
		if( $adb->num_rows( $take_info ) > 0 )
		{
			$_SESSION['picklist_tables'][$adb->query_result( $take_info,0,"fieldname" )] = $adb->query_result( $take_info,0,"fieldname" );
		}
	}
	
	function DeleteAll()
	{
		$this->DeleteHandlers();
		$this->DeleteAddedFields();
		$this->DeleteALLFields();
		$this->DeleteLinks();
		$this->DeletePicklistsTables();
		$this->DeleteFromProfile2Field();
		$this->DeleteFromModentitynum();
		$this->DeleteDefOrgInformations();
		$this->DeleteTables();
		$this->DeleteFromCronTask();
		$this->DeleteFromCRMEntity();
		$this->DeleteBlocks();
		$this->DeleteCustomview();
		$this->DeleteEntityname();
		$this->DeleteTab();
		$this->DeleteWsEntity();
		$this->recurseDelete( 'modules/'.$this->module_name );
		$this->recurseDelete( 'modules/Settings/'.$this->module_name );
		$this->recurseDelete( 'layouts/vlayout/modules/'.$this->module_name );
		$this->DeleteLanguageFiles();
		$this->DeleteRelatedLists();
		$this->DeleteSettingsField();
		$this->DeleteWorkflows();
		$this->DeleteCrmentityrel();
        $this->DeleteIcon();
	}
	
	function DeleteAddedFields()
	{
	global $adb;
	include_once('vtlib/Vtiger/Module.php');
		if( count( $this->added_fields ) > 0 )
		{
			foreach( $this->added_fields as $single_field )
			{
			$take_tabid = $adb->query( "select tabid from vtiger_tab where name = '".$single_field['module']."'", true, "Error DeleteAddedFields()" );
				if( $adb->num_rows( $take_tabid ) > 0 )
				{
					$field_tabid = $adb->query_result( $take_tabid, 0, "tabid" );
					
					$take_info = $adb->query( "select * from vtiger_field where tabid = '$field_tabid' and fieldname = '".$single_field['fieldname']."'" , true, "Error DeleteAddedFields()" );
					if( $adb->num_rows( $take_info ) > 0 )
					{
					$valuemap = array();
						$moduleInstance = Vtiger_Module::getInstance( $single_field['module'] );
						$valuemap['fieldid'] = $adb->query_result( $take_info, 0, 'fieldid');
						$valuemap['fieldname']=$adb->query_result( $take_info, 0, 'fieldname'); 
						$valuemap['fieldlabel'] = $adb->query_result( $take_info, 0, 'fieldlabel');
						$valuemap['columnname'] =$adb->query_result( $take_info, 0, 'columnname');
						$valuemap['tablename']  =$adb->query_result( $take_info, 0, 'tablename'); 
						$valuemap['uitype'] = $adb->query_result( $take_info, 0, 'uitype');
						$valuemap['typeofdata'] = $adb->query_result( $take_info, 0, 'typeofdata'); 
						$valuemap['block'] = $adb->query_result( $take_info, 0, "block" );
						$fieldInstance = new Vtiger_Field();
						$fieldInstance->initialize( $valuemap, $moduleInstance );
						$fieldInstance->delete();
					}
				}
			}
		}
		
		$delete = $adb->query( "delete from vtiger_fieldmodulerel where module = '".$this->module_name."' or relmodule = '".$this->module_name."' ", true, "Error DeleteAddedFields()");
	}
	function DeleteALLFields()
	{
	global $adb;
	include_once('vtlib/Vtiger/Module.php');

			$take_info = $adb->query( "select * from vtiger_field where tabid = '".$this->tabid."'" , true, "Error DeleteAddedFields()" );
	
			foreach( $take_info as $single_field )
			{
				$take_info = $adb->query( "select * from vtiger_field where tabid = '".$single_field['tabid']."' and fieldname = '".$single_field['fieldname']."'" , true, "Error DeleteAddedFields()" );
				if( $adb->num_rows( $take_info ) > 0 )
				{
				$valuemap = array();
					$moduleInstance = Vtiger_Module::getInstance( $single_field['module'] );
					$valuemap['fieldid'] = $adb->query_result( $take_info, 0, 'fieldid');
					$valuemap['fieldname']=$adb->query_result( $take_info, 0, 'fieldname'); 
					$valuemap['fieldlabel'] = $adb->query_result( $take_info, 0, 'fieldlabel');
					$valuemap['columnname'] =$adb->query_result( $take_info, 0, 'columnname');
					$valuemap['tablename']  =$adb->query_result( $take_info, 0, 'tablename'); 
					$valuemap['uitype'] = $adb->query_result( $take_info, 0, 'uitype');
					$valuemap['typeofdata'] = $adb->query_result( $take_info, 0, 'typeofdata'); 
					$valuemap['block'] = $adb->query_result( $take_info, 0, "block" );
					$fieldInstance = new Vtiger_Field();
					$fieldInstance->initialize( $valuemap, $moduleInstance );
					$fieldInstance->delete();
				}
			}

		
		$delete = $adb->query( "delete from vtiger_fieldmodulerel where module = '".$this->module_name."' or relmodule = '".$this->module_name."' ", true, "Error DeleteAddedFields()");
	}
	
	function DeleteLinks()
	{
		global $adb;
		if( count( $this->added_links ) > 0 )
		{
			foreach( $this->added_links as $single_link )
			{
				$drop_table_query = $adb->query( "DELETE FROM vtiger_links WHERE linktype='".$single_link['type']."' AND linklabel='".$single_link['label']."'", true, "Error DeleteLinks()" );
			}
		}
		$adb->query( "DELETE FROM vtiger_links WHERE linkurl like '%module=".$this->module_name."%'", true, "Error DeleteLinks()" );
	}
	
	function DeleteHandlers()
	{
		global $adb;
		require_once( 'include/events/include.inc' );
		if( count( $this->added_handlers ) > 0 )
		{
			foreach( $this->added_handlers as $handler )
			{
				$delete = $adb->query("delete from vtiger_eventhandlers where handler_class='".$handler['class']."' and event_name = '".$handler['type']."'", true, "Error DeleteHandlers()");
				$delete = $adb->query("delete from vtiger_eventhandler_module where handler_class='".$handler['class']."'", true, "Error DeleteHandlers()");
			}
		}
	}
	
	function DeleteTables()
	{
		global $adb;
		if( count( $this->table_list ) > 0 )
		{
			foreach( $this->table_list as $tablename )
			{
				$drop_table_query = $adb->query( "drop table if exists $tablename", true, "Error DeleteTables()" );
			}
		}
	}
	function DeleteEntityname()
	{
		global $adb;
		$delete = $adb->query( "delete from vtiger_entityname where tabid = '".$this->tabid."'", true,  "Error DeleteEntityname()" );
	}
	function DeleteBlocks()
	{
		global $adb;
		$delete = $adb->query( "delete from vtiger_blocks where tabid = '".$this->tabid."'", true,  "Error DeleteBlocks()" );
	}
	function DeleteCustomview()
	{
		if(count($this->cvid) >0){
		foreach( $this->cvid as $cvid ){
			$customViewModel = CustomView_Record_Model::getInstanceById($cvid[0]);
			$customViewModel->delete();
		}
		}
	}
	function DeletePicklistsTables()
	{
		global $adb;
		
		
		
		if( isset( $_SESSION['picklist_tables'] ) && count( $_SESSION['picklist_tables'] ) > 0 )
		{
			foreach( $_SESSION['picklist_tables'] as $picklist_name )
			{
				$drop_table_query = $adb->query( "drop table if exists vtiger_".$picklist_name, true, "Error DeletePicklistsTables()" );
				$drop_table_query = $adb->query( "drop table if exists vtiger_".$picklist_name."_seq", true, "Error DeletePicklistsTables()" );
				$select = $adb->query( "select picklistid from vtiger_picklist where name = '$picklist_name'", true, "Error DeletePicklistsTables()" );
				$picklistid = $adb->query_result( $select,0,"picklistid" );
				$delete_from = $adb->query( "delete from vtiger_role2picklist where picklistid = '$picklistid'", true, "Error DeletePicklistsTables()" );
				$delete_from = $adb->query( "delete from vtiger_picklist where name = '$picklist_name'", true, "Error DeletePicklistsTables()" );
			}
		}
	}
	function DeleteFromProfile2Field()
	{
		global $adb;
		$delete = $adb->query( "delete from vtiger_profile2field where tabid = '".$this->tabid."'", true,  "Error DeleteFromProfile2Field()" );
		$delete = $adb->query( "delete from vtiger_profile2standardpermissions where tabid = '".$this->tabid."'", true,  "Error DeleteFromProfile2Field()" );
		$delete = $adb->query( "delete from vtiger_profile2tab where tabid = '".$this->tabid."'", true,  "Error DeleteFromProfile2Field()" );
	}
	
	function DeleteFromModentitynum()
	{
		global $adb; 
		$delete = $adb->query( "delete from vtiger_modentity_num where semodule = '".$this->module_name."'", true,  "Error DeleteFromModentitynum()" );
	}
	function DeleteDefOrgInformations()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_def_org_field where tabid = '".$this->tabid."'", true,  "Error DeleteDefOrgInformations()" );
		$delete = $adb->query( "delete from vtiger_def_org_share where tabid = '".$this->tabid."'", true,  "Error DeleteDefOrgInformations()" );
		$delete = $adb->query( "delete from vtiger_org_share_action2tab where tabid = '".$this->tabid."'", true,  "Error DeleteDefOrgInformations()" );
	}
	
	function DeleteFromCronTask()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_cron_task where module = '".$this->module_name."'", true,  "Error DeleteFromCronTask()" );
	}
	
	function DeleteFromCRMEntity()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_crmentity where setype = '".$this->module_name."'", true,  "Error DeleteFromCRMEntity()" );
	}
	function DeleteTab()
	{
		global $adb,$log;
		$log->debug("Entering RemoveModule::DeleteTab(".$this->tabid.") method ...");
		$delete = $adb->query( "delete from vtiger_tab where tabid = '".$this->tabid."'");
		$delete = $adb->query( "delete from vtiger_tab_info where tabid = '".$this->tabid."'");
		$log->debug("Exiting RemoveModule::DeleteTab() method ...");
	}
	function DeleteWsEntity()
	{
		global $adb;
		
		$delete = $adb->query( "delete from vtiger_ws_entity where name = '".$this->module_name."'", true,  "Error DeleteWsEntity()" );
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
	
    // dodano 2013-10-03
    function DeleteLanguageFiles() {
        if( count( $this->supported_languages ) > 0 ) {
			foreach( $this->supported_languages as $lang ) {
				@unlink( "languages/$lang/".$this->module_name.".php" );
			}
		}
    }
    
    function DeleteRelatedLists() {
        global $adb;
		
		$delete = $adb->query( "DELETE FROM `vtiger_relatedlists` WHERE `label` = '".$this->module_name."'", true,  
        "Error DeleteRelatedLists()" );
    }
    
    function DeleteSettingsField() {
		 global $adb;
        if( count( $this->settings_links ) > 0 ) {
			foreach( $this->settings_links as $setting ) { print_r($setting);
				$delete = $adb->query( "DELETE FROM `vtiger_settings_field` WHERE `name` = '".$setting['name']."' AND `linkto` = '".$setting['linkto']."'", true,  
                            "Error DeleteSettingsField()" );
			}
		}
		$adb->query( "DELETE FROM `vtiger_settings_field` WHERE `linkto` like '%module=".$this->module_name."%'", true,  
                            "Error DeleteSettingsField()" );
    }
    function DeleteWorkflows() {
		global $adb;
		$adb->query( "DELETE com_vtiger_workflows,com_vtiger_workflowtasks FROM `com_vtiger_workflows` 
			LEFT JOIN `com_vtiger_workflowtasks` ON com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id
			WHERE `module_name` = '".$this->module_name."'", true,  
                            "Error DeleteWorkflows()" );
    }  
    function DeleteCrmentityrel() {
		global $adb;
		$adb->query( "DELETE FROM `vtiger_crmentityrel` WHERE `module` = '".$this->module_name."' OR `relmodule` = '".$this->module_name."'", true,  
                            "Error DeleteCrmentityrel()" );
    }
    function DeleteIcon() {
        $filename = "layouts/vlayout/skins/images/".$this->module_name.".png";
        
        if ( file_exists( $filename ) ) {
            @unlink( $filename );
        }
    }
}
