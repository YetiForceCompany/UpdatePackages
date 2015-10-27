<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php');

class YetiForceUpdate
{

	var $package;
	var $modulenode;
	var $return = true;
	var $filesToDelete = [
		'modules/PaymentsIn/schema.xml',
		'modules/PaymentsOut/schema.xml',
		'config.csrf-secret.php',
		'api/firefoxtoolbar.php',
		'api/thunderbirdplugin.php',
		'api/wordplugin.php',
		'layouts/vlayout/modules/OSSMail/resources/mailtemplate.js',
		'layouts/vlayout/modules/OSSMailTemplates/Config.tpl',
		'layouts/vlayout/skins/images/btnAdd.png',
		'libraries/adodb',
		'libraries/chartjs/Chartmin.js',
		'libraries/guidersjs',
		'libraries/jquery/datatables/bower.json',
		'libraries/jquery/datatables/composer.json',
		'libraries/jquery/datatables/dataTables.jquery.json',
		'libraries/jquery/datatables/extensions/ColReorder/Readme.txt',
		'libraries/jquery/datatables/extensions/ColVis/Readme.txt',
		'libraries/jquery/datatables/extensions/FixedColumns/Readme.txt',
		'libraries/jquery/datatables/media/images/back_disabled.png',
		'libraries/jquery/datatables/media/images/back_enabled.png',
		'libraries/jquery/datatables/media/images/back_enabled_hover.png',
		'libraries/jquery/datatables/media/images/forward_disabled.png',
		'libraries/jquery/datatables/media/images/forward_enabled.png',
		'libraries/jquery/datatables/media/images/forward_enabled_hover.png',
		'libraries/jquery/datatables/package.json',
		'libraries/jquery/jqplot/excanvas.js',
		'libraries/jquery/jqplot/jquery.jqplot.css',
		'libraries/jquery/jqplot/jquery.jqplot.js',
		'libraries/jquery/jquery-ui/css',
		'libraries/jquery/jquery-ui/js',
		'libraries/jquery/jquery-ui/README.md',
		'libraries/jquery/jquery-ui/third-party',
		'libraries/jquery/pnotify/jquery.pnotify.default.css',
		'libraries/jquery/pnotify/jquery.pnotify.js',
		'libraries/jquery/pnotify/jquery.pnotify.min.js',
		'libraries/jquery/pnotify/use for pines style icons/jquery.pnotify.default.icons.css',
		'libraries/jquery/select2/component.json',
		'libraries/jquery/select2/LICENSE',
		'libraries/jquery/select2/release.sh',
		'libraries/jquery/select2/select2.png',
		'libraries/jquery/select2/select2x2.png',
		'libraries/jquery/select2/spinner.gif',
		'modules/Accounts/actions',
		'modules/Contacts/actions/TransferOwnership.php',
		'modules/ModComments/actions/Delete.php',
		'modules/OSSMailTemplates/actions/GetListModule.php',
		'modules/OSSMailTemplates/actions/GetListTpl.php',
		'modules/RequirementCards/models/Module.php',
		'modules/Settings/BackUp/actions/CreateBackUp.php',
		'modules/Settings/BackUp/actions/CreateFileBackUp.php',
		'modules/Settings/BackUp/actions/SaveFTPConfig.php',
		'modules/Vtiger/resources/validator/EmailValidator.js',
		'layouts/vlayout/modules/OSSMailTemplates/Config.tpl',
		'layouts/vlayout/skins/images/btnAdd.png',
		'languages/de_de/Install.php',
		'languages/en_us/Install.php',
		'languages/pl_pl/Install.php',
		'languages/pt_br/Install.php',
		'languages/ru_ru/Install.php',
		'layouts/vlayout/modules/RecycleBin/RecycleBin.tpl',
		'layouts/vlayout/modules/RecycleBin/RecycleBinContents.tpl',
		'modules/Settings/Vtiger/views/ListUI5.php',
		'config/config.template.php',
	];

