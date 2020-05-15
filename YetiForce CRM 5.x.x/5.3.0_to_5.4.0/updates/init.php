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
// last check: 30fc2742b5b39729332a8a3e51fc9fa07e3a95ce
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
	 * @param mixed $moduleNode
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
		if (false !== stripos($message, '[ERROR]')) {
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
		copy(__DIR__ . '/files/app/Db/Importers/Base.php', ROOT_DIRECTORY . '/app/Db/Importers/Base.php');
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
		$this->importer = new \App\Db\Importer();
		try {
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->checkIntegrity(false);
			$this->importer->updateScheme();
			$this->importer->dropColumns([['vtiger_relatedlists_fields', 'fieldname']]);
			$this->importer->importData();
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
		\App\Db\Fixer::maximumFieldsLength();
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
				$this->log('[INFO] Module exist: ' . $moduleName);
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function data()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$tabIdAccounts = \App\Module::getModuleId('Accounts');
		$tabIdUsers = \App\Module::getModuleId('Users');
		$tabIdCompetition = \App\Module::getModuleId('Competition');
		$tabIdMultiCompanyt = \App\Module::getModuleId('MultiCompany');

		\App\Db\Updater::batchUpdate([
			['vtiger_cron_task', ['description' => 'Recommended frequency for Workflow is 5 mins'], ['name' => 'LBL_WORKFLOW']],
			['vtiger_cron_task', ['description' => ''], ['name' => 'LBL_SCHEDULED_IMPORT']],
			['vtiger_cron_task', ['description' => 'Recommended frequency for MailScanner is 5 mins'], ['name' => 'LBL_MAIL_SCANNER_ACTION']],
			['vtiger_cron_task', ['frequency' => 60, 'description' => null], ['name' => 'LBL_BATCH_METHODS']],
			['vtiger_cron_task', ['description' => null], [
				'name' => ['LBL_MAILER', 'LBL_BROWSING_HISTORY', 'LBL_BATCH_PROCESSES', 'LBL_CARD_DAV', 'LBL_CAL_DAV', 'LBL_MULTI_REFERENCE_VALUE', 'LBL_CACHE', 'LBL_NEVER_ENDING_RECURRING_EVENTS', 'LBL_CLEAR_FILE_UPLOAD_TEMP', 'LBL_SMSNOTIFIER', 'LBK_SYSTEM_WARNINGS']
			]],
			['vtiger_eventhandlers', ['priority' => 5], ['handler_class' => 'Vtiger_Workflow_Handler', 'event_name' => ['EntityAfterDelete', 'EntityAfterSave', 'EntityChangeState']]],
			['vtiger_eventhandlers', ['include_modules' => 'Contacts,Accounts'], ['handler_class' => 'Contacts_DuplicateEmail_Handler', 'event_name' => 'EditViewPreSave']],
			['vtiger_field', ['generatedtype' => 1], ['or',
				['tabid' => $tabIdCompetition, 'columnname' => 'parent_id'],
				['tabid' => \App\Module::getModuleId('EmailTemplates'), 'columnname' => 'smtp_id'],
				['tabid' => \App\Module::getModuleId('IncidentRegister'), 'columnname' => 'name'],
				['tabid' => $tabIdMultiCompanyt, 'columnname' => 'logo'],
				['tabid' => $tabIdMultiCompanyt, 'columnname' => 'website'],
				['tabid' => \App\Module::getModuleId('Faq'), 'columnname' => 'subject']
			]],
			['vtiger_field', ['fieldparams' => '{"hideLabel":["EventForm","QuickCreateAjax"]}'], ['tabid' => \App\Module::getModuleId('Calendar'), 'columnname' => 'activitytype']],
			['vtiger_field', ['uitype' => 12], ['tabid' => $tabIdAccounts, 'columnname' => 'accountname']],
			['vtiger_field', ['displaytype' => 2], ['tablename' => 'vtiger_entity_stats', 'fieldname' => ['crmactivity']]],
			['vtiger_field', ['typeofdata' => 'I~O'], ['tabid' => App\Module::getModuleId('OSSMailView'), 'columnname' => 'rc_user']],
			['vtiger_field', ['fieldlabel' => 'FL_EAN_SKU'], ['tabid' => \App\Module::getModuleId('Products'), 'columnname' => 'ean']],
			['vtiger_field', ['displaytype' => 2], ['tabid' => \App\Module::getModuleId('HelpDesk'), 'columnname' => ['sum_time', 'sum_time_subordinate']]],
			['vtiger_field', ['helpinfo' => 'Edit,Detail'], [
				'tabid' => $tabIdUsers, 'columnname' => ['roleid', 'accesskey', 'activity_view', 'authy_methods', 'authy_secret_totp', 'auto_assign', 'available', 'confirm_password', 'currency_decimal_separator', 'currency_grouping_pattern', 'currency_grouping_separator', 'currency_id', 'currency_symbol_placement', 'date_format', 'date_password_change', 'dayoftheweek', 'defaultactivitytype', 'defaulteventstatus', 'default_record_view', 'default_search_module', 'email1', 'emailoptout', 'end_hour', 'first_name', 'force_password_change', 'hour_format', 'imagename', 'internal_mailer', 'is_admin', 'is_owner', 'language', 'last_name', 'leftpanelhide', 'login_method', 'mail_scanner_actions', 'mail_scanner_fields', 'no_of_currency_decimals', 'othereventduration', 'phone_crm_extension', 'phone_crm_extension_extra', 'primary_phone', 'primary_phone_extra', 'records_limit', 'reminder_interval', 'reports_to_id', 'rowheight', 'start_hour', 'status', 'sync_caldav', 'sync_carddav', 'theme', 'time_zone', 'truncate_trailing_zeros', 'user_name', 'user_password', 'view_date_format']
			]],
			['vtiger_field', ['fieldlabel' => 'Description', 'helpinfo' => 'Edit,Detail'], ['tabid' => $tabIdUsers, 'columnname' => 'description']],
			['vtiger_settings_blocks', ['label' => 'LBL_MENU_DASHBOARD', 'sequence' => 1], ['label' => 'LBL_MENU_SUMMARRY']],
			['vtiger_settings_field', ['sequence' => 14], ['name' => 'LBL_SOCIAL_MEDIA']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Help&view=Index'], ['name' => 'LBL_GITHUB']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Logs&view=SystemWarnings'], ['name' => 'LBL_SYSTEM_WARNINGS']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=YetiForce&view=Vulnerabilities'], ['name' => 'LBL_VULNERABILITIES']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Map&view=Config'], ['name' => 'LBL_MAP']],
		]);
		\App\Db\Updater::batchInsert([
			['u_yf_countries',	['name' => 'South Sudan', 'code' => 'SS', 'status' => 0, 'sortorderid' => 210, 'phone' => 0, 'uitype' => 0], ['code' => 'SS']],
			['u_yf_countries',	['name' => 'Bonaire, Sint Eustatius and Saba', 'code' => 'BQ', 'status' => 0, 'sortorderid' => 27, 'phone' => 0, 'uitype' => 0], ['code' => 'BQ']],
			['u_yf_countries',	['name' => 'Curaçao', 'code' => 'CW', 'status' => 0, 'sortorderid' => 57, 'phone' => 0, 'uitype' => 0], ['code' => 'CW']],
			['u_yf_countries',	['name' => 'Guernesey', 'code' => 'GG', 'status' => 0, 'sortorderid' => 92, 'phone' => 0, 'uitype' => 0], ['code' => 'GG']],
			['u_yf_countries',	['name' => 'Isle of Man', 'code' => 'IM', 'status' => 0, 'sortorderid' => 108, 'phone' => 0, 'uitype' => 0], ['code' => 'IM']],
			['u_yf_countries',	['name' => 'Jersey', 'code' => 'JE', 'status' => 0, 'sortorderid' => 113, 'phone' => 0, 'uitype' => 0], ['code' => 'JE']],
			['u_yf_countries',	['name' => 'Saint Barthélemy', 'code' => 'BL', 'status' => 0, 'sortorderid' => 187, 'phone' => 0, 'uitype' => 0], ['code' => 'BL']],
			['u_yf_countries',	['name' => 'Saint Martin (French part)', 'code' => 'MF', 'status' => 0, 'sortorderid' => 191, 'phone' => 0, 'uitype' => 0], ['code' => 'MF']],
			['u_yf_countries',	['name' => 'Sint Maarten (Dutch part)', 'code' => 'SX', 'status' => 0, 'sortorderid' => 203, 'phone' => 0, 'uitype' => 0], ['code' => 'SX']],
			['u_yf_countries',	['name' => 'Timor-Leste', 'code' => 'TL', 'status' => 0, 'sortorderid' => 224, 'phone' => 0, 'uitype' => 0], ['code' => 'TL']],
			['vtiger_cron_task',
				['name' => 'LBL_MAGENTO', 'handler_class' => 'Vtiger_Magento_Cron', 'frequency' => 60, 'status' => 0, 'module' => 'Vtiger', 'sequence' => 34],
				['handler_class' => 'Vtiger_Magento_Cron']
			],
			['vtiger_settings_blocks',
				['label' => 'LBL_MARKETPLACE_YETIFORCE', 'sequence' => 0, 'icon' => 'yfi yfi-shop', 'type' => 1, 'linkto' => 'index.php?module=YetiForce&parent=Settings&view=Shop'],
				['label' => 'LBL_MARKETPLACE_YETIFORCE']
			],
			['vtiger_ssingleorders_source',
				['ssingleorders_source' => 'PLL_MAGENTO', 'sortorderid' => 5, 'presence' => 1],
				['ssingleorders_source' => 'PLL_MAGENTO']
			],
			['vtiger_links',
				['tabid' => 0, 'linktype' => 'EDIT_VIEW_RECORD_COLLECTOR', 'linklabel' => 'GUS', 'linkurl' => 'App\RecordCollectors\Gus'],
				['tabid' => 0, 'linkurl' => 'App\RecordCollectors\Gus']
			]
		]);

		if (!(new \App\Db\Query())->from('s_yf_record_quick_changer')->exists()) {
			\App\Db\Updater::batchInsert([
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('SSingleOrders'), 'conditions' => '{"ssingleorders_status":"PLL_ACCEPTED"}', 'values' => '{"ssingleorders_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('IGDN'), 'conditions' => '{"igdn_status":"PLL_ACCEPTED"}', 'values' => '{"igdn_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('IIDN'), 'conditions' => '{"iidn_status":"PLL_ACCEPTED"}', 'values' => '{"iidn_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('IGIN'), 'conditions' => '{"igin_status":"PLL_ACCEPTED"}', 'values' => '{"igin_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('IPreOrder'), 'conditions' => '{"ipreorder_status":"PLL_ACCEPTED"}', 'values' => '{"ipreorder_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('ISTDN'), 'conditions' => '{"istdn_status":"PLL_ACCEPTED"}', 'values' => '{"istdn_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('ISTRN'), 'conditions' => '{"istrn_status":"PLL_ACCEPTED"}', 'values' => '{"istrn_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('IGRNC'), 'conditions' => '{"igrnc_status":"PLL_ACCEPTED"}', 'values' => '{"igrnc_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('IGDNC'), 'conditions' => '{"igdnc_status":"PLL_ACCEPTED"}', 'values' => '{"igdnc_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']],
				['s_yf_record_quick_changer', ['tabid' => \App\Module::getModuleId('IGRN'), 'conditions' => '{"igrn_status":"PLL_ACCEPTED"}', 'values' => '{"igrn_status":"PLL_CANCELLED"}', 'btn_name' => 'BTN_CANCEL', 'class' => 'btn-outline-danger', 'icon' => 'mdi mdi-cancel']]
			]);
		}

		App\EventHandler::registerHandler('EditViewPreSave', 'Accounts_DuplicateVatId_Handler', 'Accounts', '', 5, true, \App\Module::getModuleId('Accounts'));
		App\EventHandler::registerHandler('EditViewPreSave', 'Products_DuplicateEan_Handler', 'Products', '', 5, true, \App\Module::getModuleId('Products'));
		App\EventHandler::registerHandler('EntityBeforeSave', 'SSalesProcesses_Finances_Handler', 'SSalesProcesses', '', 5, true, \App\Module::getModuleId('SSalesProcesses'));
		App\EventHandler::registerHandler('IStoragesAfterUpdateStock', 'IStorages_RecalculateStockHandler_Handler', '', '', 5, false, 0);
		App\EventHandler::registerHandler('EditViewPreSave', 'IGDNC_IgdnExist_Handler', 'IGDNC', '', 5, true, \App\Module::getModuleId('IGDNC'));
		App\EventHandler::registerHandler('EditViewPreSave', 'IGRNC_IgrnExist_Handler', 'IGRNC', '', 5, true, \App\Module::getModuleId('IGRNC'));
		App\EventHandler::registerHandler('EntityBeforeSave', 'Vtiger_Meetings_Handler', 'Calendar,Occurrences', '', 5, true);

		$this->blocks();
		$this->addFields();
		$this->updateAdressFields();
		$this->addSettingFields();
		$this->addWorflows();
		$this->syncPicklist();
		$this->dropColumns();
		$this->setRelations();
		$this->updateCurrencies();
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function updateCurrencies()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$currencies = [
			['South Sudanese pound', 'SSP', 'SS£'],
			['Afghani', 'AFN', 'Af'],
			['Armenian Dram', 'AMD', 'Դ'],
			['Kwanza', 'AOA', 'Kz'],
			['Taka', 'BDT', '৳'],
			['Burundi Franc', 'BIF', '₣'],
			['Boliviano Mvdol', 'BOV', '$b'],
			['Ngultrum', 'BTN', 'Nu'],
			['Belarussian Ruble', 'BYN', 'p.'],
			['Congolese Franc', 'CDF', 'FC'],
			['Unidad de Fomento', 'CLF', '$'],
			['Unidad de Valor Real', 'COU', '$'],
			['Peso Convertible', 'CUC', 'CUC$'],
			['Cabo Verde Escudo', 'CVE', '$'],
			['Djibouti Franc', 'DJF', 'Fdj'],
			['Algerian Dinar', 'DZD', 'دج'],
			['Nakfa', 'ERN', 'Nkf'],
			['Ethiopian Birr', 'ETB', 'Br'],
			['Lari', 'GEL', '₾'],
			['Dalasi', 'GMD', 'D'],
			['Guinean Franc', 'GNF', 'FG'],
			['Riel', 'KHR', '៛'],
			['Comorian Franc', 'KMF', 'CF'],
			['Loti', 'LSL', 'L'],
			['Moldovan Leu', 'MDL', 'L'],
			['Kyat', 'MMK', 'K'],
			['Pataca', 'MOP', '	MOP$'],
			['Ouguiya', 'MRU', 'UM'],
			['Kina', 'PGK', 'K'],
			['Rwanda Franc', 'RWF', 'R₣'],
			['Leone', 'SLL', 'Le'],
			['Dobra', 'STN', 'Db'],
			['Lilangeni', 'SZL', 'L'],
			['Somoni', 'TJS', 'SM'],
			['Turkmenistan New Manat', 'TMT', 'm'],
			['Tunisian Dinar', 'TND', 'د.ت'],
			['Pa’anga', 'TOP', 'T$'],
			['Bolívar Soberano', 'VES', 'Bs. S.'],
			['Vatu', 'VUV', 'VT'],
			['Tala', 'WST', 'WS$'],
			['Zambian Kwacha', 'ZMW', 'ZK'],
			['Ghana, Cedis', 'GHS', '¢']
		];

		foreach ($currencies as $currency) {
			if (!(new \App\db\Query())->from('vtiger_currencies')->where(['currency_name' => $currency[0]])->exists()) {
				$dbCommand->insert('vtiger_currencies', array_combine(['currency_name', 'currency_code', 'currency_symbol'], $currency))->execute();
			}
		}
	}

	/**
	 * PreUpdateScheme.
	 */
	public function updateAdressFields()
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
	 */
	private function blocks()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$this->updateBlocks([
			['type' => 'update', 'module' => 'SSingleOrders', 'db' => ['LBL_ADDRESS_BILLING', null, 0, 0, 0, 0, 0, 1, 0, null], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'update', 'module' => 'FInvoice', 'db' => ['LBL_ADDRESS_BILLING', null, 0, 0, 0, 0, 0, 1, 0, null], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'update', 'module' => 'FInvoiceProforma', 'db' => ['LBL_ADDRESS_BILLING', null, 0, 0, 0, 0, 0, 1, 0, null], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'add', 'module' => 'SSingleOrders', 'db' => ['LBL_ADDRESS_SHIPPING', 4, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'FInvoice', 'db' => ['LBL_ADDRESS_SHIPPING', 8, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'FInvoiceProforma', 'db' => ['LBL_ADDRESS_SHIPPING', 5, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'ProductCategory', 'db' => ['LBL_BASIC_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'ProductCategory', 'db' => ['LBL_CUSTOM_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_CONTACT_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_CONFIGURATION_WORKING_TIME', 10, 0, 0, 0, 0, 0, 2, 0, null]],
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
				if ($block['oldLabel']) {
					$blockInstance = \vtlib\Block::getInstance($block['oldLabel'], $data['tabid']);
				} else {
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
	 * Drop column.
	 */
	public function dropColumns()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$modules = [
			'u_#__ssingleorders' => ['fields' => 'company', 'moduleName' => 'SSingleOrders'],
			'vtiger_account' => ['fields' => 'ownership', 'moduleName' => 'Accounts'],
			'vtiger_troubletickets' => ['fields' => ['ordertime', 'contract_type', 'contracts_end_date'], 'moduleName' => 'HelpDesk']
		];
		foreach ($modules as $value) {
			$moduleName = $value['moduleName'];
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$fields = $value['fields'];
			if (!\is_array($fields)) {
				$fields = [$fields];
			}
			foreach ($fields as $fieldName) {
				if ($fieldModel = $moduleModel->getFieldByName($fieldName)) {
					if (!$fieldModel->isActiveField() || !$this->isExistsValueForField($moduleName, $fieldName)) {
						$this->removeField($fieldModel);
					} else {
						$dbCommand->update('vtiger_field', ['presence' => 1], ['fieldid' => $fieldModel->getId()])->execute();
						$this->log('[Warning] RemoveFields' . __METHOD__ . ': field exists and is in use, deactivated: ' . $fieldModel->getName() . ' ' . $fieldModel->getModuleName());
					}
				} else {
					$this->log("[Info] Skip removing {$moduleName}:{$fieldName}, field not exists");
				}
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	private function removeField($fieldModel)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " | {$fieldModel->getName()},{$fieldModel->getModuleName()},{$newName} | " . date('Y-m-d H:i:s'));
		try {
			$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldModel->getId());
			$fieldInstance->delete();
		} catch (\Throwable $e) {
			$message = '[ERROR] ' . __METHOD__ . ': ' . $e->__toString();
			$this->log($message);
			\App\Log::error($message);
		}
		\App\Cache::delete('ModuleFields', $fieldModel->getModuleId());
		\App\Cache::staticDelete('ModuleFields', $fieldModel->getModuleId());
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function renameField($fieldModel, string $newName, array $updateData = [])
	{
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		try {
			$dbCommand = $db->createCommand();

			$fldModule = $fieldModel->getModuleName();
			$fieldname = $fieldModel->getName();
			$tabId = $fieldModel->getModuleId();

			$updateData['fieldname'] = $newName;
			$dbCommand->update('vtiger_field', $updateData, ['fieldname' => $fieldname, 'tabid' => $tabId]);
			$dbCommand->update('vtiger_cvcolumnlist', ['field_name' => $newName], ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();
			$dbCommand->update('u_#__cv_condition', ['field_name' => $newName], ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();

			if ('picklist' === $fieldModel->getFieldDataType() || 'multipicklist' === $fieldModel->getFieldDataType()) {
				$tableName = 'vtiger_' . $fieldname;
				$newTableName = 'vtiger_' . $newName;
				if ($db->isTableExists($newTableName) && $db->isTableExists($tableName)) {
					$currentValues = (new \App\Db\Query())->select([$fieldname])->from($tableName)->column();
					$newPicklistValue = (new \App\Db\Query())->select([$newName])->from($newTableName)->column();
					$sortId = \count($newPicklistValue);
					foreach (array_diff($currentValues, $newPicklistValue) as $value) {
						$dbCommand->insert($newTableName, [$newName => $value, 'presence' => 1, 'sortorderid' => ++$sortId])->execute();
						$this->log("[INFO] Add value to: {$newTableName}:{$value};");
					}
				}
				$query = (new \App\Db\Query())->from('vtiger_field')
					->where(['fieldname' => $fieldname])
					->andWhere(['in', 'uitype', [15, 16, 33]]);
				$dataReader = $query->createCommand()->query();
				if (!$dataReader->count() && $db->isTableExists($tableName)) {
					if (15 === $fieldModel->getUIType()) {
						$picklistId = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')->where(['name' => $fieldname])->scalar();
						if ($picklistId) {
							$dbCommand->delete('vtiger_picklist', ['name' => $fieldname])->execute();
							$dbCommand->delete('vtiger_role2picklist', ['picklistid' => $picklistId])->execute();
						}
					}
					$dbCommand->dropTable($tableName)->execute();
					if ($db->isTableExists($tableName . '_seq')) {
						$dbCommand->dropTable($tableName . '_seq')->execute();
					}
					$dbCommand->delete('vtiger_picklist', ['name' => $fieldname])->execute();
				}
				$dbCommand->update('vtiger_picklist_dependency', ['sourcefield' => $newName], ['tabid' => $tabId, 'sourcefield' => $fieldname])->execute();
				$dbCommand->update('vtiger_picklist_dependency', ['targetfield' => $newName], ['tabid' => $tabId, 'targetfield' => $fieldname])->execute();
			}
			$entityFieldInfo = \App\Module::getEntityInfo($fldModule);
			$fieldsName = $entityFieldInfo['fieldnameArr'];
			$searchColumns = $entityFieldInfo['searchcolumnArr'];
			if (\in_array($fieldname, $fieldsName) || \in_array($fieldname, $searchColumns)) {
				if (false !== ($key = array_search($fieldname, $fieldsName))) {
					$fieldsName[$key] = $newName;
				}
				if (false !== ($key = array_search($fieldname, $searchColumns))) {
					$searchColumns[$key] = $newName;
				}
				$dbCommand->update('vtiger_entityname',['fieldname' => implode(',', $fieldsName), 'searchcolumn' => implode(',', $searchColumns)],
				['modulename' => $entityFieldInfo['modulename']])->execute();
				\App\Cache::delete('ModuleEntityById', $tabId);
				\App\Cache::delete('ModuleEntityByName', $fldModule);
			}
			$db->createCommand("UPDATE com_vtiger_workflowtasks SET task = REPLACE(task, '{$fieldname}', '{$newName}') WHERE task LIKE '{$fieldname}'")->execute();
			$db->createCommand("UPDATE com_vtiger_workflows SET test = REPLACE(test, '{$fieldname}', '{$newName}') WHERE test LIKE '{$fieldname}'")->execute();
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			throw $ex;
		}
		\App\Cache::delete('ModuleFields', $fieldModel->getModuleId());
		\App\Cache::staticDelete('ModuleFields', $fieldModel->getModuleId());
	}

	/**
	 * Checks if exists value for field.
	 *
	 * @param mixed $fields
	 * @param mixed $moduleName
	 * @param mixed $fieldName
	 */
	private function isExistsValueForField($moduleName, $fieldName)
	{
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->permission = false;
		$queryGenerator->setStateCondition('All');
		$queryGenerator->addNativeCondition(['<>', 'vtiger_crmentity.deleted', [0]]);
		$queryGenerator->addCondition($fieldName, '', 'ny');
		return $queryGenerator->createQuery()->exists();
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

		$tabIdSalesProcesses = \App\Module::getModuleId('SSalesProcesses');
		$tabIdSingleOrders = \App\Module::getModuleId('SSingleOrders');
		$tabIdFInvoice = \App\Module::getModuleId('FInvoice');
		$tabIdFInvoiceProforma = \App\Module::getModuleId('FInvoiceProforma');
		$tabIdProductCategory = \App\Module::getModuleId('ProductCategory');

		$importerType = new \App\Db\Importers\Base();
		if (empty($fields)) {
			$fields = [
				[\App\Module::getModuleId('Users'), 3039, 'secondary_email', 'vtiger_users', 2, 13, 'secondary_email', 'FL_SECONDARY_EMAIL', 0, 2, '', '100', 2, 468, 1, 'E~O', 1, 0, 'BAS', 1, 'Edit,Detail', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_USERLOGIN_ROLE', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_USERLOGIN_ROLE']],
				[$tabIdFInvoiceProforma, 3021, 'addresslevel1b', 'u_yf_finvoiceproforma_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3019, 'addresslevel2b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3022, 'addresslevel3b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3020, 'addresslevel4b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3015, 'addresslevel5b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3018, 'addresslevel6b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3017, 'addresslevel7b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3013, 'addresslevel8b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3024, 'first_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoiceProforma, 3025, 'first_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3026, 'last_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoiceProforma, 3027, 'last_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3014, 'localnumberb', 'u_yf_finvoiceproforma_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3016, 'buildingnumberb', 'u_yf_finvoiceproforma_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3028, 'company_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoiceProforma, 3029, 'company_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3032, 'email_a', 'u_yf_finvoiceproforma_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 325, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoiceProforma, 3033, 'email_b', 'u_yf_finvoiceproforma_address', 1, 13, 'email_b', 'FL_EMAIL', 0, 2, '', '100', 16, 467, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3034, 'phone_a', 'u_yf_finvoiceproforma_address', 1, 1, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoiceProforma, 3035, 'phone_b', 'u_yf_finvoiceproforma_address', 1, 1, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3023, 'poboxb', 'u_yf_finvoiceproforma_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoiceProforma, 3030, 'vat_id_a', 'u_yf_finvoiceproforma_address', 1, 1, 'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoiceProforma, 3031, 'vat_id_b', 'u_yf_finvoiceproforma_address', 1, 1, 'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],

				[$tabIdFInvoice, 2990, 'first_name_a', 'u_yf_finvoice_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoice, 2991, 'last_name_a', 'u_yf_finvoice_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoice, 2993, 'vat_id_a', 'u_yf_finvoice_address', 1, 1, 'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoice, 2994, 'email_a', 'u_yf_finvoice_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 312, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 2995, 'phone_a', 'u_yf_finvoice_address', 1, 1, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdFInvoice, 2996, 'addresslevel8b', 'u_yf_finvoice_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 2997, 'localnumberb', 'u_yf_finvoice_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 2998, 'addresslevel5b', 'u_yf_finvoice_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 2999, 'buildingnumberb', 'u_yf_finvoice_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3000, 'addresslevel7b', 'u_yf_finvoice_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3001, 'addresslevel6b', 'u_yf_finvoice_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3002, 'addresslevel2b', 'u_yf_finvoice_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3003, 'addresslevel4b', 'u_yf_finvoice_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3004, 'addresslevel1b', 'u_yf_finvoice_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3005, 'addresslevel3b', 'u_yf_finvoice_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3006, 'poboxb', 'u_yf_finvoice_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3007, 'first_name_b', 'u_yf_finvoice_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3008, 'last_name_b', 'u_yf_finvoice_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3009, 'company_name_b', 'u_yf_finvoice_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3010, 'vat_id_b', 'u_yf_finvoice_address', 1, 1, 'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3011, 'email_b', 'u_yf_finvoice_address', 1, 13, 'email_b', 'FL_EMAIL', 0, 2, '', '100', 16, 466, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 3012, 'phone_b', 'u_yf_finvoice_address', 1, 1, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdFInvoice, 2992, 'company_name_a', 'u_yf_finvoice_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],

				[\App\Module::getModuleId('Occurrences'), 3037, 'meeting_url', 'u_yf_occurrences', 1, 17, 'meeting_url', 'FL_MEETING_URL', 0, 2, '', '2048', 11, 460, 1, 'V~O', 1, 0, 'BAS', 1, '', 1, '{"exp":"date_end"}', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_BASIC_INFORMATION']],
				[\App\Module::getModuleId('SCalculations'), 2980, 'parent_id', 'u_yf_scalculations', 1, 10, 'parent_id', 'FL_PARENT_SCALCULATIONS', 0, 2, '', '4294967295', 11, 276, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SCALCULATIONS_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_SCALCULATIONS_INFORMATION']],
				[\App\Module::getModuleId('SQuotes'), 2979, 'parent_id', 'u_yf_squotes', 1, 10, 'parent_id', 'FL_PARENT_SQUOTES', 0, 2, '', '4294967295', 13, 280, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SQUOTES_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_SQUOTES_INFORMATION']],
				[$tabIdSalesProcesses, 2950, 'estimated_margin', 'u_yf_ssalesprocesses', 1, 71, 'estimated_margin', 'FL_ESTIMATED_MARGIN', 0, 2, '', '1.0E+20', 3, 320, 1, 'N~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => 'decimal(28,8)', 'blockLabel' => 'LBL_FINANCES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_FINANCES']],
				[$tabIdSalesProcesses, 2951, 'expected_margin', 'u_yf_ssalesprocesses', 1, 71, 'expected_margin', 'FL_EXPECTED_MARGIN', 0, 2, '', '1.0E+20', 4, 320, 2, 'N~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => 'decimal(28,8)', 'blockLabel' => 'LBL_FINANCES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_FINANCES']],
				[$tabIdSalesProcesses, 2952, 'expected_sale', 'u_yf_ssalesprocesses', 1, 71, 'expected_sale', 'FL_EXPECTED_SALE', 0, 2, '', '1.0E+20', 5, 320, 2, 'N~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => 'decimal(28,8)', 'blockLabel' => 'LBL_FINANCES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_FINANCES']],
				[$tabIdSingleOrders, 2978, 'parent_id', 'u_yf_ssingleorders', 1, 10, 'parent_id', 'FL_PARENT_SSINGLEORDERS', 0, 2, '', '4294967295', 19, 284, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_SSINGLEORDERS_INFORMATION']],
				[$tabIdSingleOrders, 2953, 'contactid', 'u_yf_ssingleorders', 1, 10, 'contactid', 'FL_CONTACT', 0, 2, '', '4294967295', 6, 284, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_SSINGLEORDERS_INFORMATION']],
				[$tabIdSingleOrders, 2970, 'addresslevel1b', 'u_yf_ssingleorders_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2969, 'addresslevel2b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2968, 'addresslevel3b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2967, 'addresslevel4b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2966, 'addresslevel5b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2965, 'addresslevel6b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2964, 'addresslevel7b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2963, 'addresslevel8b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2961, 'buildingnumberb', 'u_yf_ssingleorders_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2956, 'company_name_a', 'u_yf_ssingleorders_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdSingleOrders, 2974, 'company_name_b', 'u_yf_ssingleorders_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2959, 'email_a', 'u_yf_ssingleorders_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 295, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdSingleOrders, 2976, 'email_b', 'u_yf_ssingleorders_address', 1, 13, 'email_b', 'FL_EMAIL', 0, 2, '', '100', 16, 463, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2954, 'first_name_a', 'u_yf_ssingleorders_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdSingleOrders, 2972, 'first_name_b', 'u_yf_ssingleorders_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2955, 'last_name_a', 'u_yf_ssingleorders_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdSingleOrders, 2973, 'last_name_b', 'u_yf_ssingleorders_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 962, 'localnumberb', 'u_yf_ssingleorders_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2960, 'phone_a', 'u_yf_ssingleorders_address', 1, 1, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdSingleOrders, 2977, 'phone_b', 'u_yf_ssingleorders_address', 1, 1, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2971, 'poboxb', 'u_yf_ssingleorders_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[$tabIdSingleOrders, 2957, 'vat_id_a', 'u_yf_ssingleorders_address', 1, 1, 'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_BILLING']],
				[$tabIdSingleOrders, 2975, 'vat_id_b', 'u_yf_ssingleorders_address', 1, 1, 'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING']],
				[\App\Module::getModuleId('Calendar'), 3036, 'meeting_url', 'vtiger_activity', 1, 326, 'meeting_url', 'FL_MEETING_URL', 0, 2, '', '2048', 28, 19, 1, 'V~O', 2, 11, 'BAS', 1, '', 1, '{"exp":"due_date","roomName":"subject"}', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_TASK_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_TASK_INFORMATION']],
				[\App\Module::getModuleId('Contacts'), 3038, 'gender', 'vtiger_contactdetails', 1, 16, 'gender', 'FL_GENDER', 0, 2, '', '255', 31, 4, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_CONTACT_INFORMATION', 'picklistValues' => ['PLL_WOMAN', 'PLL_MAN'], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_CONTACT_INFORMATION']],
				[$tabIdProductCategory, 2989, 'active', 'u_yf_productcategory', 1, 56, 'active', 'FL_ACTIVE', 0, 2, '', '-128,127', 2, 464, 1, 'C~O', 2, 0, 'BAS', 1, '', 1, '', '', 0, 0, 0, 0,
					'type' => $importerType->tinyInteger(1), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_BASIC_INFORMATION']],
				[$tabIdProductCategory, 2981, 'category', 'u_yf_productcategory', 1, 2, 'category', 'FL_CATEGORY_NAME', 0, 2, '', '255', 1, 464, 1, 'V~M', 2, 0, 'BAS', 1, '', 1, '', '', 0, 0, 0, 0,
					'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_BASIC_INFORMATION']],
				[$tabIdProductCategory, 2988, 'parent_id', 'u_yf_productcategory', 1, 10, 'parent_id', 'FL_PARENT_CATEGORY', 0, 2, '', '4294967295', 3, 464, 1, 'V~O', 2, 0, 'BAS', 1, '', 1, '', '', 0, 0, 0, 0,
					'type' => $importerType->integer(10), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'picklistValues' => [], 'relatedModules' => ['ProductCategory'], 'blockData' => ['label' => 'LBL_BASIC_INFORMATION']],
			];
		}

		foreach ($fields as $field) {
			$moduleId = $field[0];
			$moduleName = \App\Module::getModuleName($moduleId);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists || !$moduleName) {
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
			// if ($field['picklistValues'] && 302 == $field[5]) {
			// 	$field[22] = $this->setTree($field['picklistValues']);
			// }
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
			['value' => [5, 'LBL_MAGENTO', 'fab fa-magento', 'LBL_MAGENTO_DESCRIPTION', 'index.php?parent=Settings&module=Magento&view=List', 13, 0, 0, null], 'blockLabel' => 'LBL_INTEGRATION'],
			['value' => [4, 'LBL_EVENT_HANDLER', 'mdi mdi-car-turbocharger', 'LBL_EVENT_HANDLER_DESC', 'index.php?parent=Settings&module=EventHandler&view=Index', 13, 0, 0, null], 'blockLabel' => 'LBL_SYSTEM_TOOLS'],
			['value' => [5, 'LBL_MEETING_SERVICES', 'mdi mdi-server-network', 'LBL_MEETING_SERVICES_DESCRIPTION', 'index.php?parent=Settings&module=MeetingServices&view=List', 15, 0, 0, null], 'blockLabel' => 'LBL_INTEGRATION']
		];
		$columnName = ['blockid', 'name', 'iconpath', 'description', 'linkto', 'sequence', 'active', 'pinned', 'admin_access'];
		foreach ($settingFields as $field) {
			$blockId = (new \App\Db\Query())->select(['blockid'])
				->from('vtiger_settings_blocks')
				->where(['label' => $field['blockLabel']])->scalar();
			if (!$blockId) {
				$this->log("[Error] Setting block not exists: {$field['value'][1]}");
				continue;
			}
			$isExistField = (new \App\Db\Query())->from('vtiger_settings_field')->where(['blockid' => $blockId, 'name' => $field['value'][1]])->exists();
			if ($isExistField) {
				$this->log("[INFO] Skip adding setting field. BlockId: {$blockId}; Setting field name: {$field['value'][1]}; field exists: {$isExistField}");
				continue;
			}
			$field['value'][0] = $blockId;
			$data = array_combine($columnName, $field['value']);
			\App\Db::getInstance()->createCommand()->insert('vtiger_settings_field', $data)->execute();
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function syncPicklist()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();

		$newFieldName = 'payment_methods';
		if (!$db->isTableExists("vtiger_{$newFieldName}")) {
			$fieldModel = Vtiger_Field_Model::init('Vtiger', ['uitype' => 16], $newFieldName);
			$fieldModel->setPicklistValues(['PLL_TRANSFER', 'PLL_CASH', 'PLL_CASH_ON_DELIVERY', 'PLL_WIRE_TRANSFER', 'PLL_REDSYS', 'PLL_DOTPAY', 'PLL_PAYPAL', 'PLL_PAYPAL_EXPRESS', 'PLL_CHECK']);
		}
		$picklistWithAutomation = [
			'FCorectingInvoice' => ['fcorectinginvoice_formpayment'],
			'FInvoice' => ['finvoice_formpayment'],
			'FInvoiceCost' => ['finvoicecost_formpayment'],
			'FInvoiceProforma' => ['finvoiceproforma_formpayment'],
			'SSingleOrders' => ['ssingleorders_method_payments']
		];
		foreach ($picklistWithAutomation as $moduleName => $fieldNames) {
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			foreach ($fieldNames as $fieldName) {
				if ($fieldModel = $moduleModel->getFieldByName($fieldName)) {
					try {
						$this->renameField($fieldModel, $newFieldName, ['uitype' => 16, 'fieldlabel' => 'FL_PAYMENTS_METHOD']);
					} catch (\Throwable $e) {
						$message = '[ERROR] ' . __METHOD__ . ": {$moduleName}:{$fieldName} " . $e->__toString();
						$this->log($message);
						\App\Log::error($message);
					}
				} else {
					$this->log("[Warning] Skip renaming {$moduleName}:{$fieldName}, field not exists");
				}
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function setRelations()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$ralations = [
			['type' => 'add', 'data' => [628, 'Contacts', 'SSingleOrders', 'getDependentsList', 13, 'SSingleOrders', 0, '', 0, 0, 0, 'RelatedTab', 'contactid']],
			['type' => 'add', 'data' => [629, 'SSingleOrders', 'SSingleOrders', 'getDependentsList', 7, 'SSingleOrders', 0, '', 0, 0, 0, 'RelatedTab', 'parent_id']],
			['type' => 'add', 'data' => [630, 'SQuotes', 'SQuotes', 'getDependentsList', 7, 'SQuotes', 0, '', 0, 0, 0, 'RelatedTab', 'parent_id']],
			['type' => 'add', 'data' => [631, 'SCalculations', 'SCalculations', 'getDependentsList', 7, 'SCalculations', 0, '', 0, 0, 0, 'RelatedTab', 'parent_id']],
			['type' => 'add', 'data' => [632, 'ProductCategory', 'ProductCategory', 'getDependentsList', 1, 'LBL_CHILD_PRODUCTCATEGORY', 0, 'ADD', 0, 0, 0, 'RelatedTab', 'parent_id']],
			['type' => 'add', 'data' => [633, 'Products', 'ProductCategory', 'getRelatedList', 1, 'ProductCategory', 0, 'SELECT', 0, 0, 0, 'RelatedTab', null]],
			['type' => 'add', 'data' => [634, 'ProductCategory', 'Products', 'getRelatedList', 2, 'Products', 0, 'SELECT', 0, 0, 0, 'RelatedTab', null]],
			['type' => 'update', 'data' => [38, 'Products', 'HelpDesk', 'getDependentsList', 1, 'HelpDesk', 0, '', 0, 0, 0, 'RelatedTab', 'product_id'], 'where' => ['tabid' => \App\Module::getModuleId('Products'), 'related_tabid' => \App\Module::getModuleId('HelpDesk'), 'name' => 'getDependentsList']],
			['type' => 'update', 'data' => [159, 'Services', 'HelpDesk', 'getDependentsList', 1, 'HelpDesk', 0, '', 0, 0, 0, 'RelatedTab', 'product_id'], 'where' => ['tabid' => \App\Module::getModuleId('Services'), 'related_tabid' => \App\Module::getModuleId('HelpDesk'), 'name' => 'getRelatedList']],
		];

		foreach ($ralations as $relation) {
			[, $moduleName, $relModuleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment, $viewType, $fieldName] = $relation['data'];
			$tabid = \App\Module::getModuleId($moduleName);
			$relTabid = \App\Module::getModuleId($relModuleName);
			$where = ['tabid' => $tabid, 'related_tabid' => $relTabid, 'name' => $name];
			$isExists = (new \App\Db\Query())->from('vtiger_relatedlists')->where($where)->exists();
			if (!$isExists && 'add' === $relation['type']) {
				$dbCommand->insert('vtiger_relatedlists', [
					'tabid' => $tabid,
					'related_tabid' => $relTabid,
					'name' => $name,
					'sequence' => $sequence,
					'label' => $label,
					'presence' => $presence,
					'actions' => $actions,
					'favorites' => $favorites,
					'creator_detail' => $creatorDetail,
					'relation_comment' => $relationComment,
					'view_type' => $viewType,
					'field_name' => $fieldName
				])->execute();
			} elseif ('update' === $relation['type'] && ($isExists || (!$isExists && isset($relation['where']['name']) && (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $tabid, 'related_tabid' => $relTabid])->exists()))) {
				$where = $relation['where'] ?? $where;
				$dbCommand->update('vtiger_relatedlists', [
					'name' => $name,
					'sequence' => $sequence,
					'label' => $label,
					'presence' => $presence,
					'actions' => $actions,
					'favorites' => $favorites,
					'creator_detail' => $creatorDetail,
					'relation_comment' => $relationComment,
					'view_type' => $viewType,
					'field_name' => $fieldName
				], $where)->execute();
			}
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
		$workflow[] = [73, 'SSingleOrders', 'Create IGDN', '[{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":0}]', 3, null, null, 6, null, null, null, null, null, null, null];
		$workflow[] = [77, 'SSingleOrders', 'Cancel IGDN', '[{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":0}]', 4, null, null, 6, null, null, null, null, null, null, null];
		$workflowTask[] = [142, 73, 'Create IGDN', 'O:18:"VTCreateEntityTask":11:{s:18:"executeImmediately";b:1;s:8:"contents";N;s:10:"workflowId";i:73;s:7:"summary";s:11:"Create IGDN";s:6:"active";b:0;s:7:"trigger";N;s:11:"entity_type";s:4:"IGDN";s:15:"reference_field";s:15:"ssingleordersid";s:19:"field_value_mapping";s:94:"[{"fieldname":"igdn_status","value":"PLL_ACCEPTED","valuetype":"rawtext","modulename":"IGDN"}]";s:12:"mappingPanel";s:1:"1";s:2:"id";i:142;}'];
		$workflowTask[] = [148, 77, 'Cancel IGDN', 'O:24:"VTUpdateRelatedFieldTask":8:{s:18:"executeImmediately";b:0;s:8:"contents";N;s:10:"workflowId";i:77;s:7:"summary";s:11:"Cancel IGDN";s:6:"active";b:0;s:7:"trigger";N;s:19:"field_value_mapping";s:81:"[{"fieldname":"IGDN::igdn_status","value":"PLL_CANCELLED","valuetype":"rawtext"}]";s:2:"id";i:148;}'];
		foreach ($workflow as $record) {
			try {
				$workflowId = (new \App\Db\Query())->select(['workflow_id'])
					->from('com_vtiger_workflows')
					->where(['module_name' => $record[1], 'summary' => $record[2]])->scalar();
				if (!$workflowId) {
					$newWorkflow = $workflowManager->newWorkFlow($record[1]);
					$newWorkflow->moduleName = $record[1];
					$newWorkflow->description = $record[2];
					$newWorkflow->test = $record[3];
					$newWorkflow->executionCondition = $record[4];
					$newWorkflow->defaultworkflow = $record[5];
					$newWorkflow->type = $record[6];
					$newWorkflow->filtersavedinnew = $record[7];
					$newWorkflow->schtypeid = $record[8];
					$newWorkflow->schdayofmonth = $record[9];
					$newWorkflow->schdayofweek = $record[10];
					$newWorkflow->schannualdates = $record[11];
					$newWorkflow->schtime = $record[12];
					$newWorkflow->nexttrigger_time = $record[13];
					$newWorkflow->params = $record[14];
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
				$this->log("[Error] {$e->getMessage()} in {$e->getTraceAsString()}");
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function actionMapp()
	{
		$start = microtime(true);
		$db = \App\Db::getInstance();

		$inventoryModules = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['type' => 1])->column();
		$actions = [
			['type' => 'add', 'name' => 'RecordCollector', 'tabsData' => $inventoryModules, 'permission' => 1],
			['type' => 'add', 'name' => 'MeetingUrl', 'tabsData' => [\App\Module::getModuleId('Users')], 'permission' => 1]
		];

		foreach ($actions as $action) {
			$key = (new \App\Db\Query())->select(['actionid'])->from('vtiger_actionmapping')->where(['actionname' => $action['name']])->limit(1)->scalar();
			if ('remove' === $action['type']) {
				if ($key) {
					$db->createCommand()->delete('vtiger_actionmapping', ['actionid' => $key])->execute();
					$db->createCommand()->delete('vtiger_profile2utility', ['activityid' => $key])->execute();
				}
				continue;
			}
			if (empty($key)) {
				$securitycheck = 0;
				$key = $db->getUniqueID('vtiger_actionmapping', 'actionid', false);
				$db->createCommand()->insert('vtiger_actionmapping', ['actionid' => $key, 'actionname' => $action['name'], 'securitycheck' => $securitycheck])->execute();
			}
			$permission = 1;
			if (isset($action['permission'])) {
				$permission = $action['permission'];
			}

			$tabsData = $action['tabsData'];
			$dataReader = (new \App\Db\Query())->select(['profileid'])->from('vtiger_profile')->createCommand()->query();
			while (false !== ($profileId = $dataReader->readColumn(0))) {
				foreach ($tabsData as $tabId) {
					$isExists = (new \App\Db\Query())->from('vtiger_profile2utility')->where(['profileid' => $profileId, 'tabid' => $tabId, 'activityid' => $key])->exists();
					if (!$isExists) {
						$db->createCommand()->insert('vtiger_profile2utility', [
							'profileid' => $profileId, 'tabid' => $tabId, 'activityid' => $key, 'permission' => $permission
						])->execute();
					}
				}
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function settingMenu()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . "\t\t|\t" . date('H:i:s'), true);
		$dbCommand = \App\Db::getInstance()->createCommand();
		$transformedUrlMapping = [
			'index.php?module=Administration&action=index&parenttab=Settings' => 'index.php?module=Users&parent=Settings&view=List',
			'index.php?module=Settings&action=listroles&parenttab=Settings' => 'index.php?module=Roles&parent=Settings&view=Index',
			'index.php?module=Settings&action=ListProfiles&parenttab=Settings' => 'index.php?module=Profiles&parent=Settings&view=List',
			'index.php?module=Settings&action=listgroups&parenttab=Settings' => 'index.php?module=Groups&parent=Settings&view=List',
			'index.php?module=Settings&action=OrgSharingDetailView&parenttab=Settings' => 'index.php?module=SharingAccess&parent=Settings&view=Index',
			'index.php?module=Settings&action=DefaultFieldPermissions&parenttab=Settings' => 'index.php?module=FieldAccess&parent=Settings&view=Index',
			'index.php?module=Settings&action=ListLoginHistory&parenttab=Settings' => 'index.php?module=LoginHistory&parent=Settings&view=List',
			'index.php?module=Settings&action=ModuleManager&parenttab=Settings' => 'index.php?module=ModuleManager&parent=Settings&view=List',
			'index.php?module=PickList&action=PickList&parenttab=Settings' => 'index.php?parent=Settings&module=Picklist&view=Index',
			'index.php?module=Settings&action=listwordtemplates&parenttab=Settings' => 'index.php?module=Settings&submodule=ModuleManager&view=WordTemplates',
			'index.php?module=Settings&action=listnotificationschedulers&parenttab=Settings' => 'index.php?module=Settings&submodule=Vtiger&view=Schedulers',
			'index.php?module=Settings&action=listinventorynotifications&parenttab=Settings' => 'index.php?module=Settings&submodule=Notifications&view=InventoryAlerts',
			'index.php?module=Settings&action=CurrencyListView&parenttab=Settings' => 'index.php?parent=Settings&module=Currency&view=List',
			'index.php?module=Settings&action=TaxConfig&parenttab=Settings' => 'index.php?module=Vtiger&parent=Settings&view=TaxIndex',
			'index.php?module=Settings&action=ProxyServerConfig&parenttab=Settings' => 'index.php?module=Settings&submodule=Server&view=ProxyConfig',
			'index.php?module=Settings&action=OrganizationTermsandConditions&parenttab=Settings' => 'index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit',
			'index.php?module=Settings&action=CustomModEntityNo&parenttab=Settings' => 'index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering',
			'index.php?module=com_vtiger_workflow&action=workflowlist&parenttab=Settings' => 'index.php?module=Workflows&parent=Settings&view=List',
			'index.php?module=com_vtiger_workflow&action=workflowlist' => 'index.php?module=Workflows&parent=Settings&view=List',
			'index.php?module=ConfigEditor&action=index' => 'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail',
			'index.php?module=Tooltip&action=QuickView&parenttab=Settings' => 'index.php?module=Settings&submodule=Tooltip&view=Index',
			'index.php?module=Settings&action=Announcements&parenttab=Settings' => 'index.php?parent=Settings&module=Vtiger&view=AnnouncementEdit',
			'index.php?module=PickList&action=PickListDependencySetup&parenttab=Settings' => 'index.php?parent=Settings&module=PickListDependency&view=List',
			'index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker' => 'index.php?module=Settings&submodule=ModTracker&view=Index',
			'index.php?module=CronTasks&action=ListCronJobs&parenttab=Settings' => 'index.php?module=CronTasks&parent=Settings&view=List',
			'index.php?module=ExchangeConnector&action=index&parenttab=Settings' => 'index.php?module=ExchangeConnector&parent=Settings&view=Index',
			'index.php?module=RecordAllocation&view=Index&parent=Settings&type=owner' => 'index.php?module=RecordAllocation&view=Index&parent=Settings&mode=owner',
			'index.php?module=RecordAllocation&view=Index&parent=Settings&type=sharedOwner' => 'index.php?module=RecordAllocation&view=Index&parent=Settings&mode=sharedOwner',
		];
		$skipMenuItemList = ['LBL_AUDIT_TRAIL', 'LBL_SYSTEM_INFO', 'LBL_PROXY_SETTINGS', 'LBL_DEFAULT_MODULE_VIEW',
			'LBL_FIELDFORMULAS', 'LBL_FIELDS_ACCESS', 'LBL_MAIL_MERGE',
			'NOTIFICATIONSCHEDULERS', 'INVENTORYNOTIFICATION', 'ModTracker',
			'LBL_WORKFLOW_LIST', 'LBL_TOOLTIP_MANAGEMENT', ];
		$dbCommand->delete('vtiger_settings_field', ['name' => $skipMenuItemList])->execute();

		foreach ($transformedUrlMapping as $old => $link) {
			if ($dbCommand->update('vtiger_settings_field', ['linkto' => $link], ['linkto' => $old])->execute()) {
				$this->log("[Info] Update setting menu: {$link}");
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Stop process.
	 */
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
				'result' => false,
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
	 * Postupdate.
	 */
	public function postupdate()
	{
		if ($this->error || false !== strpos($this->importer->logs, 'Error')) {
			$this->stopProcess();
		}
	}
}
