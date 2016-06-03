<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @license https://yetiforce.com/en/implementer/license.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php');
include_once('modules/ModTracker/ModTracker.php');

class YetiForceUpdate
{

	var $package;
	var $modulenode;
	var $return = true;
	var $filesToDelete = [
		'libraries/timelineJS3',
		'config/modules/ModComments.php',
		'modules/ModComments/actions/TimelineAjax.php',
		'libraries/Smarty/libs/sysplugins/smarty_config_source.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_config.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_function_call_handler.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_get_include_path.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_utility.php',
		'layouts/basic/modules/Vtiger/RecentActivitiesTimeLine_1.tpl',
		'layouts/basic/modules/IGDN',
		'modules/IGDN/actions/SaveAjax.php',
		'modules/IGDN/models/DetailView.php',
		'modules/IGDN/views/EditStatus.php',
		'layouts/basic/modules/IGDNC',
		'modules/IGDNC/actions/SaveAjax.php',
		'modules/IGDNC/models/DetailView.php',
		'modules/IGDNC/views/EditStatus.php',
		'layouts/basic/modules/IIDN',
		'modules/IIDN/actions/SaveAjax.php',
		'modules/IIDN/models/DetailView.php',
		'modules/IIDN/views/EditStatus.php',
		'layouts/basic/modules/IPreOrder',
		'modules/IPreOrder/actions/SaveAjax.php',
		'modules/IPreOrder/models/DetailView.php',
		'modules/IPreOrder/views/EditStatus.php',
		'layouts/basic/modules/ISTDN',
		'modules/ISTDN/actions/SaveAjax.php',
		'modules/ISTDN/models/DetailView.php',
		'modules/ISTDN/views/EditStatus.php',
		'layouts/basic/modules/ISTRN',
		'modules/ISTRN/actions/SaveAjax.php',
		'modules/ISTRN/models/DetailView.php',
		'modules/ISTRN/views/EditStatus.php',
		'layouts/basic/modules/Vtiger/CommentModal.tpl',
		'languages/de_de/NewOrders.php',
		'languages/en_us/NewOrders.php',
		'languages/fr_fr/NewOrders.php',
		'languages/pl_pl/NewOrders.php',
		'languages/pt_br/NewOrders.php',
		'languages/ru_ru/NewOrders.php',
		'layouts/basic/modules/Settings/Vtiger/Announcement.tpl',
		'layouts/basic/modules/Settings/Vtiger/resources/Announcement.js',
		'layouts/basic/modules/Settings/Vtiger/resources/Announcement.min.js',
		'layouts/basic/skins/images/btnAnnounce.png',
		'layouts/basic/skins/images/btnAnnounceOff.png',
		'modules/NewOrders',
		'modules/Settings/Vtiger/actions/AnnouncementSaveAjax.php',
		'modules/Settings/Vtiger/models/Announcement.php',
		'modules/Settings/Vtiger/views/AnnouncementEdit.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_filter_handler.php',
		'libraries/Smarty/libs/sysplugins/smarty_internal_write_file.php',
	];

	function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	function preupdate()
	{
		return true;
	}

	function update()
	{
		$this->workflowTask($this->getWorkflowTask(1));
		$this->setTablesScheme($this->getTablesAction(1));
		$this->setAlterTables($this->getAlterTables(1));
		$this->setRecords($this->getRecords(1));
		$this->addCron([['LBL_SEND_NOTIFICATIONS', 'modules/Home/cron/Notifications.php', '900', NULL, NULL, '1', 'Home', '19', '']]);
		$this->actionMapp($this->getActionMapp(1));
		$this->setRelations($this->getRelations(1));
		$this->addHandler([['vtiger.entity.aftersave.final', 'modules/Accounts/handlers/SaveChanges.php', 'SaveChanges', '', '1', '[]']]);
		$this->updatePack();
	}

