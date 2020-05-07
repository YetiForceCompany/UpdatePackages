<?php
/**
 * YetiForceUpdate Class.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
// last check: 257d52bf9d8f9f733c01e3236bd7458952242574
/**
 * YetiForceUpdate Class.
 */
class YetiForceUpdate
{
	/**
	 * @var integer
	 */
	public $tabIdAccounts = \App\Module::getModuleId('Accounts');

	/**
	 * @var integer
	 */
	public $tabIdCalendar = \App\Module::getModuleId('Calendar');

	/**
	 * @var integer
	 */
	public $tabIdUsers = \App\Module::getModuleId('Users');

	/**
	 * @var integer
	 */
	public $tabIdSalesProcesses = \App\Module::getModuleId('SSalesProcesses');

	/**
	 * @var integer
	 */
	public $tabIdSQuotes = \App\Module::getModuleId('SQuotes');

	/**
	 * @var integer
	 */
	public $tabIdSingleOrders = \App\Module::getModuleId('SSingleOrders');

	/**
	 * @var integer
	 */
	public $tabIdSRecurringOrders = \App\Module::getModuleId('SRecurringOrders');

	/**
	 * @var integer
	 */
	public $tabIdPartners = \App\Module::getModuleId('Partners');

	/**
	 * @var integer
	 */
	public $tabIdCompetition = \App\Module::getModuleId('Competition');

	/**
	 * @var integer
	 */
	public $tabIdFInvoice = \App\Module::getModuleId('FInvoice');

	/**
	 * @var integer
	 */
	public $tabIdIStorages = \App\Module::getModuleId('IStorages');

	/**
	 * @var integer
	 */
	public $tabIdFInvoiceProforma = \App\Module::getModuleId('FInvoiceProforma');

	/**
	 * @var integer
	 */
	public $tabIdIGDN = \App\Module::getModuleId('IGDN');

	/**
	 * @var integer
	 */
	public $tabIdIGRN = \App\Module::getModuleId('IGRN');

	/**
	 * @var integer
	 */
	public $tabIdFCorectingInvoice = \App\Module::getModuleId('FCorectingInvoice');

	/**
	 * @var integer
	 */
	public $tabIdFInvoiceCost = \App\Module::getModuleId('FInvoiceCost');

	/**
	 * @var integer
	 */
	public $tabIdMultiCompanyt = \App\Module::getModuleId('MultiCompany');

	/**
	 * @var integer
	 */
	public $tabIdLocations = \App\Module::getModuleId('Locations');

