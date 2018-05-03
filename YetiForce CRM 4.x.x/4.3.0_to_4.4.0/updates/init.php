<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * YetiForceUpdate Class
 */
class YetiForceUpdate
{

	/**
	 * @var \vtlib\PackageImport
	 */
	public $package;

	/**
	 * @var string
	 */
	public $logFile = 'cache/logs/updateLogsTrace.log';

	/**
	 * @var object
	 */
	public $modulenode;

	/**
	 * Fields to delete
	 * @var string[]
	 */
	public $filesToDelete = [];

	/**
	 * DbImporter
	 * @var DbImporter
	 */
	private $importer;

	/**
	 * Constructor
	 * @param object $modulenode
	 */
	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
		$this->filesToDelete = require_once('deleteFiles.php');
	}

	/**
	 * Preupdate
	 */
	public function preupdate()
	{
		return true;
	}

	/**
	 * Logs
	 * @param string $message
	 */
	public function log($message)
	{
		$fp = fopen($this->logFile, 'a+');
		fwrite($fp, $message . PHP_EOL);
		fclose($fp);
	}

	/**
	 * Update
	 */
	public function update()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		$db->createCommand()->update('vtiger_cron_task', ['status' => 0])->execute();
		try {
			$this->importer = new \App\Db\Importer();
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->updateScheme();
			$this->importer->dropColumns([['u_#__github', 'client_id'], ['vtiger_widgets', 'nomargin']]);
			$this->importer->refreshSchema();
			$this->dav();
			$this->updateScheme();
			$this->importer->refreshSchema();
			$this->importer->postUpdate();
			$this->importer->dropTable(['s_#__handler_updater', 'vtiger_selectquery_seq', 'vtiger_selectquery', 'vtiger_selectcolumn', 'vtiger_report', 'vtiger_reportdatefilter',
				'vtiger_reportfilters', 'vtiger_reportfolder', 'vtiger_reportgroupbycolumn', 'vtiger_reportmodules', 'vtiger_reportsharing', 'vtiger_reportsortcol', 'vtiger_reportsummary',
				'vtiger_reporttype', 'vtiger_scheduled_reports', 'vtiger_schedulereports', 'vtiger_relcriteria', 'vtiger_relcriteria_grouping']);
			$this->importer->logs(false);
		} catch (\Throwable $ex) {
			$this->log($ex->getMessage() . '|' . $ex->getTraceAsString());
			$this->importer->logs(false);
			throw $ex;
		}
		$this->importer->refreshSchema();
		$db->createCommand()->checkIntegrity(true)->execute();
		$this->updateData();
		$this->updateCron();
		$this->removeModule();
		$this->workflowTask();
		$this->addModules(['PermissionInspector']);
		$this->updateLangFiles();
		$this->addLanguages();
		$this->addFields();
		$this->actionMapp();
		$this->updateConfigurationFiles();
		$db->createCommand()->update('vtiger_cron_task', ['status' => 1], ['name' => 'LBL_BATCH_PROCESSES'])->execute();
		$menuRecordModel = new \Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Update scheme
	 */
	private function updateScheme()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$this->importer->logs .= "> start update tables\n";
		$dbIndexes = $db->getTableKeys('u_#__crmentity_label');
		try {
			if (!isset($dbIndexes['crmentity_label_fulltext']) && $db->getDriverName() === 'mysql') {
				$this->importer->logs .= "  > create index: crmentity_label_fulltext ... ";
				$db->createCommand('ALTER TABLE u_yf_crmentity_label ADD FULLTEXT KEY crmentity_label_fulltext(label);')->execute();
				$this->importer->logs .= "done\n";
			}
			if (!isset($dbIndexes['crmentity_searchlabel_fulltext']) && $db->getDriverName() === 'mysql') {
				$this->importer->logs .= "  > create index: crmentity_label_fulltext ... ";
				$db->createCommand('ALTER TABLE u_yf_crmentity_search_label ADD FULLTEXT KEY `crmentity_searchlabel_fulltext`(`searchlabel`);')->execute();
				$this->importer->logs .= "done\n";
			}
		} catch (\Throwable $e) {
			$this->importer->logs .= " | Error(8) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
		}
		$this->dropIndex(['u_yf_crmentity_search_label' => ['searchlabel', 'searchlabel_2']]);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Update data
	 */
	private function updateData()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$data = [
			['vtiger_settings_field', ['name' => 'LBL_TERMS_AND_CONDITIONS']],
			['vtiger_cron_task', ['handler_file' => 'cron/HandlerUpdater.php']],
			['vtiger_eventhandlers', ['handler_class' => 'Vtiger_Attachments_Handler']]
		];
		\App\Db\Updater::batchDelete($data);

		$data = [
			['vtiger_ws_operation', ['handler_method' => 'vtwsConvertlead'], ['handler_method' => 'vtws_convertlead']],
			['vtiger_field', ['typeofdata' => 'V~M'], ['columnname' => 'status', 'tablename' => 'vtiger_users']],
			['vtiger_field', ['uitype' => 69], ['uitype' => 105]],
			['vtiger_field', ['uitype' => 311], ['uitype' => 69, 'tablename' => 'vtiger_products', 'columnname' => 'imagename']],
			['vtiger_field', ['uitype' => 71], ['uitype' => 7, 'fieldname' => ['sum_total', 'sum_gross'], 'tabid' => array_map('\App\Module::getModuleId', ['FInvoice', 'SQuotes', 'SSingleOrders', 'FInvoiceProforma', 'FCorectingInvoice', 'FInvoiceCost', 'SCalculations', 'IGRN', 'ISTDN', 'ISTRN', 'IGRNC', 'SVendorEnquiries'])]],
			['vtiger_ticketstatus', ['color' => '855000'], ['color' => '#E6FAD8', 'ticketstatus' => 'Open']],
			['vtiger_ticketstatus', ['color' => '42c6ff'], ['color' => '#E6FAD8', 'ticketstatus' => 'In Progress']],
			['vtiger_ticketstatus', ['color' => 'ffa800'], ['color' => '#E6FAD8', 'ticketstatus' => 'Wait For Response']],
			['vtiger_ticketstatus', ['color' => '00ff43'], ['color' => '#E6FAD8', 'ticketstatus' => 'Closed']],
			['vtiger_ticketstatus', ['color' => '0038ff'], ['color' => '#E6FAD8', 'ticketstatus' => 'Answered']],
			['vtiger_ticketstatus', ['color' => 'e33d3d'], ['color' => '#E6FAD8', 'ticketstatus' => 'Rejected']],
			['vtiger_ticketstatus', ['color' => 'fff500'], ['color' => '#E6FAD8', 'ticketstatus' => 'PLL_SUBMITTED_COMMENTS']],
			['vtiger_ticketstatus', ['color' => '8c4381'], ['color' => '#E6FAD8', 'ticketstatus' => 'PLL_FOR_APPROVAL']],
			['vtiger_ticketstatus', ['color' => 'ffb0e7'], ['color' => '#E6FAD8', 'ticketstatus' => 'PLL_TO_CLOSE']],
			['com_vtiger_workflowtasks_entitymethod', ['method_name' => 'helpDeskNewCommentAccount', 'function_name' => 'HelpDeskWorkflow'], ['method_name' => 'HelpDeskNewCommentAccount']],
			['com_vtiger_workflowtasks_entitymethod', ['method_name' => 'helpDeskNewCommentContacts', 'function_name' => 'HelpDeskWorkflow'], ['method_name' => 'HelpDeskNewCommentContacts']],
			['com_vtiger_workflowtasks_entitymethod', ['method_name' => 'helpDeskChangeNotifyContacts', 'function_name' => 'HelpDeskWorkflow'], ['method_name' => 'HelpDeskChangeNotifyContacts']],
			['com_vtiger_workflowtasks_entitymethod', ['method_name' => 'helpDeskClosedNotifyContacts', 'function_name' => 'HelpDeskWorkflow'], ['method_name' => 'HelpDeskClosedNotifyContacts']],
			['com_vtiger_workflowtasks_entitymethod', ['method_name' => 'helpDeskNewCommentOwner', 'function_name' => 'HelpDeskWorkflow'], ['method_name' => 'HelpDeskNewCommentOwner']],
			['vtiger_relatedlists', ['actions' => 'ADD,SELECT'], ['related_tabid' => \App\Module::getModuleId('PriceBooks'), 'tabid' => \App\Module::getModuleId('Services'), 'name' => 'getServicePricebooks']],
			['vtiger_settings_field', ['iconpath' => 'far fa-image'], ['name' => 'LBL_COUNTRY_SETTINGS']],
			['vtiger_settings_field', ['linkto' => 'index.php?module=OSSMail&parent=Settings&view=Index'], ['linkto' => 'index.php?module=OSSMail&parent=Settings&view=index']],
			['u_yf_emailtemplates', ['module' => 'Users', 'content' => '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear user,<br />
						Failed login attempts have been detected.</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>'], ['module' => 'Contacts', 'sys_name' => 'BruteForceSecurityRiskHasBeenDetected']],
		];
		\App\Db\Updater::batchUpdate($data);

		$data = [
			['vtiger_settings_field', [
					'blockid' => \Settings_Vtiger_Menu_Model::getInstance('LBL_SECURITY_MANAGEMENT')->get('blockid'),
					'name' => 'LBL_ENCRYPTION',
					'iconpath' => 'fas fa-key',
					'description' => null,
					'linkto' => 'index.php?module=Password&parent=Settings&view=Encryption',
					'sequence' => 4,
					'active' => 0,
					'pinned' => 0,
					'admin_access' => null,
				], ['name' => 'LBL_ENCRYPTION', 'linkto' => 'index.php?module=Password&parent=Settings&view=Encryption']
			]
		];
		\App\Db\Updater::batchInsert($data);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Actions mapping
	 */
	private function actionMapp()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$actions = [
			['type' => 'remove', 'name' => 'NotificationCreateMail'],
			['type' => 'remove', 'name' => 'NotificationPreview'],
			['type' => 'add', 'name' => 'RecordConventer', 'tabsData' => []]
		];
		$db = \App\Db::getInstance();
		foreach ($actions as $action) {
			$key = (new \App\Db\Query())->select(['actionid'])->from('vtiger_actionmapping')->where(['actionname' => $action['name']])->scalar();
			if ($action['type'] === 'remove') {
				if ($key) {
					$db->createCommand()->delete('vtiger_actionmapping', ['actionid' => $key])->execute();
					$db->createCommand()->delete('vtiger_profile2utility', ['activityid' => $key])->execute();
				}
				continue;
			}
			$dbCommand = $db->createCommand();
			if (!$key) {
				$securitycheck = 0;
				$key = $db->getUniqueId('vtiger_actionmapping', 'actionid', false);
				$dbCommand->insert('vtiger_actionmapping', ['actionid' => $key, 'actionname' => $action['name'], 'securitycheck' => $securitycheck])->execute();
			}
			$permission = 1;
			if (isset($action['permission'])) {
				$permission = $action['permission'];
			}
			if (!empty($action['tabsData'])) {
				$tabsData = $action['tabsData'];
			} else {
				$tabsData = array_keys(\vtlib\Functions::getAllModules(true, ['SMSNotifier', 'ModComments', 'PBXManager', 'Events']));
			}
			$dataReader = (new \App\Db\Query())->select(['profileid'])->from('vtiger_profile')->createCommand()->query();
			while ($profileId = $dataReader->readColumn(0)) {
				foreach ($tabsData as $tabid) {
					if (!(new \App\Db\Query())->from('vtiger_profile2utility')->where(['profileid' => $profileId, 'tabid' => $tabId, 'activityid' => $key])->exists()) {
						$dbCommand->insert('vtiger_profile2utility', ['profileid' => $profileId, 'tabid' => $tabid, 'activityid' => $key, 'permission' => $permission])->execute();
					}
				}
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Cron data
	 */
	private function updateCron()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		\App\Db\Updater::cron([
			['type' => 'add', 'data' => ['LBL_BATCH_METHODS', 'cron/BatchMethods.php', 900, null, null, 1, 'Vtiger', 31, null]],
			['type' => 'add', 'data' => ['LBL_CLEAR_FILE_UPLOAD_TEMP', 'cron/FileUploadTemp.php', 86400, null, null, 1, 'Vtiger', 27, null]],
			['type' => 'add', 'data' => ['LBL_SESSION_CLEANER', 'cron/SessionCleaner.php', 60, null, null, 1, 'Vtiger', 32, '']],
			['type' => 'remove', 'data' => ['LBL_CLEAR_ATTACHMENTS_TABLE']]
		]);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add Language
	 */
	private function addLanguages()
	{
		$start = microtime(true);
		$db = \App\Db::getInstance();
		$langs = [
			['name' => 'Turkish', 'prefix' => 'tr_tr', 'label' => 'Turkish', 'lastupdated' => date('Y-m-d H:i:s'), 'sequence' => null, 'isdefault' => 0, 'active' => 1],
			['name' => 'French', 'prefix' => 'fr_fr', 'label' => 'French', 'lastupdated' => date('Y-m-d H:i:s'), 'sequence' => null, 'isdefault' => 0, 'active' => 1]
		];
		$id = $db->getUniqueId('vtiger_language', 'id', false);
		$db->createCommand()->insert('vtiger_language_seq', ['id' => $id])->execute();
		foreach ($langs as $lang) {
			if (!(new \App\Db\Query())->from('vtiger_language')->where(['prefix' => $lang['prefix']])->exists()) {
				$lang['id'] = $db->getUniqueId('vtiger_language');
				$db->createCommand()->insert('vtiger_language', $lang)->execute();
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add modules
	 * @param string[] $modules
	 */
	private function addModules(array $modules)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$command = \App\Db::getInstance()->createCommand();
		foreach ($modules as $moduleName) {
			if (file_exists(__DIR__ . '/' . $moduleName . '.xml') && !\vtlib\Module::getInstance($moduleName)) {
				$importInstance = new \vtlib\PackageImport();
				$importInstance->_modulexml = simplexml_load_file('cache/updates/' . $moduleName . '.xml');
				$importInstance->importModule();
				$command->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			} else {
				\App\Log::warning('Module exists: ' . $moduleName);
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Update workflow tasks
	 */
	public function workflowTask()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$tasks = [
			['moduleName' => 'HelpDesk', 'summary' => 'Notify Contact On Ticket Change', 'changes' => ['methodName' => 'helpDeskChangeNotifyContacts']],
			['moduleName' => 'HelpDesk', 'summary' => 'Notify contacts about closing of ticket.', 'changes' => ['methodName' => 'helpDeskClosedNotifyContacts']],
			['moduleName' => 'ModComments', 'summary' => 'Notify Contact On New comment added to ticket', 'changes' => ['methodName' => 'helpDeskNewCommentContacts']],
			['moduleName' => 'ModComments', 'summary' => 'Notify Account On New comment added to ticket', 'changes' => ['methodName' => 'helpDeskNewCommentAccount']],
			['moduleName' => 'ModComments', 'summary' => 'Notify Owner On new comment added to ticket from portal', 'changes' => ['methodName' => 'helpDeskNewCommentOwner']]
		];
		foreach ($tasks as $taskData) {
			if (empty($taskData)) {
				continue;
			}
			$task = (new \App\Db\Query())->select(['com_vtiger_workflowtasks.task'])->from('com_vtiger_workflowtasks')->innerJoin('com_vtiger_workflows', 'com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id')->where(['com_vtiger_workflowtasks.summary' => $taskData['summary'], 'com_vtiger_workflows.module_name' => $taskData['moduleName']])->scalar();
			if ($task) {
				$tm = new VTTaskManager();
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
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Postupdate
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->insert('yetiforce_updates', [
			'user' => \Users_Record_Model::getCurrentUserModel()->get('user_name'),
			'name' => $this->modulenode->label,
			'from_version' => $this->modulenode->from_version,
			'to_version' => $this->modulenode->to_version,
			'result' => true,
			'time' => date('Y-m-d H:i:s')
		]);
		$dbCommand->update('vtiger_version', ['current_version' => $this->modulenode->to_version]);
		\vtlib\Functions::recurseDelete('cache/updates');
		\vtlib\Functions::recurseDelete('cache/templates_c');
		\App\Session::set('UserAuthMethod', 'PASSWORD');
		$loader = require 'vendor/autoload.php';
		$loader->addPsr4('App\\', 'app/', true);
		\App\UserPrivilegesFile::recalculateAll();
		if (method_exists('\vtlib\Deprecated', 'createModuleMetaFile')) {
			\vtlib\Deprecated::createModuleMetaFile();
		} else {
			\App\Module::createModuleMetaFile();
		}
		register_shutdown_function(function () {
			if (function_exists('opcache_reset')) {
				opcache_reset();
			}
		});
		$menuRecordModel = new \Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		echo '<div class="modal fade in" style="display: block;top: 20%;"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">';
		echo '<h4 class="modal-title">' . \App\Language::translate('LBL_IMPORTING_MODULE', 'Settings:ModuleManager') . '</h4>';
		echo '</div><div class="modal-body">';
		echo \App\Language::translate('LBL_IMPORTED_UPDATE', 'Settings:ModuleManager');
		echo '</div><div class="modal-footer">';
		echo '<a class="btn btn-success" href="index.php">' . \App\Language::translate('LBL_MAIN_PAGE') . '<a>';
		echo '</div></div></div></div>';
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		exit;
	}

	/**
	 * Drop indexes.
	 *
	 * @param array $tables [$table=>[$index,...],...]
	 */
	public function dropIndex(array $tables)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$this->importer->logs .= "> start drop indexes\n";
		$db = \App\Db::getInstance();
		foreach ($tables as $tableName => $indexes) {
			$dbIndexes = $db->getTableKeys($tableName);
			foreach ($indexes as $index) {
				$this->importer->logs .= "  > drop index, $tableName:$index ... ";
				if (isset($dbIndexes[$index])) {
					try {
						$db->createCommand()->dropIndex($index, $tableName)->execute();
						$this->importer->logs .= "done\n";
					} catch (\Throwable $e) {
						$this->importer->logs .= " | Error(12) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
					}
				}
			}
		}
		$this->importer->logs .= "# end drop keys\n";
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Dav
	 */
	public function dav()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$tableData = $db->getSchema()->getTableSchema('dav_calendars', true);
		if (!$tableData->getColumn('displayname')) {
			return;
		}
		$sql = 'INSERT INTO dav_calendarinstances (calendarid, principaluri, access, displayname, uri, description, calendarorder, calendarcolor, transparent)
				SELECT  id, principaluri, 1, displayname, uri, description, calendarorder, calendarcolor, transparent FROM dav_calendars;';
		$db->createCommand($sql)->execute();
		$importer = new \App\Db\Importer();
		$importer->renameTables([['dav_calendars', 'dav_calendars_3_1_']]);
		$base = new \App\Db\Importers\Base();
		$tables = ['dav_calendars' => [
				'columns' => [
					'id' => $base->primaryKeyUnsigned(10)->notNull(),
					'synctoken' => $base->integer(10)->unsigned()->notNull()->defaultValue(1),
					'components' => $base->stringType(21),
				],
				'columns_mysql' => [
					'components' => $base->varbinary(21),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
				'collate' => 'utf8_unicode_ci'
		]];
		$importer->refreshSchema();
		foreach ($tables as $tableName => $data) {
			if (!$db->isTableExists($tableName)) {
				$base->tables = [$tableName => $data];
				$importer->addTables($base);
				$db->createCommand("ALTER TABLE `dav_calendars` COLLATE ='utf8_unicode_ci';")->execute();
				$sql = 'INSERT INTO dav_calendars (id, synctoken, components) SELECT id, synctoken, COALESCE(components,"VEVENT,VTODO,VJOURNAL") as components FROM dav_calendars_3_1_;';
				$db->createCommand($sql)->execute();
				$db->createCommand()->dropForeignKey('dav_calendarobjects_ibfk_1', 'dav_calendarobjects')->execute();
				$base->foreignKey = [
					['dav_calendarobjects_ibfk_1', 'dav_calendarobjects', 'calendarid', 'dav_calendars', 'id', 'CASCADE', null],
				];
				$importer->updateForeignKey($base);
			}
		}

		$importer->dropTable(['dav_calendars_3_1_']);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Updating of language files.
	 */
	public function updateLangFiles()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$format = 'php';
		$newFormat = 'json';
		$dirs = [
			'languages',
			'custom' . DIRECTORY_SEPARATOR . 'languages'
		];
		foreach ($dirs as $dir) {
			if (!is_dir($dir)) {
				continue;
			}
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST) as $object) {
				if ($object->getExtension() === $format) {
					$name = str_replace(".$format", ".$newFormat", $object->getPathname());
					if (!file_exists($name)) {
						$languageStrings = $jsLanguageStrings = [];
						require $object->getPathname();
						$translations['php'] = $languageStrings ?? [];
						$translations['js'] = $jsLanguageStrings ?? [];
						if (file_put_contents($name, json_encode($translations, JSON_PRETTY_PRINT)) === false) {
							\App\Log::error('MIGRATION:: Create file failure: ' . $name);
						}
					}
					unlink($object->getPathname());
				}
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Remove modules
	 */
	private function removeModule()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$moduleName = 'Reports';
		$rows = (new \App\Db\Query)->select(['emailtemplatesid'])->from('u_#__emailtemplates')->where(['module' => 'Reports'])->column();
		foreach ($rows as $recordId) {
			\Vtiger_Record_Model::getInstanceById($recordId, 'EmailTemplates')->delete();
		}
		$moduleInstance = \vtlib\Module::getInstance($moduleName);
		if ($moduleInstance) {
			$moduleInstance->delete();
			$dbCommand = \App\Db::getInstance()->createCommand();
			$dbCommand->delete('vtiger_links', ['like', 'linkurl', "module={$moduleName}&"])->execute();
			$dbCommand->delete('vtiger_profile2utility', ['tabid' => $moduleInstance->id])->execute();
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add fields
	 */
	public function addFields()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];
		$fields = [
			[42, 1318, 'parentid', 'vtiger_projectmilestone', 1, 10, 'parentid', 'FL_PARENT_PROJECT_MILESTONE', 1, 2, '', 100, 13, 104, 1, 'V~O', 1, null, 'BAS', 1, '', 0, '', null, 'int(10)', 'LBL_PROJECT_MILESTONE_INFORMATION', [], ['ProjectMilestone'], 'ProjectMilestone'],
		];

		foreach ($fields as $field) {
			$moduleId = App\Module::getModuleId($field[28]);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists) {
				continue;
			}
			\App\Cache::delete('BlockInstance', $field[25]);
			$blockInstance = \vtlib\Block::getInstance($field[25], $field[28]);
			if (!$blockInstance && !($blockInstance = reset(Vtiger_Module_Model::getInstance($field[28])->getBlocks()))) {
				\App\Log::error("No block found ({$field[25]}) to create a field, you will need to create a field manually. Module: {$field[28]}, field name: {$field[6]}, field label: {$field[7]}");
				$this->log("[ERROR] No block found to create a field, you will need to create a field manually. Module: {$field[28]}, field name: {$field[6]}, field label: {$field[7]}");
				continue;
			}
			$fieldInstance = new \vtlib\Field();
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
			if ($field[26] && ($field[5] == 15 || $field[5] == 16 || $field[5] == 33)) {
				$fieldInstance->setPicklistValues($field[26]);
			}
			if ($field[27] && $field[5] == 10) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Changes in configuration files.
	 * @return array
	 */
	private function getConfigurations()
	{
		return [
			['name' => 'config/config.inc.php', 'conditions' => [
					['type' => 'remove', 'search' => 'more than 8MB memory needed for graphics'],
					['type' => 'remove', 'search' => 'memory limit default value = 64M'],
					['type' => 'remove', 'search' => "AppConfig::iniSet('memory_limit', '512M')"],
					['type' => 'remove', 'search' => 'lifetime of session'],
					['type' => 'remove', 'search' => "AppConfig::iniSet('session.gc_maxlifetime', '21600')"],
				],
			],
			['name' => 'config/modules/HelpDesk.php', 'conditions' => [
					['type' => 'update', 'search' => "HIDE_SUMMARY_PRODUCTS_SERVICES", 'replace' => ["HIDE_SUMMARY_PRODUCTS_SERVICES", "SHOW_SUMMARY_PRODUCTS_SERVICES"]],
					['type' => 'update', 'search' => "'HIDE_SUMMARY_PRODUCTS_SERVICES' => false", 'replace' => ["'HIDE_SUMMARY_PRODUCTS_SERVICES' => false", "'SHOW_SUMMARY_PRODUCTS_SERVICES' => true"]],
				]
			],
			['name' => 'config/modules/SSalesProcesses.php', 'conditions' => [
					['type' => 'update', 'search' => "HIDE_SUMMARY_PRODUCTS_SERVICES", 'replace' => ["HIDE_SUMMARY_PRODUCTS_SERVICES", "SHOW_SUMMARY_PRODUCTS_SERVICES"]],
					['type' => 'update', 'search' => "'HIDE_SUMMARY_PRODUCTS_SERVICES' => false", 'replace' => ["'HIDE_SUMMARY_PRODUCTS_SERVICES' => false", "'SHOW_SUMMARY_PRODUCTS_SERVICES' => true"]],
				]
			],
			['name' => 'config/performance.php', 'conditions' => [
					['type' => 'remove', 'search' => 'Turn-off default sorting in ListView, could eat up time as data grows'],
					['type' => 'remove', 'search' => 'LISTVIEW_DEFAULT_SORTING'],
					['type' => 'remove', 'search' => 'LOAD_CUSTOM_LANGUAGE'],
					['type' => 'remove', 'search' => 'View MultiImage as icon or names'],
					['type' => 'remove', 'search' => 'ICON_MULTIIMAGE_VIEW'],
					['type' => 'add', 'search' => 'CRON_MAX_ATACHMENTS_DELETE', 'checkInContents' => 'defaultDetailViewName', 'addingType' => 'after', 'value' => "	// Time to execute batch methods [min].
	'CRON_BATCH_METHODS_LIMIT' => 15,
"],
					['type' => 'update', 'search' => "vendor/yetiforce/Session", 'replace' => ["vendor/yetiforce/Session", "app/Session"]],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'CHART_ADDITIONAL_FILTERS_LIMIT', 'addingType' => 'before', 'value' => "	//Additional filters limit for ChartFilter's
	'CHART_ADDITIONAL_FILTERS_LIMIT'=>6,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'CHART_MULTI_FILTER_STR_LEN', 'addingType' => 'before', 'value' => "	//Charts multi filter maximum db value length
	'CHART_MULTI_FILTER_STR_LEN' => 50,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'CHART_MULTI_FILTER_LIMIT', 'addingType' => 'before', 'value' => "	//Charts multi filter limit
	'CHART_MULTI_FILTER_LIMIT' => 5,
"],
				]
			],
			['name' => 'config/modules/Accounts.php', 'conditions' => [
					['type' => 'update', 'search' => 'Hide summary products services bookmark', 'replace' => ['Hide summary products services bookmark', 'Show summary products services bookmark']],
					['type' => 'update', 'search' => "HIDE_SUMMARY_PRODUCTS_SERVICES", 'replace' => ["HIDE_SUMMARY_PRODUCTS_SERVICES", "SHOW_SUMMARY_PRODUCTS_SERVICES"]],
					['type' => 'update', 'search' => "'HIDE_SUMMARY_PRODUCTS_SERVICES' => false", 'replace' => ["'HIDE_SUMMARY_PRODUCTS_SERVICES' => false", "'SHOW_SUMMARY_PRODUCTS_SERVICES' => true"]],
					['type' => 'update', 'search' => "Default module view. Values: List, ListPreview or DashBoard", 'checkInLine' => 'refresh menu files after you change this value', 'replace' => ["Default module view. Values: List, ListPreview or DashBoard", "Default module view. Values: List, ListPreview or DashBoard, refresh menu files after you change this value"]],
				]
			],
			['name' => 'config/search.php', 'conditions' => [
					['type' => 'update', 'search' => "Global search - Show operator", 'value' => "	// Global search - Show operator list.
"],
					['type' => 'update', 'search' => "'GLOBAL_SEARCH_OPERATOR'", 'replace' => ["'GLOBAL_SEARCH_OPERATOR'", "'GLOBAL_SEARCH_OPERATOR_SELECT'"]],
					['type' => 'add', 'search' => 'GLOBAL_SEARCH_OPERATOR', 'checkInContents' => 'GLOBAL_SEARCH_DEFAULT_OPERATOR', 'addingType' => 'after', 'value' => "	// Global search - Default search operator. (FulltextBegin,FulltextWord,Contain,Begin,End)
	'GLOBAL_SEARCH_DEFAULT_OPERATOR' => 'FulltextBegin',
"],
				]
			],
			['name' => 'config/security.php', 'conditions' => [
					['type' => 'add', 'search' => '];', 'checkInContents' => 'MAX_LIFETIME_SESSION', 'addingType' => 'before', 'value' => "	// Lifetime session (in seconds)
	'MAX_LIFETIME_SESSION' => 21600,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'PURIFIER_ALLOWED_DOMAINS', 'addingType' => 'before', 'value' => "	// List of allowed domains for fields with HTML support
	'PURIFIER_ALLOWED_DOMAINS' => [],
"],
				]
			]
		];
	}

	/**
	 * Configuration files
	 */
	private function updateConfigurationFiles()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$rootDirectory = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		foreach ($this->getConfigurations() as $config) {
			if (!$config) {
				continue;
			}
			$conditions = $config['conditions'];
			$fileName = $rootDirectory . $config['name'];
			if (file_exists($fileName)) {
				$baseContent = file_get_contents($fileName);
				$configContent = $configContentClone = file($fileName);
				$emptyLine = false;
				$addContent = [];
				$indexes = [];
				foreach ($configContent as $key => $line) {
					if ($emptyLine && strlen($line) == 1) {
						unset($configContent[$key]);
						$emptyLine = false;
						continue;
					}
					$emptyLine = false;
					foreach ($conditions as $index => $condition) {
						if (empty($condition)) {
							continue;
						}
						if ($condition['type'] === 'add' && !in_array($index, $indexes)) {
							$addContent[$index] = $condition['value'];
							$indexes[] = $index;
						}
						if (strpos($line, $condition['search']) !== false) {
							switch ($condition['type']) {
								case 'add':
									if (isset($condition['checkInContents']) && strpos($baseContent, $condition['checkInContents']) === false) {
										$configContent[$key] = $condition['addingType'] === 'before' ? $condition['value'] . $configContent[$key] : $configContent[$key] . $condition['value'];
									}
									unset($addContent[$index]);
									break;
								case 'remove':
									if (!empty($condition['before'])) {
										if (strpos($configContentClone[$key - 1], $condition['before']) !== false) {
											unset($configContent[$key]);
											$emptyLine = true;
										}
									} else {
										unset($configContent[$key]);
										$emptyLine = true;
									}
									break;
								case 'removeTo':
									unset($configContent[$key]);
									$while = 0;
									while ($while !== false) {
										$while++;
										unset($configContent[$key + $while]);
										if (strpos($configContent[$key + $while], $condition['end']) === false) {
											$while = false;
										}
									}
									$emptyLine = true;
									break;
								case 'update':
									if (isset($condition['checkInLine']) && (strpos($condition['checkInLine'], $configContent[$key]) !== false)) {
										break;
									}
									if (isset($condition['replace'])) {
										$configContent[$key] = str_replace($condition['replace'][0], $condition['replace'][1], $configContent[$key]);
									} else {
										$configContent[$key] = $condition['value'];
									}
									break;
								default:
									break;
							}
						}
					}
				}
				$content = implode("", $configContent);
				if ($addContent) {
					$addContentString = implode("", $addContent);
					$content .= $addContentString;
				}
				file_put_contents($fileName, $content);
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}
}
