<?php
class YetiForceUpdate{
	var $modulenode;
	var $filesToDelete = array(
		'languages/de_de/EmailTemplates.php',
		'languages/de_de/Migration.php',
		'languages/en_us/EmailTemplates.php',
		'languages/en_us/Migration.php',
		'languages/pl_pl/EmailTemplates.php',
		'languages/pl_pl/Migration.php',
		'modules/BackUp/BackUp.php',
		'layouts/vlayout/modules/Vtiger/EmailRelatedList.tpl',
		'layouts/vlayout/modules/Vtiger/ProjectMilestoneSummaryWidgetContents.tpl',
		'layouts/vlayout/modules/Vtiger/ProjectTaskSummaryWidgetContents.tpl',
		'modules/Invoice/CreatePDF.php',
		'modules/BackUp',
		'vtlib/ModuleDir/6.0.0/languages/en_us/ModuleName.php',
		'vtlib/ModuleDir/6.0.0/languages/en_us',
		'vtlib/ModuleDir/6.0.0/languages',
		'vtlib/ModuleDir/6.0.0/ModuleName.php',
		'vtlib/ModuleDir/6.0.0',
	);

	function YetiForceUpdate($modulenode) {
		$this->modulenode = $modulenode;
	}

	function preupdate() {
		$this->recurseCopy('cache/updates/files','');
		foreach($this->filesToDelete as $path) {
			$this->deleteFiles($path);
		}
	}
	
	function update() {
		$this->updateFiles();
		$this->addClosedtimeField();
		$this->pobox();
		$this->addFields();
		$this->addWorkflowType();
		$this->addWorkflow();
		$this->actionMapping();
		$this->updateDatabase();
	}
	
	function postupdate() {
		global $adb;
		$adb->query("UPDATE vtiger_version SET `current_version` = '".$this->modulenode->to_version."';");
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		return true;
	}
	
