<?php
/**
 * YetiForceUpdate Class.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
// last check: 7e9c346c9c1f75a43b3efcc7809928c3265f9034
/**
 * YetiForceUpdate Class.
 */
class YetiForceUpdate
{
	/**
	 * @var string
	 */
	public $logFile = 'cache/logs/updateLogsTrace.log';
	/**
	 * @var \vtlib\PackageImport
	 */
	public $package;

	/**
	 * @var object
	 */
	public $modulenode;

	/**
	 * Fields to delete.
	 *
	 * @var string[]
	 */
	public $filesToDelete = [];

	/**
	 * DbImporter.
	 *
	 * @var DbImporter
	 */
	private $importer;

	private $error = [];

	/**
	 * Constructor.
	 *
	 * @param object $modulenode
	 * @param mixed  $moduleNode
	 */
	public function __construct($moduleNode)
	{
		$this->modulenode = $moduleNode;
		$this->filesToDelete = require_once 'deleteFiles.php';
	}

	/**
	 * Logs.
	 *
	 * @param string $message
	 * @param bool   $eol
	 */
	public function log($message, bool $eol = true)
	{
		$fp = fopen($this->logFile, 'a+');
		if (0 === strpos($message, '[')) {
			$message = $message . PHP_EOL;
		}
		if ($eol) {
			$message = PHP_EOL . $message;
		}
		fwrite($fp, $message);
		fclose($fp);
		if (false !== strpos($message, '[ERROR]')) {
			$this->error[] = $message;
		}
	}

	/**
	 * Preupdate.
	 */
	public function preupdate()
	{
		$minTime = 600;
		$error = '';
		if (version_compare(PHP_VERSION, '7.2', '<')) {
			$error = 'Wrong PHP version, recommended version >= 7.2';
		}
		if (ini_get('max_execution_time') < $minTime) {
			$error .= PHP_EOL . 'max_execution_time = ' . ini_get('max_execution_time') . ' < ' . $minTime;
		}
		if (ini_get('max_input_time') < $minTime) {
			$error .= PHP_EOL . 'max_input_time = ' . ini_get('max_input_time') . ' < ' . $minTime;
		}
		if ($error) {
			$this->package->_errorText = 'The server configuration is not compatible with the requirements of the upgrade package. Please have a look at the list of errors:' . PHP_EOL . PHP_EOL . $error;
			return false;
		}
		copy(__DIR__ . '/files/app/Db/Importer.php', ROOT_DIRECTORY . '/app/Db/Importer.php');
		copy(__DIR__ . '/files/app/Db/Fixer.php', ROOT_DIRECTORY . '/app/Db/Fixer.php');
		copy(__DIR__ . '/files/app/Db/Importers/Base.php', ROOT_DIRECTORY . '/app/Db/Importers/Base.php');
		copy(__DIR__ . '/files/vtlib/Vtiger/Block.php', ROOT_DIRECTORY . '/vtlib/Vtiger/Block.php');
		copy(__DIR__ . '/files/modules/Vtiger/models/Field.php', ROOT_DIRECTORY . '/modules/Vtiger/models/Field.php');
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		try {
			$this->importer = new \App\Db\Importer();
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->updateScheme();
			$this->importer->importData();
			$this->importer->postUpdate();
			$this->createdUserId(1);
			$this->addModules(['Approvals', 'ApprovalsRegister', 'MailIntegration']);
			$this->updateRelationField();
			$this->updateWidgets();
			$this->dropColumns();
			$this->indexes();
			$this->changeColumnType();
			$this->changeColumnType();
			$this->importer->refreshSchema();
			$this->importer->postUpdate();

			// $this->importer->dropTable(['vtiger_vendorcontactrel', 'vtiger_seticketsrel']);
			$this->importer->logs(false);
		} catch (\Throwable $ex) {
			$this->log($ex->getMessage() . '|' . $ex->getTraceAsString());
			$this->importer->logs(false);
			throw $ex;
		}
		$this->importer->refreshSchema();
		$db->createCommand()->checkIntegrity(true)->execute();
		$this->data();
		$this->menu();
		$this->blocks();
		$this->addFields();
		$this->updateUsersFields();
		$this->updateWorkflowTaskSumFieldFromDependent();
		$this->createdUserId(2);
		$this->deleteFilesAfterUpgrade();
		$this->lastUpdateScheme();

		$this->importer->refreshSchema();
		$this->importer->logs(false);

		$this->createConfigFiles();
		$this->log(__METHOD__ . ' - ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . "\t\t|\t" . date('H:i:s'));

		$this->log('Fixer::baseModuleTools: ' . $this->baseModuleTools());
		$this->log('Fixer::baseModuleActions: ' . App\Db\Fixer::baseModuleActions());
		$this->log('Fixer::profileField: ' . App\Db\Fixer::profileField());
		$this->log('Fixer::maximumFieldsLength: ' . print_r($this->maximumFieldsLength(), true));

		$menuRecordModel = new \Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();

		\App\Cache::clearAll();
		\App\Cache::clear();
		\App\Cache::clearOpcache();
		\App\Module::createModuleMetaFile();
		\App\UserPrivilegesFile::recalculateAll();

		\App\Cache::clear();
		\App\Cache::clearOpcache();

		\App\Colors::generate();
		if ($this->error || false !== strpos($this->importer->logs, 'Error')) {
			$this->stopProcess();
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
		return true;
	}

	private function createdUserId(int $type)
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));

		$uiType = 52;
		$u = $i = 0;

