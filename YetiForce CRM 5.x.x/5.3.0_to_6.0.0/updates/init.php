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
// last check: c35a5ed40f8273c52e57b41f54caffd467d25076
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
			$message = "  {$message}";
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
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$minTime = 600;
		$error = '';
		if (version_compare(PHP_VERSION, '7.2', '<')) {
			$error = 'Wrong PHP version, recommended version >= 7.2';
		}
		if (0 != ini_get('max_execution_time') && ini_get('max_execution_time') < $minTime) {
			$error .= PHP_EOL . 'max_execution_time = ' . ini_get('max_execution_time') . ' < ' . $minTime;
		}
		if ('-1' != ini_get('max_input_time') && ini_get('max_input_time') < $minTime) {
			$error .= PHP_EOL . 'max_input_time = ' . ini_get('max_input_time') . ' < ' . $minTime;
		}
		if ($error) {
			$this->package->_errorText = 'The server configuration is not compatible with the requirements of the upgrade package. Please have a look at the list of errors:' . PHP_EOL . PHP_EOL . $error;
			$this->log($this->package->_errorText);
			$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
			return false;
		}
		copy(__DIR__ . '/53Field.php', ROOT_DIRECTORY . '/modules/Settings/LayoutEditor/models/Field.php');
		copy(__DIR__ . '/files/app/Db/Importer.php', ROOT_DIRECTORY . '/app/Db/Importer.php');
		copy(__DIR__ . '/files/app/Db/Importers/Base.php', ROOT_DIRECTORY . '/app/Db/Importers/Base.php');
		copy(__DIR__ . '/files/app/Db/Drivers/ColumnSchemaBuilderTrait.php', ROOT_DIRECTORY . '/app/Db/Drivers/ColumnSchemaBuilderTrait.php');
		copy(__DIR__ . '/files/modules/Vtiger/models/Field.php', ROOT_DIRECTORY . '/modules/Vtiger/models/Field.php');
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
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
		$this->importer = new \App\Db\Importer();
		try {
			$configFile = 'app/ConfigFile.php';
			copy(__DIR__ . '/files/' . $configFile, ROOT_DIRECTORY . '/' . $configFile);
			\App\Cache::resetFileCache(ROOT_DIRECTORY . '/' . $configFile);
			$db->createCommand('DELETE FROM  `vtiger_relatedlists_fields` WHERE relation_id NOT IN ( SELECT relation_id FROM vtiger_relatedlists );')->execute();
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->checkIntegrity(false);
			$this->importer->updateScheme();
			$db->createCommand('ALTER TABLE `roundcube_cache` CHANGE `cache_key` `cache_key` varchar(128)  COLLATE utf8_bin NOT NULL after `user_id`;')->execute();
			$db->createCommand('ALTER TABLE `roundcube_cache_shared` CHANGE `cache_key` `cache_key` varchar(255)  COLLATE utf8_bin NOT NULL first;')->execute();
			$this->importer->dropColumns([['vtiger_relatedlists_fields', 'fieldname']]);
			$this->importer->dropForeignKeys(['vtiger_modcomments_ibfk_1' => 'vtiger_modcomments']);
			$this->importer->dropTable(['u_yf_github', 'l_yf_sqltime']);
			$this->importer->importData();
			$this->addModules(['ProductCategory', 'BankAccounts']);
			$this->removeModule(['Portal']);
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
		$this->createConfigFiles();
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	/**
	 * Add modules.
	 *
	 * @param string[] $modules
	 */
	private function addModules(array $modules)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	/**
	 * Remove modules.
	 *
	 * @param array $modules
	 */
	private function removeModule(array $modules)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		foreach ($modules as $moduleName) {
			$rows = (new \App\Db\Query())->select(['emailtemplatesid'])->from('u_#__emailtemplates')->where(['module' => $moduleName])->column();
			foreach ($rows as $recordId) {
				\Vtiger_Record_Model::getInstanceById($recordId, 'EmailTemplates')->delete();
			}
			$moduleInstance = \vtlib\Module::getInstance($moduleName);
			if ($moduleInstance) {
				if ('Portal' === $moduleInstance->name) {
					$focus = new Portal();
					$focus->moduleName = $moduleInstance->name;
					\App\Cache::staticSave('CRMEntity', $moduleInstance->name, $focus);
				}
				$moduleInstance->delete();
				$dbCommand = \App\Db::getInstance()->createCommand();
				$dbCommand->delete('vtiger_links', ['like', 'linkurl', "module={$moduleName}&"])->execute();
				$dbCommand->delete('vtiger_profile2utility', ['tabid' => $moduleInstance->id])->execute();
				$this->log('[INFO] Removed module: ' . $moduleName);
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function data()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$tabIdAccounts = \App\Module::getModuleId('Accounts');
		$tabIdUsers = \App\Module::getModuleId('Users');
		$tabIdCompetition = \App\Module::getModuleId('Competition');
		$tabIdMultiCompanyt = \App\Module::getModuleId('MultiCompany');

		\App\Db\Updater::batchDelete([
			['vtiger_settings_field', ['name' => 'LBL_GITHUB']],
			['yetiforce_proc_tc', ['param' => ['oneDay', 'timeOverlap']]],
			['yetiforce_mail_config', ['type' => 'mailIcon', 'name' => 'showMailAccounts']]
		]);
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
			['vtiger_field', ['fieldparams' => '{"hideLabel":["EventForm","QuickCreateAjax"]}', 'quickcreatesequence' => 0], ['tabid' => \App\Module::getModuleId('Calendar'), 'columnname' => 'activitytype']],
			['vtiger_field', ['uitype' => 12], ['tabid' => $tabIdAccounts, 'columnname' => 'accountname']],
			['vtiger_field', ['displaytype' => 2], ['tablename' => 'vtiger_entity_stats', 'fieldname' => ['crmactivity']]],
			['vtiger_field', ['displaytype' => 2], ['tablename' => 'vtiger_crmentity', 'fieldname' => ['modifiedby']]],
			['vtiger_field', ['displaytype' => 2], ['tablename' => 'vtiger_users', 'fieldname' => ['authy_methods', 'authy_secret_totp']]],
			['vtiger_field', ['displaytype' => 10], ['tablename' => 'vtiger_troubletickets', 'fieldname' => 'product_id']],
			['vtiger_field', ['typeofdata' => 'I~O'], ['tabid' => App\Module::getModuleId('OSSMailView'), 'columnname' => 'rc_user']],
			['vtiger_field', ['fieldlabel' => 'FL_EAN_SKU'], ['tabid' => \App\Module::getModuleId('Products'), 'columnname' => 'ean']],
			['vtiger_field', ['displaytype' => 2], ['tabid' => \App\Module::getModuleId('HelpDesk'), 'columnname' => ['sum_time', 'sum_time_subordinate']]],
			['vtiger_field', ['helpinfo' => 'Edit,Detail'], [
				'tabid' => $tabIdUsers, 'columnname' => ['roleid', 'accesskey', 'activity_view', 'authy_methods', 'authy_secret_totp', 'auto_assign', 'available', 'confirm_password', 'currency_decimal_separator', 'currency_grouping_pattern', 'currency_grouping_separator', 'currency_id', 'currency_symbol_placement', 'date_format', 'date_password_change', 'dayoftheweek', 'defaultactivitytype', 'defaulteventstatus', 'default_record_view', 'default_search_module', 'email1', 'emailoptout', 'end_hour', 'first_name', 'force_password_change', 'hour_format', 'imagename', 'internal_mailer', 'is_admin', 'is_owner', 'language', 'last_name', 'leftpanelhide', 'login_method', 'mail_scanner_actions', 'mail_scanner_fields', 'no_of_currency_decimals', 'othereventduration', 'phone_crm_extension', 'phone_crm_extension_extra', 'primary_phone', 'primary_phone_extra', 'records_limit', 'reminder_interval', 'reports_to_id', 'rowheight', 'start_hour', 'status', 'sync_caldav', 'sync_carddav', 'theme', 'time_zone', 'truncate_trailing_zeros', 'user_name', 'user_password', 'view_date_format']
			]],
			['vtiger_field', ['fieldlabel' => 'Description', 'helpinfo' => 'Edit,Detail'], ['tabid' => $tabIdUsers, 'columnname' => 'description']],
			['vtiger_settings_blocks', ['label' => 'LBL_MENU_DASHBOARD', 'sequence' => 1], ['label' => 'LBL_MENU_SUMMARRY']],
			['vtiger_settings_field', ['sequence' => 14], ['name' => 'LBL_SOCIAL_MEDIA']],
			['vtiger_settings_field', ['sequence' => 5], ['name' => 'Mail View']],
			['vtiger_settings_field', ['sequence' => 6], ['name' => 'LBL_EMAILS_TO_SEND']],
			['vtiger_settings_field', ['sequence' => 7], ['name' => 'LBL_MAIL_SMTP']],
			['vtiger_settings_field', ['sequence' => 4], ['name' => 'Widgets']],
			['vtiger_settings_field', ['sequence' => 3], ['name' => 'LBL_ARRANGE_RELATED_TABS']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Help&view=Index'], ['name' => 'LBL_GITHUB']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Logs&view=SystemWarnings'], ['name' => 'LBL_SYSTEM_WARNINGS']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Dependencies&view=Vulnerabilities'], ['name' => 'LBL_VULNERABILITIES']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Map&view=Config'], ['name' => 'LBL_MAP']],
			['vtiger_settings_field', ['linkto' => 'index.php?module=RecordNumbering&parent=Settings&view=CustomRecordNumbering'], ['name' => 'LBL_CUSTOMIZE_RECORD_NUMBERING']],
			['vtiger_settings_field', ['linkto' => 'index.php?module=ConfigEditor&parent=Settings&view=Detail'], ['name' => 'LBL_CONFIG_EDITOR']],
			['vtiger_settings_field', ['linkto' => 'index.php?module=Watchdog&parent=Settings&view=Index'], ['name' => 'LBL_YETIFORCE_WATCHDOG_HEADER']],
			['vtiger_settings_field', ['linkto' => 'index.php?module=Dependencies&view=Credits&parent=Settings'], ['name' => 'License']],
			['vtiger_settings_field', ['blockid' => (new \App\Db\Query())->select(['blockid'])->from('vtiger_settings_blocks')->where(['label' => 'LBL_COMPANY'])->scalar()], ['name' => 'LBL_BUSINESS_HOURS']],
			['vtiger_settings_field', ['name' => 'LBL_OSSMAIL'], ['name' => 'Mail']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-pbx'], ['name' => 'LBL_PBX', 'iconpath' => 'adminIcon-pbx-manager']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-address-serch'], ['name' => 'LBL_API_ADDRESS']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-dav'], ['name' => 'LBL_DAV_KEYS']],
			['vtiger_settings_field', ['iconpath' => 'yfi-ldap'], ['name' => 'LBL_AUTHORIZATION']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-security-incidents'], ['name' => 'LBL_LOGS']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-event-handlers'], ['name' => 'LBL_EVENT_HANDLER']],
			['vtiger_settings_field', ['iconpath' => 'yfi-conflict-interests'], ['name' => 'LBL_CONFLICT_OF_INTEREST']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-admin-access'], ['name' => 'LBL_ADMIN_ACCESS']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-rbl'], ['name' => 'LBL_MAIL_RBL']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-dependent-fields'], ['name' => 'LBL_FIELDS_DEPENDENCY']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-mail-integrator-panel'], ['name' => 'LBL_MAIL_INTEGRATION']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-view-logs'], ['name' => 'LBL_LOGS_VIEWER']],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Log&view=LogsOwasp'], ['linkto' => 'index.php?module=Log&parent=Settings&view=Index']],
			['vtiger_eventhandlers', ['priority' => 8], ['handler_class' => 'ModTracker_ModTrackerHandler_Handler']],
			['vtiger_field', ['displaytype' => 1], ['tablename' => ['u_yf_scalculations', 'u_yf_squotes', 'u_yf_ssingleorders'], 'fieldname' => 'accountid']],
			['vtiger_legal_form', ['presence' => 0], ['legal_form' => 'PLL_NATURAL_PERSON']],
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
			],
			['s_yf_fields_dependency',
				['tabid' => \App\Module::getModuleId('Accounts'),
					'status' => 1,
					'name' => 'Legal form',
					'views' => '["Create","Edit","Detail","QuickCreate","QuickEdit"]',
					'gui' => 1,
					'mandatory' => 0,
					'fields' => '["vat_id","registration_number_2","registration_number_1","siccode"]',
					'conditions' => '{"condition":"OR","rules":[{"fieldname":"legal_form:Accounts","operator":"n","value":"PLL_NATURAL_PERSON"}]}',
					'conditionsFields' => '["legal_form"]'
				],
				['name' => 'Legal form']
			],
			['vtiger_cron_task',
				['name' => 'LBL_MAIL_RBL', 'handler_class' => 'Vtiger_MailRbl_Cron', 'frequency' => 86400, 'status' => 1, 'module' => 'Vtiger', 'sequence' => 35],
				['handler_class' => 'Vtiger_MailRbl_Cron']
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
		App\EventHandler::registerHandler('EditViewPreSave', 'OSSTimeControl_TimeControl_Handler', 'OSSTimeControl', '', 5, true, \App\Module::getModuleId('OSSTimeControl'));
		App\EventHandler::registerHandler('EntityAfterShowHiddenData', 'ModTracker_ModTrackerHandler_Handler', '', '', 5, true, 0);
		App\EventHandler::registerHandler('UsersAfterLogin', 'App\Extension\PwnedPassword', 'Users', '', 4, true, 0);
		App\EventHandler::registerHandler('UsersAfterPasswordChange', 'App\Extension\PwnedPassword', 'Users', '', 4, true, 0);

		$fieldModel = Vtiger_Module_Model::getInstance('Leads')->getFieldByName('leadsource');
		$fieldModel->setPicklistValues(['Magento']);

		$this->addRecords();
		$this->blocks();
		$this->addFields();
		$this->addPicklistValues('ssingleorders_status', 'SSingleOrders', ['PLL_NEW', 'PLL_PAYMENT_REVIEW', 'PLL_ON_HOLD', 'PLL_PROCESSING', 'PLL_COMPLETE', 'PLL_CLOSED', 'PLL_CANCELLED']);
		$this->updateAdressFields();
		$this->addSettingFields();
		$this->addWorflows();
		$this->syncPicklist();
		$this->dropColumns();
		$this->setRelations();
		$this->updateCurrencies();
		$this->actionMapp();
		$this->settingMenu();
		$this->updateUserSeq();
		$this->dropTableByField('lead_view', 'vtiger_users');
		$this->updateDefaults();
		$this->changeTablesEngine();
		$this->attachments();
		$this->addMissingRelations();
		$this->changeFields();
		$this->updateUserLabels();
		$this->importer->refreshSchema();
		\App\Db\Fixer::maximumFieldsLength();
		\App\Db\Fixer::baseModuleTools();
		$this->log('Fixer::baseModuleTools: ' . \App\Db\Fixer::baseModuleTools());
		$this->log('Fixer::maximumFieldsLength: ' . print_r(\App\Db\Fixer::maximumFieldsLength(), true));
		(new \App\BatchMethod(['method' => '\App\User::updateLabels', 'params' => [0]]))->save();
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	public function addRecords()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();

		$data[] = [
			115, 'Interests conflict - E-mail with the outcome of the request for access to the record', 'N27', 'PLL_RECORD', 'Users', 'Interests conflict - request for access to the record', '<div>
	<div style="width: 100%; padding: 10px 0; background-color: white; text-align: center">
		<div
			style="display: inline-block; width: 90%; min-width: 280px; text-align: left; font-family: Roboto,Arial,Helvetica,sans-serif"
		>
			<div
				style="box-shadow: 0 1px 2px 0 rgba(60,64,67,0.302), 0 2px 6px 2px rgba(60,64,67,0.149); border: 1px solid #d3d3d3; border-radius: 8px; overflow-wrap: break-word; word-break: break-word"
			>
				<div style="padding: 1.5%" dir="ltr">
					<h1 style="display: block; font: 400 18px Roboto, sans-serif; margin: 0; margin-bottom: 15px;">
						User $(params : user)$ requests access to record $(params : record)$
					</h1>
					<p>
						User\'s comment: <br> $(params : comment)$
					</p>
				</div>
			</div>
			<table style="padding: 14px 10px 0 10px;width: 100%;" dir="ltr">
				<tbody>
					<tr>
						<td
							style="width: 70%; font: 12px Roboto,Arial,Helvetica,sans-serif; color: #5f6368; line-height: 16px; min-height: 40px; letter-spacing: .3px; vertical-align: middle"
						>
							<p style="margin: 0; padding: 0">
								$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$
							</p>
						</td>
						<td style="padding-left: 20px; vertical-align: middle">
							$(organization : mailLogo)$
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>', 'InterestsConflictAccessRequest', '1', null];
		$data[] = [
			116, 'Interests conflict - E-mail sent to the person requesting access.', 'N28', 'PLL_RECORD', 'Users', 'Interests conflict - response to the request for access to the record', '<div>
	<div style="width: 100%; padding: 10px 0; background-color: white; text-align: center">
		<div
			style="display: inline-block; width: 90%; min-width: 280px; text-align: left; font-family: Roboto,Arial,Helvetica,sans-serif"
		>
			<div
				style="box-shadow: 0 1px 2px 0 rgba(60,64,67,0.302), 0 2px 6px 2px rgba(60,64,67,0.149); border: 1px solid #d3d3d3; border-radius: 8px; overflow-wrap: break-word; word-break: break-word"
			>
				<div style="padding: 1.5%" dir="ltr">
					<p>
						Hello $(record : RecordLabel)$,<br>
						your request for access to the record $(params : record)$ has been processed. Request status: $(params : status)$
					</p>
				</div>
			</div>
			<table style="padding: 14px 10px 0 10px;width: 100%;" dir="ltr">
				<tbody>
					<tr>
						<td
							style="width: 70%; font: 12px Roboto,Arial,Helvetica,sans-serif; color: #5f6368; line-height: 16px; min-height: 40px; letter-spacing: .3px; vertical-align: middle"
						>
							<p style="margin: 0; padding: 0">
								$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$
							</p>
						</td>
						<td style="padding-left: 20px; vertical-align: middle">
							$(organization : mailLogo)$
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
', 'InterestsConflictAccessResponse', '1', null];
		foreach ($data as $d) {
			if (!(new \App\Db\Query())->from('u_yf_emailtemplates')->where(['sys_name' => $d[7]])->exists()) {
				$record = \Vtiger_Record_Model::getCleanInstance('EmailTemplates');
				$record->set('name', $d[1]);
				$record->set('email_template_type', $d[3]);
				$record->set('module_name', $d[4]);
				$record->set('subject', $d[5]);
				$record->set('content', $d[6]);
				$record->set('email_template_priority', $d[8]);
				$record->setHandlerExceptions(['disableHandlers' => true]);
				$record->save();
				$db->createCommand()
					->update('u_yf_emailtemplates', [
						'sys_name' => $d[7],
					], ['emailtemplatesid' => $record->getId()])
					->execute();
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Update record label.
	 *
	 * @return void
	 */
	public function updateUserLabels(): void
	{
		$db = \App\Db::getInstance();
		$metaInfo = \App\Module::getEntityInfo('Users');

		$queryGenerator = new \App\QueryGenerator('Users');
		$queryGenerator->setFields(['id']);
		$queryGenerator->permission = false;
		$queryGenerator->setStateCondition('All');
		foreach ($metaInfo['fieldnameArr'] as $columnName) {
			$fieldModel = $queryGenerator->getModuleModel()->getFieldByColumn($columnName);
			$queryGenerator->setField($fieldModel->getName());
		}
		$moduleModel = $queryGenerator->getModuleModel();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$labelName = [];
			foreach ($metaInfo['fieldnameArr'] as $columnName) {
				$fieldModel = $moduleModel->getFieldByColumn($columnName);
				$labelName[] = $fieldModel->getDisplayValue($row[$fieldModel->getName()], false, false, true);
			}
			$label = \App\Purifier::encodeHtml(\App\TextParser::textTruncate(\App\Purifier::decodeHtml(implode(' ', $labelName)), 250, false));
			if (!empty($label) && !(new \App\Db\Query())->from('u_#__users_labels')->where(['id' => $row['id']])->exists()) {
				$db->createCommand()->insert('u_#__users_labels', ['id' => $row['id'], 'label' => $label])->execute();
			}
		}
	}

	public function changeFields()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$this->changeFieldType('FInvoiceProforma', 'localnumberc', ['fieldname' => 'localnumbera', 'columnname' => 'localnumbera']);
		$this->changeFieldType('FInvoiceProforma', 'buildingnumberc', ['fieldname' => 'buildingnumbera', 'columnname' => 'buildingnumbera']);
		$this->changeFieldType('FInvoiceProforma', 'poboxc', ['fieldname' => 'poboxa', 'columnname' => 'poboxa']);
		$this->changeFieldType('FInvoiceProforma', 'addresslevel1c', ['fieldname' => 'addresslevel1a', 'columnname' => 'addresslevel1a']);
		$this->changeFieldType('FInvoiceProforma', 'addresslevel2c', ['fieldname' => 'addresslevel2a', 'columnname' => 'addresslevel2a']);
		$this->changeFieldType('FInvoiceProforma', 'addresslevel3c', ['fieldname' => 'addresslevel3a', 'columnname' => 'addresslevel3a']);
		$this->changeFieldType('FInvoiceProforma', 'addresslevel4c', ['fieldname' => 'addresslevel4a', 'columnname' => 'addresslevel4a']);
		$this->changeFieldType('FInvoiceProforma', 'addresslevel5c', ['fieldname' => 'addresslevel5a', 'columnname' => 'addresslevel5a']);
		$this->changeFieldType('FInvoiceProforma', 'addresslevel6c', ['fieldname' => 'addresslevel6a', 'columnname' => 'addresslevel6a']);
		$this->changeFieldType('FInvoiceProforma', 'addresslevel7c', ['fieldname' => 'addresslevel7a', 'columnname' => 'addresslevel7a']);
		$this->changeFieldType('FInvoiceProforma', 'addresslevel8c', ['fieldname' => 'addresslevel8a', 'columnname' => 'addresslevel8a']);

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function changeFieldType(string $moduleName, string $fromField, array $toFieldData)
	{
		$moduleInstance = \Vtiger_Module_Model::getInstance($moduleName);
		try {
			if ($moduleInstance && $fieldModel = $moduleInstance->getFieldByColumn($fromField)) {
				$fieldData = (new \App\Db\Query())->from('vtiger_field')->where(['fieldid' => $fieldModel->getId()])->one();
				$data = array_intersect_key($toFieldData, $fieldData);
				if ($data && ($toField = $toFieldData['fieldname'] ?? null)) {
					$this->renameField($fieldModel, $toField, $data);
					$this->log("[info] Change field: {$fromField}=>{$toFieldData['fieldname']}");
				}
			}
		} catch (\Throwable $ex) {
			$this->log('[ERROR] ' . $ex->getMessage() . '|' . $ex->getTraceAsString());
		}
	}

	public function addMissingRelations()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$dbCommand = \App\Db::getInstance()->createCommand();
		$query = (new \App\Db\Query())->select(['tabid', 'fieldname'])->from('vtiger_field')->where(['uitype' => 10])->andWhere(['not', ['tablename' => ['vtiger_modcomments']]]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$moduleModel = Vtiger_Module_Model::getInstance($row['tabid']);
			if ('ModComments' === $moduleModel->getName()) {
				continue;
			}
			$fieldName = $row['fieldname'];
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			foreach ($fieldModel->getReferenceList() as $relatedModule) {
				if ('ModComments' === $relatedModule || $relatedModule === $moduleModel->getName()) {
					continue;
				}
				$targetModule = vtlib\Module::getInstance($relatedModule);
				$relation = \App\Relation::getAll($targetModule->id, ['related_tabid' => $row['tabid'], 'name' => 'getDependentsList']);
				$relation = \is_array($relation) ? current($relation) : $relation;
				if (!$relation || ($relation && empty($relation['field_name']))) {
					if ($relation) {
						$dbCommand->update('vtiger_relatedlists', ['field_name' => $fieldName], ['relation_id' => $relation['relation_id']])->execute();
						$this->log("[INFO] Updated relation data: {$relation['relation_id']}:{$fieldName}({$targetModule->name}:{$moduleModel->getName()})");
					} else {
						$sequence = $targetModule->__getNextRelatedListSequence();
						$dbCommand->insert('vtiger_relatedlists', [
							'tabid' => $targetModule->id,
							'related_tabid' => $moduleModel->id,
							'name' => 'getDependentsList',
							'sequence' => $sequence,
							'label' => $moduleModel->getName(),
							'presence' => 1,
							'actions' => 'ADD',
							'field_name' => $fieldName,
						])->execute();
						\App\Cache::delete('App\Relation::getAll', '');
						$this->log("[INFO] Added missing relation: {$targetModule->name}:{$moduleModel->getName()}");
					}
				}
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function createSettingsModulesData()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$tableName = 'a_yf_settings_modules';
		$db = \App\Db::getInstance();
		foreach ($this->getSettingsModules() as $moduleName) {
			if (!(new \App\Db\Query())->from($tableName)->where(['name' => $moduleName])->exists()) {
				$db->createCommand()->insert($tableName, ['name' => $moduleName, 'status' => 1, 'created_time' => date('Y-m-d H:i:s')])->execute();
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	public function getSettingsModules()
	{
		$modules = [];
		$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'modules/Settings';
		foreach ((new \DirectoryIterator($dir)) as $item) {
			if ($item->isDir() && !$item->isDot() && (new \FilesystemIterator($item->getPathname()))->valid()) {
				$modules[] = $item->getFilename();
			}
		}
		return array_diff($modules, ['Vtiger', 'YetiForce', 'AdminAccess', 'MeetingServices']);
	}

	private function changeTablesEngine()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();

		$tables = ['s_yf_address_finder', 'u_yf_crmentity_label', 'u_yf_crmentity_search_label'];
		$sqlTables = implode("','", $tables);
		$tablesData = $db->createCommand("SHOW TABLE STATUS WHERE NAME IN ('{$sqlTables}')")->queryAll();
		foreach ($tablesData as $info) {
			if ('MyISAM' === $info['Engine']) {
				if ('s_yf_address_finder' !== $info['Name']) {
					$db->createCommand()->truncateTable($info['Name'])->execute();
				}
				$db->createCommand("ALTER TABLE {$info['Name']} ENGINE = InnoDB")->execute();
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function attachments()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$importerBase = new \App\Db\Importers\Base();

		$column = $db->getTableSchema('vtiger_attachments')->getColumn('attachmentsid');
		if (null !== $column && !$column->autoIncrement) {
			$count = 0;
			$dataReader = $db->createCommand('SELECT vtiger_seattachmentsrel.*
					FROM vtiger_seattachmentsrel
					LEFT JOIN `vtiger_attachments` ON `vtiger_attachments`.attachmentsid = `vtiger_seattachmentsrel`.attachmentsid
					WHERE vtiger_attachments.attachmentsid IS NULL')->query();
			while ($row = $dataReader->read()) {
				$count += $dbCommand->delete('vtiger_seattachmentsrel', $row)->execute();
			}
			if ($count) {
				$this->log("[info] Removed incorrect entries in vtiger_seattachmentsrel: {$count}");
			}
			$importerBase->tables = [
				// 'vtiger_attachments' => [
				// 	'columns' => [
				// 		'attachmentsid' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
				// 	],
				// 	// 'primaryKeys' => [
				// 	// 	['attachments_pk', 'attachmentsid']
				// 	// ],
				// 	'engine' => 'InnoDB',
				// 	'charset' => 'utf8'
				// ],
				'vtiger_seattachmentsrel' => [
					'columns' => [
						'attachmentsid' => $importerBase->integer(10)->unsigned()->notNull()->defaultValue(0),
					],
					'engine' => 'InnoDB',
					'charset' => 'utf8'
				]
			];
			$importerBase->foreignKey = [
				['vtiger_seattachmentsrel_attachmentsid_fk', 'vtiger_seattachmentsrel', 'attachmentsid', 'vtiger_attachments', 'attachmentsid', 'CASCADE', null]
			];
			try {
				$this->importer->dropForeignKeys(['fk_1_vtiger_attachments' => 'vtiger_attachments', 'vtiger_seattachmentsrel_attachmentsid_fk' => 'vtiger_seattachmentsrel']);
				$db->createCommand('ALTER TABLE `vtiger_attachments` CHANGE `attachmentsid` `attachmentsid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;')->execute();
				$this->importer->updateTables($importerBase);
				$this->importer->addForeignKey($importerBase);
				$this->importer->refreshSchema();
				$res = $dbCommand->delete('vtiger_crmentity', ['like', 'vtiger_crmentity.setype', '% Attachment', false])->execute();
				$this->log("[info] Redundant entries were deleted: {$res}");
				$this->importer->logs(false);
			} catch (\Throwable $ex) {
				$this->log('[ERROR] ' . $ex->getMessage() . '|' . $ex->getTraceAsString());
				$this->importer->logs(false);
				throw $ex;
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function addPicklistValues($fieldName, $moduleName, $values)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$fieldModel = Vtiger_Module_Model::getInstance($moduleName)->getFieldByName($fieldName);
		if ($fieldModel && \in_array($fieldModel->getFieldDataType(), ['picklist', 'multipicklist'])) {
			$currentValues = (new \App\Db\Query())->select([$fieldName])->from("vtiger_{$fieldName}")->column();
			if ($diff = array_diff($values, $currentValues)) {
				$fieldModel->setPicklistValues($diff);
				$this->log("[info] Added new values for picklist: {$fieldName}({$moduleName})");
			}
		} else {
			$this->log("[ERROR] Field does not exist or is of the wrong type: {$fieldName}({$moduleName})");
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateDefaults()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$replace = ['paymentsin_status' => ['Created' => 'PLL_CREATED']];
		$data = (new \App\Db\Query())->select(['fieldname', 'defaultvalue'])->from('vtiger_field')->where(['uitype' => [15, 16, 33]])->andWhere(['not', ['defaultvalue' => ['', null]]])->andWhere(['not', ['fieldname' => ['defaulteventstatus', 'defaultactivitytype']]])->createCommand()->queryAllByGroup();
		foreach ($data as $fieldName => $default) {
			$tableName = "vtiger_{$fieldName}";
			if (!$db->isTableExists($tableName)) {
				$this->log("[ERROR] Table not exists: {$tableName}");
				continue;
			}
			if (!(new \App\Db\Query())->from($tableName)->where([$fieldName => $default])->exists()) {
				if (isset($replace[$fieldName][$default])) {
					$db->createCommand()->update('vtiger_field', ['defaultvalue' => $replace[$fieldName][$default]], ['fieldname' => $fieldName, 'defaultvalue' => $default, 'uitype' => [15, 16, 33]])->execute();
					$this->log("[info] Changed default value for {$fieldName}: {$default}->{$replace[$fieldName][$default]}");
				} else {
					$this->log("[Warning] Unidentified default value for {$fieldName}: {$default}");
				}
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function updateCurrencies()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
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
			} else {
				$this->log("[Info] Skip adding currency '{$currency[0]}': currency exists");
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	/**
	 * PreUpdateScheme.
	 */
	public function updateAdressFields()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	/**
	 * Blocks.
	 */
	private function blocks()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$this->updateBlocks([
			['type' => 'update', 'module' => 'SSingleOrders', 'db' => ['LBL_ADDRESS_BILLING', 6, 0, 0, 0, 0, 0, 2, 0, null], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'update', 'module' => 'FInvoice', 'db' => ['LBL_ADDRESS_BILLING', 3, 0, 0, 0, 0, 0, 2, 0, null], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'update', 'module' => 'FInvoiceProforma', 'db' => ['LBL_ADDRESS_BILLING', 3, 0, 0, 0, 0, 0, 2, 0, null], 'oldLabel' => 'LBL_ADDRESS_INFORMATION'],
			['type' => 'add', 'module' => 'SSingleOrders', 'db' => ['LBL_ADDRESS_SHIPPING', 4, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'FInvoice', 'db' => ['LBL_ADDRESS_SHIPPING', 8, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'FInvoiceProforma', 'db' => ['LBL_ADDRESS_SHIPPING', 5, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'ProductCategory', 'db' => ['LBL_BASIC_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'ProductCategory', 'db' => ['LBL_CUSTOM_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_CONTACT_INFORMATION', 10, 0, 0, 0, 0, 0, 2, 0, null]],
			['type' => 'add', 'module' => 'Users', 'db' => ['LBL_USER_CONFIGURATION_WORKING_TIME', 10, 0, 0, 0, 0, 0, 2, 0, null]],
		], ['blocklabel', 'sequence', 'show_title', 'visible', 'create_view', 'edit_view', 'detail_view', 'display_status', 'iscustom', 'icon']);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
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
			\App\Cache::delete('BlockInstance', $blockInstance->label . '|' . $blockInstance->tabid);
			\App\Cache::delete('BlockInstance', $blockInstance->id . '|' . $blockInstance->tabid);
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function updateUserSeq()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = App\Db::getInstance();
		$fields = [
			['user_name', 1, 'User Name', 'LBL_USERLOGIN_ROLE'],
			['is_admin', 2, 'Admin', 'LBL_USERLOGIN_ROLE'],
			['user_password', 7, 'Password', 'LBL_USERLOGIN_ROLE'],
			['confirm_password', 8, 'Confirm Password', 'LBL_USERLOGIN_ROLE'],
			['first_name', 3, 'First Name', 'LBL_USERLOGIN_ROLE'],
			['last_name', 4, 'Last Name', 'LBL_USERLOGIN_ROLE'],
			['roleid', 5, 'Role', 'LBL_USERLOGIN_ROLE'],
			['email1', 1, 'Email', 'LBL_USER_CONTACT_INFORMATION'],
			['status', 6, 'Status', 'LBL_USERLOGIN_ROLE'],
			['activity_view', 5, 'Default Activity View', 'LBL_CALENDAR_SETTINGS'],
			['hour_format', 2, 'Calendar Hour Format', 'LBL_CALENDAR_SETTINGS'],
			['start_hour', 1, 'Day starts at', 'LBL_USER_CONFIGURATION_WORKING_TIME'],
			['reports_to_id', 3, 'Reports To', 'LBL_MORE_INFORMATION'],
			['date_format', 1, 'Date Format', 'LBL_CALENDAR_SETTINGS'],
			['description', 2, 'Description', 'LBL_MORE_INFORMATION'],
			['accesskey', 4, 'Webservice Access Key', 'LBL_USER_INTEGRATION'],
			['time_zone', 3, 'Time Zone', 'LBL_CALENDAR_SETTINGS'],
			['currency_id', 1, 'Currency', 'LBL_CURRENCY_CONFIGURATION'],
			['currency_grouping_pattern', 5, 'Digit Grouping Pattern', 'LBL_CURRENCY_CONFIGURATION'],
			['currency_decimal_separator', 2, 'Decimal Separator', 'LBL_CURRENCY_CONFIGURATION'],
			['currency_grouping_separator', 6, 'Digit Grouping Separator', 'LBL_CURRENCY_CONFIGURATION'],
			['currency_symbol_placement', 3, 'Symbol Placement', 'LBL_CURRENCY_CONFIGURATION'],
			['imagename', 6, 'User Image', 'LBL_USER_GUI'],
			['internal_mailer', 3, 'INTERNAL_MAIL_COMPOSER', 'LBL_USER_MAIL'],
			['theme', 5, 'Theme', 'LBL_USER_GUI'],
			['language', 1, 'Language', 'LBL_USER_GUI'],
			['reminder_interval', 10, 'Reminder Interval', 'LBL_CALENDAR_SETTINGS'],
			['phone_crm_extension', 4, 'CRM Phone Extension', 'LBL_USER_CONTACT_INFORMATION'],
			['no_of_currency_decimals', 7, 'Number Of Currency Decimals', 'LBL_CURRENCY_CONFIGURATION'],
			['truncate_trailing_zeros', 4, 'Truncate Trailing Zeros', 'LBL_CURRENCY_CONFIGURATION'],
			['dayoftheweek', 4, 'Starting Day of the week', 'LBL_CALENDAR_SETTINGS'],
			['othereventduration', 11, 'Other Event Duration', 'LBL_CALENDAR_SETTINGS'],
			['default_record_view', 4, 'Default Record View', 'LBL_USER_GUI'],
			['leftpanelhide', 3, 'LBL_MENU_EXPANDED_BY_DEFAULT', 'LBL_USER_GUI'],
			['rowheight', 2, 'Row Height', 'LBL_USER_GUI'],
			['defaulteventstatus', 7, 'Default Event Status', 'LBL_CALENDAR_SETTINGS'],
			['defaultactivitytype', 6, 'Default Activity Type', 'LBL_CALENDAR_SETTINGS'],
			['is_owner', 7, 'Account Owner', 'LBL_MORE_INFORMATION'],
			['end_hour', 2, 'Day ends at', 'LBL_USER_CONFIGURATION_WORKING_TIME'],
			['emailoptout', 7, 'Approval for email', 'LBL_USER_CONTACT_INFORMATION'],
			['available', 1, 'FL_AVAILABLE', 'LBL_USER_AUTOMATION'],
			['auto_assign', 2, 'FL_AUTO_ASSIGN_RECORDS', 'LBL_USER_AUTOMATION'],
			['records_limit', 3, 'FL_RECORD_LIMIT_IN_MODULE', 'LBL_USER_AUTOMATION'],
			['phone_crm_extension_extra', 6, 'FL_PHONE_CUSTOM_INFORMATION', 'LBL_USER_CONTACT_INFORMATION'],
			['date_password_change', 8, 'FL_DATE_PASSWORD_CHANGE', 'LBL_MORE_INFORMATION'],
			['force_password_change', 2, 'FL_FORCE_PASSWORD_CHANGE', 'LBL_USER_ADV_OPTIONS'],
			['view_date_format', 9, 'FL_VIEW_DATE_FORMAT', 'LBL_CALENDAR_SETTINGS'],
			['authy_methods', 4, 'FL_AUTHY_METHODS', 'LBL_USER_ADV_OPTIONS'],
			['authy_secret_totp', 3, 'FL_AUTHY_SECRET_TOTP', 'LBL_USER_ADV_OPTIONS'],
			['login_method', 1, 'FL_LOGIN_METHOD', 'LBL_USER_ADV_OPTIONS'],
			['sync_carddav', 1, 'LBL_CARDDAV_SYNCHRONIZATION_CONTACT', 'LBL_USER_INTEGRATION'],
			['sync_caldav', 2, 'LBL_CALDAV_SYNCHRONIZATION_CALENDAR', 'LBL_USER_INTEGRATION'],
			['sync_carddav_default_country', 3, 'LBL_CARDDAV_DEFAULT_COUNTRY', 'LBL_USER_INTEGRATION'],
			['default_search_module', 0, 'FL_DEFAULT_SEARCH_MODULE', 'LBL_GLOBAL_SEARCH_SETTINGS'],
			['default_search_override', 0, 'FL_OVERRIDE_SEARCH_MODULE', 'LBL_GLOBAL_SEARCH_SETTINGS'],
			['primary_phone_extra', 5, 'FL_PHONE_CUSTOM_INFORMATION', 'LBL_USER_CONTACT_INFORMATION'],
			['primary_phone', 3, 'FL_PRIMARY_PHONE', 'LBL_USER_CONTACT_INFORMATION'],
			['mail_scanner_actions', 1, 'FL_MAIL_SCANNER_ACTIONS', 'LBL_USER_MAIL'],
			['mail_scanner_fields', 2, 'FL_MAIL_SCANNER_FIELDS', 'LBL_USER_MAIL'],
			['secondary_email', 2, 'FL_SECONDARY_EMAIL', 'LBL_USER_CONTACT_INFORMATION']
		];

		$fieldIdList = [];
		$fieldsAll = (new \App\Db\Query())->select(['fieldname', 'fieldid'])->from('vtiger_field')
			->where(['tabid' => \App\Module::getModuleId('Users')])->createCommand()->queryAllByGroup();
		$blockAll = (new \App\Db\Query())->select(['blocklabel', 'blockid'])->from('vtiger_blocks')
			->where(['tabid' => \App\Module::getModuleId('Users')])->createCommand()->queryAllByGroup();

		$caseSequence = 'CASE';
		$caseBlock = 'CASE';
		foreach ($fields as $fieldSequence) {
			$fieldId = $fieldsAll[$fieldSequence[0]] ?? null;
			$blockId = $blockAll[$fieldSequence[3]] ?? null;
			if (!$fieldId || !$blockId) {
				$this->log("[Error] field or block not exists in Users {$fieldSequence[0]}:{$fieldSequence[3]}");
				continue;
			}
			$fieldIdList[] = $fieldId;
			$caseSequence .= " WHEN fieldid = {$db->quoteValue($fieldId)} THEN {$db->quoteValue($fieldSequence[1])}";
			$caseBlock .= " WHEN fieldid = {$db->quoteValue($fieldId)} THEN {$db->quoteValue($blockId)}";
		}
		$caseSequence .= ' END';
		$caseBlock .= ' ELSE block END';

		$db->createCommand()->update('vtiger_field', [
			'sequence' => new yii\db\Expression($caseSequence),
			'block' => new yii\db\Expression($caseBlock),
		], ['fieldid' => $fieldIdList])->execute();

		// update sequence of blocks
		$blocks = [
			['LBL_USERLOGIN_ROLE', 1],
			['LBL_CURRENCY_CONFIGURATION', 4],
			['LBL_MORE_INFORMATION', 12],
			['LBL_USER_ADV_OPTIONS', 7],
			['LBL_CALENDAR_SETTINGS', 3],
			['LBL_GLOBAL_SEARCH_SETTINGS', 10],
			['LBL_USER_MAIL', 8],
			['LBL_USER_INTEGRATION', 9],
			['LBL_USER_GUI', 6],
			['LBL_USER_AUTOMATION', 11],
			['LBL_USER_CONTACT_INFORMATION', 2],
			['LBL_USER_CONFIGURATION_WORKING_TIME', 5]
		];
		$blockIdList = [];
		$case = ' CASE blockid ';
		foreach ($blocks as $sequence) {
			$blockId = $blockAll[$sequence[0]] ?? null;
			if (!$blockId) {
				$this->log("[Warning] block not exists in Users {$sequence[0]}");
				continue;
			}
			$blockIdList[] = $blockId;
			$case .= " WHEN {$db->quoteValue($blockId)} THEN {$db->quoteValue($sequence[1])}";
		}
		$case .= ' END';
		$db->createCommand()->update('vtiger_blocks', ['sequence' => new yii\db\Expression($case)], ['blockid' => $blockIdList])
			->execute();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	public function dropTableByField(string $fieldName, string $baseTableName)
	{
		$tableName = "vtiger_{$fieldName}";
		$isExisteField = (new \App\Db\Query())->from('vtiger_field')->where(['fieldname' => $fieldName])->andWhere(['in', 'uitype', [15, 16, 33]])->exists();
		if (!$isExisteField) {
			$this->importer->dropTable([$tableName]);
			$this->importer->dropColumns([$baseTableName, $fieldName]);
		}
	}

	/**
	 * Drop column.
	 */
	public function dropColumns()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$modules = [
			'u_#__ssingleorders' => ['fields' => 'company', 'moduleName' => 'SSingleOrders'],
			'vtiger_account' => ['fields' => 'ownership', 'moduleName' => 'Accounts'],
			'vtiger_troubletickets' => ['fields' => ['ordertime', 'contract_type', 'contracts_end_date'], 'moduleName' => 'HelpDesk'],
			'vtiger_users' => ['fields' => ['lead_view'], 'moduleName' => 'Users']
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function removeField($fieldModel)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " | {$fieldModel->getName()},{$fieldModel->getModuleName()} | " . date('Y-m-d H:i:s'));
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function renameField($fieldModel, string $newName, array $updateData = [])
	{
		$db = \App\Db::getInstance();
		$queryBuilder = $db->getSchema()->getQueryBuilder();
		$transaction = $db->beginTransaction();
		try {
			$dbCommand = $db->createCommand();

			$fldModule = $fieldModel->getModuleName();
			$fieldname = $fieldModel->getName();
			$tabId = $fieldModel->getModuleId();

			$updateData['fieldname'] = $newName;
			if (isset($updateData['columnname']) && empty($updateData['tablename'])) {
				$type = $queryBuilder->getColumnType($fieldModel->getDBColumnType());
				$db->createCommand("ALTER TABLE {$fieldModel->getTableName()} CHANGE {$fieldModel->getColumnName()} {$updateData['columnname']} {$type};")->execute();
			}
			$dbCommand->update('vtiger_field', $updateData, ['fieldname' => $fieldname, 'tabid' => $tabId])->execute();
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
					if (15 === $fieldModel->getUIType() || 33 === $fieldModel->getUIType()) {
						$picklistId = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')->where(['name' => $fieldname])->scalar();
						$newPicklistId = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')->where(['name' => $newName])->scalar();
						if ($picklistId && $newPicklistId) {
							$dbCommand->delete('vtiger_picklist', ['name' => $fieldname])->execute();
							$dbCommand->delete('vtiger_role2picklist', ['picklistid' => $picklistId])->execute();
						} elseif ($picklistId && !$newPicklistId) {
							$dbCommand->update('vtiger_picklist', ['name' => $newName], ['name' => $fieldname])->execute();
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
	 * @param string $moduleName
	 * @param string $fieldName
	 */
	private function isExistsValueForField($moduleName, $fieldName)
	{
		if ('Users' === $moduleName) {
			return false;
		}
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$importerType = new \App\Db\Importers\Base();
		if (empty($fields)) {
			$fields = [[86, 2950, 'estimated_margin', 'u_yf_ssalesprocesses', 1, 71, 'estimated_margin', 'FL_ESTIMATED_MARGIN', 0, 2, '', '1.0E+20', 3, 320, 1, 'N~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->decimal('28,8'), 'blockLabel' => 'LBL_FINANCES', 'blockData' => ['label' => 'LBL_FINANCES', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSalesProcesses'],
				[86, 2951, 'expected_margin', 'u_yf_ssalesprocesses', 1, 71, 'expected_margin', 'FL_EXPECTED_MARGIN', 0, 2, '', '1.0E+20', 4, 320, 2, 'N~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->decimal('28,8'), 'blockLabel' => 'LBL_FINANCES', 'blockData' => ['label' => 'LBL_FINANCES', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSalesProcesses'],
				[86, 2952, 'expected_sale', 'u_yf_ssalesprocesses', 1, 71, 'expected_sale', 'FL_EXPECTED_SALE', 0, 2, '', '1.0E+20', 5, 320, 2, 'N~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->decimal('28,8'), 'blockLabel' => 'LBL_FINANCES', 'blockData' => ['label' => 'LBL_FINANCES', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSalesProcesses'],
				[90, 2953, 'contactid', 'u_yf_ssingleorders', 1, 10, 'contactid', 'FL_CONTACT', 0, 2, '', '4294967295', 6, 284, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'blockData' => ['label' => 'LBL_SSINGLEORDERS_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['Contacts'], 'moduleName' => 'SSingleOrders'],
				[90, 2954, 'first_name_a', 'u_yf_ssingleorders_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2955, 'last_name_a', 'u_yf_ssingleorders_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2956, 'company_name_a', 'u_yf_ssingleorders_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2957, 'vat_id_a', 'u_yf_ssingleorders_address', 1, 1, 'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2959, 'email_a', 'u_yf_ssingleorders_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 295, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2960, 'phone_a', 'u_yf_ssingleorders_address', 1, 11, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 295, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2961, 'buildingnumberb', 'u_yf_ssingleorders_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2962, 'localnumberb', 'u_yf_ssingleorders_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2963, 'addresslevel8b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2964, 'addresslevel7b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2965, 'addresslevel6b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2966, 'addresslevel5b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2967, 'addresslevel4b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2968, 'addresslevel3b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2969, 'addresslevel2b', 'u_yf_ssingleorders_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2970, 'addresslevel1b', 'u_yf_ssingleorders_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2971, 'poboxb', 'u_yf_ssingleorders_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2972, 'first_name_b', 'u_yf_ssingleorders_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2973, 'last_name_b', 'u_yf_ssingleorders_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2974, 'company_name_b', 'u_yf_ssingleorders_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2975, 'vat_id_b', 'u_yf_ssingleorders_address', 1, 1, 'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2976, 'email_b', 'u_yf_ssingleorders_address', 1, 13, 'email_b', 'FL_EMAIL', 0, 2, '', '100', 16, 463, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2977, 'phone_b', 'u_yf_ssingleorders_address', 1, 11, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 463, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 2978, 'parent_id', 'u_yf_ssingleorders', 1, 10, 'parent_id', 'FL_PARENT_SSINGLEORDERS', 0, 2, '', '4294967295', 19, 284, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'blockData' => ['label' => 'LBL_SSINGLEORDERS_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['SSingleOrders'], 'moduleName' => 'SSingleOrders'],
				[89, 2979, 'parent_id', 'u_yf_squotes', 1, 10, 'parent_id', 'FL_PARENT_SQUOTES', 0, 2, '', '4294967295', 13, 280, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_SQUOTES_INFORMATION', 'blockData' => ['label' => 'LBL_SQUOTES_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['SQuotes'], 'moduleName' => 'SQuotes'],
				[88, 2980, 'parent_id', 'u_yf_scalculations', 1, 10, 'parent_id', 'FL_PARENT_SCALCULATIONS', 0, 2, '', '4294967295', 11, 276, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_SCALCULATIONS_INFORMATION', 'blockData' => ['label' => 'LBL_SCALCULATIONS_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['SCalculations'], 'moduleName' => 'SCalculations'],
				[95, 2990, 'first_name_a', 'u_yf_finvoice_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2991, 'last_name_a', 'u_yf_finvoice_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2992, 'company_name_a', 'u_yf_finvoice_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2993, 'vat_id_a', 'u_yf_finvoice_address', 1, 1, 'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2994, 'email_a', 'u_yf_finvoice_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 312, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2995, 'phone_a', 'u_yf_finvoice_address', 1, 11, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 312, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2996, 'addresslevel8b', 'u_yf_finvoice_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2997, 'localnumberb', 'u_yf_finvoice_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2998, 'addresslevel5b', 'u_yf_finvoice_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 2999, 'buildingnumberb', 'u_yf_finvoice_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3000, 'addresslevel7b', 'u_yf_finvoice_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3001, 'addresslevel6b', 'u_yf_finvoice_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3002, 'addresslevel2b', 'u_yf_finvoice_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3003, 'addresslevel4b', 'u_yf_finvoice_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3004, 'addresslevel1b', 'u_yf_finvoice_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3005, 'addresslevel3b', 'u_yf_finvoice_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3006, 'poboxb', 'u_yf_finvoice_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3007, 'first_name_b', 'u_yf_finvoice_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3008, 'last_name_b', 'u_yf_finvoice_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3009, 'company_name_b', 'u_yf_finvoice_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3010, 'vat_id_b', 'u_yf_finvoice_address', 1, 1, 'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3011, 'email_b', 'u_yf_finvoice_address', 1, 13, 'email_b', 'FL_EMAIL', 0, 2, '', '100', 16, 466, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3012, 'phone_b', 'u_yf_finvoice_address', 1, 11, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 466, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[99, 3013, 'addresslevel8b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel8b', 'AddressLevel8', 0, 2, '', '255', 1, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3014, 'localnumberb', 'u_yf_finvoiceproforma_address', 1, 1, 'localnumberb', 'Local number', 0, 2, '', '50', 2, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3015, 'addresslevel5b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel5b', 'AddressLevel5', 0, 2, '', '255', 3, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3016, 'buildingnumberb', 'u_yf_finvoiceproforma_address', 1, 1, 'buildingnumberb', 'Building number', 0, 2, '', '255', 4, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3017, 'addresslevel7b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel7b', 'AddressLevel7', 0, 2, '', '255', 5, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3018, 'addresslevel6b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel6b', 'AddressLevel6', 0, 2, '', '255', 6, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3019, 'addresslevel2b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel2b', 'AddressLevel2', 0, 2, '', '255', 7, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3020, 'addresslevel4b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel4b', 'AddressLevel4', 0, 2, '', '255', 8, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3021, 'addresslevel1b', 'u_yf_finvoiceproforma_address', 1, 35, 'addresslevel1b', 'AddressLevel1', 0, 2, '', '255', 9, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3022, 'addresslevel3b', 'u_yf_finvoiceproforma_address', 1, 1, 'addresslevel3b', 'AddressLevel3', 0, 2, '', '255', 10, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3023, 'poboxb', 'u_yf_finvoiceproforma_address', 1, 1, 'poboxb', 'Po Box', 0, 2, '', '50', 11, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3024, 'first_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'first_name_a', 'First Name', 0, 2, '', '255', 12, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3025, 'first_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'first_name_b', 'First Name', 0, 2, '', '255', 12, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3026, 'last_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'last_name_a', 'Last Name', 0, 2, '', '255', 13, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3027, 'last_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'last_name_b', 'Last Name', 0, 2, '', '255', 13, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3028, 'company_name_a', 'u_yf_finvoiceproforma_address', 1, 1, 'company_name_a', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3029, 'company_name_b', 'u_yf_finvoiceproforma_address', 1, 1, 'company_name_b', 'FL_COMPANY_NAME', 0, 2, '', '255', 14, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3030, 'vat_id_a', 'u_yf_finvoiceproforma_address', 1, 1, 'vat_id_a', 'Vat ID', 0, 2, '', '50', 15, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3031, 'vat_id_b', 'u_yf_finvoiceproforma_address', 1, 1, 'vat_id_b', 'Vat ID', 0, 2, '', '50', 15, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3032, 'email_a', 'u_yf_finvoiceproforma_address', 1, 13, 'email_a', 'FL_EMAIL', 0, 2, '', '100', 16, 325, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3033, 'email_b', 'u_yf_finvoiceproforma_address', 1, 13, 'email_b', 'FL_EMAIL', 0, 2, '', '100', 16, 467, 1, 'E~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3034, 'phone_a', 'u_yf_finvoiceproforma_address', 1, 11, 'phone_a', 'FL_PHONE', 0, 2, '', '100', 17, 325, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3035, 'phone_b', 'u_yf_finvoiceproforma_address', 1, 11, 'phone_b', 'FL_PHONE', 0, 2, '', '100', 17, 467, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[9, 3036, 'meeting_url', 'vtiger_activity', 1, 326, 'meeting_url', 'FL_MEETING_URL', 0, 2, '', '2048', 28, 19, 1, 'V~O', 2, 11, 'BAS', 1, '', 1, '{"exp":"due_date","roomName":"subject"}', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(2048), 'blockLabel' => 'LBL_TASK_INFORMATION', 'blockData' => ['label' => 'LBL_TASK_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'Calendar'],
				[131, 3037, 'meeting_url', 'u_yf_occurrences', 1, 326, 'meeting_url', 'FL_MEETING_URL', 0, 2, '', '2048', 11, 460, 1, 'V~O', 1, 0, 'BAS', 1, '', 1, '{"exp":"date_end"}', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(2048), 'blockLabel' => 'LBL_BASIC_INFORMATION', 'blockData' => ['label' => 'LBL_BASIC_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 1, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'Occurrences'],
				[4, 3038, 'gender', 'vtiger_contactdetails', 1, 16, 'gender', 'FL_GENDER', 0, 2, '', '255', 31, 4, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType()->defaultValue(''), 'blockLabel' => 'LBL_CONTACT_INFORMATION', 'blockData' => ['label' => 'LBL_CONTACT_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'picklistValues' => ['PLL_WOMAN', 'PLL_MAN'], 'moduleName' => 'Contacts'],
				[29, 3039, 'secondary_email', 'vtiger_users', 2, 13, 'secondary_email', 'FL_SECONDARY_EMAIL', 0, 2, '', '100', 2, 468, 1, 'E~O', 1, 0, 'BAS', 1, 'Edit,Detail', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100)->defaultValue(''), 'blockLabel' => 'LBL_USER_CONTACT_INFORMATION', 'blockData' => ['label' => 'LBL_USER_CONTACT_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'Users'],
				[29, 3040, 'default_search_operator', 'vtiger_users', 1, 16, 'default_search_operator', 'FL_DEFAULT_SEARCH_OPERATOR', 0, 0, 'PLL_CONTAINS', '255', 0, 437, 1, 'V~M', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_GLOBAL_SEARCH_SETTINGS', 'blockData' => ['label' => 'LBL_GLOBAL_SEARCH_SETTINGS', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => 'fas fa-search'], 'picklistValues' => ['PLL_FULLTEXT_BEGIN', 'PLL_FULLTEXT_WORD', 'PLL_CONTAINS', 'PLL_STARTS_WITH', 'PLL_ENDS_WITH'], 'moduleName' => 'Users'],
				[29, 3041, 'super_user', 'vtiger_users', 1, 56, 'super_user', 'FL_SUPER_USER', 0, 0, '', '-128,127', 9, 77, 1, 'C~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->tinyInteger(1)->defaultValue(0), 'blockLabel' => 'LBL_USERLOGIN_ROLE', 'blockData' => ['label' => 'LBL_USERLOGIN_ROLE', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => 'fas fa-user-tie'], 'moduleName' => 'Users'],
				[99, 3055, 'payment_sum', 'u_yf_finvoiceproforma', 1, 7, 'payment_sum', 'FL_PAYMENT_SUM', 0, 2, '', '1.0E+20', 9, 323, 2, 'NN~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->decimal('28,8'), 'blockLabel' => 'LBL_BASIC_DETAILS', 'blockData' => ['label' => 'LBL_BASIC_DETAILS', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3056, 'payment_status', 'u_yf_finvoiceproforma', 1, 16, 'payment_status', 'FL_PAYMENT_STATUS', 0, 2, '', '255', 10, 323, 2, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(), 'blockLabel' => 'LBL_BASIC_DETAILS', 'blockData' => ['label' => 'LBL_BASIC_DETAILS', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'picklistValues' => ['PLL_NOT_PAID', 'PLL_UNDERPAID', 'PLL_PAID', 'PLL_OVERPAID'], 'moduleName' => 'FInvoiceProforma'],
				[95, 3057, 'payment_sum', 'u_yf_finvoice', 1, 7, 'payment_sum', 'FL_PAYMENT_SUM', 0, 2, '', '1.0E+20', 16, 310, 2, 'NN~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->decimal('28,8'), 'blockLabel' => 'LBL_BASIC_DETAILS', 'blockData' => ['label' => 'LBL_BASIC_DETAILS', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[99, 3059, 'phone_a_extra', 'u_yf_finvoiceproforma_address', 1, 1, 'phone_a_extra', 'FL_PHONE_CUSTOM_INFORMATION', 0, 2, '', '100', 18, 325, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[99, 3060, 'phone_b_extra', 'u_yf_finvoiceproforma_address', 1, 1, 'phone_b_extra', 'FL_PHONE_CUSTOM_INFORMATION', 0, 2, '', '100', 18, 467, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoiceProforma'],
				[95, 3061, 'phone_b_extra', 'u_yf_finvoice_address', 1, 1, 'phone_b_extra', 'FL_PHONE_CUSTOM_INFORMATION', 0, 2, '', '100', 18, 466, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[95, 3062, 'phone_a_extra', 'u_yf_finvoice_address', 1, 1, 'phone_a_extra', 'FL_PHONE_CUSTOM_INFORMATION', 0, 2, '', '100', 18, 312, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'FInvoice'],
				[90, 3063, 'phone_a_extra', 'u_yf_ssingleorders_address', 1, 1, 'phone_a_extra', 'FL_PHONE_CUSTOM_INFORMATION', 0, 2, '', '100', 18, 295, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_BILLING', 'blockData' => ['label' => 'LBL_ADDRESS_BILLING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders'],
				[90, 3064, 'phone_b_extra', 'u_yf_ssingleorders_address', 1, 1, 'phone_b_extra', 'FL_PHONE_CUSTOM_INFORMATION', 0, 2, '', '100', 18, 463, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(100), 'blockLabel' => 'LBL_ADDRESS_SHIPPING', 'blockData' => ['label' => 'LBL_ADDRESS_SHIPPING', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'SSingleOrders']];
		}

		foreach ($fields as $field) {
			$moduleName = $field['moduleName'];
			$moduleId = \App\Module::getModuleId($moduleName);
			if (!$moduleId) {
				$this->log("[ERROR] Module not exists: {$moduleName}");
				continue;
			}
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if ($isExists) {
				$this->log("[INFO] Skip adding field. Module: {$moduleName}({$moduleId}); field name: {$field[2]}, field exists: {$isExists}");
				continue;
			}

			$blockInstance = false;
			$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => $field['blockData']['label'], 'tabid' => $moduleId])->scalar();
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
				\App\Log::error("No block found ({$field['blockData']['label']}) to create a field, you will need to create a field manually.
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
			if (!empty($field['picklistValues']) && (15 == $field[5] || 16 == $field[5] || 33 == $field[5])) {
				$fieldInstance->setPicklistValues($field['picklistValues']);
			}
			if (!empty($field['relatedModules']) && 10 == $field[5]) {
				$fieldInstance->setRelatedModules($field['relatedModules']);
			}
			if ('default_search_operator' === $field[6]) {
				$default = \App\Config::search('GLOBAL_SEARCH_DEFAULT_OPERATOR', 'Contain');
				$defVal = [
					'FulltextBegin' => 'PLL_FULLTEXT_BEGIN',
					'FulltextWord' => 'PLL_FULLTEXT_WORD',
					'Contain' => 'PLL_CONTAINS',
					'Begin' => 'PLL_STARTS_WITH',
					'End' => 'PLL_ENDS_WITH'
				];
				\App\Db::getInstance()->createCommand()->update('vtiger_users', ['default_search_operator' => $defVal[$default] ?? 'PLL_CONTAINS'])->execute();
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add setting fields.
	 */
	public function addSettingFields()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$settingFields = [
			['value' => [5, 'LBL_MAGENTO', 'fab fa-magento', 'LBL_MAGENTO_DESCRIPTION', 'index.php?parent=Settings&module=Magento&view=List', 13, 0, 0, null], 'blockLabel' => 'LBL_INTEGRATION'],
			['value' => [4, 'LBL_EVENT_HANDLER', 'yfi yfi-event-handlers', 'LBL_EVENT_HANDLER_DESC', 'index.php?parent=Settings&module=EventHandler&view=Index', 13, 0, 0, null], 'blockLabel' => 'LBL_SYSTEM_TOOLS'],
			['value' => [5, 'LBL_MEETING_SERVICES', 'mdi mdi-server-network', 'LBL_MEETING_SERVICES_DESCRIPTION', 'index.php?parent=Settings&module=MeetingServices&view=List', 15, 0, 0, null], 'blockLabel' => 'LBL_INTEGRATION'],
			['value' => [4, 'LBL_CONFLICT_OF_INTEREST', 'yfi-conflict-interests', 'LBL_CONFLICT_OF_INTEREST_DESCRIPTION', 'index.php?parent=Settings&module=InterestsConflict&view=Index', 15, 0, 0, null], 'blockLabel' => 'LBL_SYSTEM_TOOLS'],
			['value' => [1, 'LBL_ADMIN_ACCESS', 'yfi yfi-admin-access', 'LBL_ADMIN_ACCESS_DESCRIPTION', 'index.php?parent=Settings&module=AdminAccess&view=Index', 12, 0, 0, null], 'blockLabel' => 'LBL_USER_MANAGEMENT'],
			['value' => [8, 'LBL_MAIL_RBL', 'yfi yfi-rbl', 'LBL_MAIL_RBL_DESCRIPTION', 'index.php?parent=Settings&module=MailRbl&view=Index', 8, 0, 0, null], 'blockLabel' => 'LBL_MAIL_TOOLS'],
			['value' => [2, 'LBL_FIELDS_DEPENDENCY', 'yfi yfi-dependent-fields', 'LBL_FIELDS_DEPENDENCY_DESCRIPTION', 'index.php?parent=Settings&module=FieldsDependency&view=List', 7, 0, 0, null], 'blockLabel' => 'LBL_STUDIO'],
			['value' => [5, 'LBL_MAIL_INTEGRATION', 'yfi yfi-mail-integrator-panel', 'LBL_MAIL_INTEGRATION_DESCRIPTION', 'index.php?parent=Settings&module=MailIntegration&view=Index', 16, 0, 0, null], 'blockLabel' => 'LBL_INTEGRATION'],
			['value' => [14, 'LBL_LOGS_VIEWER', 'yfi yfi-view-logs', 'LBL_LOGS_VIEWER_DESCRIPTION', 'index.php?parent=Settings&module=Log&view=LogsViewer', 6, 0, 0, null], 'blockLabel' => 'LBL_LOGS']
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function syncPicklist()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();

		$newFieldName = 'payment_methods';
		$fieldModel = Vtiger_Field_Model::init('Vtiger', ['uitype' => 16], $newFieldName);
		if (!$db->isTableExists("vtiger_{$newFieldName}")) {
			$fieldModel->setPicklistValues(['PLL_TRANSFER', 'PLL_CASH', 'PLL_CASH_ON_DELIVERY', 'PLL_WIRE_TRANSFER', 'PLL_REDSYS', 'PLL_DOTPAY', 'PLL_PAYPAL', 'PLL_PAYPAL_EXPRESS', 'PLL_CHECK']);
		}
		$picklistWithAutomation = (new \App\Db\Query())->select(['vtiger_tab.name', 'vtiger_field.fieldname'])
			->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_field.tabid=vtiger_tab.tabid')
			->where(['fieldname' => ['fcorectinginvoice_formpayment', 'finvoice_formpayment', 'finvoicecost_formpayment', 'finvoiceproforma_formpayment', 'ssingleorders_method_payments']])
			->createCommand()->queryAllByGroup();
		foreach ($picklistWithAutomation as $moduleName => $fieldName) {
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
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
		if ((new \App\Db\Query())->from('vtiger_picklist')->where(['name' => $newFieldName])->exists() &&
			!(new \App\Db\Query())->from('vtiger_field')->where(['fieldname' => $newFieldName, 'uitype' => [15, 33]])->exists()
		) {
			\App\Db::getInstance()->createCommand()->delete('vtiger_picklist', ['name' => $newFieldName])->execute();
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function addWidgets()
	{
		$moduleName = 'Occurrences';
		$tabId = \App\Module::getModuleId($moduleName);
		if ($tabId && !(new \App\Db\Query())->select(['tabid'])->from('vtiger_widgets')->where(['tabid' => $tabId])->exists()) {
			$this->addWidget($moduleName, ['data' => ['type' => 'Summary'], 'wcol' => 1, 'sequence' => 0]);
			$this->addWidget($moduleName, ['data' => ['type' => 'Comments', 'label' => 'ModComments', 'relatedmodule' => 'ModComments', 'limit' => 5], 'wcol' => 2, 'sequence' => 2]);
			$docId = \App\Module::getModuleId('Documents');
			$this->addWidget($moduleName, ['data' => ['type' => 'RelatedModule', 'label' => 'Documents', 'relatedmodule' => 'ModComments',
				'relation_id' => (new \App\Db\Query())->select(['relation_id'])->from('vtiger_relatedlists')->where(['related_tabid' => $tabId, 'name' => 'getAttachments', 'tabid' => $docId])->scalar(),
				'relatedmodule' => $docId,
				'relatedfields' => ["{$docId}::notes_title", "{$docId}::filename", "{$docId}::ossdc_status"],
				'viewtype' => 'List', 'limit' => 5, 'action' => 0, 'actionSelect' => 0, 'no_result_text' => 0, 'switchHeader' => '-', 'filter' => '-', 'checkbox' => '-'
			], 'wcol' => 2, 'sequence' => 2]);
		}
	}

	public function addWidget(string $moduleName, array $widgetData)
	{
		if (!empty($tabId = \App\Module::getModuleId($moduleName))) {
			$exists = (new \App\Db\Query())->select(['tabid'])->from('vtiger_widgets')->where(['tabid' => $tabId, 'label' => $widgetData['data']['label'], 'type' => $widgetData['data']['type']])->exists();
			if (!$exists) {
				$widgetData['tabid'] = $tabId;
				Settings_Widgets_Module_Model::saveWidget($widgetData);
				if (isset($widgetData['sequence'], $widgetData['wcol'])) {
					\App\Db::getInstance()->createCommand()->update('vtiger_widgets',
					 ['wcol' => $widgetData['wcol'], 'sequence' => $widgetData['sequence']],
					 ['type' => $widgetData['data']['type'], 'tabid' => $tabId, 'label' => $widgetData['data']['label']])->execute();
					$this->logs[__METHOD__][$moduleName][$widgetData['data']['label']] = '[Info] Sequence saved [' . $widgetData['data']['type'] . ' in: ' . $moduleName . ']';
				}
				$this->logs[__METHOD__][$moduleName][$widgetData['data']['label']] = '[Info] Widget saved [' . $widgetData['data']['type'] . ' in: ' . $moduleName . ']';
			}
		}
	}

	private function setRelations()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
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
			['type' => 'add', 'data' => [635, 'SQuoteEnquiries', 'Contacts', 'getRelatedList', 4, 'Contacts', 0, 'ADD,SELECT', 1, 0, 0, 'RelatedTab', null]],
			['type' => 'add', 'data' => [636, 'Occurrences', 'Documents', 'getAttachments', 3, 'Documents', 0, 'ADD,SELECT', 1, 0, 0, 'RelatedTab', null]]
		];
		if ((new \App\Db\Query())->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId('OSSEmployees'), 'fieldname' => 'multicompanyid'])->exists()) {
			$ralations[] = ['type' => 'update', 'data' => [578, 'MultiCompany', 'OSSEmployees', 'getDependentsList', 1, 'OSSEmployees', 0, 'ADD,SELECT', 0, 0, 0, 'RelatedTab', 'multicompanyid'], 'where' => ['tabid' => \App\Module::getModuleId('MultiCompany'), 'related_tabid' => \App\Module::getModuleId('OSSEmployees'), 'name' => 'getRelatedList']];
		}

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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add worflows.
	 */
	public function addWorflows()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		require_once 'modules/com_vtiger_workflow/VTWorkflowManager.php';
		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		$workflowManager = new VTWorkflowManager();
		$taskManager = new VTTaskManager();
		$workflow[] = [73, 'SSingleOrders', 'Create IGDN', '[{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_COMPLETE","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":1}]', 3, null, null, 6, null, null, null, null, null, null, null];
		$workflow[] = [77, 'SSingleOrders', 'Cancel IGDN', '[{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_NEW","valuetype":"rawtext","joincondition":"or","groupjoin":"and","groupid":1},{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_PAYMENT_REVIEW","valuetype":"rawtext","joincondition":"or","groupjoin":"and","groupid":1},{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_ON_HOLD","valuetype":"rawtext","joincondition":"or","groupjoin":"and","groupid":1},{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_PROCESSING","valuetype":"rawtext","joincondition":"or","groupjoin":"and","groupid":1},{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_CLOSED","valuetype":"rawtext","joincondition":"or","groupjoin":"and","groupid":1},{"fieldname":"ssingleorders_status","operation":"has changed to","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":1}]', 4, null, null, 6, null, null, null, null, null, null, null];
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function actionMapp()
	{
		$start = microtime(true);
		$db = \App\Db::getInstance();

		$modulesForInterestsConflict = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['and', ['isentitytype' => 1], ['not', ['name' => ['OSSMailView', 'CallHistory']]]])->column();
		$actions = [
			['type' => 'add', 'name' => 'RecordCollector', 'tabsData' => [], 'permission' => 1],
			['type' => 'add', 'name' => 'MeetingUrl', 'tabsData' => [\App\Module::getModuleId('Users')], 'permission' => 1],
			['type' => 'add', 'name' => 'InterestsConflictUsers', 'tabsData' => $modulesForInterestsConflict, 'permission' => 1]
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function settingMenu()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
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
			'LBL_WORKFLOW_LIST', 'LBL_TOOLTIP_MANAGEMENT', 'LBL_SHOP_YETIFORCE'];
		$dbCommand->delete('vtiger_settings_field', ['name' => $skipMenuItemList])->execute();

		foreach ($transformedUrlMapping as $old => $link) {
			if ($dbCommand->update('vtiger_settings_field', ['linkto' => $link], ['linkto' => $old])->execute()) {
				$this->log("[Info] Update setting menu: {$link}");
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Generating the current configuration.
	 *
	 * @return void
	 */
	private function createConfigFiles()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . "\t\t|\t" . date('H:i:s'));
		\App\Cache::resetOpcache();
		clearstatcache();
		\App\Config::set('module', 'OSSMail', 'root_directory', new \Nette\PhpGenerator\PhpLiteral('ROOT_DIRECTORY . DIRECTORY_SEPARATOR'));
		\App\Config::set('module', 'OSSMail', 'plugins', ['identity_smtp', 'thunderbird_labels', 'zipdownload', 'archive', 'html5_notifier', 'advanced_search', 'contextmenu', 'yetiforce']);
		\App\Config::set('module', 'OSSMail', 'des_key', \App\Encryption::generatePassword(24));
		\App\Config::set('module', 'OSSMail', 'mail_pagesize', 30 === \App\Config::module('OSSMail', 'mail_pagesize', 30) ? 40 : 30);
		\App\Config::set('module', 'OSSMail', 'smtp_timeout', 5 === \App\Config::module('OSSMail', 'smtp_timeout', 5) ? 10 : 5);
		\App\Config::set('module', 'OSSMail', 'product_name', '');
		\App\Config::set('module', 'OSSMail', 'skin', 'elastic');
		\App\Config::set('module', 'OSSMail', 'skin_logo', '/images/null.png');
		\App\Config::set('module', 'Chat', 'MAX_LENGTH_MESSAGE', 2000);

		$breadcrumbs = null;
		if (!class_exists('\\Config\\Layout')) {
			$breadcrumbs = \App\Config::main('breadcrumbs', true);
		}

		$skip = ['module', 'component'];
		$configTemplates = 'config/ConfigTemplates.php';
		if (file_exists(__DIR__ . '/files/' . $configTemplates)) {
			copy(__DIR__ . '/files/' . $configTemplates, ROOT_DIRECTORY . '/' . $configTemplates);
			foreach (array_diff(\App\ConfigFile::TYPES, $skip) as $type) {
				(new \App\ConfigFile($type))->create();
				if ($type = 'layout' && false === $breadcrumbs) {
					\App\Config::set($type, 'breadcrumbs', $breadcrumbs);
					(new \App\ConfigFile($type))->create();
				}
			}
		}
		$dirPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Modules';
		if (!is_dir($dirPath)) {
			mkdir($dirPath, 0755, true);
		}
		foreach ((new \DirectoryIterator(__DIR__ . '/files/modules/')) as $item) {
			if ($item->isDir() && !$item->isDot()) {
				$moduleName = $item->getBasename();
				$filePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
				if (file_exists(__DIR__ . '/files/' . $filePath)) {
					if (!is_dir(ROOT_DIRECTORY . '/modules/' . $moduleName)) {
						mkdir(ROOT_DIRECTORY . '/modules/' . $moduleName, 0755, true);
					}
					copy(__DIR__ . '/files/' . $filePath, ROOT_DIRECTORY . '/' . $filePath);
					(new \App\ConfigFile('module', $moduleName))->create();
				}
			}
		}
		$configTemplates = 'config/Components/ConfigTemplates.php';
		if (file_exists(__DIR__ . '/files/' . $configTemplates)) {
			$path = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $configTemplates;
			copy(__DIR__ . '/files/' . $configTemplates, $path);
			$componentsData = require "$path";
			foreach ($componentsData as $component => $data) {
				(new \App\ConfigFile('component', $component))->create();
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	/**
	 * Stop process.
	 */
	public function stopProcess()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
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

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
		exit;
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$this->createSettingsModulesData();
		\App\Cache::clear();
		\App\Cache::resetOpcache();
		if ($this->error || false !== strpos($this->importer->logs, 'Error')) {
			$this->stopProcess();
		} else {
			$this->finishUpdate();
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
		exit;
	}

	public function finishUpdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . "\t\t|\t" . date('H:i:s'));

		$db = \App\Db::getInstance();

		\App\UserPrivilegesFile::recalculateAll();
		(new \App\BatchMethod(['method' => '\App\Fixer::baseModuleTools', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\Fixer::baseModuleActions', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\Fixer::profileField', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\UserPrivilegesFile::recalculateAll', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => 'Settings_SharingAccess_Module_Model::recalculateSharingRules', 'params' => []]))->save();
		foreach (['roundcube_cache', 'roundcube_cache_index', 'roundcube_cache_messages', 'roundcube_cache_shared', 'roundcube_cache_thread'] as $table) {
			$db->createCommand()->truncateTable($table)->execute();
		}
		$db->createCommand()->insert('yetiforce_updates', [
			'user' => \Users_Record_Model::getCurrentUserModel()->get('user_name'),
			'name' => (string) $this->modulenode->label,
			'from_version' => (string) $this->modulenode->from_version,
			'to_version' => (string) $this->modulenode->to_version,
			'result' => 1,
			'time' => date('Y-m-d H:i:s'),
		])->execute();
		$db->createCommand()->update('vtiger_version', ['current_version' => (string) $this->modulenode->to_version])->execute();
		\vtlib\Functions::recurseDelete('cache/updates/updates');
		register_shutdown_function(function () {
			$viewer = \Vtiger_Viewer::getInstance();
			$viewer->clearAllCache();
			\vtlib\Functions::recurseDelete('cache/templates_c');
		});
		\App\Cache::clear();
		\App\Cache::clearOpcache();
		\vtlib\Functions::recurseDelete('app_data/LanguagesUpdater.json');
		\vtlib\Functions::recurseDelete('app_data/SystemUpdater.json');
		\vtlib\Functions::recurseDelete('app_data/cron.php');
		\vtlib\Functions::recurseDelete('app_data/ConfReport_AllErrors.php');
		\vtlib\Functions::recurseDelete('app_data/shop.php');
		file_put_contents('cache/logs/update.log', ob_get_contents(), FILE_APPEND);
		ob_end_clean();
		echo '<div class="modal in" style="display: block;overflow-y: auto;top: 30px;"><div class="modal-dialog" style="max-width: 80%;"><div class="modal-content" style="-webkit-box-shadow: inset 2px 2px 14px 1px rgba(0,0,0,0.75);-moz-box-shadow: inset 2px 2px 14px 1px rgba(0,0,0,0.75);box-shadow: inset 2px 2px 14px 1px rgba(0,0,0,0.75);-webkit-box-shadow: 2px 2px 14px 1px rgba(0,0,0,0.75);
    -moz-box-shadow: 2px 2px 14px 1px rgba(0,0,0,0.75);box-shadow: 2px 2px 14px 1px rgba(0,0,0,0.75);"><div class="modal-header">
		<h1 class="modal-title"><span class="fas fa-thumbs-up mr-2"></span>' . \App\Language::translate('LBL__UPDATING_MODULE', 'Settings:ModuleManager') . '</h1>
		</div><div class="modal-body" style="font-size: 27px;">Successfully updated</div><div class="modal-footer">
		<a class="btn btn-success" href="' . \App\Config::main('site_URL') . '"><span class="fas fa-home mr-2"></span>' . \App\Language::translate('LBL_HOME') . '<a>
		</div></div></div></div>';

		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}
}

class Portal extends CRMEntity
{
	public $table_name = 'vtiger_portal';
	public $table_index = 'portalid';
	public $tab_name = ['vtiger_portal'];
	public $tab_name_index = ['vtiger_portal' => 'portalid'];
}
