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

		$record = ['109', 'Notify Account On New comment added to ticket', 'NewCommentAddedToTicketAccount', 'ModComments', '#t#LBL_ADDED_COMMENT_TO_TICKET#tEnd#', '<div>
<h3>#t#LBL_NOTICE_WELCOME#tEnd# <strong>YetiForce Sp. z o.o.</strong></h3>
#t#LBL_NEW_COMMENT_FOR_TICKET#tEnd# (#t#LBL_NOTICE_CREATED#tEnd# #a#745#aEnd#).

<hr /> #b#597#bEnd#: #a#597#aEnd#
<hr /><span><em>#t#LBL_NOTICE_FOOTER#tEnd#</em></span></div>', 'PLL_RECORD'];

		$result = $adb->pquery('SELECT ossmailtemplatesid FROM vtiger_ossmailtemplates WHERE `sysname` = ?;', ['NewCommentAddedToTicketAccount']);
		if (!$adb->getRowCount($result)) {
			$user = Users_Record_Model::getCurrentUserModel();
			$instance = new $moduleName();
			$instance->column_fields['assigned_user_id'] = $user->id;
			$instance->column_fields['name'] = $record[1];
			$instance->column_fields['oss_module_list'] = $record[3];
			$instance->column_fields['subject'] = $record[4];
			$instance->column_fields['content'] = $record[5];
			$instance->column_fields['ossmailtemplates_type'] = $record[6];
			$save = $instance->save($moduleName);
			$adb->update('vtiger_ossmailtemplates', ['sysname' => $record[2]], 'name = ? AND oss_module_list = ?', [$record[1], $record[3]]);
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
}
