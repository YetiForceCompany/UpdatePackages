<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (yetiforce.com)
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
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
	 * @var object
	 */
	public $modulenode;

	/**
	 * Fields to delete
	 * @var string[]
	 */
	public $filesToDelete = [];

	/**
	 * Cron list
	 * @var string[] 
	 */
	private $cronAction = [];

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
		copy(__DIR__ . '/files/vendor/yii/db/mysql/ColumnSchemaBuilder.php', ROOT_DIRECTORY . '/vendor/yii/db/mysql/ColumnSchemaBuilder.php');
		copy(__DIR__ . '/files/vendor/yii/db/ColumnSchemaBuilder.php', ROOT_DIRECTORY . '/vendor/yii/db/ColumnSchemaBuilder.php');
		copy(__DIR__ . '/files/vendor/yii/db/Schema.php', ROOT_DIRECTORY . '/vendor/yii/db/Schema.php');
		copy(__DIR__ . '/files/vtlib/Vtiger/Functions.php', ROOT_DIRECTORY . '/vtlib/Vtiger/Functions.php');
		copy(__DIR__ . '/files/vendor/yetiforce/Db/Updater.php', ROOT_DIRECTORY . '/vendor/yetiforce/Db/Updater.php');
		copy(__DIR__ . '/files/vendor/yetiforce/Db/Fixer.php', ROOT_DIRECTORY . '/vendor/yetiforce/Db/Fixer.php');
		include_once __DIR__ . '/DbType.php';
		include_once __DIR__ . '/DbImporter.php';
	}

	/**
	 * Preupdate
	 */
	public function preupdate()
	{
		return true;
	}

	/**
	 * Update
	 */
	public function update()
	{
		$this->addRecords();
		$this->updatePicklistType();

		$db = App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		$this->importer = new DbImporter();
		$this->updateDbSchema();

		$this->importer->loadFiles(__DIR__ . '/dbscheme');
		$this->importer->updateScheme();
		$this->importer->postUpdate();
		$this->importer->logs(false);
		$this->importer->refreshSchema();
		\App\Db::getInstance()->createCommand()->dropIndex('github_id', 'u_yf_github')->execute();
		$db->createCommand()->checkIntegrity(true)->execute();

		$this->updateData();

		$moduleBaseInstance = vtlib\Module::getInstance('WSAPP');
		if ($moduleBaseInstance) {
			$moduleBaseInstance->delete();
		}
		$moduleBaseInstance = vtlib\Module::getInstance('AJAXChat');
		if ($moduleBaseInstance) {
			$moduleBaseInstance->delete();
		}
		$module = new \vtlib\Module();
		$module->name = 'Chat';
		$module->tablabel = 'Chat';
		$module->isentitytype = false;
		$module->version = '0';
		$module->presence = '0';
		$module->ownedby = '0';
		$module->customized = '0';
		$module->type = '0';
		$module->save();
		$db->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => 'Chat'])->execute();
		mkdir(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html/layouts/resources/Logo', 0777, true);
		\vtlib\Functions::recurseCopy('storage/Logo', 'public_html/layouts/resources/Logo');
	}

	/**
	 * Postupdate
	 */
	public function postupdate()
	{
		foreach ($this->cronAction as $cronName) {
			$cron = \vtlib\Cron::getInstance($cronName);
			if (!empty($cron)) {
				$cron->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
			}
		}
		if (function_exists('opcache_reset')) {
			opcache_reset();
		}
		return true;
	}

	/**
	 * Update
	 */
	public function updateDbSchema()
	{
		$this->dropTables();
		$this->dropColumns();
		$this->renameTables();
		$this->renameColumns();
		$this->addFields();
	}

	/**
	 * delete tables
	 */
	public function dropTables()
	{
		$tables = [
			'chat_bans',
			'chat_invitations',
			'chat_messages',
			'chat_online',
			'vtiger_home_layout',
			'vtiger_homedashbd',
			'vtiger_homedefault',
			'vtiger_homemodule',
			'vtiger_homemoduleflds',
			'vtiger_homereportchart',
			'vtiger_homerss',
			'vtiger_homestuff',
			'vtiger_homestuff_seq',
			'vtiger_smsnotifier_servers',
			'vtiger_tracker',
			'vtiger_wsapp',
			'vtiger_wsapp_handlerdetails',
			'vtiger_wsapp_queuerecords',
			'vtiger_wsapp_recordmapping',
			'vtiger_wsapp_sync_state',
		];
		$this->importer->dropTable($tables);
		$db = App\Db::getInstance();
		$table = 'vtiger_smsnotifier_status';
		if ($db->isTableExists($table) && !is_null($db->getSchema()->getTableSchema($table)->getColumn('smsmessageid'))) {
			$this->importer->dropTable($table);
		}
	}

	/**
	 * alter columns
	 */
	public function dropColumns()
	{
		$this->importer->dropColumns([
			['vtiger_fixed_assets_fuel_type', 'picklist_valueid'],
			['vtiger_users', 'user_hash'],
		]);
	}

	/**
	 * Rename tables
	 */
	public function renameTables()
	{
		$this->importer->renameTables([
			['u_yf_mail_address_boock', 'u_yf_mail_address_book']
		]);
	}

	/**
	 * Changing name of columns
	 */
	public function renameColumns()
	{
		$this->importer->renameColumns([
			['s_yf_mail_smtp', 'replay_to', 'reply_to'],
			['vtiger_smsnotifier', 'status', 'smsnotifier_status'],
		]);
	}

	/**
	 * Update picklist type
	 */
	public function updatePicklistType()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		\App\Db\Updater::addRoleToPicklist([
			'osstimecontrol_status',
			'lin_status',
			'lout_status',
			'reservations_status',
			'squoteenquiries_status',
			'srequirementscards_status',
			'scalculations_status',
			'squotes_status',
			'ssingleorders_status',
			'srecurringorders_status',
			'storage_status',
			'ssalesprocesses_status',
			'igrn_status',
			'igdn_status',
			'iidn_status',
			'igin_status',
			'ipreorder_status',
			'istdn_status',
			'istn_status',
			'istrn_status',
			'knowledgebase_status',
			'igrnc_status',
			'igdnc_status',
			'svendorenquiries_status'
		]);
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function addFields()
	{

//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];
		$fields = [
			[43, 2604, 'parentid', 'vtiger_project', 1, 10, 'parentid', 'FL_MEMBER_OF', 1, 2, '', 100, 10, 108, 1, 'I~O', 1, 0, 'BAS', 0, '', 0, '', NULL, 'int(10)', 'LBL_CUSTOM_INFORMATION', [], ['Project'], 'Project'],
			[83, 2605, 'is_mandatory', 'u_yf_announcement', 1, 56, 'is_mandatory', 'FL_IS_MANDATORY', 1, 2, '', 100, 5, 258, 1, 'C~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 'smallint(5)', 'LBL_ANNOUNCEMENTS_INFORMATION', [], [], 'Announcements'],
			[45, 2606, 'smsnotifier_status', 'vtiger_smsnotifier', 1, 16, 'smsnotifier_status', 'FL_STATUS', 1, 2, '', 100, 9, 110, 10, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 'varchar(255)', 'LBL_SMSNOTIFIER_INFORMATION', ['PLL_UNDEFINED', 'PLL_DELIVERED', 'PLL_FAILED'], [], 'SMSNotifier']
		];
		foreach ($fields as $field) {
			$moduleId = \vtlib\Functions::getModuleId($field[28]);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists) {
				continue;
			}
			$moduleInstance = \vtlib\Module::getInstance($field[28]);
			$blockInstance = \vtlib\Block::getInstance($field[25], $moduleInstance);
			if (!$blockInstance) {
				App\Log::error("No block found to create a field, you will need to create a field manually. Module: {$field[28]}, field name: {$field[6]}, field label: {$field[7]}");
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
			if ($field[26] && ($field[5] == 15 || $field[5] == 16 || $field[5] == 33 )) {
				$fieldInstance->setPicklistValues($field[26]);
			}
			if ($field[27] && $field[5] == 10) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}
		$importers = new \App\Db\Importers\Base();
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$dbCommand->alterColumn('vtiger_smsnotifier_status', 'smsnotifier_statusid', $importers->integer(10)->autoIncrement()->notNull())->execute();
		$dbCommand->alterColumn('vtiger_smsnotifier_status', 'sortorderid', $importers->smallInteger(5)->defaultValue(0))->execute();
	}

	/**
	 * update data
	 */
	public function updateData()
	{
		$this->updateRows();
		$this->deleteRows();
		$this->insertRows();
		$this->updateCron();
		App\Db\Fixer::defOrgField();
		App\Db\Fixer::profileField();
	}

	/**
	 * update rows
	 */
	public function updateRows()
	{
		$db = \App\Db::getInstance();
		$db->createCommand("UPDATE a_yf_pdf SET body_content = REPLACE( body_content, '$(reletedRecord :', '$(relatedRecord :');")->execute();
		$db->createCommand("UPDATE a_yf_pdf SET header_content = REPLACE( header_content, '$(reletedRecord :', '$(relatedRecord :');")->execute();
		$db->createCommand("UPDATE a_yf_pdf SET footer_content = REPLACE( footer_content, '$(reletedRecord :', '$(relatedRecord :');")->execute();
		$db->createCommand("UPDATE com_vtiger_workflowtasks SET task = REPLACE( task, 'HeldDesk', 'HelpDesk');")->execute();
		$db->createCommand("UPDATE com_vtiger_workflowtasks_entitymethod SET method_name = REPLACE( method_name, 'HeldDesk', 'HelpDesk');")->execute();
		$db->createCommand("UPDATE com_vtiger_workflowtasks_entitymethod SET function_name = REPLACE( function_name, 'HeldDesk', 'HelpDesk');")->execute();
		\App\Db\Updater::batchUpdate([
			['u_yf_squotes_invfield', ['colspan' => 25], ['columnname' => 'name']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'qty']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'discount']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'margin']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'price']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'total']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'purchase']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'tax']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'gross']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'net']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'subunit']],
			['u_yf_squotes_invfield', ['colspan' => 6], ['columnname' => 'unit']],
			['vtiger_apiaddress', ['name' => 'min_length'], ['name' => 'min_lenght']],
			['vtiger_customview', ['viewname' => 'LBL_UNREAD'], ['viewname' => 'All', 'entitytype' => 'Notification']],
			['vtiger_field', ['defaultvalue' => 'This Month'], ['columnname' => 'activity_view', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_field', ['defaultvalue' => 'yyyy-mm-dd'], ['columnname' => 'date_format', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_field', ['defaultvalue' => '123,456,789'], ['columnname' => 'currency_grouping_pattern', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_field', ['defaultvalue' => '.'], ['columnname' => 'currency_decimal_separator', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_field', ['defaultvalue' => '1.0$'], ['columnname' => 'currency_symbol_placement', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_field', ['defaultvalue' => '15 Minutes'], ['columnname' => 'reminder_interval', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_field', ['defaultvalue' => '30'], ['columnname' => 'callduration', 'tablename' => 'vtiger_users', 'defaultvalue' => '5']],
			['vtiger_field', ['defaultvalue' => '30'], ['columnname' => 'othereventduration', 'tablename' => 'vtiger_users', 'defaultvalue' => '5']],
			['vtiger_field', ['defaultvalue' => 'PLL_PLANNED'], ['columnname' => 'defaulteventstatus', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_field', ['defaultvalue' => 'Meeting'], ['columnname' => 'defaultactivitytype', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_field', ['defaultvalue' => '16:00'], ['columnname' => 'end_hour', 'tablename' => 'vtiger_users', 'defaultvalue' => '']],
			['vtiger_timecontrol_type', ['timecontrol_type' => 'PLL_HOLIDAY_TIME'], ['timecontrol_type' => 'PLL_HOLIDAY']],
			['vtiger_osstimecontrol', ['timecontrol_type' => 'PLL_HOLIDAY_TIME'], ['timecontrol_type' => 'PLL_HOLIDAY']],
			['vtiger_smsnotifier_status', ['presence' => '0'], ['presence' => '1']],
		]);
	}

	/**
	 * update rows
	 */
	public function insertRows()
	{
		$data = [
			['vtiger_eventhandlers', ['event_name' => 'EntityAfterSave', 'handler_class' => 'Vtiger_MultiReferenceUpdater_Handler', 'is_active' => 1, 'include_modules' => '', 'exclude_modules' => '', 'priority' => 5, 'owner_id' => 0]],
			['vtiger_eventhandlers', ['event_name' => 'EntityAfterSave', 'handler_class' => 'Vtiger_Attachments_Handler', 'is_active' => 1, 'include_modules' => '', 'exclude_modules' => '', 'priority' => 5, 'owner_id' => 0]],
			['vtiger_language', ['name' => 'Portuguese', 'prefix' => 'pt_br', 'label' => 'Brazilian Portuguese', 'lastupdated' => '2017-06-05 11:20:40', 'sequence' => NULL, 'isdefault' => 0, 'active' => 1,]],
			['vtiger_language', ['name' => 'Spanish', 'prefix' => 'es_es', 'label' => 'ES Spanish', 'lastupdated' => '2017-03-11 00:00:00', 'sequence' => NULL, 'isdefault' => 0, 'active' => 1,]],
			['vtiger_links', ['tabid' => App\Module::getModuleId('SSalesProcesses'), 'linktype' => 'DASHBOARDWIDGET', 'linklabel' => 'DW_TEAMS_ESTIMATED_SALES', 'linkurl' => 'index.php?module=SSalesProcesses&view=ShowWidget&name=TeamsEstimatedSales', 'linkicon' => '', 'sequence' => 0,]],
			['vtiger_links', ['tabid' => App\Module::getModuleId('SSalesProcesses'), 'linktype' => 'DASHBOARDWIDGET', 'linklabel' => 'DW_ACTUAL_SALES_OF_TEAM', 'linkurl' => 'index.php?module=SSalesProcesses&view=ShowWidget&name=ActualSalesOfTeam', 'linkicon' => '', 'sequence' => 0,]],
			['vtiger_settings_field', ['blockid' => 5, 'name' => 'LBL_PBX', 'iconpath' => 'adminIcon-pbx-manager', 'description' => 'LBL_PBX_DESCRIPTION', 'linkto' => 'index.php?module=PBX&parent=Settings&view=List', 'sequence' => 3, 'active' => 0, 'pinned' => 0, 'admin_access' => NULL,]],
			['vtiger_settings_field', ['blockid' => 5, 'name' => 'LBL_SMSNOTIFIER', 'iconpath' => 'userIcon-SMSNotifier', 'description' => 'LBL_SMSNOTIFIER_DESCRIPTION', 'linkto' => 'index.php?module=SMSNotifier&parent=Settings&view=List', 'sequence' => 12, 'active' => 0, 'pinned' => 0, 'admin_access' => NULL,]],
			['vtiger_ws_fieldtype', ['uitype' => 311, 'fieldtype' => 'multiImage',]],
		];

		$cvid = (new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->where(['viewname' => 'LBL_UNREAD', 'entitytype' => 'Notification'])->scalar();
		if ($cvid) {
			$data[] = ['vtiger_cvadvfilter', ['cvid' => $cvid, 'columnindex' => 0, 'columnname' => 'u_yf_notification:notification_status:notification_status:Notification_FL_STATUS:V', 'comparator' => 'e', 'value' => 'PLL_UNREAD', 'groupid' => 1, 'column_condition' => '']];
			$data[] = ['vtiger_cvadvfilter_grouping', ['groupid' => 1, 'cvid' => $cvid, 'group_condition' => 'and', 'condition_expression' => ' 0 ']];
			$data[] = ['vtiger_cvadvfilter_grouping', ['groupid' => 2, 'cvid' => $cvid, 'group_condition' => '', 'condition_expression' => ' 0 ']];
		}


		\App\Db\Updater::batchInsert($data);
	}

	public function deleteRows()
	{
		$data = [
			['vtiger_picklist', ['name' => 'fixed_assets_fuel_type']],
			['vtiger_relatedlists', ['tabid' => 4, 'label' => 'OSSPasswords']],
			['vtiger_ws_operation', ['handler_method' => 'vtws_login']],
			['vtiger_ws_operation', ['handler_method' => 'vtws_sync']],
			['vtiger_ws_operation', ['handler_method' => 'vtws_query']],
			['vtiger_ws_operation', ['handler_method' => 'vtws_logout']],
			['vtiger_ws_operation', ['handler_method' => 'vtws_getchallenge']],
			['vtiger_ws_operation', ['handler_method' => 'vtws_extendSession']],
			['vtiger_ws_operation', ['handler_method' => 'wsapp_register']],
			['vtiger_ws_operation', ['handler_method' => 'wsapp_deregister']],
			['vtiger_ws_operation', ['handler_method' => 'wsapp_get']],
			['vtiger_ws_operation', ['handler_method' => 'wsapp_put']],
			['vtiger_ws_operation', ['handler_method' => 'wsapp_map']],
		];
		\App\Db\Updater::batchDelete($data);
	}

	/**
	 * Cron data
	 * @param int $index
	 * @return array
	 */
	private function updateCron()
	{
		\App\Db\Updater::cron([
			['type' => 'add', 'data' => ['LBL_BROWSING_HISTORY', 'cron/BrowsingHistory.php', 86400, NULL, NULL, 1, 'Vtiger', 29, NULL]],
			['type' => 'add', 'data' => ['LBL_BATCH_PROCESSES', 'cron/BatchProcesses.php', 600, NULL, NULL, 1, 'Vtiger', 30, NULL]],
			['type' => 'add', 'data' => ['LBL_CLEAR_ATTACHMENTS_TABLE', 'cron/Attachments.php', 86400, NULL, NULL, 1, 'Vtiger', 27, NULL]],
			['type' => 'add', 'data' => ['LBL_SMSNOTIFIER', 'modules/SMSNotifier/cron/SMSNotifier.php', 300, NULL, NULL, 1, 'SMSNotifier', 27, NULL]],
			['type' => 'add', 'data' => ['LBK_SYSTEM_WARNINGS', 'cron/SystemWarnings.php', 86400, NULL, NULL, 1, 'Vtiger', 31, NULL]],
		]);
	}

	private function addRecords()
	{
		$db = \App\Db::getInstance();

		$record = Vtiger_Record_Model::getCleanInstance('EmailTemplates');
		$record->set('name', 'System warnings');
		$record->set('assigned_user_id', \App\User::getCurrentUserId());
		$record->set('email_template_type', 'PLL_RECORD');
		$record->set('module_name', 'Users');
		$record->set('subject', 'System warnings');
		$record->set('content', '$(params : warnings)$');
		$record->set('email_template_priority', 7);
		$record->save();
		$db->createCommand()
			->update('u_yf_emailtemplates', [
				'sys_name' => 'SystemWarnings',
				], ['emailtemplatesid' => $record->getId()])
			->execute();
	}
}