		$mods = ['Documents', 'HolidaysEntitlement'];
		$query = (new \App\Db\Query())->select(['tabid', 'name', 'isentitytype'])->from('vtiger_tab')->where(['isentitytype' => 1]);
		if (1 === $type) {
			$query->andWhere(['not in', 'name', $mods]);
		} else {
			$query->andWhere(['name' => $mods]);
		}
		$rows = $query->all();
		foreach ($rows as $row) {
			$tabId = $row['tabid'];
			$moduleName = $row['name'];
			$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tabid' => $tabId, 'columnname' => 'smcreatorid'])->scalar();
			if ($fieldId) {
				\App\Db::getInstance()->createCommand()->update('vtiger_field', ['uitype' => $uiType], ['fieldid' => $fieldId])->execute();
				++$u;
			} else {
				$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['tabid' => $tabId])->orderBy(['sequence' => \SORT_DESC])->scalar();
				$blockInstance = vtlib\Block::getInstance($blockId, $moduleName);
				$fieldInstance = new \vtlib\Field();
				$fieldInstance->name = 'created_user_id';
				$fieldInstance->table = 'vtiger_crmentity';
				$fieldInstance->label = 'Created By';
				$fieldInstance->column = 'smcreatorid';
				$fieldInstance->columntype = 'int(19)';
				$fieldInstance->maximumlength = 65535;
				$fieldInstance->uitype = $uiType;
				$fieldInstance->typeofdata = 'V~O';
				$fieldInstance->displaytype = 2;
				$blockInstance->addField($fieldInstance);
				++$i;
			}
		}

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . " min. [u: $u|i: $i]", false);
	}

	private function data()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));

		\App\Db\Updater::batchUpdate([
			['s_yf_address_finder_config', ['val' => 'YetiForceGeocoder'], ['name' => 'default_provider', 'type' => 'global', 'val' => '']],
			['vtiger_field', ['quickcreate' => 3, 'masseditable' => 0, 'displaytype' => 2, 'quickcreatesequence' => 0], ['uitype' => 52, 'columnname' => 'smcreatorid']],
			['vtiger_approvals_register_status', ['presence' => 0], ['approvals_register_status' => 'PLL_ACCEPTED']],
			['vtiger_approvals_register_type', ['presence' => 0], ['or', ['approvals_register_type' => 'PLL_ACCEPTANCE'], ['approvals_register_type' => 'PLL_RESIGNATION']]],
			['vtiger_approvals_status', ['presence' => 0], ['approvals_status' => 'PLL_ACTIVE']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_Workflow_Cron'], ['name' => 'LBL_WORKFLOW']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_AddressBook_Cron'], ['name' => 'LBL_ADDRESS_BOOK']],
			['vtiger_cron_task', ['handler_class' => 'Calendar_SendReminder_Cron'], ['name' => 'LBL_SEND_REMINDER']],
			['vtiger_cron_task', ['handler_class' => 'Settings_CurrencyUpdate_CurrencyUpdate_Cron'], ['name' => 'LBL_CURRENCY_UPDATE']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_Mailer_Cron'], ['name' => 'LBL_MAILER']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_BrowsingHistory_Cron'], ['name' => 'LBL_BROWSING_HISTORY']],
			['vtiger_cron_task', ['handler_class' => 'Import_ScheduledImport_Cron'], ['name' => 'LBL_SCHEDULED_IMPORT']],
			['vtiger_cron_task', ['handler_class' => 'OSSMailScanner_Action_Cron'], ['name' => 'LBL_MAIL_SCANNER_ACTION']],
			['vtiger_cron_task', ['handler_class' => 'OSSMailScanner_Verification_Cron'], ['name' => 'LBL_MAIL_SCANNER_VERIFICATION']],
			['vtiger_cron_task', ['handler_class' => 'OSSMailScanner_Bind_Cron'], ['name' => 'LBL_MAIL_SCANNER_BIND']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_BatchProcesses_Cron'], ['name' => 'LBL_BATCH_PROCESSES']],
			['vtiger_cron_task', ['handler_class' => 'Contacts_CardDav_Cron'], ['name' => 'LBL_CARD_DAV']],
			['vtiger_cron_task', ['handler_class' => 'Calendar_CalDav_Cron'], ['name' => 'LBL_CAL_DAV']],
			['vtiger_cron_task', ['handler_class' => 'Calendar_ActivityState_Cron'], ['name' => 'LBL_ACTIVITY_STATE']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_MultiReference_Cron'], ['name' => 'LBL_MULTI_REFERENCE_VALUE']],
			['vtiger_cron_task', ['handler_class' => 'Calendar_SetCrmActivity_Cron'], ['name' => 'LBL_CRMACTIVITY_DAYS']],
			['vtiger_cron_task', ['handler_class' => 'Assets_Renewal_Cron'], ['name' => 'LBL_ASSETS_RENEWAL']],
			['vtiger_cron_task', ['handler_class' => 'OSSSoldServices_Renewal_Cron'], ['name' => 'LBL_SOLD_SERVICES_RENEWAL']],
			['vtiger_cron_task', ['handler_class' => 'Notification_Notifications_Cron'], ['name' => 'LBL_SEND_NOTIFICATIONS']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_LabelUpdater_Cron'], ['name' => 'LBL_RECORD_LABEL_UPDATER']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_PrivilegesUpdater_Cron'], ['name' => 'LBL_PRIVILEGES_UPDATER']],
			['vtiger_cron_task', ['handler_class' => 'OpenStreetMap_UpdaterCoordinates_Cron'], ['name' => 'LBL_UPDATER_COORDINATES']],
			['vtiger_cron_task', ['handler_class' => 'OpenStreetMap_UpdaterRecordsCoordinates_Cron'], ['name' => 'LBL_UPDATER_RECORDS_COORDINATES']],
			['vtiger_cron_task', ['handler_class' => 'ModTracker_ReviewChanges_Cron'], ['name' => 'LBL_MARK_RECORDS_AS_REVIEWED']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_Cache_Cron'], ['name' => 'LBL_CACHE']],
			['vtiger_cron_task', ['handler_class' => 'Calendar_RecurringEvents_Cron'], ['name' => 'LBL_NEVER_ENDING_RECURRING_EVENTS']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_FileUploadTemp_Cron'], ['name' => 'LBL_CLEAR_FILE_UPLOAD_TEMP']],
			['vtiger_cron_task', ['handler_class' => 'SMSNotifier_SMSNotifier_Cron'], ['name' => 'LBL_SMSNOTIFIER']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_SystemWarnings_Cron'], ['name' => 'LBK_SYSTEM_WARNINGS']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_BatchMethods_Cron'], ['name' => 'LBL_BATCH_METHODS']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_SessionCleaner_Cron'], ['name' => 'LBL_SESSION_CLEANER']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_Social_Cron'], ['name' => 'LBL_ARCHIVE_OLD_RECORDS']],
			['vtiger_cron_task', ['handler_class' => 'Vtiger_SocialGet_Cron'], ['name' => 'LBL_GET_SOCIAL_MEDIA_MESSAGES']],
			['vtiger_ticketstatus', ['record_state' => 1], ['or',
				['ticketstatus' => 'Open'],
				['ticketstatus' => 'In Progress'],
				['ticketstatus' => 'Wait For Response'],
				['ticketstatus' => 'Answered'],
				['ticketstatus' => 'PLL_SUBMITTED_COMMENTS'],
				['ticketstatus' => 'PLL_FOR_APPROVAL'],
				['ticketstatus' => 'PLL_TO_CLOSE'],
			]],
			['vtiger_ticketstatus', ['sortorderid' => 2], ['ticketstatus' => 'Wait For Response']],
			['vtiger_ticketstatus', ['sortorderid' => 3], ['ticketstatus' => 'Answered']],
			['vtiger_field', ['summaryfield' => 1, 'masseditable' => 2], ['columnname' => 'status', 'tablename' => 'vtiger_activity']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'priority', 'tablename' => 'vtiger_activity']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'description', 'tabid' => \App\Module::getModuleId('Calendar')]],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'location', 'tablename' => 'vtiger_activity']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'linkextend', 'tablename' => 'vtiger_activity']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'start_date', 'tablename' => 'vtiger_servicecontracts']],
			['vtiger_field', ['summaryfield' => 1, 'masseditable' => 2], ['columnname' => 'business_phone', 'tablename' => 'vtiger_ossemployees']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'servicecontractsid', 'tablename' => 'vtiger_troubletickets']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'contactstatus', 'tablename' => 'vtiger_contactdetails']],
			['vtiger_field', ['summaryfield' => 1, 'quickcreate' => 2], ['columnname' => 'allday', 'tablename' => 'vtiger_activity']],
			['vtiger_field', ['maximumlength' => '-2147483648,2147483647'], ['columnname' => 'currency_id', 'tablename' => 'vtiger_paymentsin']],
			['vtiger_field', ['maximumlength' => '-2147483648,2147483647'], ['columnname' => 'currency_id', 'tablename' => 'vtiger_paymentsout']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'followup', 'tablename' => 'vtiger_activity']],
			['vtiger_field', ['typeofdata' => 'V~O'], ['columnname' => 'products', 'tablename' => 'vtiger_account']],
			['vtiger_field', ['typeofdata' => 'V~O'], ['columnname' => 'services', 'tablename' => 'vtiger_account']],
			['vtiger_field', ['summaryfield' => 1, 'displaytype' => 1], ['columnname' => 'contract_type', 'tablename' => 'vtiger_troubletickets']],
			['vtiger_field', ['summaryfield' => 1, 'displaytype' => 1], ['columnname' => 'contracts_end_date', 'tablename' => 'vtiger_troubletickets']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'position', 'tablename' => 'vtiger_ossemployees']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'category', 'tablename' => 'vtiger_troubletickets']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'response_expected', 'tablename' => 'vtiger_troubletickets']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'solution_expected', 'tablename' => 'vtiger_troubletickets']],
			['vtiger_field', ['summaryfield' => 1], ['columnname' => 'multicompanyid', 'tablename' => 'vtiger_ossemployees']],
			['vtiger_field', ['maximumlength' => '99999999'], ['columnname' => 'weight', 'tablename' => 'vtiger_products']],
		]);

		\App\Db\Updater::batchInsert([
			['s_yf_address_finder_config', ['name' => 'active', 'type' => 'YetiForceGeocoder', 'val' => 1]],
			['vtiger_eventhandlers', ['event_name' => 'EntityAfterSave', 'handler_class' => 'ApprovalsRegister_Approvals_Handler', 'is_active' => 1, 'include_modules' => 'ApprovalsRegister', 'exclude_modules' => '', 'priority' => 5, 'owner_id' => \App\Module::getModuleId('ApprovalsRegister')]],
		]);

		\App\Db\Updater::batchDelete([
			['vtiger_module_dashboard_blocks',  ['NOT IN',  'authorized', (new \App\Db\Query())->select(['roleid'])->from('vtiger_role')]],
			['vtiger_module_dashboard',  ['and', ['<>', 'blockid', 0],  ['NOT IN',  'blockid', (new \App\Db\Query())->select(['id'])->from('vtiger_module_dashboard_blocks')]]],
			['vtiger_module_dashboard_blocks',  ['NOT IN',  'authorized', (new \App\Db\Query())->select(['roleid'])->from('vtiger_role')]],
			['yetiforce_proc_marketing',  ['type' => 'lead',  'param' => 'currentuser_status']]
		]);

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function updateUsersFields()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$rows = [
			[['generatedtype' => 1, 'uitype' => 106, 'fieldname' => 'user_name', 'fieldlabel' => 'User Name', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '3,64', 'sequence' => 1, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'user_name']],
			[['generatedtype' => 1, 'uitype' => 156, 'fieldname' => 'is_admin', 'fieldlabel' => 'Admin', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => null, 'sequence' => 2, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'is_admin']],
			[['generatedtype' => 1, 'uitype' => 99, 'fieldname' => 'user_password', 'fieldlabel' => 'Password', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '200', 'sequence' => 7, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 4, 'typeofdata' => 'P~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'user_password']],
			[['generatedtype' => 1, 'uitype' => 99, 'fieldname' => 'confirm_password', 'fieldlabel' => 'Confirm Password', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '200', 'sequence' => 8, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 4, 'typeofdata' => 'P~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'confirm_password']],
			[['generatedtype' => 1, 'uitype' => 1, 'fieldname' => 'first_name', 'fieldlabel' => 'First Name', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '30', 'sequence' => 3, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'first_name']],
			[['generatedtype' => 1, 'uitype' => 2, 'fieldname' => 'last_name', 'fieldlabel' => 'Last Name', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '30', 'sequence' => 5, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'last_name']],
			[['generatedtype' => 1, 'uitype' => 98, 'fieldname' => 'roleid', 'fieldlabel' => 'Role', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '255', 'sequence' => 6, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_user2role', 'columnname' => 'roleid']],
			[['generatedtype' => 1, 'uitype' => 104, 'fieldname' => 'email1', 'fieldlabel' => 'Email', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '100', 'sequence' => 4, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'E~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'email1']],
			[['generatedtype' => 1, 'uitype' => 115, 'fieldname' => 'status', 'fieldlabel' => 'Status', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => 'Active', 'maximumlength' => '25', 'sequence' => 9, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'status']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'activity_view', 'fieldlabel' => 'Default Activity View', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => 'This Month', 'maximumlength' => '200', 'sequence' => 6, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'activity_view']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'lead_view', 'fieldlabel' => 'Default Lead View', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => 'Today', 'maximumlength' => '200', 'sequence' => 12, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'lead_view']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'hour_format', 'fieldlabel' => 'Calendar Hour Format', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '24', 'maximumlength' => '30', 'sequence' => 3, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'hour_format']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'start_hour', 'fieldlabel' => 'Day starts at', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '08:00', 'maximumlength' => '30', 'sequence' => 2, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'start_hour']],
			[['generatedtype' => 1, 'uitype' => 101, 'fieldname' => 'reports_to_id', 'fieldlabel' => 'Reports To', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '4294967295', 'sequence' => 3, 'block' => 'LBL_MORE_INFORMATION', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'reports_to_id']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'date_format', 'fieldlabel' => 'Date Format', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => 'yyyy-mm-dd', 'maximumlength' => '200', 'sequence' => 1, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'date_format']],
			[['generatedtype' => 1, 'uitype' => 21, 'fieldname' => 'description', 'fieldlabel' => 'Documents', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '65535', 'sequence' => 2, 'block' => 'LBL_MORE_INFORMATION', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'description']],
			[['generatedtype' => 1, 'uitype' => 3, 'fieldname' => 'accesskey', 'fieldlabel' => 'Webservice Access Key', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '36', 'sequence' => 4, 'block' => 'LBL_USER_INTEGRATION', 'displaytype' => 2, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'accesskey']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'time_zone', 'fieldlabel' => 'Time Zone', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '200', 'sequence' => 5, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'time_zone']],
			[['generatedtype' => 1, 'uitype' => 117, 'fieldname' => 'currency_id', 'fieldlabel' => 'Currency', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '-2147483648,2147483647', 'sequence' => 1, 'block' => 'LBL_CURRENCY_CONFIGURATION', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'currency_id']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'currency_grouping_pattern', 'fieldlabel' => 'Digit Grouping Pattern', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '123,456,789', 'maximumlength' => '100', 'sequence' => 2, 'block' => 'LBL_CURRENCY_CONFIGURATION', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'currency_grouping_pattern']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'currency_decimal_separator', 'fieldlabel' => 'Decimal Separator', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '.', 'maximumlength' => '2', 'sequence' => 3, 'block' => 'LBL_CURRENCY_CONFIGURATION', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'currency_decimal_separator']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'currency_grouping_separator', 'fieldlabel' => 'Digit Grouping Separator', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => ' ', 'maximumlength' => '2', 'sequence' => 4, 'block' => 'LBL_CURRENCY_CONFIGURATION', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'currency_grouping_separator']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'currency_symbol_placement', 'fieldlabel' => 'Symbol Placement', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '1.0$', 'maximumlength' => '20', 'sequence' => 5, 'block' => 'LBL_CURRENCY_CONFIGURATION', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'currency_symbol_placement']],
			[['generatedtype' => 1, 'uitype' => 69, 'fieldname' => 'imagename', 'fieldlabel' => 'User Image', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '65535', 'sequence' => 6, 'block' => 'LBL_USER_GUI', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'imagename']],
			[['generatedtype' => 1, 'uitype' => 56, 'fieldname' => 'internal_mailer', 'fieldlabel' => 'INTERNAL_MAIL_COMPOSER', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '255', 'sequence' => 2, 'block' => 'LBL_USER_MAIL', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'internal_mailer']],
			[['generatedtype' => 1, 'uitype' => 31, 'fieldname' => 'theme', 'fieldlabel' => 'Theme', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => 'twilight', 'maximumlength' => '100', 'sequence' => 4, 'block' => 'LBL_USER_GUI', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'theme']],
			[['generatedtype' => 1, 'uitype' => 32, 'fieldname' => 'language', 'fieldlabel' => 'Language', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '36', 'sequence' => 1, 'block' => 'LBL_USER_GUI', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'language']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'reminder_interval', 'fieldlabel' => 'Reminder Interval', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '15 Minutes', 'maximumlength' => '100', 'sequence' => 11, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'reminder_interval']],
			[['generatedtype' => 1, 'uitype' => 11, 'fieldname' => 'phone_crm_extension', 'fieldlabel' => 'CRM Phone Extension', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '100', 'sequence' => 12, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'phone_crm_extension']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'no_of_currency_decimals', 'fieldlabel' => 'Number Of Currency Decimals', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '2', 'maximumlength' => '1', 'sequence' => 6, 'block' => 'LBL_CURRENCY_CONFIGURATION', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'no_of_currency_decimals']],
			[['generatedtype' => 1, 'uitype' => 56, 'fieldname' => 'truncate_trailing_zeros', 'fieldlabel' => 'Truncate Trailing Zeros', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '0', 'maximumlength' => '255', 'sequence' => 7, 'block' => 'LBL_CURRENCY_CONFIGURATION', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'truncate_trailing_zeros']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'dayoftheweek', 'fieldlabel' => 'Starting Day of the week', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'Monday', 'maximumlength' => '100', 'sequence' => 7, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'dayoftheweek']],
			[['generatedtype' => 1, 'uitype' => 315, 'fieldname' => 'othereventduration', 'fieldlabel' => 'Other Event Duration', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '65535', 'sequence' => 13, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'othereventduration']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'default_record_view', 'fieldlabel' => 'Default Record View', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'Summary', 'maximumlength' => '10', 'sequence' => 5, 'block' => 'LBL_USER_GUI', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'default_record_view']],
			[['generatedtype' => 1, 'uitype' => 56, 'fieldname' => 'leftpanelhide', 'fieldlabel' => 'LBL_MENU_EXPANDED_BY_DEFAULT', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '0', 'maximumlength' => '255', 'sequence' => 3, 'block' => 'LBL_USER_GUI', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'leftpanelhide']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'rowheight', 'fieldlabel' => 'Row Height', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'medium', 'maximumlength' => '10', 'sequence' => 2, 'block' => 'LBL_USER_GUI', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'rowheight']],
			[['generatedtype' => 1, 'uitype' => 15, 'fieldname' => 'defaulteventstatus', 'fieldlabel' => 'Default Event Status', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'PLL_PLANNED', 'maximumlength' => '50', 'sequence' => 10, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'defaulteventstatus']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'defaultactivitytype', 'fieldlabel' => 'Default Activity Type', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'Meeting', 'maximumlength' => '50', 'sequence' => 8, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'defaultactivitytype']],
			[['generatedtype' => 1, 'uitype' => 1, 'fieldname' => 'is_owner', 'fieldlabel' => 'Account Owner', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '0', 'maximumlength' => '5', 'sequence' => 7, 'block' => 'LBL_MORE_INFORMATION', 'displaytype' => 5, 'typeofdata' => 'V~O', 'quickcreate' => 0, 'quickcreatesequence' => 1, 'info_type' => 'BAS', 'masseditable' => 0, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'is_owner']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'end_hour', 'fieldlabel' => 'Day ends at', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '16:00', 'maximumlength' => '30', 'sequence' => 4, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'end_hour']],
			[['generatedtype' => 1, 'uitype' => 56, 'fieldname' => 'emailoptout', 'fieldlabel' => 'Approval for email', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '255', 'sequence' => 4, 'block' => 'LBL_USER_MAIL', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => null, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'emailoptout']],
			[['generatedtype' => 1, 'uitype' => 56, 'fieldname' => 'available', 'fieldlabel' => 'FL_AVAILABLE', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '-128,127', 'sequence' => 1, 'block' => 'LBL_USER_AUTOMATION', 'displaytype' => 1, 'typeofdata' => 'C~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'available']],
			[['generatedtype' => 1, 'uitype' => 56, 'fieldname' => 'auto_assign', 'fieldlabel' => 'FL_AUTO_ASSIGN_RECORDS', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '-128,127', 'sequence' => 3, 'block' => 'LBL_USER_AUTOMATION', 'displaytype' => 1, 'typeofdata' => 'C~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'auto_assign']],
			[['generatedtype' => 1, 'uitype' => 7, 'fieldname' => 'records_limit', 'fieldlabel' => 'FL_RECORD_LIMIT_IN_MODULE', 'readonly' => 0, 'presence' => 0, 'defaultvalue' => '', 'maximumlength' => '-2147483648,2147483647', 'sequence' => 2, 'block' => 'LBL_USER_AUTOMATION', 'displaytype' => 1, 'typeofdata' => 'I~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'records_limit']],
			[['generatedtype' => 1, 'uitype' => 1, 'fieldname' => 'phone_crm_extension_extra', 'fieldlabel' => 'FL_PHONE_CUSTOM_INFORMATION', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '100', 'sequence' => 13, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 3, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'phone_crm_extension_extra']],
			[['generatedtype' => 1, 'uitype' => 80, 'fieldname' => 'date_password_change', 'fieldlabel' => 'FL_DATE_PASSWORD_CHANGE', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => null, 'sequence' => 8, 'block' => 'LBL_MORE_INFORMATION', 'displaytype' => 2, 'typeofdata' => 'DT~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'date_password_change']],
			[['generatedtype' => 1, 'uitype' => 56, 'fieldname' => 'force_password_change', 'fieldlabel' => 'FL_FORCE_PASSWORD_CHANGE', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '-128,127', 'sequence' => 4, 'block' => 'LBL_USER_ADV_OPTIONS', 'displaytype' => 1, 'typeofdata' => 'C~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'force_password_change']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'view_date_format', 'fieldlabel' => 'FL_VIEW_DATE_FORMAT', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'PLL_ELAPSED', 'maximumlength' => '50', 'sequence' => 9, 'block' => 'LBL_CALENDAR_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'view_date_format']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'authy_methods', 'fieldlabel' => 'FL_AUTHY_METHODS', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '255', 'sequence' => 3, 'block' => 'LBL_USER_ADV_OPTIONS', 'displaytype' => 10, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'authy_methods']],
			[['generatedtype' => 1, 'uitype' => 312, 'fieldname' => 'authy_secret_totp', 'fieldlabel' => 'FL_AUTHY_SECRET_TOTP', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '255', 'sequence' => 2, 'block' => 'LBL_USER_ADV_OPTIONS', 'displaytype' => 10, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'authy_secret_totp']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'login_method', 'fieldlabel' => 'FL_LOGIN_METHOD', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'PLL_PASSWORD', 'maximumlength' => '255', 'sequence' => 1, 'block' => 'LBL_USER_ADV_OPTIONS', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'login_method']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'sync_carddav', 'fieldlabel' => 'LBL_CARDDAV_SYNCHRONIZATION_CONTACT', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'PLL_OWNER', 'maximumlength' => '255', 'sequence' => 1, 'block' => 'LBL_USER_INTEGRATION', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'sync_carddav']],
			[['generatedtype' => 1, 'uitype' => 16, 'fieldname' => 'sync_caldav', 'fieldlabel' => 'LBL_CALDAV_SYNCHRONIZATION_CALENDAR', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => 'PLL_OWNER', 'maximumlength' => '255', 'sequence' => 3, 'block' => 'LBL_USER_INTEGRATION', 'displaytype' => 1, 'typeofdata' => 'V~M', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'sync_caldav']],
			[['generatedtype' => 1, 'uitype' => 35, 'fieldname' => 'sync_carddav_default_country', 'fieldlabel' => 'LBL_CARDDAV_DEFAULT_COUNTRY', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '255', 'sequence' => 2, 'block' => 'LBL_USER_INTEGRATION', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => 'Edit,Detail,PreferenceDetail', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'sync_carddav_default_country']],
			[['generatedtype' => 1, 'uitype' => 301, 'fieldname' => 'default_search_module', 'fieldlabel' => 'FL_DEFAULT_SEARCH_MODULE', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '25', 'sequence' => 0, 'block' => 'LBL_GLOBAL_SEARCH_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'default_search_module']],
			[['generatedtype' => 1, 'uitype' => 56, 'fieldname' => 'default_search_override', 'fieldlabel' => 'FL_OVERRIDE_SEARCH_MODULE', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '-128,127', 'sequence' => 0, 'block' => 'LBL_GLOBAL_SEARCH_SETTINGS', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => 'Edit,Detail,PreferenceDetail', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'default_search_override']],
			[['generatedtype' => 1, 'uitype' => 1, 'fieldname' => 'primary_phone_extra', 'fieldlabel' => 'FL_PHONE_CUSTOM_INFORMATION', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '100', 'sequence' => 11, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 3, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'primary_phone_extra']],
			[['generatedtype' => 1, 'uitype' => 11, 'fieldname' => 'primary_phone', 'fieldlabel' => 'FL_PRIMARY_PHONE', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '50', 'sequence' => 10, 'block' => 'LBL_USERLOGIN_ROLE', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'primary_phone']],
			[['generatedtype' => 1, 'uitype' => 322, 'fieldname' => 'mail_scanner_actions', 'fieldlabel' => 'FL_MAIL_SCANNER_ACTIONS', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '65535', 'sequence' => 1, 'block' => 'LBL_USER_MAIL', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'mail_scanner_actions']],
			[['generatedtype' => 1, 'uitype' => 323, 'fieldname' => 'mail_scanner_fields', 'fieldlabel' => 'FL_MAIL_SCANNER_FIELDS', 'readonly' => 0, 'presence' => 2, 'defaultvalue' => '', 'maximumlength' => '65535', 'sequence' => 3, 'block' => 'LBL_USER_MAIL', 'displaytype' => 1, 'typeofdata' => 'V~O', 'quickcreate' => 1, 'quickcreatesequence' => 0, 'info_type' => 'BAS', 'masseditable' => 1, 'helpinfo' => '', 'summaryfield' => 0, 'fieldparams' => '', 'header_field' => null, 'maxlengthtext' => 0, 'maxwidthcolumn' => 0, 'visible' => 0, 'tabindex' => 0], ['tablename' => 'vtiger_users', 'columnname' => 'mail_scanner_fields']],
		];

		$blocks = (new App\Db\Query())->select(['blocklabel', 'blockid'])
			->from('vtiger_blocks')
			->where(['tabid' => \App\Module::getModuleId('Users')])
			->createCommand()->queryAllByGroup(0);

		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($rows as $row) {
			if (isset($blocks[$row[0]['block']])) {
				$row[0]['block'] = $blocks[$row[0]['block']];
				$dbCommand->update('vtiger_field', $row[0], $row[1])->execute();
			} else {
				$this->log("[ERROR] No block found to create a field, you will need to create a field manually.
				Module: Users, field name: {$row['fieldname']}, field label: {$row['fieldlabel']}");
			}
		}
		$this->updateBlocks([
			['type' => 'remove', 'module' => 'Users', 'db' => ['LBL_USER_IMAGE_INFORMATION']],
		], ['blocklabel']);
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function blocks()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$this->updateBlocks([
			['type' => 'update', 'module' => 'Users', 'db' => ['LBL_USERLOGIN_ROLE', 1, 0, 0, 0, 0, 0, 2, 0, 'fas fa-user-tie']],
			['type' => 'update', 'module' => 'Users', 'db' => ['LBL_CURRENCY_CONFIGURATION', 3, 0, 0, 0, 0, 0, 2, 0, 'mdi-numeric']],
			['type' => 'update', 'module' => 'Users', 'db' => ['LBL_MORE_INFORMATION', 6, 0, 0, 0, 0, 0, 2, 0, 'fas fa-info']],
			['type' => 'update', 'module' => 'Users', 'db' => ['LBL_USER_ADV_OPTIONS', 7, 0, 0, 0, 0, 0, 2, 0, 'fas fa-user-lock']],
			['type' => 'update', 'module' => 'Users', 'db' => ['LBL_CALENDAR_SETTINGS', 2, 0, 0, 0, 0, 0, 2, 0, 'far fa-calendar-alt']],
			['type' => 'update', 'module' => 'Users', 'db' => ['LBL_GLOBAL_SEARCH_SETTINGS', 9, 0, 0, 0, 0, 0, 2, 0, 'fas fa-search']],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_MAIL', 4, 0, 0, 0, 0, 0, 2, 0, 'fas fa-mail-bulk']],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_INTEGRATION', 8, 0, 0, 0, 0, 0, 2, 0, 'fas fa-sync-alt']],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_GUI', 5, 0, 0, 0, 0, 0, 2, 0, 'fas fa-layer-group']],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_AUTOMATION', 10, 0, 0, 0, 0, 0, 2, 0, 'fas fa-fan']],
		], ['blocklabel', 'sequence', 'show_title', 'visible', 'create_view', 'edit_view', 'detail_view', 'display_status', 'iscustom', 'icon']);

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function menu()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));

		$id = ((new App\Db\Query())->select(['id'])->from('yetiforce_menu')->where(['label' => 'MEN_GDPR'])->scalar()) ?? 0;

		\App\Db\Updater::batchInsert([
			['yetiforce_menu', ['role' => 0, 'parentid' => $id, 'type' => 0, 'module' => \App\Module::getModuleId('Approvals'), 'label' => '', 'newwindow' => 0, 'dataurl' => null, 'showicon' => 0, 'icon' => '', 'sizeicon' => null, 'hotkey' => '', 'filters' => null, 'source' => 0]],
			['yetiforce_menu', ['role' => 0, 'parentid' => $id, 'type' => 0, 'module' => \App\Module::getModuleId('ApprovalsRegister'), 'label' => '', 'newwindow' => 0, 'dataurl' => null, 'showicon' => 0, 'icon' => '', 'sizeicon' => null, 'hotkey' => '', 'filters' => null, 'source' => 0]],
		]);

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function updateWorkflowTaskSumFieldFromDependent()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));

		Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTTask.php');
		require_once 'modules/com_vtiger_workflow/tasks/SumFieldFromDependent.php';

		$query = (new \App\Db\Query())->select(['task_id', 'task'])->from('com_vtiger_workflowtasks')->where(['like', 'task', 'O:21:"SumFieldFromDependent"%', false]);
		$dataReader = $query->createCommand()->query();
		$tabdataCache = require \ROOT_DIRECTORY . '/user_privileges/tabdata.php';
		$tabdataCache['tabName'] = array_flip($tabdataCache['tabId']);
		$modules = $tabdataCache['tabName'];
		$dbCommand = \App\Db::getInstance()->createCommand();
		$u = 0;
		while ($row = $dataReader->read()) {
			$update = false;
			$unserializeTask = unserialize($row['task']);
			$field = explode('::', $unserializeTask->targetField);
			if (!\in_array($field[1], $modules)) {
				$moduleName = $field[0];
				if (\in_array($moduleName, $modules)) {
					$fieldName = $field[1];
					$field[0] = $fieldName;
					$field[1] = $moduleName;
					$unserializeTask->targetField = implode('::', $field);
					$update = true;
				}
			}
			if (!empty($unserializeTask->conditions['rules'])) {
				foreach ($unserializeTask->conditions['rules'] as &$value) {
					$field = explode(':', $value['fieldname']);
					if (!\in_array($field[1], $modules)) {
						$moduleName = $field[0];
						if (\in_array($moduleName, $modules)) {
							$fieldName = $field[1];
							$field[0] = $fieldName;
							$field[1] = $moduleName;
							$value['fieldname'] = implode(':', $field);
							$update = true;
						}
					}
				}
				if ($update) {
					$dbCommand->update('com_vtiger_workflowtasks', ['task' => serialize($unserializeTask)], ['task_id' => $row['task_id']])->execute();
					++$u;
				}
			}
		}

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min. u:' . $u, false);
	}

	private function updateBlocks(array $blocks, array $blockNames)
	{
		foreach ($blocks as $block) {
			$data = array_combine($blockNames, $block['db']);
			$data['tabid'] = \App\Module::getModuleId($block['module']);
			if ('add' === $block['type']) {
				$blockInstance = new \vtlib\Block();
			} else {
				$blockInstance = \vtlib\Block::getInstance($data['blocklabel'], $data['tabid']);
			}
			if (!$blockInstance) {
				$this->log("[Warning] block does not exist | label:{$data['blocklabel']}, module: {$block['module']}");
				continue;
			}
			$createCommand = \App\Db::getInstance()->createCommand();
			if ('remove' === $block['type']) {
				$blockInstance->delete(false);
			//continue;
			} elseif ('update' === $block['type']) {
				$createCommand->update('vtiger_blocks', $data, ['blockid' => $blockInstance->id])->execute();
			} else {
				$createCommand->insert('vtiger_blocks', $data)->execute();
			}
			// $blockInstance->initialize($data);
			// $blockInstance->save();
		}
	}

	/**
	 * Add fields.
	 *
	 * @param mixed $fields
	 */
	public function addFields($fields = [])
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		if (empty($fields)) {
			$importerType = new \App\Db\Importers\Base();
			$fields = [
				//[34, 2828, 'attention', 'vtiger_crmentity', 1, 300, 'attention', 'Attention', 0, 2, '', null, 0, 445, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_DESCRIPTION_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'ServiceContracts'],
				[29, 2899, 'mail_scanner_actions', 'vtiger_users', 1, 322, 'mail_scanner_actions', 'FL_MAIL_SCANNER_ACTIONS', 0, 2, '', '65535', 1, 452, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_USER_MAIL', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Users'],
				[29, 2900, 'mail_scanner_fields', 'vtiger_users', 1, 323, 'mail_scanner_fields', 'FL_MAIL_SCANNER_FIELDS', 0, 2, '', '65535', 3, 452, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_USER_MAIL', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Users'],
				[4, 2901, 'approvals', 'vtiger_contactdetails', 1, 321, 'approvals', 'FL_APPROVALS', 0, 2, '', '65535', 12, 5, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_CUSTOM_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Contacts']
			];
		}

		foreach ($fields as $field) {
			$moduleId = App\Module::getModuleId($field['moduleName']);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists) {
				$this->log("[INFO] Skip adding field. Module: {$moduleId}-{$field['moduleName']}; field name: {$field[2]}, field exists: {$isExists}");
				continue;
			}
			$blockInstance = false;
			$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => $field['blockLabel'], 'tabid' => $moduleId])->scalar();
			if ($blockId) {
				$blockInstance = \vtlib\Block::getInstance($blockId, $moduleId);
			} elseif (isset($field['blockData'])) {
				$blockInstance = new \vtlib\Block();
				foreach ($field['blockData'] as $key => $value) {
					$blockInstance->{$key} = $value;
				}
				\Vtiger_Module_Model::getInstance($field['moduleName'])->addBlock($blockInstance);
				$blockId = $blockInstance->id;
				$blockInstance = \vtlib\Block::getInstance($blockId, $moduleId);
			}
			if (!$blockInstance &&
			!($blockInstance = reset(Vtiger_Module_Model::getInstance($field['moduleName'])->getBlocks()))) {
				$this->log("[ERROR] No block found to create a field, you will need to create a field manually.
				Module: {$field['moduleName']}, field name: {$field[6]}, field label: {$field[7]}");
				\App\Log::error("No block found ({$field['blockLabel']}) to create a field, you will need to create a field manually.
				Module: {$field['moduleName']}, field name: {$field[6]}, field label: {$field[7]}");
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
			$fieldInstance->columntype = $field['type'];
			$fieldInstance->presence = $field[9];
			$fieldInstance->maximumlength = $field[11];
			$fieldInstance->quicksequence = $field[17];
			$fieldInstance->info_type = $field[18];
			$fieldInstance->helpinfo = $field[20];
			$fieldInstance->summaryfield = $field[21];
			$fieldInstance->generatedtype = $field[4];
			$fieldInstance->defaultvalue = $field[10];
			if ($field['picklistValues'] && 302 == $field[5]) {
				$field[22] = $this->setTree($field['picklistValues']);
			}
			$fieldInstance->fieldparams = $field[22];
			$blockInstance->addField($fieldInstance);
			if ($field['picklistValues'] && (15 == $field[5] || 16 == $field[5] || 33 == $field[5])) {
				$fieldInstance->setPicklistValues($field['picklistValues']);
			}
			if ($field['relatedModules'] && 10 == $field[5]) {
				$fieldInstance->setRelatedModules($field['relatedModules']);
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function setTree($tree)
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));

		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$skipCheckData = false;
		$templateId = (new \App\Db\Query())->select(['templateid'])->from('vtiger_trees_templates')->where(['module' => $tree['base'][2], 'name' => $tree['base'][1]])->scalar();
		if (!$templateId) {
			$dbCommand->insert('vtiger_trees_templates', [
				'name' => $tree['base'][1],
				'module' => $tree['base'][2],
				'access' => $tree['base'][3]
			])->execute();
			$templateId = $db->getLastInsertID('vtiger_trees_templates_templateid_seq');
			$skipCheckData = true;
		}
		foreach ($tree['data'] as $data) {
			if (!$skipCheckData && (new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => $templateId, 'name' => $data[1]])->exists()) {
				continue;
			}
			$dbCommand->insert('vtiger_trees_templates_data', [
				'templateid' => $templateId,
				'name' => $data[1],
				'tree' => $data[2],
				'parentTree' => $data[3],
				'depth' => $data[4],
				'label' => $data[5],
				'state' => $data[6],
				'icon' => $data[7]
			])->execute();
		}

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
		return $templateId;
	}

	/**
	 * Add modules.
	 *
	 * @param string[] $modules
	 */
	private function addModules(array $modules)
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$command = \App\Db::getInstance()->createCommand();
		foreach ($modules as $moduleName) {
			if (file_exists(__DIR__ . '/' . $moduleName . '.xml') && !\vtlib\Module::getInstance($moduleName)) {
				$importInstance = new \vtlib\PackageImport();
				$importInstance->_modulexml = simplexml_load_file('cache/updates/updates/' . $moduleName . '.xml');
				$importInstance->importModule();
				$command->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			} else {
				$this->log('[INFO] Module exist: ') . $moduleName;
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function updateRelationField()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$db = \App\Db::getInstance();
		$query = new \App\Db\Query();
		$query->select(['vtiger_relatedlists.*', 'modulename' => 'vtiger_tab.name', 'moduleid' => 'vtiger_tab.tabid'])
			->from('vtiger_relatedlists')
			->innerJoin('vtiger_tab', 'vtiger_relatedlists.related_tabid = vtiger_tab.tabid')->where(['vtiger_relatedlists.name' => 'getDependentsList']);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', App\Module::getModuleName($row['tabid']));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel(Vtiger_Module_Model::getInstance($row['tabid']))->set('relatedModuleName', $row['modulename']);
			if ($fieldModel = $relationModel->getRelationField()) {
				$db->createCommand()->update('vtiger_relatedlists', ['field_name' => $fieldModel->getFieldName()], ['relation_id' => $row['relation_id']])->execute();
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function updateWidgets()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$db = App\Db::getInstance();
		$dataReader = (new App\Db\Query())->from('vtiger_widgets')->where(['type' => 'RelatedModule'])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$data = \App\Json::decode($row['data']);
			$id = (new \App\Db\Query())->select(['relation_id'])->from('vtiger_relatedlists')
				->where(['tabid' => $row['tabid'], 'related_tabid' => $data['relatedmodule']])->scalar();
			if ($id) {
				$data['relation_id'] = $id;
				$db->createCommand()->update('vtiger_widgets', [
					'data' => \App\Json::encode($data),
				], ['id' => $row['id']])->execute();
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function indexes()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$this->importer->dropIndexes([
			'vtiger_relatedlists' => ['tabid', 'tabid_2'],
		]);
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function dropColumns()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$this->importer->dropColumns([
			['vtiger_cron_task', 'handler_file']
		]);
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function deleteFilesAfterUpgrade()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));

		\vtlib\Functions::recurseDelete('app_data/LanguagesUpdater.json');
		\vtlib\Functions::recurseDelete('app_data/SystemUpdater.json');
		\vtlib\Functions::recurseDelete('app_data/cron.php');

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function changeColumnType()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$importerType = new \App\Db\Importers\Base();
		$data = [
			'u_yf_cfixedassets' => [
				'purchase_price' => $importerType->decimal(28, 8),
				'actual_price' => $importerType->decimal(28, 8),
			],
			'u_yf_fcorectinginvoice' => [
				'sum_total' => $importerType->decimal(28, 8),
				'sum_gross' => $importerType->decimal(28, 8),
			],
			'u_yf_finvoice' => [
				'sum_total' => $importerType->decimal(28, 8),
				'sum_gross' => $importerType->decimal(28, 8),
			],
			'u_yf_finvoicecost' => [
				'sum_total' => $importerType->decimal(28, 8),
				'sum_gross' => $importerType->decimal(28, 8),
			],
			'u_yf_finvoiceproforma' => [
				'sum_total' => $importerType->decimal(28, 8),
				'sum_gross' => $importerType->decimal(28, 8),
			],
			'u_yf_ssalesprocesses' => [
				'estimated' => $importerType->decimal(28, 8),
				'actual_sale' => $importerType->decimal(28, 8),
			],
			'vtiger_account' => [
				'accountname' => $importerType->stringType(255)->notNull(),
				'annualrevenue' => $importerType->decimal(28, 8),
				'balance' => $importerType->decimal(28, 8),
			],
			'vtiger_leaddetails' => [
				'annualrevenue' => $importerType->decimal(28, 8),
			],
			'vtiger_lettersin' => [
				'cash_amount_on_delivery' => $importerType->decimal(28, 8),
			],
			'vtiger_ossemployees' => [
				'rbh' => $importerType->decimal(28, 8),
			],
			'vtiger_paymentsin' => [
				'paymentsvalue' => $importerType->decimal(28, 8),
			],
			'vtiger_paymentsout' => [
				'paymentsvalue' => $importerType->decimal(28, 8),
			],
			'vtiger_campaign' => [
				'expectedrevenue' => $importerType->decimal(28, 8),
				'budgetcost' => $importerType->decimal(28, 8),
				'actualcost' => $importerType->decimal(28, 8),
				'expectedroi' => $importerType->decimal(28, 8),
				'actualroi' => $importerType->decimal(28, 8),
			],
			'vtiger_ossmailview' => [
				'from_email' => $importerType->stringType(),
				'content' => $importerType->mediumText()->after('reply_to_email'),
				'id' => $importerType->integer(10)->unsigned()->null()->after('attachments_exist'),
				'mbox' => $importerType->stringType(100)->after('id'),
				'cid' => $importerType->char(64)->after('date'),
				'rc_user' => $importerType->integer(10)->unsigned()->after('mbox'),
				'attachments_exist' => $importerType->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->after('type'),
				'from_id' => $importerType->text()->null()->after('rc_user'),
				'to_id' => $importerType->text()->null()->after('from_id'),
				'date' => $importerType->dateTime()->after('content'),
				'uid' => $importerType->stringType(255)->after('cid'),
			],
		];
		$schema = $importerType->db->getSchema();
		$dbCommand = $importerType->db->createCommand();
		foreach ($data as $tableName => $columns) {
			if (!$importerType->db->isTableExists($tableName)) {
				$this->log("[ERROR] table does not exist: $tableName");
				continue;
			}
			$tableSchema = $schema->getTableSchema($tableName);
			foreach ($columns as $columnName => $column) {
				if (isset($tableSchema->columns[$columnName])) {
					try {
						$dbCommand->alterColumn($tableName, $columnName, $column)->execute();
					} catch (\Throwable $e) {
						$this->log("[ERROR] Error(2) query error: {$e->__toString()}");
					}
				} else {
					$this->log("[WARNING] column does not exist: $tableName - $columnName ");
				}
			}
		}
		$this->importer->refreshSchema();
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	public function lastUpdateScheme()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . "\t\t|\t" . date('H:i:s'));

		$base = (new \App\Db\Importers\Base());
		$base->foreignKey = [
			//['u_yf_modtracker_inv_id_fk', 'u_yf_modtracker_inv', 'id', 'vtiger_modtracker_basic', 'id', 'CASCADE', null]
		];
		$this->importer->updateForeignKey($base);

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
		return true;
	}

	public function stopProcess()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . "\t\t|\t" . date('H:i:s'), true);
		try {
			$dbCommand = \App\Db::getInstance()->createCommand();
			$dbCommand->insert('yetiforce_updates', [
				'user' => \Users_Record_Model::getCurrentUserModel()->get('user_name'),
				'name' => (string) $this->modulenode->label,
				'from_version' => (string) $this->modulenode->from_version,
				'to_version' => (string) $this->modulenode->to_version,
				'result' => true,
				'time' => date('Y-m-d H:i:s')
			])->execute();
			$dbCommand->update('vtiger_version', ['current_version' => (string) $this->modulenode->to_version])->execute();
			\vtlib\Functions::recurseDelete('cache/updates');
			\vtlib\Functions::recurseDelete('cache/templates_c');

			\App\Cache::clear();
			\App\Cache::clearOpcache();
			clearstatcache();
		} catch (\Throwable $ex) {
			file_put_contents('cache/logs/update.log', $ex->__toString(), FILE_APPEND);
		}
		$logs = '';
		if ($this->error) {
			$logs = '<blockquote style="font-size: 14px;background: #EDEDED;padding: 10px;white-space: pre-line;margin-top: 10px;">' . implode(PHP_EOL, $this->error) . '</blockquote>';
		}

		file_put_contents('cache/logs/update.log', ob_get_contents(), FILE_APPEND);
		ob_end_clean();
		echo '<div class="modal in" style="display: block;overflow-y: auto;top: 30px;"><div class="modal-dialog" style="max-width: 80%;"><div class="modal-content" style="-webkit-box-shadow: inset 2px 2px 14px 1px rgba(0,0,0,0.75);-moz-box-shadow: inset 2px 2px 14px 1px rgba(0,0,0,0.75);box-shadow: inset 2px 2px 14px 1px rgba(0,0,0,0.75);-webkit-box-shadow: 2px 2px 14px 1px rgba(0,0,0,0.75);
    -moz-box-shadow: 2px 2px 14px 1px rgba(0,0,0,0.75);box-shadow: 2px 2px 14px 1px rgba(0,0,0,0.75);"><div class="modal-header">
		<h1 class="modal-title"><span class="fas fa-skull-crossbones mr-2"></span>' . \App\Language::translate('LBL__UPDATING_MODULE', 'Settings:ModuleManager') . '</h1>
		</div><div class="modal-body" style="font-size: 27px;">Some errors appeared during the update.
		We recommend verifying logs and updating the system once again.' . $logs . '<blockquote style="font-size: 14px;background: #EDEDED;padding: 10px;white-space: pre-line;">' . $this->importer->logs . '</blockquote></div><div class="modal-footer">
		<a class="btn btn-success" href="' . \App\Config::main('site_URL') . '"><span class="fas fa-home mr-2"></span>' . \App\Language::translate('LBL_HOME') . '<a>
		</div></div></div></div>';

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
		exit;
	}

	/**
	 * Generating the current configuration.
	 *
	 * @return void
	 */
	private function createConfigFiles()
	{
		$changeConfiguration = [
			'base' => [
				'sounds' => [
					'CHAT' => 'sound_2.mp3',
				]
			],
			'module' => [
				'ModTracker' => [
					'TEASER_TEXT_LENGTH' => 100,
				]
			]
		];
		\App\Cache::resetOpcache();
		\App\Config::set('module', 'OSSMail', 'root_directory', new \Nette\PhpGenerator\PhpLiteral('ROOT_DIRECTORY . DIRECTORY_SEPARATOR'));
		$configTemplates = 'config/ConfigTemplates.php';
		if (file_exists(__DIR__ . '/files/' . $configTemplates)) {
			copy(__DIR__ . '/files/' . $configTemplates, ROOT_DIRECTORY . '/' . $configTemplates);
			foreach (array_diff(\App\ConfigFile::TYPES, ['module', 'component']) as $type) {
				$configFile = new \App\ConfigFile($type);
				if (isset($changeConfiguration['base'][$type])) {
					foreach ($changeConfiguration['base'][$type] as $key => $value) {
						$configFile->set($key, $value);
					}
				}
				$configFile->create();
			}
		}
		foreach ((new \DirectoryIterator(__DIR__ . '/files/modules/')) as $item) {
			if ($item->isDir() && !$item->isDot()) {
				$moduleName = $item->getBasename();
				$configTemplates = "modules/{$moduleName}/ConfigTemplate.php";
				if (file_exists(__DIR__ . '/files/' . $configTemplates)) {
					copy(__DIR__ . '/files/' . $configTemplates, ROOT_DIRECTORY . '/' . $configTemplates);
					(new \App\ConfigFile('module', $moduleName))->create();
					$configFile = new \App\ConfigFile('module', $moduleName);
					if (isset($changeConfiguration['module'][$moduleName])) {
						foreach ($changeConfiguration['module'][$moduleName] as $key => $value) {
							$configFile->set($key, $value);
						}
					}
					$configFile->create();
				}
			}
		}
		$configTemplates = 'config/Components/ConfigTemplates.php';
		if (file_exists(__DIR__ . '/files/' . $configTemplates)) {
			copy(__DIR__ . '/files/' . $configTemplates, ROOT_DIRECTORY . '/' . $configTemplates);
			$componentsData = require_once ROOT_DIRECTORY . '/' . $configTemplates;
			foreach ($componentsData as $component => $data) {
				(new \App\ConfigFile('component', $component))->create();
			}
		}
	}

	/**
	 * Fixes the maximum value allowed for fields.
	 *
	 * @return int[]
	 */
	public function maximumFieldsLength(): array
	{
		$start = microtime(true);
		$this->log(__METHOD__ . "\t\t|\t" . date('H:i:s'));
		$typesNotSupported = ['datetime', 'date', 'year', 'timestamp', 'time'];
		$uiTypeNotSupported = [30];
		$typesMaxLength = [
			'tinytext' => 255,
			'text' => 65535,
			'mediumtext' => 16777215,
			'longtext' => 4294967295,
			'blob' => 65535,
			'mediumblob' => 16777215,
			'longblob' => 4294967295,
		];
		$uiTypeMaxLength = [
			120 => 65535,
			106 => '3,64',
			156 => '3',
		];
		$updated = $requiresVerification = $typeNotFound = $notSupported = 0;
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$schema = $db->getSchema();
		$query = (new \App\Db\Query())->select(['tablename', 'columnname', 'fieldid', 'maximumlength', 'uitype'])->from('vtiger_field');
		$dataReader = $query->createCommand()->query();
		while ($field = $dataReader->read()) {
			$column = $schema->getTableSchema($field['tablename'])->columns[$field['columnname']];
			preg_match('/^([\w\-]+)/i', $column->dbType, $matches);
			$type = $matches[1] ?? $column->type;
			if (\in_array($type, $typesNotSupported) || \in_array($field['uitype'], $uiTypeNotSupported)) {
				++$notSupported;
				continue;
			}
			if (isset($uiTypeMaxLength[$field['uitype']])) {
				$range = $uiTypeMaxLength[$field['uitype']];
			} elseif (isset($typesMaxLength[$type])) {
				$range = $typesMaxLength[$type];
			} else {
				switch ($type) {
					case 'binary':
					case 'string':
					case 'varchar':
					case 'varbinary':
						$range = (int) $column->size;
						break;
					case 'bigint':
					case 'mediumint':
						$this->log("[ERROR] Type not allowed: {$field['tablename']}.{$field['columnname']} |uitype: {$field['uitype']} |maximumlength: {$field['maximumlength']} |type:{$type}|{$column->type}|{$column->dbType}");
						\App\Log::error("Type not allowed: {$field['tablename']}.{$field['columnname']} |uitype: {$field['uitype']} |maximumlength: {$field['maximumlength']} |type:{$type}|{$column->type}|{$column->dbType}", __METHOD__);
						break;
					case 'integer':
					case 'int':
						if ($column->unsigned) {
							$range = '4294967295';
						} else {
							$range = '-2147483648,2147483647';
						}
						break;
					case 'smallint':
						if ($column->unsigned) {
							$range = '65535';
						} else {
							$range = '-32768,32767';
						}
						break;
					case 'tinyint':
						if ($column->unsigned) {
							$range = '255';
						} else {
							$range = '-128,127';
						}
						break;
					case 'decimal':
						$range = pow(10, ((int) $column->size) - ((int) $column->scale)) - 1;
						break;
					default:
						$range = false;
						break;
				}
			}
			$update = false;
			if (false === $range) {
				\App\Log::warning("[Warning] Type not found: {$field['tablename']}.{$field['columnname']} |uitype: {$field['uitype']} |maximumlength: {$field['maximumlength']} |type:{$type}|{$column->type}|{$column->dbType}", __METHOD__);
				++$typeNotFound;
			} elseif ($field['maximumlength'] != $range) {
				if (\in_array($field['uitype'], [1, 2, 7, 10, 16, 52, 56, 71, 72, 156, 300, 308, 317])) {
					$update = true;
				} else {
					$this->log("[Warning] Requires verification: {$field['tablename']}.{$field['columnname']} |uitype: {$field['uitype']} |maximumlength: {$field['maximumlength']} <> {$range} |type:{$type}|{$column->type}|{$column->dbType}");
					\App\Log::warning("Requires verification: {$field['tablename']}.{$field['columnname']} |uitype: {$field['uitype']} |maximumlength: {$field['maximumlength']} <> {$range} |type:{$type}|{$column->type}|{$column->dbType}", __METHOD__);
					++$requiresVerification;
				}
			}
			if ($update && false !== $range) {
				$dbCommand->update('vtiger_field', ['maximumlength' => $range], ['fieldid' => $field['fieldid']])->execute();
				++$updated;
				\App\Log::trace("Updated: {$field['tablename']}.{$field['columnname']} |maximumlength:  before:{$field['maximumlength']} after: $range |type:{$type}|{$column->type}|{$column->dbType}", __METHOD__);
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
		return ['NotSupported' => $notSupported, 'TypeNotFound' => $typeNotFound, 'RequiresVerification' => $requiresVerification, 'Updated' => $updated];
	}

	public function baseModuleTools(): int
	{
		$i = 0;
		$baseModuleTools = ['Import', 'Export', 'Merge', 'CreateCustomFilter',
			'DuplicateRecord', 'MassEdit', 'MassArchived', 'MassActive', 'MassDelete', 'MassAddComment', 'MassTransferOwnership',
			'ReadRecord', 'WorkflowTrigger', 'Dashboard', 'CreateDashboardFilter', 'QuickExportToExcel', 'ExportPdf', 'RecordMapping',
			'RecordMappingList', 'FavoriteRecords', 'WatchingRecords', 'WatchingModule', 'RemoveRelation', 'ReviewingUpdates', 'OpenRecord', 'CloseRecord', 'ReceivingMailNotifications', 'CreateDashboardChartFilter', 'TimeLineList', 'ArchiveRecord', 'ActiveRecord', 'MassTrash', 'MoveToTrash', 'RecordConventer', 'AutoAssignRecord', 'AssignToYourself'];
		$allUtility = $missing = $curentProfile2utility = [];
		foreach ((new \App\Db\Query())->from('vtiger_profile2utility')->all() as $row) {
			$curentProfile2utility[$row['profileid']][$row['tabid']][$row['activityid']] = true;
			$allUtility[$row['tabid']][$row['activityid']] = true;
		}
		$profileIds = \vtlib\Profile::getAllIds();
		$moduleIds = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['isentitytype' => 1])->column();
		$baseActionIds = array_map('App\Module::getActionId', $baseModuleTools);
		$exceptions = \Settings_ModuleManager_Module_Model::getBaseModuleToolsExceptions();
		foreach ($profileIds as $profileId) {
			foreach ($moduleIds as $moduleId) {
				foreach ($baseActionIds as $actionId) {
					if (!isset($curentProfile2utility[$profileId][$moduleId][$actionId])) {
						$missing["$profileId:$moduleId:$actionId"] = ['profileid' => $profileId, 'tabid' => $moduleId, 'activityid' => $actionId];
					}
				}
				if (isset($allUtility[$moduleId])) {
					foreach ($allUtility[$moduleId] as $actionId => $value) {
						if (!isset($curentProfile2utility[$profileId][$moduleId][$actionId])) {
							$missing["$profileId:$moduleId:$actionId"] = ['profileid' => $profileId, 'tabid' => $moduleId, 'activityid' => $actionId];
						}
					}
				}
			}
		}
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($missing as $row) {
			if (isset($exceptions[$row['tabid']]['allowed'])) {
				if (!isset($exceptions[$row['tabid']]['allowed'][$row['activityid']])) {
					continue;
				}
			} elseif (isset($exceptions[$row['tabid']]['notAllowed']) && (false === $exceptions[$row['tabid']]['notAllowed'] || isset($exceptions[$row['tabid']]['notAllowed'][$row['activityid']]))) {
				continue;
			}
			$dbCommand->insert('vtiger_profile2utility', ['profileid' => $row['profileid'], 'tabid' => $row['tabid'], 'activityid' => $row['activityid'], 'permission' => 1])->execute();
			++$i;
		}
		\Settings_SharingAccess_Module_Model::recalculateSharingRules();
		return $i;
	}
}