	function postupdate()
	{
		$db = PearDatabase::getInstance();
		if ($this->updateLabelsByModule) {
			Vtiger_Cache::set('module', $this->updateLabelsByModule, NULL);
			Settings_Search_Module_Model::UpdateLabels(['tabid' => $this->updateLabelsByModule]);
		}
		$menuRecordModel = new Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		Vtiger_Deprecated::createModuleMetaFile();
		Vtiger_Access::syncSharingAccess();
		$dirName = 'cache/updates';
		$result = true;
		$modulenode = $this->modulenode;
		$db->query('SET FOREIGN_KEY_CHECKS = 1;');
		$db->insert('yetiforce_updates', [
			'user' => Users_Record_Model::getCurrentUserModel()->get('user_name'),
			'name' => $modulenode->label,
			'from_version' => $modulenode->from_version,
			'to_version' => $modulenode->to_version,
			'result' => $result,
		]);
		if ($result) {
			$db->update('vtiger_version', ['current_version' => $modulenode->to_version]);
		}
		Vtiger_Functions::recurseDelete($dirName);
		Vtiger_Functions::recurseDelete('cache/templates_c');
		header('Location: ' . vglobal('site_URL'));
		exit;
	}

	public function getFieldsToMove($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'neworders_no' => ['moduleName' => 'NewOrders', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_NEWORDERS_INFORMATION'],
					'modifiedtime' => ['moduleName' => 'NewOrders', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_NEWORDERS_INFORMATION'],
					'smownerid' => ['moduleName' => 'NewOrders', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_NEWORDERS_INFORMATION'],
					'createdtime' => ['moduleName' => 'NewOrders', 'toBlock' => 'LBL_CUSTOM_INFORMATION', 'fromBlock' => 'LBL_NEWORDERS_INFORMATION'],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function move($data)
	{
		$log = vglobal('log');
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' ()| Start');
		$db = PearDatabase::getInstance();
		$move = [];
		$removeBlocks = [];
		foreach ($data as $columName => $info) {
			if (empty($info)) {
				continue;
			}
			$tabId = getTabid($info['moduleName']);
			if ($info['removeBlock']) {
				$removeBlocks[$tabId][] = $info['fromBlock'];
			}
			$result = $db->pquery('SELECT fieldid FROM vtiger_field LEFT JOIN vtiger_blocks ON vtiger_blocks.`blockid` = vtiger_field.`block` WHERE columnname = ? AND vtiger_field.tabid = ? AND vtiger_blocks.`blocklabel` = ?;', [$columName, $tabId, $info['fromBlock']]);
			$id = $db->getSingleValue($result);
			if (empty($id)) {
				continue;
			}
			if (!empty($info['moveFor'])) {
				$result = $db->pquery('SELECT sequence,block FROM vtiger_field WHERE columnname = ? AND tabid = ?;', [$info['moveFor'], $tabId]);
			} elseif (!empty($info['toBlock'])) {
				$result = $db->pquery('SELECT MAX(vtiger_field.sequence) AS sequence,vtiger_blocks.`blockid` as block FROM vtiger_field LEFT JOIN vtiger_blocks ON vtiger_blocks.`blockid` = vtiger_field.`block` WHERE vtiger_blocks.`blocklabel` = ? AND vtiger_field.tabid = ?', [$info['toBlock'], $tabId]);
				$checkBlock = $db->query_result($result, 0, 'block');
				if (!$checkBlock) {
					$result = $db->pquery('SELECT vtiger_blocks.sequence = 0 AS sequence,  vtiger_blocks.`blockid` AS block FROM vtiger_blocks WHERE vtiger_blocks.`blocklabel` = ? AND vtiger_blocks.tabid = ?', [$info['toBlock'], $tabId]);
				}
			}
			if ($result) {
				$row = $db->getRow($result);
				$seq = $row['sequence'];
				$block = $row['block'];
			}
			if (!empty($block)) {
				$move[] = ['fieldid' => $id, 'sequence' => (int) $seq + 1, 'block' => $block];
			}
		}

		//This will update the fields sequence for the updated blocks
		if ($move) {
			Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($move);
		}
		if ($removeBlocks) {
//			$this->deleteBlocks($removeBlocks);
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function addHandler($addHandler = [])
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($addHandler) {
			$em = new VTEventsManager($db);
			foreach ($addHandler as $handler) {
				$em->registerHandler($handler[0], $handler[1], $handler[2], $handler[3], $handler[5]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function addCron($addCrons = [])
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($addCrons) {
			foreach ($addCrons as $cron) {
				$result = $db->pquery('SELECT * FROM `vtiger_cron_task` WHERE name = ? AND handler_file = ?;', [$cron[0], $cron[1]]);
				if ($db->getRowCount($result) == 0) {
					Vtiger_Cron::register($cron[0], $cron[1], $cron[2], $cron[6], $cron[5], 0, $cron[8]);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function setRecords($data)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		foreach ($data as $record) {
			if (empty($record)) {
				continue;
			}
			$result = $db->query($record['sql']);
			$num = $db->getRowCount($result);
			if (!$num && $record['type'] == 'add') {
				$recordModel = Vtiger_Record_Model::getCleanInstance($record['moduleName']);
				foreach ($record['data'] as $name => $value) {
					$recordModel->set($name, $value);
				}
				$recordModel->save();
				if ('OSSMailTemplates' == $record['moduleName'] && isset($record['data']['sysname']))
					$db->update('vtiger_ossmailtemplates', ['sysname' => 'SendNotificationsViaMail'], '`ossmailtemplatesid` = ?;', [$recordModel->getId()]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function getRecords($index)
	{
		$tasks = [];
		switch ($index) {
			case 1:
				$tasks = [
					['type' => 'add', 'moduleName' => 'OSSMailTemplates', 'sql' => 'SELECT ossmailtemplatesid FROM vtiger_ossmailtemplates WHERE sysname = "SendNotificationsViaMail";',
						'data' => [
							'name' => 'Send notifications',
							'sysname' => 'SendNotificationsViaMail',
							'oss_module_list' => 'System',
							'subject' => 'Notifications #s#CurrentDate#sEnd#',
							'content' => '#s#Notifications#sEnd#',
							'ossmailtemplates_type' => 'PLL_MODULE']],
				];
				break;
			default:
				break;
		}
		return $tasks;
	}

	function getWorkflowTask($index)
	{
		$tasks = [];
		switch ($index) {
			case 1:
				$tasks = [
					['moduleName' => 'Leads', 'summary' => 'Weryfikacja danych', 'changes' => ['summary' => 'Data verification', 'todo' => 'Data verification', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Zapoznanie się z aktualnościami na stronie', 'changes' => ['summary' => 'Red news on the website', 'todo' => 'Red news on the website', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Zapoznanie się z aktualnościami społecznościowymi', 'changes' => ['summary' => 'Read social networking news  ', 'todo' => 'Read social networking news  ', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Kontakt telefoniczny lub mailowy', 'changes' => ['summary' => 'Mail or call', 'todo' => 'Mail or call', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Określenie osoby decyzyjnej', 'changes' => ['summary' => 'Determine the decision maker ', 'todo' => 'Determine the decision maker ', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Prezentacja doświadczenia firmy', 'changes' => ['summary' => 'Present experience of company', 'todo' => 'Present experience of company', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Prezentacja produktów i usług', 'changes' => ['summary' => 'Present products and services', 'todo' => 'Present products and services', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Wstępna analiza potrzeb Klienta', 'changes' => ['summary' => "Preliminary analysis of the client's needs", 'todo' => "Preliminary analysis of the client's needs", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => "Uzupełnienie informacji o: 'Usługi obce'", 'changes' => ['summary' => "Update: 'Outsourced services'", 'todo' => "Update: 'Outsourced services'", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => "Uzupełnienie informacji o: 'Produkty obce'", 'changes' => ['summary' => "Update: 'Outsourced products'", 'todo' => "Update: 'Outsourced products'", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Uzupełnienie wstępnych ustaleń w systemie', 'changes' => ['summary' => 'Update preliminary agreements in the system', 'todo' => 'Update preliminary agreements in the system', 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => 'Uszczegółowienie potrzeb Klienta', 'changes' => ['summary' => "Specify client's needs", 'todo' => "Specify client's needs", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => "Uzupełnienie informacji o: 'Zainteresowany usługami'", 'changes' => ['summary' => "Update information on: 'Interested in services' ", 'todo' => "Update information on: 'Interested in services' ", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
					['moduleName' => 'Leads', 'summary' => "Uzupełnienie informacji o: 'Zainteresowany produktami'", 'changes' => ['summary' => "Update information on: 'Interested in products'", 'todo' => "Update information on: 'Interested in products'", 'status' => '', 'doNotDuplicate' => '', 'duplicateStatus' => '', 'updateDates' => '']],
				];
				break;
			default:
				break;
		}
		return $tasks;
	}

	function workflowTask($data)
	{
		$db = PearDatabase::getInstance();
		foreach ($data as $taskData) {
			if (empty($taskData)) {
				continue;
			}
			$result = $db->pquery('SELECT task FROM `com_vtiger_workflowtasks` t INNER JOIN com_vtiger_workflows w ON w.workflow_id = t.workflow_id 
				WHERE t.summary = ? AND w.module_name = ?;', [$taskData['summary'], $taskData['moduleName']]);
			while ($task = $db->getSingleValue($result)) {
				$tm = new VTTaskManager($db);
				$task = $tm->unserializeTask($task);
				$save = false;
				foreach ($taskData['changes'] as $name => $value) {
					if (!isset($task->$name) || $task->$name != $value) {
						$task->$name = $value;
						$save = true;
					}
				}
				if ($save) {
					$tm->saveTask($task);
				}
			}
		}
	}

	public function updatePack()
	{
		$db = PearDatabase::getInstance();

		$db->update('vtiger_field', ['defaultvalue' => 'PLL_TO_APPROVAL'], '`tabid` = ? AND columnname = ?;', [getTabid('Reservations'), 'reservations_status']);
		$modules = ['ModTracker', 'Users', 'Mobile', 'Integration', 'WSAPP', 'ConfigEditor', 'FieldFormulas', 'VtigerBackup', 'CronTasks', 'Import', 'Tooltip', 'CustomerPortal', 'Home'];
		$db->pquery('DELETE p FROM vtiger_profile2tab p INNER JOIN vtiger_tab t ON t.`tabid` = p.`tabid` WHERE t.`name` IN (' . $db->generateQuestionMarks($modules) . ');', $modules);
		$result = $db->query('SHOW TABLE STATUS WHERE NAME LIKE "vtiger_neworders";');
		if ($result->rowCount()) {
			$this->renameModule();
		}
		$db->update('vtiger_account', ['active' => 1], 'parentid = ?', [0]);
	}

	public function renameModule()
	{
		$db = PearDatabase::getInstance();
		$tabId = getTabid('NewOrders');
		$this->move($this->getFieldsToMove(1));
		$this->setFields($this->getFields(1));
		$result = $db->pquery('SELECT workflow_id FROM com_vtiger_workflows WHERE module_name = ?', ['NewOrders']);
		while ($recordId = $db->getSingleValue($result)) {
			$recordModel = Settings_Workflows_Record_Model::getInstance($recordId);
			$recordModel->delete();
		}
		$db->query('ALTER TABLE `vtiger_neworders` DROP KEY `vtiger_neworderscf` , DROP FOREIGN KEY `vtiger_neworderscf`;');
		$db->query('ALTER TABLE `vtiger_neworderscf` DROP FOREIGN KEY `fk_1_vtiger_neworderscf`;');
		$db->query('DROP TABLE IF EXISTS vtiger_announcement;');
		$db->query('RENAME TABLE vtiger_neworders TO `u_yf_announcement`;');
		$db->query('ALTER TABLE `u_yf_announcement` CHANGE `newordersid` `announcementid` INT(11) NOT NULL,
					CHANGE `neworders_no` `announcement_no` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci NULL,
					ADD PRIMARY KEY (`announcementid`);');
		$db->query('RENAME TABLE vtiger_neworderscf TO `u_yf_announcementcf`;');
		$db->query('ALTER TABLE `u_yf_announcementcf` 
					CHANGE `newordersid` `announcementid` INT(11) NOT NULL, 
					DROP KEY `PRIMARY`, ADD PRIMARY KEY(`announcementid`) ;');
		$db->query('ALTER TABLE `u_yf_announcement`
					ADD CONSTRAINT `fk_1_u_yf_announcement` 
					FOREIGN KEY (`announcementid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;');
		$db->query('ALTER TABLE `u_yf_announcementcf`
					ADD CONSTRAINT `fk_1_u_yf_announcementcf` 
					FOREIGN KEY (`announcementid`) REFERENCES `u_yf_announcement` (`announcementid`) ON DELETE CASCADE ;');
		$db->update('vtiger_blocks', ['blocklabel' => 'LBL_ANNOUNCEMENTS_INFORMATION'], 'blocklabel = ? AND tabid = ?', ['LBL_NEWORDERS_INFORMATION', $tabId]);
		$db->update('vtiger_customview', ['entitytype' => 'Announcements'], 'entitytype = ?', ['NewOrders']);
		$db->delete('vtiger_customview', 'entitytype = ? AND presence <> ?', ['Announcements', 0]);
		$db->update('vtiger_cvcolumnlist', ['columnname' => 'u_yf_announcement:subject:subject:Announcements_LBL_SUBJECT:V'], 'columnname = ?', ['vtiger_neworders:subject:subject:NewOrders_LBL_SUBJECT:V']);
		$db->update('vtiger_cvcolumnlist', ['columnname' => 'vtiger_crmentity:smownerid:assigned_user_id:Announcements_Assigned_To:V'], 'columnname = ?', ['vtiger_crmentity:smownerid:assigned_user_id:NewOrders_Assigned_To:V']);
		$db->update('vtiger_cvcolumnlist', ['columnname' => 'vtiger_crmentity:createdtime:createdtime:Announcements_Created_Time:DT'], 'columnname = ?', ['vtiger_crmentity:createdtime:createdtime:NewOrders_Created_Time:DT']);
		$db->update('vtiger_cvcolumnlist', ['columnname' => 'vtiger_crmentity:modifiedtime:modifiedtime:Announcements_Modified_Time:DT'], 'columnname = ?', ['vtiger_crmentity:modifiedtime:modifiedtime:NewOrders_Modified_Time:DT']);
		$db->delete('vtiger_cvcolumnlist', 'columnname LIKE "%NewOrders_%"');
		$db->update('vtiger_entityname', ['modulename' => 'Announcements', 'tablename' => 'u_yf_announcement', 'entityidfield' => 'announcementid', 'entityidcolumn' => 'announcementid', 'searchcolumn' => 'subject'], 'modulename = ?', ['NewOrders']);
		$db->update('vtiger_field', ['tablename' => 'u_yf_announcement'], 'tablename = ?', ['vtiger_neworders']);
		$db->update('vtiger_field', ['columnname' => 'announcement_no', 'fieldname' => 'announcement_no'], 'columnname = ? AND tabid = ?', ['neworders_no', $tabId]);
		$db->update('vtiger_field', ['quickcreate' => 2], 'columnname = ? AND tabid = ?', ['description', $tabId]);
		$db->update('vtiger_modentity_num', ['semodule' => 'Announcements'], 'semodule = ?', ['NewOrders']);
		$db->update('vtiger_ws_entity', ['name' => 'Announcements'], 'name = ?', ['NewOrders']);
		$db->update('vtiger_crmentity', ['setype' => 'Announcements'], 'setype = ?', ['NewOrders']);
		$this->setTablesScheme($this->getTablesAction(2));
		$result = $db->pquery('SELECT dataaccessid FROM vtiger_dataaccess WHERE module_name = ?', ['NewOrders']);
		if ($result->rowCount()) {
			$ids = $db->getArrayColumn($result, 'dataaccessid');
			$db->delete('vtiger_dataaccess', 'module_name = ?', ['NewOrders']);
			$db->delete('vtiger_dataaccess_cnd', 'dataaccessid IN (' . $db->generateQuestionMarks($ids) . ')', $ids);
		}
		$db->delete('vtiger_settings_field', '`name` = ? AND linkto = ?', ['LBL_ANNOUNCEMENT', 'index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit']);
		$db->update('vtiger_tab', ['name' => 'Announcements', 'tablabel' => 'Announcements'], '`name` = ?', ['NewOrders']);
		$db->update('vtiger_announcementstatus', ['presence' => 0], '`announcementstatus` = ?', ['PLL_PUBLISHED']);
		ModTracker::enableTrackingForModule($tabId);
		$this->updateLabelsByModule = $tabId;
	}

	function getRelations($index)
	{
		$ralations = [];
		switch ($index) {
			case 1:
				$ralations = [
					['type' => 'remove', 'data' => [189, 'Assets', 'OSSTimeControl', 'get_dependents_list', 3, 'OSSTimeControl', 0, 'ADD', 0, 0, 0]],
				];
				break;
			default:
				break;
		}
		return $ralations;
	}

	function setRelations($data)
	{
		$db = PearDatabase::getInstance();
		if (!empty($data)) {
			foreach ($data as $relation) {
				if (empty($relation)) {
					continue;
				}
				list($id, $moduleName, $relModuleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment) = $relation['data'];
				$tabid = getTabid($moduleName);
				$relTabid = getTabid($relModuleName);
				$result = $db->pquery("SELECT 1 FROM `vtiger_relatedlists` WHERE tabid=? AND related_tabid = ? AND name = ? AND label = ?;", [$tabid, $relTabid, $name, $label]);
				if ($result->rowCount() == 0 && $relation['type'] == 'add') {
					$sequence = $this->getMax('vtiger_relatedlists', 'sequence', "WHERE tabid = $tabid");
					$db->insert('vtiger_relatedlists', [
						'relation_id' => $db->getUniqueID('vtiger_relatedlists'),
						'tabid' => $tabid,
						'related_tabid' => $relTabid,
						'name' => $name,
						'sequence' => $sequence,
						'label' => $label,
						'presence' => $presence,
						'actions' => $actions,
						'favorites' => $favorites,
						'creator_detail' => $creatorDetail,
						'relation_comment' => $relationComment
					]);
				} elseif ($result->rowCount() > 0 && $relation['type'] == 'remove') {
					$db->delete('vtiger_relatedlists', '`tabid` = ? AND `related_tabid` = ? AND `name` = ?;', [$tabid, $relTabid, $name]);
				}
			}
		}
	}

	function getActionMapp($index)
	{
		$actions = [];
		switch ($index) {
			case 1:
				$actions = [
					['type' => 'add', 'name' => 'ReceivingMailNotifications', 'tabsData' => [getTabid('Dashboard')]],
					['type' => 'add', 'name' => 'WatchingRecords', 'tabsData' => [getTabid('ModComments')]],
					['type' => 'add', 'name' => 'WatchingModule', 'tabsData' => [getTabid('ModComments')]],
					['type' => 'add', 'name' => 'ReviewingUpdates'],
				];
				break;
			default:
				break;
		}
		return $actions;
	}

	public function actionMapp($actions)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		foreach ($actions as $action) {
			$result = $db->pquery('SELECT actionid FROM vtiger_actionmapping WHERE actionname=? LIMIT 1;', [$action['name']]);
			if (!$db->getRowCount($result)) {
				$securitycheck = 0;
				$key = $this->getMax('vtiger_actionmapping', 'actionid');
				$db->insert('vtiger_actionmapping', ['actionid' => $key, 'actionname' => $action['name'], 'securitycheck' => $securitycheck]);
			} else {
				$key = $db->getSingleValue($result);
			}
			$permission = 0;
			if (isset($action['permission'])) {
				$permission = $action['permission'];
			}

			if (!empty($action['tabsData'])) {
				$tabsData = $action['tabsData'];
			} elseif (!empty($action['sql'])) {
				$result = $db->query($action['sql']);
				$tabsData = $db->getArrayColumn($result, 'tabid');
			} else {
				$result = $db->query("SELECT tabid FROM `vtiger_tab` WHERE `isentitytype` = '1' AND name NOT IN ('SMSNotifier','ModComments','PBXManager','Events','Emails','');");
				$tabsData = $db->getArrayColumn($result, 'tabid');
			}
			$resultP = $db->query('SELECT profileid FROM vtiger_profile;');
			while ($profileId = $db->getSingleValue($resultP)) {
				foreach ($tabsData as $tabid) {
					$resultC = $db->pquery('SELECT activityid FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=? ;', [$profileId, $tabid, $key]);
					if ($db->getRowCount($resultC) == 0) {
						$db->pquery('INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)', [$profileId, $tabid, $key, $permission]);
					}
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function getFields($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['83', '2355', 'announcementstatus', 'vtiger_neworders', '1', '15', 'announcementstatus', 'FL_STATUS', '1', '2', 'PLL_DRAFT', '100', '2', '258', '1', 'V~M', '2', NULL, 'BAS', '1', '', '1', '', '0', "varchar(255) DEFAULT '' ", 'LBL_NEWORDERS_INFORMATION', ['PLL_DRAFT', 'PLL_FOR_ACCEPTANCE', 'PLL_PUBLISHED'], [], 'NewOrders'],
					['83', '2356', 'interval', 'vtiger_neworders', '1', '7', 'interval', 'FL_INTERVAL', '1', '2', '', '100', '3', '258', '1', 'I~O', '1', NULL, 'BAS', '1', 'Edit,Detail,QuickCreateAjax', '0', '', NULL, "smallint(5) DEFAULT NULL", 'LBL_NEWORDERS_INFORMATION', [], [], 'NewOrders'],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	public function setFields($fields)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];

		foreach ($fields as $field) {
			if (!getTabid($field[28]) || self::checkFieldExists($field[28], $field[2], $field[3])) {
				continue;
			}
			$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' addField - ' . print_r($field[2], true));
			$moduleInstance = Vtiger_Module::getInstance($field[28]);
			$blockInstance = Vtiger_Block::getInstance($field[25], $moduleInstance);
			$fieldInstance = new Vtiger_Field();
			$fieldInstance->column = $field[2];
			$fieldInstance->name = $field[6];
			$fieldInstance->label = $field[7];
			$fieldInstance->table = $field[3];
			$fieldInstance->uitype = $field[5];
			$fieldInstance->typeofdata = $field[15];
			$fieldInstance->readonly = $field[8];
			$fieldInstance->displaytype = $field[14];
			$fieldInstance->masseditable = $field[19];
			$fieldInstance->quickcreate = $field[16];
			$fieldInstance->columntype = $field[24];
			$fieldInstance->presence = $field[9];
			$fieldInstance->maximumlength = $field[11];
			$fieldInstance->quicksequence = $field[17];
			$fieldInstance->info_type = $field[18];
			$fieldInstance->helpinfo = $field[20];
			$fieldInstance->summaryfield = $field[21];
			$fieldInstance->generatedtype = $field[4];
			$fieldInstance->defaultvalue = $field[10];
			$fieldInstance->fieldparams = $field[22];
			$blockInstance->addField($fieldInstance);
			if ($field[26] && ($field[5] == 15 || $field[5] == 16 || $field[5] == 33 ))
				$fieldInstance->setPicklistValues($field[26]);
			if ($field[27] && $field[5] == 10) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function getAlterTables($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					['type' => ['add'], 'name' => 'last_reviewed_users', 'table' => 'vtiger_modtracker_basic', 'sql' => "ALTER TABLE `vtiger_modtracker_basic` 
	ADD COLUMN `last_reviewed_users` varchar(255)  COLLATE utf8_general_ci NULL DEFAULT '' after `status` ;"],
					['type' => ['add'], 'name' => 'color', 'table' => 'vtiger_customview', 'sql' => "ALTER TABLE `vtiger_customview` 
	ADD COLUMN `color` varchar(10)  COLLATE utf8_general_ci NULL DEFAULT '' after `sort` ;"],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	function setAlterTables($data)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if (!empty($data)) {
			foreach ($data as $alter) {
				switch ($alter['type'][1]) {
					case 'Key_name':
						$checkSql = 'SHOW KEYS FROM `' . $alter['table'] . '` WHERE Key_name="' . $alter['name'] . '";';
						break;
					case 'Column_name':
						$checkSql = 'SHOW KEYS FROM `' . $alter['table'] . '` WHERE Column_name="' . $alter['name'] . '";';
						break;
					case 'exception':
						$db->query($alter['sql']);
						continue;
						break;
					default:
						if ($alter['type'][0] == 'changeTable') {
							$checkSql = 'SHOW TABLE STATUS WHERE NAME LIKE "' . $alter['table'] . '";';
						} else {
							$checkSql = 'SHOW COLUMNS FROM `' . $alter['table'] . '` LIKE "' . $alter['name'] . '";';
						}
						break;
				}
				$result = $db->query($checkSql);
				$num = $result->rowCount();
				if (( $num == 0 && $alter['type'][0] == 'add') || ($num > 0 && $alter['type'][0] == 'remove')) {
					$db->query($alter['sql']);
				} elseif ($num == 1 && in_array($alter['type'][0], ['change', 'changeTable'])) {
					$row = $db->getRow($result);
					if (strpos($row[$alter['type'][1]], $alter['validType']) === false) {
						$db->query($alter['sql']);
					}
				}
			}
		}
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
	}

	public function getTablesAction($index)
	{
		$tables = [];
		switch ($index) {
			case 1:
				$tables = [
					['type' => 'add', 'name' => 'u_yf_watchdog_schedule', 'sql' => '`u_yf_watchdog_schedule`(
						`userid` int(11) NOT NULL  , 
						`frequency` smallint(6) NOT NULL  , 
						`last_execution` datetime NULL  , 
						PRIMARY KEY (`userid`) , 
						CONSTRAINT `u_yf_watchdog_schedule_ibfk_1` 
						FOREIGN KEY (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE 
					)'],
					['type' => 'add', 'name' => 'u_yf_crmentity_last_changes', 'sql' => '`u_yf_crmentity_last_changes` (
						`crmid` int(11) NOT NULL,
						`fieldname` varchar(255) NOT NULL,
						`user_id` int(11) NOT NULL,
						`date_updated` datetime NOT NULL
					  )'],
				];
				break;
			case 2:
				$tables = [
					['type' => 'add', 'name' => 'u_yf_announcement_mark', 'sql' => "`u_yf_announcement_mark` (
						`announcementid` int(19) NOT NULL,
						`userid` int(19) NOT NULL,
						`date` datetime NOT NULL,
						`status` tinyint(1) NOT NULL DEFAULT '0',
						PRIMARY KEY (`announcementid`,`userid`),
						KEY `userid` (`userid`,`status`),
						KEY `announcementid` (`announcementid`,`userid`,`date`,`status`),
						CONSTRAINT `u_yf_announcement_mark_ibfk_1` FOREIGN KEY (`announcementid`) REFERENCES `u_yf_announcement` (`announcementid`) ON DELETE CASCADE
					  )"],
				];
				break;
			default:
				break;
		}
		return $tables;
	}

	public function setTablesScheme($tables)
	{
		$db = PearDatabase::getInstance();
		foreach ($tables as $table) {
			if (empty($table)) {
				continue;
			}
			switch ($table['type']) {
				case 'add':
					$db->query('CREATE TABLE IF NOT EXISTS ' . $table['sql'] . ' ENGINE=InnoDB DEFAULT CHARSET="utf8";');
					break;
				case 'remove':
					$db->query('DROP TABLE IF EXISTS ' . $table['sql'] . ';');
					break;
				case 'rename':
					$result = $db->query("SHOW TABLES LIKE '" . $table['name'] . "';");
					if ($result->rowCount()) {
						$db->query($table['sql']);
					}
					break;
				case 'exception':
					$db->query($table['sql']);
					break;
				default:
					break;
			}
		}
	}

	public function getMax($table, $field, $filter = '')
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT MAX($field) AS max_seq  FROM $table $filter;");
		$id = (int) $db->getSingleValue($result) + 1;
		$log->debug(__CLASS__ . '::' . __METHOD__ . ' | END');
		return $id;
	}

	public static function checkFieldExists($moduleName, $column, $table)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if ($moduleName == 'Settings')
			$result = $db->pquery('SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ? ;', [$column, $table]);
		else {
			if (is_numeric($moduleName)) {
				$tabId = $moduleName;
			} else {
				$tabId = getTabid($moduleName);
			}
			$result = $db->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? AND tabid = ?;", [$column, $table, $tabId]);
		}
		if (!$db->getRowCount($result)) {
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return false;
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return true;
	}
}