	function recurseCopy($src,$dst) {
		global $root_directory;
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
	
	function deleteFiles($src) {
		global $root_directory;
		$src = $root_directory.$src;
		if( file_exists($src) )
			@unlink($src);
		if(is_dir($src))
			@rmdir($src);
	}
	
	function updateFiles() {
		$config = '

// Maximum number of displayed search results
$max_number_search_result = 100;

// List of products in the inventory module popup limited to products / services selected in Potentials
$inventory_popup_limited_from_potentials = true;

//Should menu breadcrumbs be visible? true = show, false = hide
$breadcrumbs = true;

//Separator for menu breadcrumbs default value = '>'
$breadcrumbs_separator = \'>\';';
		file_put_contents( 'config/config.inc.php', $config, FILE_APPEND );
		$file = file('modules/OSSMail/roundcube/config/config.inc.php'); 
		$newFile = '';
		foreach($file as $row) { 
			if (strpos($row, '$config[\'plugins\']') !== false) {
				$newFile .= '$config[\'plugins\'] = array(\'autologon\',\'identity_smtp\',\'ical_attachments\');'.PHP_EOL;
			}else{
				$newFile .= $row;
			}
		}
		file_put_contents( 'modules/OSSMail/roundcube/config/config.inc.php', $newFile );
	}
	
	function pobox(){
		global $log,$adb;
		$sql = "SELECT * FROM `vtiger_field` WHERE `fieldname` LIKE 'addresslevel1%';";
		$result = $adb->query($sql,true);
		$Num = $adb->num_rows($result);

		for($i = 0; $i < $Num; $i++){
			$row = $adb->query_result_rowdata($result, $i);
			$tabid = $row['tabid'];
			$moduleName = Vtiger_Functions::getModuleName($tabid);
			$block = $row['block'];
			$tablename = $row['tablename'];
			$fieldname = $row['fieldname'];
			$name = 'pobox'.substr($fieldname, -1);
			
			$moduleInstance = Vtiger_Module::getInstance($moduleName);
			$blockInstance = Vtiger_Block::getInstance($block,$moduleInstance);
			$fieldInstance = new Vtiger_Field(); 
			$fieldInstance->name = $name; 
			$fieldInstance->table = $tablename; 
			$fieldInstance->label = 'Po Box'; 
			$fieldInstance->column = $name; 
			$fieldInstance->columntype = 'varchar(50)'; 
			$fieldInstance->uitype = 1;
			$fieldInstance->typeofdata = 'V~O'; 
			$blockInstance->addField($fieldInstance);
		}
	}
	
	public function addClosedtimeField(){
		global $log,$adb;
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_crmentity` LIKE 'closedtime';");
		if($adb->num_rows($result) != 0)
			return;
		$adb->query('ALTER TABLE `vtiger_crmentity` ADD COLUMN `closedtime` DATETIME NULL AFTER `viewedtime`;');
		$restrictedModules = array('Emails', 'Integration', 'Dashboard', 'ModComments', 'SMSNotifier','PBXManager');
		$sql = 'SELECT * FROM vtiger_tab WHERE isentitytype = ? AND name NOT IN ('.generateQuestionMarks($restrictedModules).')';
		$params = array(1, $restrictedModules);
		$tabresult = $adb->pquery($sql, $params,true);

		for($i = 0; $i < $adb->num_rows($tabresult); $i++){
			$tabId = $adb->query_result_raw($tabresult, $i, 'tabid');
			$fieldresult = $adb->query("SELECT * FROM `vtiger_field` WHERE tabid = '$tabId' AND columnname = 'closedtime'",true);
			$blockresult = $adb->query("SELECT block FROM `vtiger_field` WHERE tabid = '$tabId' AND columnname = 'createdtime'",true);
			if( $adb->num_rows($fieldresult) == 0 && $adb->num_rows($blockresult) > 0){
				$name = $adb->query_result_raw($tabresult, $i, 'name');
				$block = $adb->query_result_raw($blockresult, 0, 'block');
				
				$moduleInstance = Vtiger_Module::getInstance($name);
				$blockInstance = Vtiger_Block::getInstance($block,$moduleInstance);

				$fieldInstance = new Vtiger_Field(); 
				$fieldInstance->name = 'closedtime';
				$fieldInstance->table = 'vtiger_crmentity';
				$fieldInstance->label = 'Closed Time';
				$fieldInstance->column = 'closedtime';
				$fieldInstance->columntype = 'datetime';
				$fieldInstance->uitype = 70;
				$fieldInstance->typeofdata = 'DT~O'; 
				$fieldInstance->displaytype = 2;
				$blockInstance->addField($fieldInstance); 
			}
		}
	}
	
	function addFields(){
		global $log,$adb;
		$columnName = array("tabid","id","column","table","generatedtype","uitype","name","label","readonly","presence","defaultvalue","maximumlength","sequence","block","displaytype","typeofdata","quickcreate","quicksequence","info_type","masseditable","helpinfo","summaryfield","columntype","blocklabel","setpicklistvalues","setrelatedmodules");

		$HelpDesk = array(
			array('13','1482','pssold_id','vtiger_troubletickets','2','10','pssold_id','P&S Sold','1','2','','100','25','25','1','V~O','1','','BAS','1','0','0',"int(19)","LBL_TICKET_INFORMATION"),
			array('13','1483','ordertime','vtiger_troubletickets','2','7','ordertime','Czas realizacji','1','2','','100','26','25','1','NN~O','1','','BAS','1','0','0',"decimal(10,2)","LBL_TICKET_INFORMATION"),
		);

		$Assets = array(
			array('37','1484','ordertime','vtiger_assets','2','7','ordertime','Czas realizacji','1','2','','100','7','192','1','NN~O','1','','BAS','1','0','0',"decimal(10,2)","BLOCK_INFORMATION_TIME",array()),
		);
		
		$Contacts = array(
			array('4','1503','contactstatus','vtiger_contactdetails','2','15','contactstatus','Status','1','2','','100','29','4','1','V~O','1',NULL,'BAS','1','0','0',"varchar(255)","LBL_CONTACT_INFORMATION", array('Active','Inactive'))
		);
		
		$OSSSoldServices = array(
			array('58','1483','ordertime','vtiger_osssoldservices','2','7','ordertime','Czas realizacji','1','2','','100','26','25','1','NN~O','1','','BAS','1','0','0',"decimal(10,2)","LBL_CUSTOM_INFORMATION"),
		);
		
		$setToCRM = array('HelpDesk'=>$HelpDesk,'Assets'=>$Assets,'OSSSoldServices'=>$OSSSoldServices,'Contacts'=>$Contacts,);
		
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
				if($this->checkFieldExists($field, $moduleName)){
					continue;
				}
				try {
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
				} catch (Exception $e) {
					//$e->getMessage()
				}
			}
		}
	}
	
	public function addWorkflowType (){
		global $log, $adb;

		$newTaskType = array();
		$newTaskType[] = array('VTUpdateClosedTime','Update Closed Time','VTUpdateClosedTime','modules/com_vtiger_workflow/tasks/VTUpdateClosedTime.inc','com_vtiger_workflow/taskforms/VTUpdateClosedTime.tpl','{"include":[],"exclude":[]}',NULL);
		$newTaskType[] = array('VTSendNotificationTask','Send Notification','VTSendNotificationTask','modules/com_vtiger_workflow/tasks/VTSendNotificationTask.inc','com_vtiger_workflow/taskforms/VTSendNotificationTask.tpl','{"include":["Calendar","Events"],"exclude":[]}',NULL);
		$newTaskType[] = array('VTAddressBookTask','Create Address Book','VTAddressBookTask','modules/com_vtiger_workflow/tasks/VTAddressBookTask.inc','com_vtiger_workflow/taskforms/VTAddressBookTask.tpl','{"include":["Contacts"],"exclude":[]}',NULL);
		
		foreach($newTaskType as $taskType){
			$result = $adb->query("SELECT * FROM `com_vtiger_workflow_tasktypes` WHERE tasktypename = '".$taskType[0]."';");
			if($adb->num_rows($result) == 0){
				$taskTypeId = $adb->getUniqueID("com_vtiger_workflow_tasktypes");
				$adb->pquery("INSERT INTO com_vtiger_workflow_tasktypes (id, tasktypename, label, classname, classpath, templatepath, modules, sourcemodule) values (?,?,?,?,?,?,?,?)", array($taskTypeId, $taskType[0], $taskType[1], $taskType[2],  $taskType[3], $taskType[4], $taskType[5], $taskType[6]));
			}
		}
	}
	
	public function addWorkflow (){
		global $log, $adb;
		$workflow = array();
		$result = $adb->query("SELECT * FROM `com_vtiger_workflows` WHERE summary = 'Update Closed Time';");
		if($adb->num_rows($result) == 0){
			$workflow[] = array(54,'HelpDesk','Update Closed Time','[{"fieldname":"ticketstatus","operation":"is","value":"Rejected","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]',2,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		}
		$result = $adb->query("SELECT * FROM `com_vtiger_workflows` WHERE summary = 'Generate mail address book';");
		if($adb->num_rows($result) == 0){
			$workflow[] = array(55,'Contacts','Generate mail address book','[]',3,NULL,'basic',6,NULL,NULL,NULL,NULL,NULL,NULL);
		}
		
		$workflowTask = array();
		$workflowTask[] = array(121,54,'Update Closed Time','O:18:"VTUpdateClosedTime":6:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"54";s:7:"summary";s:18:"Update Closed Time";s:6:"active";b:1;s:7:"trigger";N;s:2:"id";i:121;}');
		$workflowTask[] = array(123,55,'Generate mail address book','O:17:"VTAddressBookTask":7:{s:18:"executeImmediately";b:0;s:10:"workflowId";s:2:"55";s:7:"summary";s:26:"Generate mail address book";s:6:"active";b:1;s:7:"trigger";N;s:4:"test";s:0:"";s:2:"id";i:123;}');

		require_once 'modules/com_vtiger_workflow/include.inc';
		require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
		require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
		$workflowManager = new VTWorkflowManager($adb);
		$taskManager = new VTTaskManager($adb);
		foreach($workflow as $record){
			$newWorkflow = $workflowManager->newWorkFlow($record[1]);
			$newWorkflow->description = $record[2];
			$newWorkflow->test = $record[3];
			$newWorkflow->executionCondition = $record[4];
			$newWorkflow->defaultworkflow = $record[5];
			$newWorkflow->type = $record[6];
			$newWorkflow->filtersavedinnew = $record[7];
			$workflowManager->save($newWorkflow);
			foreach($workflowTask as $indexTask){
				if($indexTask[1] == $record[0]){
					$task = $taskManager->unserializeTask($indexTask[3]);
					$task->id = '';
					$task->workflowId = $newWorkflow->id;
					$taskManager->saveTask($task);
				}
			}
		}
	}
	
	public function actionMapping(){
		global $log,$adb;
		$result = $adb->query("SELECT * FROM `vtiger_actionmapping` WHERE actionname = 'EditableComments';");
		if($adb->num_rows($result) != 0)
			return;
		$adb->query("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES ('16', 'EditableComments','0');");

		$profileresult = $adb->query('SELECT * FROM `vtiger_profile`');
		for($p = 0; $p < $adb->num_rows($profileresult); $p++){
			$profileId = $adb->query_result_raw($profileresult, $p, 'profileid');
			$adb->pquery("INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)", array($profileId, 40, 16, 0));
		}
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
	
	public function updateDatabase(){
		global $log,$adb;
		$result1 = $adb->query("SELECT tabid FROM `vtiger_tab` WHERE name = 'Calendar'");
		$result2 = $adb->query("SELECT tabid FROM `vtiger_tab` WHERE name = 'Events'");
		$tabid1 = $adb->query_result_raw($result1, 0, 'tabid');
		$tabid2 = $adb->query_result_raw($result2, 0, 'tabid');
		$adb->query("UPDATE vtiger_field SET `uitype` = '300' WHERE `columnname` = 'description' AND `tablename` = 'vtiger_crmentity' AND tabid NOT IN( '$tabid1','$tabid2');");
		$result = $adb->query("SELECT * FROM `vtiger_language` WHERE prefix = 'pt_br'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_language`(`name`,`prefix`,`label`,`lastupdated`,`sequence`,`isdefault`,`active`) values ('Portuguese','pt_br','Brazilian Portuguese','2014-12-11 11:12:39',NULL,0,1);");
			$adb->query("UPDATE vtiger_language_seq SET `id` = (SELECT count(*) FROM `vtiger_language`);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_links` WHERE linklabel = 'KPI'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_links");
			$adb->query("insert  into `vtiger_links`(`linkid`,`tabid`,`linktype`,`linklabel`,`linkurl`,`linkicon`,`sequence`,`handler_path`,`handler_class`,`handler`) values ($lastId,".getTabid('Potentials').",'DASHBOARDWIDGET','KPI','index.php?module=Potentials&view=ShowWidget&name=Kpi',NULL,11,NULL,NULL,NULL);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_UPDATES_HISTORY'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,9,'LBL_UPDATES_HISTORY',NULL,'LBL_UPDATES_HISTORY_DESCRIPTION','index.php?parent=Settings&module=Updates&view=Index',3,0,0);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'Backup'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,7,'Backup','','LBL_BACKUP_DESCRIPTION','index.php?parent=Settings&module=BackUp&view=Index',20,0,0);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_CONFREPORT'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,9,'LBL_CONFREPORT','','LBL_CONFREPORT_DESCRIPTION','index.php?parent=Settings&module=ConfReport&view=Index',20,0,0);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_ACTIVITY_TYPES'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,4,'LBL_ACTIVITY_TYPES','','LBL_ACTIVITY_TYPES_DESCRIPTION','index.php?parent=Settings&module=Calendar&view=ActivityTypes',25,0,0);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_settings_field` WHERE name = 'LBL_WIDGETS_MANAGEMENT'");
		if($adb->num_rows($result) == 0){
			$lastId = $adb->getUniqueID("vtiger_settings_field");
			$adb->query("insert  into `vtiger_settings_field`(`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) values ($lastId,2,'LBL_WIDGETS_MANAGEMENT',NULL,'LBL_WIDGETS_MANAGEMENT_DESCRIPTION','index.php?module=WidgetsManagement&parent=Settings&view=Configuration',12,0,0);");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS`vtiger_backup` (
`backupid` int(11) NOT NULL AUTO_INCREMENT,
`file_name` varchar(50) NOT NULL,
`created_at` datetime NOT NULL,
`create_time` varchar(40) NOT NULL,
`how_many` int(11) NOT NULL,
PRIMARY KEY (`backupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_backup_db_tmp` (
  `tmpbackupid` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`tmpbackupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_backup_db_tmp_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `time` varchar(40) DEFAULT '0',
  `howmany` int(11) NOT NULL DEFAULT '0',
  `tables_prepare` tinyint(1) NOT NULL,
  `backup_db` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_backup_dir` (
  `name` varchar(200) NOT NULL,
  `backup` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_backup_ftp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(50) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_contactsbookmails` (
  `contactid` int(19) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `users` text,
  KEY `email` (`email`,`name`),
  KEY `contactid` (`contactid`),
  CONSTRAINT `vtiger_contactsbookmails_ibfk_1` FOREIGN KEY (`contactid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		$adb->query("UPDATE vtiger_ossmailtemplates SET `name` = 'Send invitations', subject = '#a#267#aEnd#:  #a#255#aEnd#', content = '<table border=\"0\" cellpadding=\"8\" cellspacing=\"0\" style=\"width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;\" summary=\"\"><tbody><tr><td style=\"background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;\">\r\n			<h3 style=\"padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;\"><span>#a#255#aEnd#</span></h3>\r\n			</td>\r\n		</tr><tr><td>\r\n			<div style=\"padding:2px;\">\r\n			<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" ><tbody><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#257#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#257#aEnd# #a#258#aEnd#</td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#259#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#259#aEnd# #a#260#aEnd#</td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#264#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#264#aEnd#</td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#277#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#277#aEnd#</td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#267#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#267#aEnd#</td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#271#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#271#aEnd#</td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#268#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\"><span><span>#a#268#aEnd#</span><span dir=\"ltr\"> (<a href=\"https://maps.google.pl/maps?q=%23a%23268%23aEnd%23\" style=\"color:#20c;white-space:nowrap;\">mapa</a>)</span></span></td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#265#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#265#aEnd#</td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#275#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#275#aEnd#</td>\r\n					</tr><tr><td style=\"padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;\" valign=\"top\">\r\n						<div><i style=\"font-style:normal;\">#b#256#bEnd#</i></div>\r\n						</td>\r\n						<td style=\"padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;\" valign=\"top\">#a#256#aEnd#</td>\r\n					</tr></tbody></table></div>\r\n			</td>\r\n		</tr><tr><td style=\"background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;\">\r\n			<p>YetiForce CRM - Notification activities on the calendar</p>\r\n			</td>\r\n		</tr></tbody></table>' WHERE `name` = 'Send Notification Email to Record Owner' AND `oss_module_list` = 'Events';");
		$adb->query("DROP TABLE IF EXISTS `vtiger_continent`;");
		$adb->query("DROP TABLE IF EXISTS `vtiger_continent_seq`;");
		$adb->query("ALTER TABLE vtiger_ossmenumanager ADD COLUMN `color` VARCHAR(10) NULL; ");
		$adb->query("DROP TABLE IF EXISTS `vtiger_symbol`;");
		$adb->query("DROP TABLE IF EXISTS `vtiger_symbol_seq`;");
		$adb->query("DELETE FROM com_vtiger_workflow_activatedonce WHERE `entity_id` = '101';");
		$adb->query("UPDATE `com_vtiger_workflowtasks` SET `summary` = 'Update Inventory Products' WHERE `task_id` = '1'; ");
		$result1 = $adb->query("SELECT workflow_id FROM `com_vtiger_workflows` WHERE summary = 'Workflow for Events when Send Notification is True'");
		$result2 = $adb->query("SELECT ossmailtemplatesid FROM `vtiger_ossmailtemplates` WHERE name = 'Send invitations'");
		if($adb->num_rows($result) == 1){
			$workflow_id = $adb->query_result_raw($result1, 0, 'workflow_id');
			$ossmailtemplatesid = $adb->query_result_raw($result1, 0, 'ossmailtemplatesid');
			$adb->query("UPDATE `com_vtiger_workflowtasks` SET `summary` = 'Send invitations',`task` = 'O:22:\"VTSendNotificationTask\":7:{s:18:\"executeImmediately\";b:1;s:10:\"workflowId\";s:2:\"$workflow_id\";s:7:\"summary\";s:16:\"Send invitations\";s:6:\"active\";b:0;s:7:\"trigger\";N;s:8:\"template\";s:2:\"$ossmailtemplatesid\";s:2:\"id\";i:122;}' WHERE `summary` = 'Send Notification is True'; ");
		}
		$adb->query("UPDATE vtiger_calendar_default_activitytypes SET fieldname = 'End of support for contact' WHERE `module` = 'Contacts' AND fieldname = 'support_end_date' ;");
		$adb->query("UPDATE vtiger_calendar_default_activitytypes SET fieldname = 'Birthdays of contacts' WHERE `module` = 'Contacts' AND fieldname = 'birthday';");
		$result = $adb->query("SELECT * FROM `vtiger_cron_task` WHERE name = 'Backup'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_cron_task`(`name`,`handler_file`,`frequency`,`laststart`,`lastend`,`status`,`module`,`sequence`,`description`) values ('Backup','cron/backup.service',43200,NULL,NULL,0,'BackUp',11,NULL);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_dataaccess` WHERE summary = 'Lock edit on the status'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_dataaccess`(`module_name`,`summary`,`data`) values ('HelpDesk','Lock edit on the status','a:1:{i:0;a:2:{s:2:\"cf\";b:0;s:2:\"an\";s:21:\"Vtiger!!blockEditView\";}}');");
			$adb->query("insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (51,(SELECT dataaccessid FROM vtiger_dataaccess WHERE summary = 'Lock edit on the status'),'ticketstatus','is','Closed',0,'picklist');");
			$adb->query("insert  into `vtiger_dataaccess_cnd`(`dataaccess_cndid`,`dataaccessid`,`fieldname`,`comparator`,`val`,`required`,`field_type`) values (50,(SELECT dataaccessid FROM vtiger_dataaccess WHERE summary = 'Lock edit on the status'),'ticketstatus','is','Rejected',0,'picklist');");
		}
		$adb->query("UPDATE vtiger_field SET `fieldlabel` = 'Campaign status' WHERE `columnname` = 'campaignrelstatus';");
		$adb->query("UPDATE vtiger_field SET `uitype` = '10',displaytype = '10' WHERE `columnname` = 'product_id' AND `tablename` = 'vtiger_troubletickets';");
		$adb->query("UPDATE vtiger_field SET `typeofdata` = 'D~M', `quickcreate` = '2' WHERE `columnname` = 'startdate' AND `tablename` = 'vtiger_projecttask';");
		$adb->query("UPDATE vtiger_field SET `typeofdata` = 'D~M', `quickcreate` = '2' WHERE `columnname` = 'targetenddate' AND `tablename` = 'vtiger_projecttask';");
		
		$result = $adb->query("SELECT * FROM `vtiger_fieldmodulerel` WHERE module = 'HelpDesk' AND relmodule = 'Products'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_fieldmodulerel`(`fieldid`,`module`,`relmodule`,`status`,`sequence`) values ((SELECT fieldid FROM `vtiger_field` WHERE `columnname` = 'product_id' AND `tablename` = 'vtiger_troubletickets'),'HelpDesk','Products',NULL,1);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_fieldmodulerel` WHERE module = 'HelpDesk' AND relmodule = 'Services'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_fieldmodulerel`(`fieldid`,`module`,`relmodule`,`status`,`sequence`) values ((SELECT fieldid FROM `vtiger_field` WHERE `columnname` = 'product_id' AND `tablename` = 'vtiger_troubletickets'),'HelpDesk','Services',NULL,2);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_fieldmodulerel` WHERE module = 'HelpDesk' AND relmodule = 'Assets'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_fieldmodulerel`(`fieldid`,`module`,`relmodule`,`status`,`sequence`) values ((SELECT fieldid FROM `vtiger_field` WHERE `columnname` = 'pssold_id' AND `tablename` = 'vtiger_troubletickets'),'HelpDesk','Assets',NULL,NULL);");
		}
		$result = $adb->query("SELECT * FROM `vtiger_fieldmodulerel` WHERE module = 'HelpDesk' AND relmodule = 'OSSSoldServices'");
		if($adb->num_rows($result) == 0){
			$adb->query("insert  into `vtiger_fieldmodulerel`(`fieldid`,`module`,`relmodule`,`status`,`sequence`) values ((SELECT fieldid FROM `vtiger_field` WHERE `columnname` = 'pssold_id' AND `tablename` = 'vtiger_troubletickets'),'HelpDesk','OSSSoldServices',NULL,NULL);");
		}
		$adb->query("DELETE FROM `vtiger_picklist` WHERE `name` = 'productcategory'; ");
		$adb->query("UPDATE vtiger_settings_field SET `sequence` = '1' WHERE `name` = 'License';");
		$adb->query("UPDATE vtiger_settings_field SET `sequence` = '2' WHERE `name` = 'Credits';");
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_links` LIKE 'linkdata';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_links` ADD COLUMN `linkdata` TEXT NULL;");
		}
		$result = $adb->query("SHOW COLUMNS FROM `vtiger_module_dashboard_widgets` LIKE 'isdefault';");
		if($adb->num_rows($result) == 0){
			$adb->query("ALTER TABLE `vtiger_module_dashboard_widgets` ADD COLUMN `isdefault` INT(1) DEFAULT 0 NULL;");
		}
	}
}