	function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	function preupdate()
	{
		//$this->package->_errorText = 'Errot';
		return true;
	}

	function update()
	{
		$this->changeActivity();
		$this->deleteCustomView();
		$this->databaseSchema();
		$this->updateFiles();
		$this->enableTracking();
		$this->addCurrencies();
		$this->addTimeZone();
		$this->updateMailTemplate();
		$this->deleteWorkflow();
		$this->AddWorkflows();
		$this->worflowEnityMethod();
	}

	function postupdate()
	{
		return true;
	}

	public function changeActivity()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$adb = PearDatabase::getInstance();
		$adb->query("UPDATE `vtiger_activity_reminder_popup` SET `status` = '1' WHERE recordid IN (SELECT activityid FROM `vtiger_activity` WHERE `status` IN ('PLL_CANCELLED','PLL_COMPLETED','PLL_OVERDUE'))");
		$adb->query("UPDATE `vtiger_activity_reminder_popup` SET `status` = '0' WHERE recordid IN (SELECT activityid FROM `vtiger_activity` WHERE `status` IN ('PLL_IN_REALIZATION','PLL_POSTPONED','PLL_PLANNED'))");
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function deleteCustomView()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT cvid FROM vtiger_customview WHERE entitytype = ?', ['Emails']);
		if ($result->rowCount() > 0) {
			$cvid = $adb->query_result($result, 0, 'cvid');
			$adb->delete('vtiger_customview', 'cvid = ?', [$cvid]);
			$adb->delete('vtiger_cvcolumnlist', 'cvid = ?', [$cvid]);
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function databaseSchema()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

		$adb = PearDatabase::getInstance();
		$result = $adb->query("SHOW COLUMNS FROM `s_yf_multireference` LIKE 'type';");
		if ($result->rowCount() == 0) {
			$adb->query("ALTER TABLE `s_yf_multireference` ADD COLUMN `type` TINYINT(1) DEFAULT 0 NOT NULL AFTER `lastid`;");
		}
		$adb->query("CREATE TABLE IF NOT EXISTS `l_yf_sqltime` (
  `id` int(19) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `data` text,
  `date` datetime DEFAULT NULL,
  `qtime` decimal(20,3) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
		$adb->query('DROP TABLE IF EXISTS `vtiger_sqltimelog`');

		$this->addHandler([['vtiger.entity.aftersave.final', 'modules/Vtiger/handlers/MultiReferenceUpdater.php', 'Vtiger_MultiReferenceUpdater_Handler', '', '1', '[]']]);

		$sql = 'UPDATE vtiger_relatedlists SET `related_tabid` = ?, `label` = ? WHERE `related_tabid` = ?';
		$adb->pquery($sql, [getTabid('OSSMailView'), 'OSSMailView', getTabid('Emails')]);

		$query = 'UPDATE vtiger_currencies_seq SET id = (SELECT currencyid FROM vtiger_currencies ORDER BY currencyid DESC LIMIT 1)';
		$adb->query($query);

		$uniqId = $adb->getUniqueID('vtiger_currencies');
		$result = $adb->pquery('SELECT 1 FROM vtiger_currencies WHERE currency_name = ?', ['CFP Franc']);

		if ($adb->num_rows($result) <= 0) {
			$adb->pquery('INSERT INTO vtiger_currencies VALUES (?,?,?,?)', [$uniqId, 'CFP Franc', 'XPF', 'F']);
		}

		$sortOrderResult = $adb->pquery("SELECT sortorderid FROM vtiger_time_zone WHERE time_zone = ?", ['Asia/Yakutsk']);
		if ($adb->num_rows($sortOrderResult)) {
			$sortOrderId = $adb->query_result($sortOrderResult, 0, 'sortorderid');
			$adb->pquery('UPDATE vtiger_time_zone SET sortorderid = (sortorderid + 1) WHERE sortorderid > ?', [$sortOrderId]);
			$adb->pquery('INSERT INTO vtiger_time_zone (time_zone, sortorderid, presence) VALUES (?, ?, ?)', ['Etc/GMT-11', ($sortOrderId + 1), 1]);
		}

		$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_layout` (
					`id` int(11) NOT NULL,
					`name` varchar(50) DEFAULT NULL,
					`label` varchar(30) DEFAULT NULL,
					`lastupdated` datetime DEFAULT NULL,
					`isdefault` tinyint(1) DEFAULT NULL,
					`active` tinyint(1) DEFAULT NULL,
					PRIMARY KEY (`id`)
				  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$result = $adb->query("SHOW COLUMNS FROM `vtiger_ossmailtemplates` LIKE 'sysname';");
		if ($result->rowCount() == 0) {
			$adb->query("ALTER TABLE `vtiger_ossmailtemplates` ADD COLUMN `sysname` varchar(50) NULL DEFAULT '' after `name`;");
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function addHandler($addHandler = [])
	{
		$log = vglobal('log');
		$adb = PearDatabase::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if ($addHandler) {
			$em = new VTEventsManager($adb);
			foreach ($addHandler as $handler) {
				$result = $adb->pquery('SELECT * FROM `vtiger_eventhandlers` WHERE event_name = ? AND handler_class = ?;', [$handler[0], $handler[2]]);
				if ($result->rowCount() == 0) {
					$em->registerHandler($handler[0], $handler[1], $handler[2], $handler[3], $handler[5]);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	function updateFiles()
	{
		$log = vglobal('log');
		$root_directory = vglobal('root_directory');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		if (!$root_directory)
			$root_directory = getcwd();
		$config = $root_directory . '/config/config.inc.php';
		if (file_exists($config)) {
			$configContent = file($config);
			$defaultLayout = true;
			foreach ($configContent as $key => $line) {
				if (strpos($line, 'defaultLayout') !== false) {
					$defaultLayout = false;
				}
				if (strpos($line, 'support@vtiger.com') !== false) {
					$configContent[$key] = str_replace('vtiger', 'yetiforce', $configContent[$key]);
				}
			}
			$content = implode("", $configContent);
			if ($defaultLayout) {
				$content .= '
// Set the default layout 
$defaultLayout = \'vlayout\';

';
			}
			$file = fopen($config, "w+");
			fwrite($file, $content);
			fclose($file);
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function enableTracking()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		include_once('modules/ModTracker/ModTracker.php');
		ModTracker::enableTrackingForModule(Vtiger_Functions::getModuleId('OSSTimeControl'));

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function addCurrencies()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT 1 FROM `vtiger_currencies` WHERE `currency_name` = ?;', ['CFP Franc']);
		if (!$adb->getRowCount($result)) {
			$id = $adb->getUniqueID('vtiger_currencies');
			$adb->insert('vtiger_currencies', [
				'currencyid' => $id,
				'currency_name' => 'CFP Franc',
				'currency_code' => 'XPF',
				'currency_symbol' => 'F',
			]);
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function addTimeZone()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT 1 FROM `vtiger_time_zone` WHERE `time_zone` = ?;', ['Etc/GMT-11']);
		if (!$adb->getRowCount($result)) {
			$id = $adb->getUniqueID('vtiger_time_zone');
			$seq = $this->getMax('vtiger_time_zone', 'sortorderid');
			$adb->insert('vtiger_time_zone', [
				'time_zoneid' => $id,
				'time_zone' => 'Etc/GMT-11',
				'sortorderid' => $seq,
				'presence' => 1,
			]);
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function getMax($table, $field)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$adb = PearDatabase::getInstance();
		$result = $adb->query("SELECT MAX(" . $field . ") AS max_seq  FROM " . $table . " ;");
		$id = (int) $adb->getSingleValue($result) + 1;
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $id;
	}

	public function updateMailTemplate()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$adb = PearDatabase::getInstance();
		$moduleName = 'OSSMailTemplates';
		$data = ['Notify Contact On Ticket Closed' => 'NotifyContactOnTicketClosed', 'Notify Contact On Ticket Create' => 'NotifyContactOnTicketCreate', 'Notify Contact On Ticket Change' => 'NotifyContactOnTicketChange', 'Activity Reminder Notification' => 'ActivityReminderNotificationEvents', 'Test mail about the mail server configuration.' => 'TestMailAboutTheMailServerConfiguration', 'ForgotPassword' => 'UsersForgotPassword', 'Customer Portal - ForgotPassword' => 'YetiPortalForgotPassword', 'New comment added to ticket' => 'NewCommentAddedToTicketContact', 'Security risk has been detected - Brute Force' => 'BruteForceSecurityRiskHasBeenDetected', 'Backup has been made' => 'BackupHasBeenMade', 'New comment added to ticket from portal' => 'NewCommentAddedToTicketOwner'];

		$query = 'UPDATE vtiger_ossmailtemplates SET ';
		$query .=' sysname = CASE ';
		foreach ($data as $name => $sysName) {
			$query .= " WHEN `name` = '" . $name . "' THEN '" . $sysName . "'";
		}
		$query .= ' END WHERE `name` IN (' . generateQuestionMarks(array_keys($data)) . ')';

		$adb->pquery($query, [array_keys($data)]);

		$adb->update('vtiger_ossmailtemplates', ['sysname' => 'ActivityReminderNotificationTask'], 'name = ? AND oss_module_list = ?', ['Activity Reminder Notification', 'Calendar']);

		$result = $adb->pquery('SELECT ossmailtemplatesid FROM vtiger_ossmailtemplates WHERE `name` = ?;', ['New comment added to ticket from portal']);
		if ($adb->getRowCount($result)) {
			$id = $adb->getSingleValue($result);
			$recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
			$recordModel->set('id', $id);
			$recordModel->set('mode', 'edit');
			$recordModel->set('sysname', 'NewCommentAddedToTicketOwner');
			$recordModel->set('name', 'Notify Owner On new comment added to ticket from portal');
			$recordModel->set('subject', '#t#LBL_ADDED_COMMENT_TO_TICKET#tEnd#');
			$recordModel->set('content', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#LBL_NEW_COMMENT_FOR_TICKET#tEnd# (#t#LBL_NOTICE_CREATED#tEnd# #a#691#aEnd#).

<hr /> #b#597#bEnd#: #a#597#aEnd#
</div>');
			$recordModel->save();
		}
		$result = $adb->pquery('SELECT ossmailtemplatesid FROM vtiger_ossmailtemplates WHERE `name` = ?;', ['New comment added to ticket']);
		if ($adb->getRowCount($result)) {
			$id = $adb->getSingleValue($result);
			$recordModel = Vtiger_Record_Model::getInstanceById($id, 'OSSMailTemplates');
			$recordModel->set('id', $id);
			$recordModel->set('mode', 'edit');
			$recordModel->set('sysname', 'NewCommentAddedToTicketContact');
			$recordModel->set('name', 'Notify Contact On New comment added to ticket');
			$recordModel->set('subject', '#t#LBL_ADDED_COMMENT_TO_TICKET#tEnd#');
			$recordModel->set('content', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#LBL_NEW_COMMENT_FOR_TICKET#tEnd# (#t#LBL_NOTICE_CREATED#tEnd# #a#745#aEnd#).

<hr /> #b#597#bEnd#: #a#597#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
			$recordModel->save();
		}
		$result = $adb->pquery('SELECT ossmailtemplatesid FROM vtiger_ossmailtemplates WHERE `name` = ?;', ['Notify Contact On Ticket Create']);
		if ($adb->getRowCount($result)) {
			$id = $adb->getSingleValue($result);
			$recordModel = Vtiger_Record_Model::getInstanceById($id, 'OSSMailTemplates');
			$recordModel->set('id', $id);
			$recordModel->set('mode', 'edit');
			$recordModel->set('content', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# (#t#LBL_NOTICE_CREATED#tEnd# #a#168#aEnd#).

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>');
			$recordModel->save();
		}

		$mailTemplates = $this->getMailTemplate();
		foreach ($mailTemplates as $mailTemplate) {
			$result = $adb->pquery('SELECT ossmailtemplatesid FROM vtiger_ossmailtemplates WHERE `sysname` = ?;', [$mailTemplate[2]]);
			if (!$adb->getRowCount($result)) {
				$user = Users_Record_Model::getCurrentUserModel();
				$instance = new $moduleName();
				$instance->column_fields['assigned_user_id'] = $user->id;
				$instance->column_fields['name'] = $mailTemplate[1];
				$instance->column_fields['oss_module_list'] = $mailTemplate[3];
				$instance->column_fields['subject'] = $mailTemplate[4];
				$instance->column_fields['content'] = $mailTemplate[5];
				$instance->column_fields['ossmailtemplates_type'] = $mailTemplate[6];
				$save = $instance->save($moduleName);
				$adb->update('vtiger_ossmailtemplates', ['sysname' => $mailTemplate[2]], 'ossmailtemplatesid = ?', [$instance->id]);
			}
		}

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function deleteWorkflow()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$adb = PearDatabase::getInstance();
		$adb->pquery('UPDATE com_vtiger_workflows SET defaultworkflow = "0" WHERE module_name = ?;', ['ModComments']);
		$result = $adb->pquery('SELECT * FROM com_vtiger_workflows WHERE module_name = ?;', ['ModComments']);
		for ($i = 0; $i < $adb->getRowCount($result); $i++) {
			$recordId = $adb->query_result($result, $i, 'workflow_id');
			$recordModel = Settings_Workflows_Record_Model::getInstance($recordId);
			$recordModel->delete();
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function AddWorkflows()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$adb = PearDatabase::getInstance();

		$workflow = [];
		$workflow[] = ['57', 'ModComments', 'New comment added to ticket - Owner', '[{"fieldname":"customer","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', '1', NULL, 'basic', '6', NULL, NULL, NULL, NULL, NULL, NULL];
		$workflow[] = ['58', 'ModComments', 'New comment added to ticket - account', '[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', '1', NULL, 'basic', '6', NULL, NULL, NULL, NULL, NULL, NULL];
		$workflow[] = ['59', 'ModComments', 'New comment added to ticket - contact', '[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', '1', NULL, 'basic', '6', NULL, NULL, NULL, NULL, NULL, NULL];

		$workflowTask = [];
		$workflowTask[] = ['135', '59', 'Notify Contact On New comment added to ticket', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:59;s:7:"summary";s:45:"Notify Contact On New comment added to ticket";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:26:"HeldDeskNewCommentContacts";s:2:"id";i:135;}'];
		$workflowTask[] = ['136', '58', 'Notify Account On New comment added to ticket', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:58;s:7:"summary";s:45:"Notify Account On New comment added to ticket";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:25:"HeldDeskNewCommentAccount";s:2:"id";i:136;}'];
		$workflowTask[] = ['137', '57', 'Notify Owner On new comment added to ticket from portal', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:57;s:7:"summary";s:55:"Notify Owner On new comment added to ticket from portal";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:23:"HeldDeskNewCommentOwner";s:2:"id";i:137;}'];

		$workflowManager = new VTWorkflowManager($adb);
		$taskManager = new VTTaskManager($adb);
		foreach ($workflow as $record) {
			$newWorkflow = $workflowManager->newWorkFlow($record[1]);
			$newWorkflow->description = $record[2];
			$newWorkflow->test = $record[3];
			$newWorkflow->executionCondition = $record[4];
			$newWorkflow->defaultworkflow = $record[5];
			$newWorkflow->type = $record[6];
			$newWorkflow->filtersavedinnew = $record[7];
			$workflowManager->save($newWorkflow);
			foreach ($workflowTask as $indexTask) {
				if ($indexTask[1] == $record[0]) {
					$task = $taskManager->unserializeTask($indexTask[3]);
					$task->id = '';
					$task->workflowId = $newWorkflow->id;
					$taskManager->saveTask($task);
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function worflowEnityMethod()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

		$adb = PearDatabase::getInstance();
		$task_entity_method = [];
		$task_entity_method[] = ['ModComments', 'HeldDeskNewCommentOwner', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HeldDeskNewCommentOwner'];

		$emm = new VTEntityMethodManager($adb);
		foreach ($task_entity_method as $method) {
			$result = $adb->pquery('SELECT 1 FROM `com_vtiger_workflowtasks_entitymethod` WHERE `method_name` = ? AND module_name = ?;', [$method[1], $method[0]]);
			if (!$adb->getRowCount($result)) {
				$emm->addEntityMethod($method[0], $method[1], $method[2], $method[3]);
			}
		}
		$adb->update('com_vtiger_workflowtasks_entitymethod', ['method_name' => 'HeldDeskNewCommentAccount', 'function_path' => 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'function_name' => 'HeldDeskNewCommentAccount'], 'method_name = ?', ['CustomerCommentFromPortal']);
		$adb->update('com_vtiger_workflowtasks_entitymethod', ['method_name' => 'HeldDeskNewCommentContacts', 'function_path' => 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'function_name' => 'HeldDeskNewCommentContacts'], 'method_name = ?', ['TicketOwnerComments']);

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	public function getMailTemplate()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$mailTemplate[] = ['94', 'Activity Reminder Notification', 'ActivityReminderNotificationEvents', 'Events', 'Reminder: #a#255#aEnd#', '<span style="line-height:20.7999992370605px;">This is a reminder notification for the Activity:</span><br style="line-height:20.7999992370605px;" /><span style="line-height:20.7999992370605px;">Subject:</span>#a#255#aEnd#<br style="line-height:20.7999992370605px;" /><span style="line-height:20.7999992370605px;">Date & Time: </span>#a#257#aEnd# #a#258#aEnd#<br style="line-height:20.7999992370605px;" /><span style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;">Contact Name: </span>#a#277#aEnd#<br style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;" /><span style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;">Related To: </span>#a#264#aEnd#<br style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;" /><span style="line-height:20.7999992370605px;color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;">Description: </span>#a#275#aEnd#', 'PLL_RECORD'];

		$mailTemplate[] = ['93', 'Activity Reminder Notification', 'ActivityReminderNotificationTask', 'Calendar', 'Reminder:  #a#231#aEnd#', 'This is a reminder notification for the Activity:<br />Subject: #a#231#aEnd#<br />Date & Time: #a#233#aEnd# #a#234#aEnd#<br /><span style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;">Contact Name: </span>#a#238#aEnd#<br style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;" /><span style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;">Related To: </span>#a#237#aEnd#<br style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;" /><span style="color:rgb(43,43,43);font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:20.7999992370605px;">Description: </span>#a#247#aEnd#', 'PLL_RECORD'];

		$mailTemplate[] = ['108', 'Backup has been made', 'BackupHasBeenMade', 'Contacts', 'Backup has been made notification', 'Dear User,<br />
Backup has been made.', 'PLL_MODULE'];
		$mailTemplate[] = ['107', 'Security risk has been detected - Brute Force', 'BruteForceSecurityRiskHasBeenDetected', 'Contacts', 'Security risk has been detected', '<span class="value">Dear user,<br />
Failed login attempts have been detected. </span>', 'PLL_MODULE'];
		$mailTemplate[] = ['109', 'Notify Account On New comment added to ticket', 'NewCommentAddedToTicketAccount', 'ModComments', '#t#LBL_ADDED_COMMENT_TO_TICKET#tEnd#', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#LBL_NEW_COMMENT_FOR_TICKET#tEnd# (#t#LBL_NOTICE_CREATED#tEnd# #a#745#aEnd#).

<hr /> #b#597#bEnd#: #a#597#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>', 'PLL_RECORD'];
		$mailTemplate[] = ['106', 'Notify Contact On New comment added to ticket', 'NewCommentAddedToTicketContact', 'ModComments', '#t#LBL_ADDED_COMMENT_TO_TICKET#tEnd#', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#LBL_NEW_COMMENT_FOR_TICKET#tEnd# (#t#LBL_NOTICE_CREATED#tEnd# #a#745#aEnd#).

<hr /> #b#597#bEnd#: #a#597#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>', 'PLL_RECORD'];
		$mailTemplate[] = ['105', 'Notify Owner On new comment added to ticket from portal', 'NewCommentAddedToTicketOwner', 'ModComments', '#t#LBL_ADDED_COMMENT_TO_TICKET#tEnd#', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#LBL_NEW_COMMENT_FOR_TICKET#tEnd# (#t#LBL_NOTICE_CREATED#tEnd# #a#691#aEnd#).

<hr /> #b#597#bEnd#: #a#597#aEnd#
</div>', 'PLL_RECORD'];
		$mailTemplate[] = ['41', 'Notify Contact On Ticket Change', 'NotifyContactOnTicketChange', 'HelpDesk', '#t#LBL_NOTICE_MODIFICATION#tEnd# #a#155#aEnd#: #a#169#aEnd#', '<div>
<h3><span>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></span></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_UPDATED#tEnd# #a#168#aEnd#). #s#ChangesList#sEnd#

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_MODIFICATION#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /> #b#171#bEnd#: #a#171#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>', 'PLL_RECORD'];
		$mailTemplate[] = ['37', 'Notify Contact On Ticket Closed', 'NotifyContactOnTicketClosed', 'HelpDesk', '#t#LBL_NOTICE_CLOSE#tEnd# #a#155#aEnd#: #a#169#aEnd#', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# #t#LBL_NOTICE_CLOSED#tEnd# #a#168#aEnd#). #s#ChangesList#sEnd#

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_CLOSE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /> #b#171#bEnd#: #a#171#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>', 'PLL_RECORD'];
		$mailTemplate[] = ['39', 'Notify Contact On Ticket Create', 'NotifyContactOnTicketCreate', 'HelpDesk', '#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#SINGLE_HelpDesk#tEnd# #a#155#aEnd# (#t#LBL_NOTICE_CREATED#tEnd# #a#168#aEnd#).

<hr /><h1><a href="%23s%23LinkToPortalRecord%23sEnd%23">#t#LBL_NOTICE_CREATE#tEnd# #a#155#aEnd#: #a#169#aEnd#</a></h1>

<ul><li>#b#161#bEnd#: #a#161#aEnd#</li>
	<li>#b#158#bEnd#: #a#158#aEnd#</li>
	<li>#b#156#bEnd#: #a#156#aEnd#</li>
	<li>#b#157#bEnd#: #a#157#aEnd#</li>
</ul><hr /> #b#170#bEnd#: #a#170#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>', 'PLL_RECORD'];
		$mailTemplate[] = ['95', 'Test mail about the mail server configuration.', 'TestMailAboutTheMailServerConfiguration', 'Users', 'Test mail about the mail server configuration.', '<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Dear </span>#a#478#aEnd# #a#479#aEnd#<span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">, </span><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><b style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">This is a test mail sent to confirm if a mail is actually being sent through the smtp server that you have configured. </b><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Feel free to delete this mail. </span><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Thanks and Regards,</span><br style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;" /><span style="color:rgb(0,0,0);font-family:arial, sans-serif;line-height:normal;">Team YetiForce</span>', 'PLL_RECORD'];
		$mailTemplate[] = ['103', 'ForgotPassword', 'UsersForgotPassword', 'Users', 'Request: ForgotPassword', 'Dear #a#67#aEnd# #a#70#aEnd#,<br /><br />
You recently requested a reminder of your access data for the YetiForce Portal.<br /><br />
You can login by entering the following data:<br /><br />
Your username: #a#80#aEnd#<br />
Your password: #s#ContactsPortalPass#sEnd#<br /><br /><br />
Regards,<br />
YetiForce CRM Support Team.', 'PLL_RECORD'];
		return $mailTemplate;
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}
}