	/**
	 * @var integer
	 */
	public $tabIdProductCategory = \App\Module::getModuleId('ProductCategory');

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
		// copy(__DIR__ . '/TempImporter.php', ROOT_DIRECTORY . '/app/Db/Importer.php');
		// copy(__DIR__ . '/files/app/Db/Fixer.php', ROOT_DIRECTORY . '/app/Db/Fixer.php');
		// copy(__DIR__ . '/files/app/Db/Importers/Base.php', ROOT_DIRECTORY . '/app/Db/Importers/Base.php');
		// copy(__DIR__ . '/files/vtlib/Vtiger/Block.php', ROOT_DIRECTORY . '/vtlib/Vtiger/Block.php');
		// copy(__DIR__ . '/files/modules/Vtiger/models/Field.php', ROOT_DIRECTORY . '/modules/Vtiger/models/Field.php');
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$this->importer = new \App\Db\Importer();
		try {
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->checkIntegrity(false);
			$this->importer->updateScheme();
			$this->importer->importData();
			$this->importer->postUpdate();
			$this->addModules(['ProductCategory']);
			$this->importer->refreshSchema();
			$this->importer->postUpdate();
			$this->importer->logs(false);
		} catch (\Throwable $ex) {
			$this->log($ex->getMessage() . '|' . $ex->getTraceAsString());
			$this->importer->logs(false);
			throw $ex;
		}
		$this->importer->refreshSchema();
		$this->importer->checkIntegrity(true);
		$this->data();
		$this->log(__METHOD__ . ' - ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
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
				if ('Locations' === $moduleName && ($tabId = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['name' => $moduleName])->scalar())) {
					\CRMEntity::getInstance('ModTracker')->enableTrackingForModule($tabId);
				}
			} else {
				$this->log('[INFO] Module exist: ') . $moduleName;
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}


	private function data()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		\App\Db\Updater::batchUpdate([
			['vtiger_cron_task', ['description' => 'Recommended frequency for Workflow is 5 mins', 'lase_error' => NULL], ['name' => 'LBL_WORKFLOW']],
			['vtiger_cron_task', ['description' => '', 'lase_error' => NULL], ['name' => 'LBL_SCHEDULED_IMPORT']],
			['vtiger_cron_task', ['description' => 'Recommended frequency for MailScanner is 5 mins', 'lase_error' => NULL], ['name' => 'LBL_MAIL_SCANNER_ACTION']],
			['vtiger_cron_task', ['frequency' => 60, 'description' => NULL, 'lase_error' => NULL], ['name' => 'LBL_BATCH_METHODS']],
			['vtiger_cron_task', ['lase_error' => NULL], ['or',
				'name' => ['LBL_ADDRESS_BOOK', 'LBL_SEND_REMINDER', 'LBL_CURRENCY_UPDATE', 'LBL_MAIL_SCANNER_VERIFICATION', 'LBL_MAIL_SCANNER_BIND', 'LBL_ACTIVITY_STATE', 'LBL_MULTI_REFERENCE_VALUE', 'LBL_CRMACTIVITY_DAYS', 'LBL_ASSETS_RENEWAL', 'LBL_SEND_NOTIFICATIONS', 'LBL_RECORD_LABEL_UPDATER', 'LBL_PRIVILEGES_UPDATER', 'LBL_UPDATER_COORDINATES', 'LBL_UPDATER_RECORDS_COORDINATES', 'LBL_MARK_RECORDS_AS_REVIEWED', 'LBL_SESSION_CLEANER', 'LBL_ARCHIVE_OLD_RECORDS', 'LBL_GET_SOCIAL_MEDIA_MESSAGES']
			]],
			['vtiger_cron_task', ['description' => NULL, 'lase_error' => NULL], ['or',
				'name' => ['LBL_MAILER', 'LBL_BROWSING_HISTORY', 'LBL_BATCH_PROCESSES', 'LBL_CARD_DAV', 'LBL_CAL_DAV', 'LBL_MULTI_REFERENCE_VALUE', 'LBL_CACHE', 'LBL_NEVER_ENDING_RECURRING_EVENTS', 'LBL_CLEAR_FILE_UPLOAD_TEMP', 'LBL_SMSNOTIFIER', 'LBK_SYSTEM_WARNINGS']
			]],
			['vtiger_eventhandlers', ['priority' => 5], ['handler_class' => 'Vtiger_Workflow_Handler', 'event_name' => ['EntityAfterDelete', 'EntityAfterSave', 'EntityChangeState']]],
			['vtiger_eventhandlers', ['include_modules' => 'Contacts,Accounts'], ['handler_class' => 'Contacts_DuplicateEmail_Handler', 'event_name' => 'EditViewPreSave']],
			['vtiger_field', ['uitype' => 16, 'fieldname' => 'payment_methods', 'fieldlabel' => 'FL_PAYMENTS_METHOD'], ['or',
				['tabid' => $this->tabIdFCorectingInvoice, 'columnname' => 'fcorectinginvoice_formpayment'],
				['tabid' => $this->tabIdFInvoice, 'columnname' => 'finvoice_formpayment'],
				['tabid' => $this->tabIdFInvoiceCost, 'columnname' => 'finvoicecost_formpayment'],
				['tabid' => $this->tabIdFInvoiceProforma, 'columnname' => 'finvoiceproforma_formpayment']
			]],
			['vtiger_field', ['generatedtype' => 1], ['or',
				['tabid' => $this->tabIdCompetition, 'columnname' => 'parent_id'],
				['tabid' => \App\Module::getModuleId('EmailTemplates'), 'columnname' => 'smtp_id'],
				['tabid' => \App\Module::getModuleId('IncidentRegister'), 'columnname' => 'name'],
				['tabid' => $this->tabIdMultiCompanyt, 'columnname' => 'logo'],
				['tabid' => $this->tabIdMultiCompanyt, 'columnname' => 'website'],
				['tabid' => \App\Module::getModuleId('Faq'), 'columnname' => 'subject']
			]],
			['vtiger_field', ['maximumlength' => '50'], ['or',
				['tabid' => $this->tabIdCompetition, 'columnname' => 'vat_id'],
				['tabid' => $this->tabIdPartners, 'columnname' => 'vat_id'],
				['tabid' => $this->tabIdAccounts, 'columnname' => 'vat_id'],
				['tabid' => \App\Module::getModuleId('Vendors'), 'columnname' => 'vat_id']
			]],
			['vtiger_field', ['uitype' => 16], ['tabid' => $this->tabIdFInvoiceCost, 'columnname' => 'finvoicecost_paymentstatus']],
			['vtiger_field', ['uitype' => 12], ['tabid' => $this->tabIdAccounts, 'columnname' => 'accountname']],
			['vtiger_field', ['displaytype' => 2], [
				'tabid' => [$this->tabIdAccounts, $this->tabIdSalesProcesses, $this->tabIdSQuotes, $this->tabIdSingleOrders, $this->tabIdSRecurringOrders, $this->tabIdPartners, $this->tabIdCompetition, \App\Module::getModuleId('Contacts'), \App\Module::getModuleId('Leads'), \App\Module::getModuleId('HelpDesk'), \App\Module::getModuleId('Vendors'), \App\Module::getModuleId('Campaigns'), \App\Module::getModuleId('ServiceContracts'), \App\Module::getModuleId('Project'), \App\Module::getModuleId('OSSEmployees'), \App\Module::getModuleId('SQuoteEnquiries'), \App\Module::getModuleId('SRequirementsCards'), \App\Module::getModuleId('SCalculations'), \App\Module::getModuleId('SVendorEnquiries')], 'columnname' => 'crmactivity'
			]],
			['vtiger_field', ['typeofdata' => 'I~O'], ['tabid' => App\Module::getModuleId('OSSMailView'), 'columnname' => 'rc_user']],
			['vtiger_field', ['fieldlabel' => 'FL_EAN_SKU', 'maximumlength' => '64'], ['tabid' => \App\Module::getModuleId('Products'), 'columnname' => 'ean']],
			['vtiger_field', ['displaytype' => 2], ['tabid' => \App\Module::getModuleId('HelpDesk'), 'columnname' => 'sum_time']],
			['vtiger_field', ['displaytype' => 2], ['tabid' => \App\Module::getModuleId('HelpDesk'), 'columnname' => 'sum_time_subordinate']],
			['vtiger_field', ['helpinfo' => 'Edit,Detail'], [
				'tabid' =>  $this->tabIdUsers, 'columnname' => ['roleid', 'accesskey', 'activity_view', 'authy_methods', 'authy_secret_totp', 'auto_assign', 'available', 'confirm_password', 'currency_decimal_separator', 'currency_grouping_pattern', 'currency_grouping_separator', 'currency_id', 'currency_symbol_placement', 'date_format', 'date_password_change', 'dayoftheweek', 'defaultactivitytype', 'defaulteventstatus', 'default_record_view', 'default_search_module', 'email1', 'emailoptout', 'end_hour', 'first_name', 'force_password_change', 'hour_format', 'imagename', 'internal_mailer', 'is_admin', 'is_owner', 'language', 'last_name', 'leftpanelhide', 'login_method', 'mail_scanner_actions', 'mail_scanner_fields', 'no_of_currency_decimals', 'othereventduration', 'phone_crm_extension', 'phone_crm_extension_extra', 'primary_phone', 'primary_phone_extra', 'records_limit', 'reminder_interval', 'reports_to_id', 'rowheight', 'start_hour', 'status', 'sync_caldav', 'sync_carddav', 'theme', 'time_zone', 'truncate_trailing_zeros', 'user_name', 'user_password', 'view_date_format', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek', 'dayoftheweek'],
			]],
			['vtiger_field', ['fieldlabel' => 'Description', 'helpinfo' => 'Edit,Detail'], ['tabid' => $this->tabIdUsers, 'columnname' => 'description']],
			['vtiger_settings_blocks', ['label' => 'LBL_MENU_DASHBOARD', 'sequence' => 1], ['label' => 'LBL_MENU_SUMMARRY']],
			['vtiger_settings_field', ['sequence' => 14], ['name' => 'LBL_SOCIAL_MEDIA']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Help&view=Index'], ['name' => 'LBL_GITHUB']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Logs&view=SystemWarnings'], ['name' => 'LBL_SYSTEM_WARNINGS']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=YetiForce&view=Vulnerabilities'], ['name' => 'LBL_VULNERABILITIES']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Map&view=Config'], ['name' => 'LBL_MAP']],
		]);
		\App\Db\Updater::batchInsert([]);
		\App\Db\Updater::batchDelete([]);
		$this->blocks();
		$this->addFields();
		$this->addSettingFields();
		$this->addWorflows();
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	/**
	 * PreUpdateScheme.
	 */
	public function ddddd()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$address = [
			'addresslevel8a' => [1, 255],
			'addresslevel8b' => [1, 255],
			'addresslevel8c' => [1, 255],
			'localnumbera' => [2, 50],
			'localnumberb' => [2, 50],
			'localnumberc' => [2, 50],
			'addresslevel5a' => [3, 255],
			'addresslevel5b' => [3, 255],
			'addresslevel5c' => [3, 255],
			'buildingnumbera' => [4, 255],
			'buildingnumberb' => [4, 255],
			'buildingnumberc' => [4, 255],
			'addresslevel7a' => [5, 255],
			'addresslevel7b' => [5, 255],
			'addresslevel7c' => [5, 255],
			'addresslevel6a' => [6, 255],
			'addresslevel6b' => [6, 255],
			'addresslevel6c' => [6, 255],
			'addresslevel2a' => [7, 255],
			'addresslevel2b' => [7, 255],
			'addresslevel2c' => [7, 255],
			'addresslevel4a' => [8, 255],
			'addresslevel4b' => [8, 255],
			'addresslevel4c' => [8, 255],
			'addresslevel1a' => [9, 255],
			'addresslevel1b' => [9, 255],
			'addresslevel1c' => [9, 255],
			'addresslevel3a' => [10, 255],
			'addresslevel3b' => [10, 255],
			'addresslevel3c' => [10, 255],
			'poboxa' => [11, 50],
			'poboxb' => [11, 50],
			'poboxc' => [11, 50],
			'first_name_a' => [12, 255],
			'first_name_b' => [12, 255],
			'first_name_c' => [12, 255],
			'last_name_a' => [13, 255],
			'last_name_b' => [13, 255],
			'last_name_c' => [13, 255],
			'company_name_a' => [14, 255],
			'company_name_b' => [14, 255],
			'company_name_c' => [14, 255],
			'vat_id_a' => [15, 50],
			'vat_id_b' => [15, 50],
			'vat_id_c' => [15, 50],
			'email_a' => [16, 100],
			'email_b' => [16, 100],
			'email_c' => [16, 100],
			'phone_a' => [17, 100],
			'phone_b' => [17, 100],
			'phone_c' => [17, 100],
		];
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($address as $fieldName => $row) {
			$dbCommand->update('vtiger_field', ['sequence' => $row[0], 'maximumlength' => $row[1]], ['fieldname' => $fieldName])->execute();
			$all = (new \App\Db\Query())->select(['tablename', 'columnname'])->from('vtiger_field')->where(['fieldname' => $fieldName])->all();
			foreach ($all as $row2) {
				$dbCommand->alterColumn($row2['tablename'], $row2['columnname'], "varchar({$row[1]})")->execute();
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	/**
	 * Blocks.
	 *
	 */
	private function blocks()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$this->updateBlocks([
			['type' => 'update', 'module' => 'SSingleOrders', 'db' => ['LBL_ADDRESS_BILLING', 6, 0, 0, 0, 0, 0, 2, 0, ''], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'update', 'module' => 'FInvoice', 'db' => ['LBL_ADDRESS_BILLING', 3, 0, 0, 0, 0, 0, 2, 0, ''], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'update', 'module' => 'FInvoiceProforma', 'db' => ['LBL_ADDRESS_BILLING', 3, 0, 0, 0, 0, 0, 2, 0, ''], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'add', 'module' => 'SSingleOrders', 'db' => ['LBL_ADDRESS_SHIPPING', 4, 0, 0, 0, 0, 0, 2, 0, '']],
			['type' => 'add', 'module' => 'FInvoice', 'db' => ['LBL_ADDRESS_SHIPPING', 8, 0, 0, 0, 0, 0, 2, 0, '']],
			['type' => 'add', 'module' => 'FInvoiceProforma', 'db' => ['LBL_ADDRESS_SHIPPING', 5, 0, 0, 0, 0, 0, 2, 0, '']],
			['type' => 'add', 'module' => 'ProductCategory', 'db' => ['LBL_BASIC_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, '']],
			['type' => 'add', 'module' => 'ProductCategory', 'db' => ['LBL_CUSTOM_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, '']],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_CONTACT_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, '']],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_CONFIGURATION_WORKING_TIME', 10, 0, 0, 0, 0, 0, 2, 0, '']],
		], ['blocklabel', 'sequence', 'show_title', 'visible', 'create_view', 'edit_view', 'detail_view', 'display_status', 'iscustom', 'icon']);

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	/**
	 * Update blocks.
	 *
	 * @param array $blocks
	 * @param array $blockNames
	 */
	private function updateBlocks(array $blocks, array $blockNames)
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		foreach ($blocks as $block) {
			$data = array_combine($blockNames, $block['db']);
			$data['tabid'] = \App\Module::getModuleId($block['module']);
			if ('add' === $block['type']) {
				$blockInstance = new \vtlib\Block();
			} else {
				if($block['oldLabel']){
					$blockInstance = \vtlib\Block::getInstance($block['oldLabel'], $data['tabid']);
				}else{
					$blockInstance = \vtlib\Block::getInstance($data['blocklabel'], $data['tabid']);
				}

			}
			if (!$blockInstance) {
				$this->log("[Warning] block does not exist | label:{$data['blocklabel']}, module: {$block['module']}");
				continue;
			}
			$createCommand = \App\Db::getInstance()->createCommand();
			if ('remove' === $block['type']) {
				$blockInstance->delete(false);
			} elseif ('update' === $block['type']) {
				$createCommand->update('vtiger_blocks', $data, ['blockid' => $blockInstance->id])->execute();
			} elseif ('add' === $block['type'] && !(\vtlib\Block::getInstance($data['blocklabel'], $data['tabid']))) {
				$createCommand->insert('vtiger_blocks', $data)->execute();
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	/**
	 * Add fields.
	 *
	 * @param mixed $fields
	 */
	public function addFields($fields = [])
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$importerType = new \App\Db\Importers\Base();
		if (empty($fields)) {
			$fields = [
				[$this->tabIdUsers, 3039,'secondary_email','vtiger_users',2,13,'secondary_email','FL_SECONDARY_EMAIL',0,2,'','100',2,468,1,'E~O',1,0,'BAS',1,'Edit,Detail',0,'',NULL,0,0,0,0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_USERLOGIN_ROLE', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_USERLOGIN_ROLE']],
				[$this->tabIdFInvoiceProforma, 3021, 'addresslevel1b', 'u_yf_finvoiceproforma_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3019, 'addresslevel2b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3022, 'addresslevel3b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3020, 'addresslevel4b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3015, 'addresslevel5b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3018, 'addresslevel6b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3017, 'addresslevel7b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3013, 'addresslevel8b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3024, 'first_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoiceProforma, 3025, 'first_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3026, 'last_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoiceProforma, 3027, 'last_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3014, 'localnumberb', 'u_yf_finvoiceproforma_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3016, 'buildingnumberb', 'u_yf_finvoiceproforma_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3028, 'company_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoiceProforma, 3029, 'company_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3032, 'email_a', 'u_yf_finvoiceproforma_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 325, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoiceProforma, 3033, 'email_b', 'u_yf_finvoiceproforma_address', 1, 13, 'email_b', 'FL_EMAIL', 0, 2, '', '100', 16, 467, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3034, 'phone_a', 'u_yf_finvoiceproforma_address', 1, 1, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoiceProforma, 3035, 'phone_b', 'u_yf_finvoiceproforma_address', 1, 1, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3023, 'poboxb', 'u_yf_finvoiceproforma_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoiceProforma, 3030, 'vat_id_a', 'u_yf_finvoiceproforma_address', 1, 1, 'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoiceProforma, 3031, 'vat_id_b', 'u_yf_finvoiceproforma_address', 1, 1, 'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 3004, 'addresslevel1b', 'u_yf_finvoice_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 3002, 'addresslevel2b', 'u_yf_finvoice_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 3005, 'addresslevel3b', 'u_yf_finvoice_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 3003, 'addresslevel4b', 'u_yf_finvoice_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2998, 'addresslevel5b', 'u_yf_finvoice_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 3001, 'addresslevel6b', 'u_yf_finvoice_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 3000, 'addresslevel7b', 'u_yf_finvoice_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2996, 'addresslevel8b', 'u_yf_finvoice_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2999, 'buildingnumberb', 'u_yf_finvoice_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2992, 'company_name_a', 'u_yf_finvoice_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoice, 3009, 'company_name_b', 'u_yf_finvoice_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2994, 'email_a', 'u_yf_finvoice_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 312, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2990, 'first_name_a', 'u_yf_finvoice_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoice, 3007, 'first_name_b', 'u_yf_finvoice_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2991, 'last_name_a', 'u_yf_finvoice_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoice, 3008, 'last_name_b', 'u_yf_finvoice_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2997, 'localnumberb', 'u_yf_finvoice_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2995, 'phone_a', 'u_yf_finvoice_address', 1, 1, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoice, 3012, 'phone_b', 'u_yf_finvoice_address', 1, 1, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 3006, 'poboxb', 'u_yf_finvoice_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdFInvoice, 2993, 'vat_id_a', 'u_yf_finvoice_address', 1, 1, 'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdFInvoice, 3010, 'vat_id_b', 'u_yf_finvoice_address', 1, 1, 'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[\App\Module::getModuleId('Occurrences'), 3037, 'meeting_utl', 'u_yf_occurrences', 1, 17, 'meeting_utl', 'FL_MEETING_UTL', 0, 2, '', '255', 11, 460, 1, 'V~O', 1, 0, 'BAS', 1, '', 1, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_BASIC_INFORMATION']],
				[\App\Module::getModuleId('SCalculations'), 2980, 'parent_id', 'u_yf_scalculations', 1, 10, 'parent_id', 'FL_PARENT_SCALCULATIONS', 0, 2, '', '4294967295', 11, 276, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SCALCULATIONS_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_SCALCULATIONS_INFORMATION']],
				[$this->tabIdSQuotes, 2979, 'parent_id', 'u_yf_squotes', 1, 10, 'parent_id', 'FL_PARENT_SQUOTES', 0, 2, '', '4294967295', 13, 280, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SQUOTES_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_SQUOTES_INFORMATION']],
				[$this->tabIdSalesProcesses, 2950, 'estimated_margin', 'u_yf_ssalesprocesses', 1, 71, 'estimated_margin', 'FL_ESTIMATED_MARGIN', 0, 2, '', '1.0E+20', 3, 320, 1, 'N~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => 'decimal(28,8)', 'blockLabel' => 'LBL_FINANCES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_FINANCES']],
				[$this->tabIdSalesProcesses, 2951, 'expected_margin', 'u_yf_ssalesprocesses', 1, 71, 'expected_margin', 'FL_EXPECTED_MARGIN', 0, 2, '', '1.0E+20', 4, 320, 2, 'N~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => 'decimal(28,8)', 'blockLabel' => 'LBL_FINANCES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_FINANCES']],
				[$this->tabIdSalesProcesses, 2952, 'expected_sale', 'u_yf_ssalesprocesses', 1, 71, 'expected_sale', 'FL_EXPECTED_SALE', 0, 2, '', '1.0E+20', 5, 320, 2, 'N~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => 'decimal(28,8)', 'blockLabel' => 'LBL_FINANCES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_FINANCES']],
				[$this->tabIdSingleOrders, 2978, 'parent_id', 'u_yf_ssingleorders', 1, 10, 'parent_id', 'FL_PARENT_SSINGLEORDERS', 0, 2, '', '4294967295', 19, 284, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_SSINGLEORDERS_INFORMATION']],
				[$this->tabIdSingleOrders, 2953, 'contactid', 'u_yf_ssingleorders', 1, 10, 'contactid', 'FL_CONTACT', 0, 2, '', '4294967295', 6, 284, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_SSINGLEORDERS_INFORMATION']],
				[$this->tabIdSingleOrders, 2970, 'addresslevel1b', 'u_yf_ssingleorders_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2969, 'addresslevel2b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2968, 'addresslevel3b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2967, 'addresslevel4b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2966, 'addresslevel5b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2965, 'addresslevel6b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2964, 'addresslevel7b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2963, 'addresslevel8b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2961, 'buildingnumberb', 'u_yf_ssingleorders_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2956, 'company_name_a', 'u_yf_ssingleorders_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdSingleOrders, 2974, 'company_name_b', 'u_yf_ssingleorders_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2959, 'email_a', 'u_yf_ssingleorders_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 295, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdSingleOrders, 2976, 'email_b', 'u_yf_ssingleorders_address', 1, 13, 'email_b', 'FL_EMAIL', 0, 2, '', '100', 16, 463, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2954, 'first_name_a', 'u_yf_ssingleorders_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdSingleOrders, 2972, 'first_name_b', 'u_yf_ssingleorders_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2955, 'last_name_a', 'u_yf_ssingleorders_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdSingleOrders, 2973, 'last_name_b', 'u_yf_ssingleorders_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 962, 'localnumberb', 'u_yf_ssingleorders_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2960, 'phone_a', 'u_yf_ssingleorders_address', 1, 1, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdSingleOrders, 2977, 'phone_b', 'u_yf_ssingleorders_address', 1, 1, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2971, 'poboxb', 'u_yf_ssingleorders_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdSingleOrders, 2957, 'vat_id_a', 'u_yf_ssingleorders_address', 1, 1,'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$this->tabIdSingleOrders, 2975, 'vat_id_b', 'u_yf_ssingleorders_address', 1, 1,'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$this->tabIdCalendar, 3036, 'meeting_utl', 'vtiger_activity', 1, 17, 'meeting_utl', 'FL_MEETING_UTL', 0, 2, '', '255', 28, 19, 1, 'V~O', 1, 0, 'BAS', 1, '', 1, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_TASK_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_TASK_INFORMATION']],
				[\App\Module::getModuleId('Contacts'), 3038, 'gender', 'vtiger_contactdetails', 1, 16, 'gender', 'FL_GENDER', 0, 2, '', '255', 31, 4, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_CONTACT_INFORMATION', 'picklistValues' => ['PLL_WOMAN', 'PLL_MAN'], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_CONTACT_INFORMATION']],
				[$this->tabIdProductCategory, 2989, 'active', 'u_yf_productcategory', 1, 56, 'active', 'FL_ACTIVE', 0, 2, '', '-128,127', 2, 464, 1, 'C~O', 2, 0, 'BAS', 1, '', 1, '', '', 0, 0, 0, 0,
					'type' => $importerType->tinyInteger(1), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_BASIC_INFORMATION']],
				[$this->tabIdProductCategory, 2981, 'category', 'u_yf_productcategory', 1, 2, 'category', 'FL_CATEGORY_NAME', 0, 2, '', '255', 1, 464, 1, 'V~M', 2, 0, 'BAS', 1, '', 1, '', '', 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_BASIC_INFORMATION']],
				[$this->tabIdProductCategory, 2988, 'parent_id', 'u_yf_productcategory', 1, 10, 'parent_id', 'FL_PARENT_CATEGORY', 0, 2, '', '4294967295', 3, 464, 1, 'V~O', 2, 0, 'BAS', 1, '', 1, '', '', 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'picklistValues' => [], 'relatedModules' => ['ProductCategory'], 'blockData' => ['label' => 'LBL_BASIC_INFORMATION']],
			];
		}

		foreach ($fields as $field) {
			$moduleId = $field[0];
			$moduleName = \App\Module::getModuleName($moduleId);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists) {
				$this->log("[INFO] Skip adding field. Module: {$moduleId}-{$moduleName}; field name: {$field[2]}, field exists: {$isExists}");
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
				\Vtiger_Module_Model::getInstance($moduleName)->addBlock($blockInstance);
				$blockId = $blockInstance->id;
				$blockInstance = \vtlib\Block::getInstance($blockId, $moduleId);
			}
			if (!$blockInstance &&
			!($blockInstance = reset(Vtiger_Module_Model::getInstance($moduleName)->getBlocks()))) {
				$this->log("[ERROR] No block found to create a field, you will need to create a field manually.
				Module: {$moduleName}, field name: {$field[6]}, field label: {$field[7]}");
				\App\Log::error("No block found ({$field['blockLabel']}) to create a field, you will need to create a field manually.
				Module: {$moduleName}, field name: {$field[6]}, field label: {$field[7]}");
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
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add setting fields.
	 */
	public function addSettingFields()
	{
		$start = microtime(true);
		$settingFields = [
			['value' => [5,'LBL_MAGENTO','fab fa-magento','LBL_MAGENTO_DESCRIPTION','index.php?parent=Settings&module=Magento&view=List',13,0,0,NULL], 'blockLabel' => 'LBL_INTEGRATION'],
			['value' => [4,'LBL_EVENT_HANDLER','mdi mdi-car-turbocharger','LBL_EVENT_HANDLER_DESC','index.php?parent=Settings&module=EventHandler&view=Index',13,0,0,NULL], 'blockLabel' => 'LBL_SYSTEM_TOOLS']
		];
		$columnName = ['blockid','name','iconpath','description','linkto','sequence','active','pinned','admin_access'];
		foreach($settingFields as $field){
			$blockId = (new \App\Db\Query())->select(['blockid'])
					->from('vtiger_settings_blocks')
					->where(['label' => $field['blockLabel']])->scalar();
			$isExistField = (new \App\Db\Query())->from('vtiger_settings_field')->where(['blockid' => $blockId, 'name' => $field['value'][1]])->exists();
			if (!$blockId || $isExistField) {
				$this->log("[INFO] Skip adding setting field. BlockId: {$blockId}; Setting field name: {$field['value'][1]}; field exists: {$isExistField}");
				continue;
			}
			$field['value'][0] = $blockId;
			$data = array_combine($columnName, $field['value']);
			\App\Db::getInstance()->createCommand()->insert('vtiger_settings_field', $data)->execute();

		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add worflows.
	 */
	public function addWorflows()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		require_once 'modules/com_vtiger_workflow/VTWorkflowManager.php';
		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		$workflowManager = new VTWorkflowManager();
		$taskManager = new VTTaskManager();
		$workflow[] = [73, 'SSingleOrders', 'IGDN', '[{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":0}]', 3, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL];
		$workflow[] = [75, 'SSingleOrders', 'IGDN', '[]', 5, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL];
		$workflow[] = [77, 'SSingleOrders', 'IGDN', '[{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":0}]', 4, NULL, NULL, 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL];
		$workflowTask[] = [142, 73, 'Tworzenie WZ', 'O:18:"VTCreateEntityTask":11:{s:18:"executeImmediately";b:1;s:8:"contents";N;s:10:"workflowId";i:73;s:7:"summary";s:12:"Tworzenie WZ";s:6:"active";b:0;s:7:"trigger";N;s:11:"entity_type";s:4:"IGDN";s:15:"reference_field";s:15:"ssingleordersid";s:19:"field_value_mapping";s:94:"[{"fieldname":"igdn_status","value":"PLL_ACCEPTED","valuetype":"rawtext","modulename":"IGDN"}]";s:12:"mappingPanel";s:1:"1";s:2:"id";i:142;}'];
		$workflowTask[] = [144, 75, 'Generowanie PZ', 'O:18:"VTCreateEntityTask":11:{s:18:"executeImmediately";b:1;s:8:"contents";N;s:10:"workflowId";i:75;s:7:"summary";s:14:"Generowanie PZ";s:6:"active";b:0;s:7:"trigger";N;s:11:"entity_type";s:4:"IGRN";s:15:"reference_field";s:0:"";s:19:"field_value_mapping";s:94:"[{"fieldname":"igrn_status","value":"PLL_ACCEPTED","valuetype":"rawtext","modulename":"IGRN"}]";s:12:"mappingPanel";s:1:"1";s:2:"id";i:144;}'];
		$workflowTask[] = [146, 77, 'Generowanie PZ', 'O:18:"VTCreateEntityTask":11:{s:18:"executeImmediately";b:1;s:8:"contents";N;s:10:"workflowId";i:77;s:7:"summary";s:14:"Generowanie PZ";s:6:"active";b:0;s:7:"trigger";N;s:11:"entity_type";s:4:"IGRN";s:15:"reference_field";s:0:"";s:19:"field_value_mapping";s:94:"[{"fieldname":"igrn_status","value":"PLL_ACCEPTED","valuetype":"rawtext","modulename":"IGRN"}]";s:12:"mappingPanel";s:1:"1";s:2:"id";i:146;}'];
		foreach ($workflow as $record) {
			try {
				$workflowId = (new \App\Db\Query())->select(['workflow_id'])
					->from('com_vtiger_workflows')
					->where(['module_name' => $record[1], 'summary' => $record[2], 'execution_condition' => $record[4]])->scalar();
				if (!$workflowId) {
					$newWorkflow = $workflowManager->newWorkFlow($record[1]);
					$newWorkflow->description = $record[2];
					$newWorkflow->test = $record[3];
					$newWorkflow->executionCondition = $record[4];
					$newWorkflow->defaultworkflow = $record[5];
					$newWorkflow->type = $record[6];
					$newWorkflow->filtersavedinnew = $record[7];
					$workflowManager->save($newWorkflow);
					$workflowId = $newWorkflow->id;
					$this->log("[INFO] Create workflow {$record[1]} {$record[2]}");
				}
				foreach ($workflowTask as $indexTask) {
					if ($indexTask[1] === $record[0] &&
						!(new \App\Db\Query())->select(['workflow_id'])->from('com_vtiger_workflowtasks')->where(['workflow_id' => $workflowId, 'summary' => $indexTask[2]])->exists()
						) {
						$task = $taskManager->unserializeTask($indexTask[3]);
						$task->id = '';
						$task->workflowId = $workflowId;
						$taskManager->saveTask($task);
						$this->log("[INFO] Create workflow task {$indexTask[1]} {$indexTask[2]}");
					}
				}
			} catch (\Throwable $e) {
				$this->log("[ERROR] {$e->getMessage()} in {$e->getTraceAsString()}");
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
	}
}
