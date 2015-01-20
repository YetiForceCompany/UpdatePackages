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
		'layouts/vlayout/modules/Settings/MenuEditor/Index.tpl',
		'modules/Settings/MenuEditor/actions/Save.php',
		'modules/Settings/MenuEditor/views/Index.php',
		'languages/de_de/Proposal.php',
		'languages/en_us/Proposal.php',
		'languages/pl_pl/Proposal.php',
		'languages/pt_br/Proposal.php',
		'languages/ru_ru/Proposal.php',
		'modules/Settings/ModuleManager/models/Extension.php',
		'api/mobile_services/callhistory.php',
		'modules/Settings/WidgetsManagement/actions/SaveWidgets.php',
		'languages/pl_pl/Settings/Mobile.php',
		'layouts/vlayout/modules/Settings/Mobile/MobileKeys.tpl',
		'layouts/vlayout/modules/Settings/Mobile/resources/Mobile.js',
		'layouts/vlayout/modules/Settings/Mobile/resources',
		'layouts/vlayout/modules/Settings/Mobile',
	);
	function YetiForceUpdate($modulenode) {
		$this->modulenode = $modulenode;
	}
	function preupdate() {
		$this->recurseCopy('cache/updates/files','');
	}
	
	function update() {
		$this->addModules();
		$this->databaseStructure();
		$this->databaseData();
	}
	
	function postupdate() {
		global $adb;
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		return true;
	}
	public function databaseStructure(){
		global $log,$adb;
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_calendar_default_activitytypes` LIKE 'active';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_calendar_default_activitytypes` ADD COLUMN `active` tinyint(1)   NULL DEFAULT 0 after `defaultcolor` ;");
		}

		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_mobile_keys`(
					`id` int(19) NOT NULL  auto_increment , 
					`user` int(19) NOT NULL  , 
					`service` varchar(50) COLLATE utf8_general_ci NOT NULL  , 
					`key` varchar(30) COLLATE utf8_general_ci NOT NULL  , 
					`privileges_users` text COLLATE utf8_general_ci NULL  , 
					PRIMARY KEY (`id`) , 
					KEY `user`(`user`,`service`) 
				) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';");

		$adb->query("CREATE TABLE IF NOT EXISTS `yetiforce_mobile_pushcall`(
					`user` int(19) NOT NULL  , 
					`number` varchar(20) COLLATE utf8_general_ci NULL  , 
					PRIMARY KEY (`user`) 
				) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';");

		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_module_dashboard_blocks` (
					  `id` int(100) NOT NULL AUTO_INCREMENT,
					  `authorized` varchar(10) NOT NULL,
					  `tabid` int(19) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;");

		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_module_dashboard` (
					  `id` int(100) NOT NULL AUTO_INCREMENT,
					  `blockid` int(100) NOT NULL,
					  `linkid` int(19) DEFAULT NULL,
					  `filterid` int(19) DEFAULT NULL,
					  `title` varchar(100) DEFAULT NULL,
					  `data` text,
					  `isdefault` int(1) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`id`),
					  KEY `vtiger_module_dashboard_ibfk_1` (`blockid`),
					  CONSTRAINT `vtiger_module_dashboard_ibfk_1` FOREIGN KEY (`blockid`) REFERENCES `vtiger_module_dashboard_blocks` (`id`) ON DELETE CASCADE
					) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;");
	}
	
	public function databaseData(){
		global $log,$adb;
		$adb->query("UPDATE vtiger_field SET `displaytype` = '4' WHERE `columnname` = 'user_name' AND `tablename` = 'vtiger_users';");
		$adb->query("UPDATE vtiger_field SET `fieldlabel` = 'LBL_ORDER_TIME' WHERE `fieldlabel` = 'Czas realizacji';");
		$adb->query("UPDATE vtiger_field SET `uitype` = '301' WHERE `columnname` = 'oss_module_list' AND `tablename` = 'vtiger_ossmailtemplates';");
		$result = $adb->query("SELECT * FROM `vtiger_language` WHERE prefix = 'ru_ru'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_language`(`name`,`prefix`,`label`,`lastupdated`,`sequence`,`isdefault`,`active`) values ('Russian','ru_ru','Russian','2015-01-13 15:12:39',NULL,0,1);");
			$adb->query("UPDATE vtiger_language_seq SET `id` = (SELECT count(*) FROM `vtiger_language`);");
		}
		$result = $adb->query("SHOW TABLES LIKE 'vtiger_oss_module_list';");
		if($adb->num_rows($result) == 1){
			$adb->query("DELETE FROM vtiger_oss_module_list;");
		}
		$adb->query("DROP TABLE IF EXISTS vtiger_oss_module_list;");
		$adb->query("DROP TABLE IF EXISTS vtiger_oss_module_list_seq;");
		
		$result = $adb->query("SELECT * FROM `vtiger_tab` WHERE name = 'CallHistory'");
		if($adb->num_rows($result) == 1){
			$tabid = $adb->query_result_raw($result, 0, 'tabid');
			$result = $adb->query("SELECT id FROM `vtiger_ossmenumanager` WHERE parent_id = '0' ORDER BY id DESC;");
			$parent_id = $adb->query_result_raw($result, 0, 'id');
			$adb->query("insert  into `vtiger_ossmenumanager`(`parent_id`,`tabid`,`label`,`sequence`,`visible`,`type`,`url`,`new_window`,`permission`,`locationicon`,`sizeicon`,`langfield`,`paintedicon`,`color`) values ($parent_id,$tabid,'CallHistory',1011,1,0,'index.php?module=CallHistory&view=List',0,'  ','','16x16','',0,NULL);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_MDULES_COLOR_EDITOR'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,2,'LBL_MDULES_COLOR_EDITOR',NULL,'LBL_MDULES_COLOR_EDITOR_DESCRIPTION','index.php?parent=Settings&module=MenuEditor&view=Colors',13,0,0);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_MOBILE_KEYS'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,5,'LBL_MOBILE_KEYS',NULL,'LBL_MOBILE_KEYS_DESCRIPTION','index.php?parent=Settings&module=MobileApps&view=MobileKeys',5,0,0);");
		}
		$adb->query("UPDATE vtiger_entityname SET `searchcolumn` = 'to_number' WHERE `modulename` = 'CallHistory';");
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_links` LIKE 'linkdata';");
		if($adb->num_rows($result) == 1){
			$adb->query("ALTER TABLE `vtiger_links` DROP COLUMN `linkdata`;");
		}
		$adb->query("DELETE FROM `vtiger_module_dashboard_widgets`;");
		$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` CHANGE `linkid` `linkid` INT(19) NOT NULL, ADD COLUMN `templateid` INT(19) NOT NULL AFTER `userid`, ADD COLUMN `active` INT(1) DEFAULT 0 NULL AFTER `isdefault`, ADD INDEX (`templateid`), ADD FOREIGN KEY (`templateid`) REFERENCES `vtiger_module_dashboard_widgets`(`id`) ON DELETE CASCADE;");
		
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_ossmenumanager` LIKE 'color';");
		if($adb->num_rows($result) == 1){
			$adb->query("ALTER TABLE `vtiger_ossmenumanager` DROP COLUMN `color`; ");
		}
		$adb->query("ALTER TABLE `vtiger_tab` ADD COLUMN `color` VARCHAR(30) NULL AFTER `trial`, ADD COLUMN `coloractive` TINYINT(1) DEFAULT 0 NULL AFTER `color`;");
		$adb->query("UPDATE `vtiger_settings_field` SET `blockid` = '2' WHERE `name` = 'LBL_ACTIVITY_TYPES';");
	}
	
	public function addModules(){
		try {
			if(file_exists('cache/updates/CallHistory.xml') && !Vtiger_Module::getInstance('CallHistory')){
				$importInstance = new Vtiger_PackageImport();
				$importInstance->_modulexml = simplexml_load_file('cache/updates/CallHistory.xml');
				$importInstance->import_Module();
			}
		} catch (Exception $e) {
			echo $e->getMessage();
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