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
		$db = \App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		$db->createCommand()->update('vtiger_cron_task', ['status' => 0])->execute();
		try {
			$this->importer = new \App\Db\Importer();
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->updateScheme();
			$this->importer->dropColumns([['u_#__github', 'client_id'], ['vtiger_widgets', 'nomargin'], ['vtiger_entity_stats', 'presence']]);
			$this->importer->refreshSchema();
			$this->dav();
			$this->updateScheme();
			$this->importer->refreshSchema();
			$this->importer->postUpdate();
			$this->importer->dropTable(['s_#__handler_updater', 'vtiger_selectquery_seq', 'vtiger_selectquery', 'vtiger_selectcolumn', 'vtiger_report', 'vtiger_reportdatefilter',
				'vtiger_reportfilters', 'vtiger_reportfolder', 'vtiger_reportgroupbycolumn', 'vtiger_reportmodules', 'vtiger_reportsharing', 'vtiger_reportsortcol', 'vtiger_reportsummary', 'vtiger_reporttype', 'vtiger_scheduled_reports', 'vtiger_schedulereports', 'vtiger_relcriteria', 'vtiger_relcriteria_grouping', 'vtiger_apiaddress']);
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
		$this->addModules(['PermissionInspector', 'DataSetRegister', 'ActivityRegister', 'LocationRegister', 'IncidentRegister', 'AuditRegister']);
		$this->updateLangFiles();
		$this->addLanguages();
		$this->addFields();
		$this->actionMapp();
		$this->updateSecondData();
		$this->updateConfigurationFiles();
		$db->createCommand()->update('vtiger_cron_task', ['status' => 1], ['name' => 'LBL_BATCH_PROCESSES'])->execute();
		$menuRecordModel = new \Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Update data
	 */
	public function updateSecondData()
	{
		\App\EventHandler::registerHandler('EntityAfterTransferLink', 'ModTracker_ModTrackerHandler_Handler');
		\App\EventHandler::registerHandler('EntityAfterTransferUnLink', 'ModTracker_ModTrackerHandler_Handler');
		\App\EventHandler::registerHandler('EntityAfterTransferLink', 'Vtiger_MultiReferenceUpdater_Handler');
		\App\EventHandler::registerHandler('EntityAfterTransferUnLink', 'Vtiger_MultiReferenceUpdater_Handler');
		\App\Db::getInstance()->createCommand()->update('vtiger_field', ['maximumlength' => null])->execute();
		$this->setTrees($this->getTrees(1));
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
		$db = \App\Db::getInstance();
		$data = [
			['vtiger_settings_field', ['name' => 'LBL_TERMS_AND_CONDITIONS']],
			['vtiger_cron_task', ['handler_file' => 'cron/HandlerUpdater.php']],
			['vtiger_cron_task', ['handler_file' => 'cron/Attachments.php']],
			['vtiger_eventhandlers', ['handler_class' => 'Vtiger_Attachments_Handler']],
			['vtiger_relatedlists', ['name' => 'getParentProducts']]
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
			['vtiger_actionmapping', ['actionname' => 'ModTracker'], ['actionname' => 'Print']],
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
			['vtiger_blocks', ['iscustom' => 0], ['blocklabel' => ['Contact Information', 'LBL_ADDRESS_MAILING_INFORMATION', 'LBL_ADDRESS_DELIVERY_INFORMATION', 'LBL_ADDRESS_DELIVERY_INFORMATION', 'LBL_REGISTRATION_INFO', 'BLOCK_INFORMATION_TIME', 'LBL_CONTACT_INFO', 'LBL_ADVANCED_BLOCK', 'LBL_FINANSIAL_SUMMARY', 'LBL_ATTENTION_BLOCK', 'LBL_TICKET_RESOLUTION', 'LBL_STATISTICS', 'LBL_DESCRIPTION_INFORMATION', 'LBL_PERIODIC_GENERATION', 'LBL_ADDRESS_INFORMATION']]],
			['vtiger_eventhandlers', ['priority' => 4], ['event_name' => 'EntityAfterSave', 'handler_class' => 'Vtiger_Workflow_Handler']]
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
			],
			['com_vtiger_workflow_tasktypes', [
					'id' => $db->getUniqueId('com_vtiger_workflow_tasktypes'),
					'tasktypename' => 'SumFieldFromDependent',
					'label' => 'LBL_SUM_FIELD_FROM_DEPENDENT',
					'classname' => 'SumFieldFromDependent',
					'classpath' => 'modules/com_vtiger_workflow/tasks/SumFieldFromDependent.php',
					'templatepath' => 'com_vtiger_workflow/taskforms/SumFieldFromDependent.tpl',
					'modules' => '{"include":[],"exclude":[]}',
					'sourcemodule' => ''
				], ['tasktypename' => 'SumFieldFromDependent']
			],
			['vtiger_realization_process', ['module_id' => \App\Module::getModuleId('ProjectMilestone'), 'status_indicate_closing' => ''], ['module_id' => \App\Module::getModuleId('ProjectMilestone')]],
			['vtiger_realization_process', ['module_id' => \App\Module::getModuleId('ProjectTask'), 'status_indicate_closing' => ''], ['module_id' => \App\Module::getModuleId('ProjectTask')]]
		];
		\App\Db\Updater::batchInsert($data);

		$dbCommand = $db->createCommand();
		$addresses = [['min_length', 'global', '3'], ['key', 'google_map_api', ''], ['nominatim', 'google_map_api', '0'], ['key', 'opencage_data', ''], ['nominatim', 'opencage_data', '0'], ['result_num', 'global', '10']];
		foreach ($addresses as $address) {
			if (!(new \App\Db\Query())->from('s_#__address_finder_config')->where(['name' => $address[0], 'type' => $address[1]])->exists()) {
				$dbCommand->insert('s_#__address_finder_config', ['id' => $db->getUniqueId('s_#__address_finder_config', 'id', false), 'name' => $address[0], 'type' => $address[1], 'val' => $address[2]])->execute();
			}
		}

		$actions = [
			['type' => 'remove', 'name' => 'DuplicatesHandling']
		];
		foreach ($actions as $action) {
			$key = (new \App\Db\Query())->select(['actionid'])->from('vtiger_actionmapping')->where(['actionname' => $action['name']])->scalar();
			if ($action['type'] === 'remove' && $key) {
				$dbCommand->delete('vtiger_actionmapping', ['actionid' => $key])->execute();
				$dbCommand->delete('vtiger_profile2utility', ['activityid' => $key])->execute();
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function getTrees($index)
	{
		$trees = [];
		switch ($index) {
			case 1:
				$trees = [
					[
						'column' => 'legal_basis',
						'base' => [25, 'LBL_LEGAL_BASIS', \App\Module::getModuleId('DataSetRegister'), 1, ''],
						'data' => [[25, 'PLL_CONSENT', 'T1', 'T1', 0, 'PLL_CONSENT', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[25, 'PLL_FULFIILMENT', 'T2', 'T2', 0, 'PLL_FULFIILMENT', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[25, 'PLL_EXECUCTION_OF_AGREMMENT', 'T3', 'T3', 0, 'PLL_EXECUCTION_OF_AGREMMENT', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[25, 'PLL_PERFORMACNE_PUBLIC_TASKS', 'T4', 'T4', 0, 'PLL_PERFORMACNE_PUBLIC_TASKS', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[25, 'PLL_JUSTIFIED_ADMIN_DATA', 'T5', 'T5', 0, 'PLL_JUSTIFIED_ADMIN_DATA', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1']]
					],
					[
						'column' => 'scope_data',
						'base' => [26, 'LBL_SCOPE_DATA', \App\Module::getModuleId('DataSetRegister'), 1, ''],
						'data' => [[26, 'PLL_NAME_AND_LASTNAME', 'T1', 'T1', 0, 'PLL_NAME_AND_LASTNAME', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_PARENTS_NAMES', 'T2', 'T2', 0, 'PLL_PARENTS_NAMES', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_BIRTHDAY', 'T3', 'T3', 0, 'PLL_BIRTHDAY', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_PLACE_OF_BIRTH', 'T4', 'T4', 0, 'PLL_PLACE_OF_BIRTH', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_ADDRESS', 'T5', 'T5', 0, 'PLL_ADDRESS', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_PESEL_NUMBER', 'T6', 'T6', 0, 'PLL_PESEL_NUMBER', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_VAT', 'T7', 'T7', 0, 'PLL_VAT', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_EDUCATION', 'T8', 'T8', 0, 'PLL_EDUCATION', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_ID_CARD_NUMBER', 'T10', 'T10', 0, 'PLL_ID_CARD_NUMBER', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_PHONE', 'T11', 'T11', 0, 'PLL_PHONE', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_EMAIL', 'T12', 'T12', 0, 'PLL_EMAIL', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_EMPLOYMENT_PLACE', 'T13', 'T13', 0, 'PLL_EMPLOYMENT_PLACE', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[26, 'PLL_PROFESSION', 'T14', 'T14', 0, 'PLL_PROFESSION', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1']]
					],
					[
						'column' => 'activity_type',
						'base' => [27, 'LBL_ACTIVITY_TYPE', \App\Module::getModuleId('ActivityRegister'), 1, ''],
						'data' => [[27, 'PLL_ENTRUSTMENT', 'T1', 'T1', 0, 'PLL_ENTRUSTMENT', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[27, 'PLL_SHARING', 'T2', 'T2', 0, 'PLL_SHARING', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[27, 'PLL_TRANSFER', 'T3', 'T3', 0, 'PLL_TRANSFER', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[27, 'PLL_ENTRUSTMENT_ACCEPTANCE', 'T4', 'T4', 0, 'PLL_ENTRUSTMENT_ACCEPTANCE', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[27, 'PLL_INTERNAL', 'T5', 'T5', 0, 'PLL_INTERNAL', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1']]
					],
					[
						'column' => 'security_type',
						'base' => [28, 'LBL_SECURITY_TYPE', \App\Module::getModuleId('LocationRegister'), 1, ''],
						'data' => [[28, 'PLL_PHYSICAL', 'T1', 'T1', 0, 'PLL_PHYSICAL', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1'],
							[28, 'PLL_ELECTRONIC', 'T2', 'T2', 0, 'PLL_ELECTRONIC', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '1']]
					]
				];
				break;
			default:
				break;
		}
		return $trees;
	}

	private function setTrees($trees)
	{
		$db = PearDatabase::getInstance();
		foreach ($trees as $tree) {
			$skipCheckData = false;
			$result = $db->pquery('SELECT templateid FROM vtiger_trees_templates WHERE module = ? AND name=?;', [$tree['base'][2], $tree['base'][1]]);
			if ($result->rowCount()) {
				$templateId = $db->getSingleValue($result);
			} else {
				$db->insert('vtiger_trees_templates', [
					'name' => $tree['base'][1],
					'module' => $tree['base'][2],
					'access' => $tree['base'][3],
					'share' => $tree['base'][4]
				]);
				$templateId = $db->getLastInsertID();
				$db->update('vtiger_field', ['fieldparams' => $templateId], '`tabid` = ? AND columnname = ?;', [$tree['base'][2], $tree['column']]);
				$skipCheckData = true;
			}
			foreach ($tree['data'] as $data) {
				if (!$skipCheckData) {
					$result = $db->pquery('SELECT templateid FROM vtiger_trees_templates_data WHERE templateid = ? AND `name` = ?;', [$templateId, $data[1]]);
					if ($result->rowCount()) {
						continue;
					}
				}
				$db->insert('vtiger_trees_templates_data', [
					'templateid' => $templateId,
					'name' => $data[1],
					'tree' => $data[2],
					'parenttrre' => $data[3],
					'depth' => $data[4],
					'label' => $data[5],
					'state' => $data[6],
					'icon' => $data[7]
				]);
			}
		}
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
				$this->postInstalModule($moduleName);
			} else {
				\App\Log::warning('Module exists: ' . $moduleName);
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Post instal
	 * @param string $moduleName
	 */
	public function postInstalModule(string $moduleName)
	{
		$this->widgets($moduleName);
		$tabId = \App\Module::getModuleId($moduleName);

		$prefixes = [
			'DataSetRegister' => 'DSR',
			'ActivityRegister' => 'AR',
			'LocationRegister' => 'LR',
			'IncidentRegister' => 'IR',
			'AuditRegister' => 'N'
		];
		if (isset($prefixes[$moduleName])) {
			$settingsModel = Settings_Menu_Module_Model::getInstance();
			$settingsModel->getMenuTypeKey($item);
			if (!(new App\Db\Query())->select(['id'])->from('yetiforce_menu')->where(['label' => 'MEN_GDPR'])->exists()) {
				$menu = ['yetiforce_menu',
					['role' => 0, 'parentid' => 0, 'type' => $settingsModel->getMenuTypeKey('Label'),
						'sequence' => (new \App\Db\Query())->from('yetiforce_menu')->where(['role' => 0, 'parentid' => 0])->max('sequence') + 1,
						'label' => 'MEN_GDPR', 'newwindow' => 0, 'showicon' => 0, 'icon' => 'fas fa-lock', 'hotkey' => ''],
					['label' => 'MEN_GDPR']
				];
				\App\Db\Updater::batchInsert([$menu]);
			}

			$id = (new App\Db\Query())->select(['id'])->from('yetiforce_menu')->where(['label' => 'MEN_GDPR'])->scalar();
			if ($id) {
				$menu = ['yetiforce_menu', ['role' => 0,
						'parentid' => $id,
						'type' => $settingsModel->getMenuTypeKey('Module'),
						'sequence' => (new \App\Db\Query())->from('yetiforce_menu')->where(['role' => 0, 'parentid' => $id])->max('sequence') + 1,
						'module' => $tabId,
						'label' => '',
						'newwindow' => 0,
						'showicon' => 0,
						'icon' => '',
						'hotkey' => ''], ['label' => 'MEN_GDPR']];
				\App\Db\Updater::batchInsert([$menu]);
			}

			\CRMEntity::getInstance('ModTracker')->enableTrackingForModule($tabId);
			\App\Fields\RecordNumber::setNumber($tabId, $prefixes[$moduleName], 1);
			$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tablename' => 'vtiger_modcomments', 'columnname' => 'related_to'])->scalar();
			$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
			$fieldModel->setRelatedModules($moduleName);
		}
	}

	/**
	 * Get widgets data
	 * @param string $moduleName
	 * @return array
	 */
	public function getWidgetToSummary(string $moduleName)
	{
		$widgets = [];
		$calendarId = \App\Module::getModuleId('Calendar');
		$documentId = \App\Module::getModuleId('Documents');
		switch ($moduleName) {
			case 'LocationRegister':
				$widgets = [
					['186', $moduleName, 'Summary', null, '1', '0', '[]'],
					['187', $moduleName, 'Updates', 'LBL_UPDATES', '1', '1', '[]'],
					['188', $moduleName, 'Comments', 'ModComments', '1', '2', '{"relatedmodule":"ModComments","limit":"5"}'],
					['189', $moduleName, 'RelatedModule', '', '2', '3', '{"relatedmodule":"' . $calendarId . '","relatedfields":["' . $calendarId . '::subject","' . $calendarId . '::assigned_user_id","' . $calendarId . '::activitystatus","' . $calendarId . '::taskpriority"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}'],
					['190', $moduleName, 'RelatedModule', '', '2', '4', '{"relatedmodule":"' . $documentId . '","relatedfields":["' . $documentId . '::notes_title","' . $documentId . '::assigned_user_id","' . $documentId . '::ossdc_status"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}']
				];
				break;
			case 'IncidentRegister':
				$widgets = [
					['191', $moduleName, 'Summary', null, '1', '0', '[]'],
					['192', $moduleName, 'Comments', 'ModComments', '1', '1', '{"relatedmodule":"ModComments","limit":"5"}'],
					['193', $moduleName, 'Updates', 'LBL_UPDATES', '1', '2', '[]'],
					['194', $moduleName, 'RelatedModule', '', '2', '3', '{"relatedmodule":"' . $calendarId . '","relatedfields":["' . $calendarId . '::subject","' . $calendarId . '::assigned_user_id","' . $calendarId . '::activitystatus","' . $calendarId . '::taskpriority"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}'],
					['195', $moduleName, 'RelatedModule', '', '2', '4', '{"relatedmodule":"' . $documentId . '","relatedfields":["' . $documentId . '::notes_title","' . $documentId . '::assigned_user_id","' . $documentId . '::ossdc_status"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}']
				];
				break;
			case 'AuditRegister':
				$widgets = [
					['196', $moduleName, 'Summary', null, '1', '0', '[]'],
					['197', $moduleName, 'Comments', 'ModComments', '1', '1', '{"relatedmodule":"ModComments","limit":"5"}'],
					['198', $moduleName, 'Updates', 'LBL_UPDATES', '1', '2', '[]'],
					['199', $moduleName, 'RelatedModule', '', '2', '3', '{"relatedmodule":"' . $calendarId . '","relatedfields":["' . $calendarId . '::subject","' . $calendarId . '::activitystatus","' . $calendarId . '::taskpriority"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}'
					],
					['200', $moduleName, 'RelatedModule', '', '2', '4', '{"relatedmodule":"' . $documentId . '","relatedfields":["' . $documentId . '::notes_title","' . $documentId . '::assigned_user_id","' . $documentId . '::ossdc_status"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}']
				];
				break;
			case 'ActivityRegister':
				$widgets = [
					['182', $moduleName, 'Summary', NULL, '1', '0', '[]'],
					['183', $moduleName, 'Comments', 'ModComments', '1', '1', '{"relatedmodule":"ModComments","limit":"5"}'],
					['184', $moduleName, 'RelatedModule', '', '2', '2', '{"relatedmodule":"' . $calendarId . '","relatedfields":["' . $calendarId . '::subject","' . $calendarId . '::assigned_user_id","' . $calendarId . '::activitystatus","' . $calendarId . '::taskpriority"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}'],
					['185', $moduleName, 'RelatedModule', '', '2', '3', '{"relatedmodule":"' . $documentId . '","relatedfields":["' . $documentId . '::notes_title","' . $documentId . '::assigned_user_id","' . $documentId . '::ossdc_status"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}']
				];
				break;
			case 'DataSetRegister':
				$widgets = [
					['177', $moduleName, 'Comments', 'ModComments', '2', '3', '{"relatedmodule":"ModComments","limit":"5"}'],
					['178', $moduleName, 'Summary', NULL, '1', '0', '[]'],
					['179', $moduleName, 'Updates', 'LBL_UPDATES', '1', '1', '[]'],
					['180', $moduleName, 'RelatedModule', '', '2', '2', '{"relatedmodule":"' . $documentId . '","relatedfields":["' . $documentId . '::notes_title","' . $documentId . '::assigned_user_id","' . $documentId . '::ossdc_status"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}'],
					['181', $moduleName, 'RelatedModule', '', '1', '4', '{"relatedmodule":"' . $calendarId . '","relatedfields":["' . $calendarId . '::subject","' . $calendarId . '::assigned_user_id","' . $calendarId . '::activitystatus","' . $calendarId . '::taskpriority"],"viewtype":"List","limit":"5","action":"0","actionSelect":"0","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}']
				];
				break;
			default:
				break;
		}
		return $widgets;
	}

	/**
	 * Widgets
	 * @param string $moduleName
	 */
	public function widgets(string $moduleName)
	{
		$rows = [];
		foreach ($this->getWidgetToSummary($moduleName) as $widget) {
			if (empty($widget)) {
				continue;
			}
			$rows[] = ['vtiger_widgets', [
					'tabid' => \App\Module::getModuleId($widget[1]),
					'type' => $widget[2],
					'label' => $widget[3],
					'wcol' => $widget[4],
					'sequence' => $widget[5],
					'data' => $widget[6]
			]];
		}
		\App\Db\Updater::batchInsert($rows);
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
		$userIds = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['deleted' => 0])->column();
		foreach ($userIds as $id) {
			\App\UserPrivilegesFile::createUserPrivilegesfile($id);
			\App\UserPrivilegesFile::createUserSharingPrivilegesfile($id);
		}
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
			[41, 2763, 'projectmilestone_status', 'vtiger_projectmilestone', 1, 15, 'projectmilestone_status', 'FL_STATUS', 1, 2, '', '255', 6, 101, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 'string(255)', 'LBL_PROJECT_MILESTONE_INFORMATION', ['PLL_OPEN', 'PLL_IN_PROGRESS', 'PLL_COMPLETED', 'PLL_DEFERRED', 'PLL_CANCELLED'], [], 'ProjectMilestone'],
			[42, 1318, 'parentid', 'vtiger_projectmilestone', 1, 10, 'parentid', 'FL_PARENT_PROJECT_MILESTONE', 1, 2, '', 100, 13, 104, 1, 'V~O', 1, null, 'BAS', 1, '', 0, '', null, 'int(10)', 'LBL_PROJECT_MILESTONE_INFORMATION', [], ['ProjectMilestone'], 'ProjectMilestone'],
			[29, 2764, 'authy_methods', 'vtiger_users', 1, 16, 'authy_methods', 'FL_AUTHY_METHODS', 1, 2, '', null, 3, 83, 10, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 'string(255)', 'LBL_USER_ADV_OPTIONS', ['PLL_AUTHY_TOTP'], [], 'Users'],
			[29, 2765, 'authy_secret_totp', 'vtiger_users', 1, 358, 'authy_secret_totp', 'FL_AUTHY_SECRET_TOTP', 1, 2, '', null, 4, 83, 10, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 'string(255)', 'LBL_USER_ADV_OPTIONS', [], [], 'Users']
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
					['type' => 'add', 'search' => '];', 'checkInContents' => 'MAX_MERGE_RECORDS', 'addingType' => 'before', 'value' => "	// Maximum number of merged records
	'MAX_MERGE_RECORDS' => 4,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'CHART_ADDITIONAL_FILTERS_LIMIT', 'addingType' => 'before', 'value' => "	//Additional filters limit for ChartFilter's
	'CHART_ADDITIONAL_FILTERS_LIMIT'=>6,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'CHART_MULTI_FILTER_STR_LEN', 'addingType' => 'before', 'value' => "	//Charts multi filter maximum db value length
	'CHART_MULTI_FILTER_STR_LEN' => 50,
"],
					['type' => 'add', 'search' => '];', 'checkInContents' => 'CHART_MULTI_FILTER_LIMIT', 'addingType' => 'before', 'value' => "	//Charts multi filter limit
	'CHART_MULTI_FILTER_LIMIT' => 5,
"]
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
					['type' => 'add', 'search' => '];', 'checkInContents' => 'USER_AUTHY_MODE', 'addingType' => 'before', 'value' => "	/**
	 * User authentication mode possible values:
	 * TOTP_OFF - 2FA TOTP is checking off
	 * TOTP_OPTIONAL - It is defined by the user
	 * TOTP_OBLIGATORY - It is obligatory.
	 */
	'USER_AUTHY_MODE' => 'TOTP_OPTIONAL',
	/**
	 * Exceptions list of users (int[])
	 * TOTP - Time-based One-time Password.
	 */
	'USER_AUTHY_TOTP_EXCEPTIONS' => [],
"],
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
