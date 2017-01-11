<?php

/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @license https://yetiforce.com/en/implementer/license.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class YetiForceUpdate
{

	public $package;
	public $modulenode;
	public $return = true;
	public $userEntity = [];
	private $cron = [];
	public $filesToDelete = [
		'backup/.htaccess',
		'backup/index.html',
		'cache/backup/.htaccess',
		'cron/AddressBoock.php',
		'cron/backup.service',
		'cron/modules/com_vtiger_workflow/com_vtiger_workflow.service',
		'cron/modules/Import/ScheduledImport.service',
		'cron/modules/Reports/ScheduleReports.service',
		'cron/MultiReference.service',
		'cron/SendReminder.service',
		'include/events/include.inc',
		'include/events/SqlResultIterator.inc',
		'include/events/VTBatchData.inc',
		'include/events/VTBatchEventTrigger.inc',
		'include/events/VTEntityData.inc',
		'include/events/VTEntityDelta.php',
		'include/events/VTEntityType.inc',
		'include/events/VTEventHandler.inc',
		'include/events/VTEventsManager.inc',
		'include/events/VTEventTrigger.inc',
		'include/events/VTWSEntityType.inc',
		'include/fields/Email.php',
		'include/fields/File.php',
		'include/fields/Owner.php',
		'include/fields/Picklist.php',
		'include/fields/RecordNumber.php',
		'include/Language.php',
		'include/ListView/ListViewController.php',
		'include/ListView/ListViewSession.php',
		'include/Modules.php',
		'include/QueryGenerator/QueryGenerator.php',
		'include/Record.php',
		'include/utils/encryption.php',
		'include/utils/Icon.php',
		'include/utils/Json.php',
		'include/Webservices/LineItem',
		'include/Webservices/RelatedModuleMeta.php',
		'include/Webservices/Relation.php',
		'languages/en_us/Settings/BackUp.php',
		'languages/fr_fr/Settings/BackUp.php',
		'languages/pl_pl/Settings/BackUp.php',
		'languages/ru_ru/Settings/BackUp.php',
		'layouts/basic/modules/Calendar/MassEditForm.tpl',
		'layouts/basic/modules/Contacts/ModuleSummaryView.tpl',
		'layouts/basic/modules/HelpDesk/ModuleSummaryView.tpl',
		'layouts/basic/modules/Leads/ModuleSummaryView.tpl',
		'layouts/basic/modules/LettersIn/ModuleSummaryView.tpl',
		'layouts/basic/modules/LettersOut/ModuleSummaryView.tpl',
		'layouts/basic/modules/Notification/CreateNotificationModal.tpl',
		'layouts/basic/modules/OSSEmployees/ModuleSummaryView.tpl',
		'layouts/basic/modules/OSSMailView/ModuleSummaryView.tpl',
		'layouts/basic/modules/OSSTimeControl/ModuleSummaryView.tpl',
		'layouts/basic/modules/ProjectTask/ModuleSummaryView.tpl',
		'layouts/basic/modules/Reservations/ModuleSummaryView.tpl',
		'layouts/basic/modules/Settings/BackUp/Index.tpl',
		'layouts/basic/modules/Settings/BackUp/resources/BackUp.js',
		'layouts/basic/modules/Settings/BackUp/resources/BackUp.min.js',
		'layouts/basic/modules/Settings/BruteForce/resources/BruteForce.js',
		'layouts/basic/modules/Settings/BruteForce/resources/BruteForce.min.js',
		'layouts/basic/modules/Settings/BruteForce/Show.tpl',
		'layouts/basic/modules/Settings/Vtiger/TaxIndex.tpl',
		'layouts/basic/modules/Vtiger/dashboards/TagCloud.tpl',
		'layouts/basic/modules/Vtiger/dashboards/TagCloudContents.tpl',
		'layouts/basic/modules/Vtiger/ModuleSummaryView.tpl',
		'layouts/basic/modules/Vtiger/ShowTagCloud.tpl',
		'layouts/basic/modules/Vtiger/ShowTagCloudTop.tpl',
		'layouts/basic/modules/Vtiger/ShowThreadComments.tpl',
		'layouts/basic/modules/Vtiger/TagCloudResults.tpl',
		'layouts/basic/modules/Vtiger/uitypes/CurrencyList.tpl',
		'layouts/basic/modules/Vtiger/uitypes/FileLocationType.tpl',
		'layouts/basic/modules/Vtiger/uitypes/InventoryLimit.tpl',
		'layouts/basic/modules/Vtiger/uitypes/InventoryLimitSearchView.tpl',
		'layouts/basic/modules/Vtiger/uitypes/Languages.tpl',
		'layouts/basic/modules/Vtiger/uitypes/LanguagesFieldSearchView.tpl',
		'layouts/basic/modules/Vtiger/uitypes/Pos.tpl',
		'layouts/basic/modules/Vtiger/uitypes/ProductTax.tpl',
		'layouts/basic/modules/Vtiger/uitypes/TaxesFieldSearchView.tpl',
		'layouts/basic/modules/Vtiger/uitypes/UserRole.tpl',
		'libraries/dhtmlxGantt',
		'libraries/freetag/freetag.class.php',
		'libraries/freetag/license.txt',
		'libraries/jquery/ckeditor/plugins/glyphicons',
		'libraries/jquery/datatables/extensions/AutoFill/examples',
		'libraries/jquery/datatables/extensions/ColReorder/examples',
		'libraries/jquery/datatables/extensions/ColVis/examples',
		'libraries/jquery/datatables/extensions/FixedColumns/examples',
		'libraries/jquery/datatables/extensions/FixedHeader/examples',
		'libraries/jquery/datatables/extensions/KeyTable/examples',
		'libraries/jquery/datatables/extensions/Responsive/examples',
		'libraries/jquery/datatables/extensions/Scroller/examples',
		'libraries/jquery/datatables/extensions/TableTools/examples',
		'libraries/jquery/jquery.tagcloud.js',
		'libraries/jquery/timepicker/index.html',
		'libraries/magpierss/extlib/Snoopy.class.inc',
		'libraries/magpierss/rss_cache.inc',
		'libraries/magpierss/rss_fetch.inc',
		'libraries/magpierss/rss_parse.inc',
		'libraries/magpierss/rss_utils.inc',
		'modules/com_vtiger_workflow/edittask.php',
		'modules/com_vtiger_workflow/expression_engine/include.inc',
		'modules/com_vtiger_workflow/expression_engine/VTExpressionEvaluater.inc',
		'modules/com_vtiger_workflow/expression_engine/VTExpressionsManager.inc',
		'modules/com_vtiger_workflow/expression_engine/VTParser.inc',
		'modules/com_vtiger_workflow/expression_engine/VTTokenizer.inc',
		'modules/com_vtiger_workflow/include.inc',
		'modules/com_vtiger_workflow/tasks/VTAddressBookTask.inc',
		'modules/com_vtiger_workflow/tasks/VTCreateEntityTask.inc',
		'modules/com_vtiger_workflow/tasks/VTCreateEventTask.inc',
		'modules/com_vtiger_workflow/tasks/VTCreateTodoTask.inc',
		'modules/com_vtiger_workflow/tasks/VTDummyTask.inc',
		'modules/com_vtiger_workflow/tasks/VTEmailTask.inc',
		'modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.inc',
		'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc',
		'modules/com_vtiger_workflow/tasks/VTSendNotificationTask.inc',
		'modules/com_vtiger_workflow/tasks/VTSendPdf.inc',
		'modules/com_vtiger_workflow/tasks/VTSMSTask.inc',
		'modules/com_vtiger_workflow/tasks/VTUpdateCalendarDates.inc',
		'modules/com_vtiger_workflow/tasks/VTUpdateClosedTime.inc',
		'modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.inc',
		'modules/com_vtiger_workflow/tasks/VTUpdateRelatedFieldTask.inc',
		'modules/com_vtiger_workflow/tasks/VTUpdateWorkTime.inc',
		'modules/com_vtiger_workflow/tasks/VTWatchdog.inc',
		'modules/com_vtiger_workflow/VTConditionalExpression.inc',
		'modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc',
		'modules/com_vtiger_workflow/VTEntityCache.inc',
		'modules/com_vtiger_workflow/VTEntityMethodManager.inc',
		'modules/com_vtiger_workflow/VTEventHandler.inc',
		'modules/com_vtiger_workflow/VTJsonCondition.inc',
		'modules/com_vtiger_workflow/VTSimpleTemplate.inc',
		'modules/com_vtiger_workflow/VTTaskManager.inc',
		'modules/com_vtiger_workflow/VTTaskQueue.inc',
		'modules/com_vtiger_workflow/VTWorkflowApplication.inc',
		'modules/com_vtiger_workflow/VTWorkflowManager.inc',
		'modules/com_vtiger_workflow/VTWorkflowTemplateManager.inc',
		'modules/com_vtiger_workflow/WorkflowScheduler.inc',
		'modules/Contacts/actions/SaveAjax.php',
		'modules/Emails/actions/BasicAjax.php',
		'modules/Emails/class.phpmailer.php',
		'modules/Emails/class.smtp.php',
		'modules/Emails/models/Module.php',
		'modules/HelpDesk/handlers/HelpDeskHandler.php',
		'modules/ModComments/handlers/ModCommentsHandler.php',
		'modules/ModTracker/ModTrackerUtils.php',
		'modules/Notification/views/CreateNotificationModal.php',
		'modules/Notification/views/NotificationsList.php',
		'modules/OSSMailScanner/actions/ImportMail.php',
		'modules/Products/actions/MassSave.php',
		'modules/Services/actions/MassSave.php',
		'modules/Settings/BackUp/actions/Backup.php',
		'modules/Settings/BackUp/actions/Pagination.php',
		'modules/Settings/BackUp/actions/SaveAjax.php',
		'modules/Settings/BackUp/models/Module.php',
		'modules/Settings/BackUp/views/Index.php',
		'modules/Settings/BruteForce/actions/SaveConfig.php',
		'modules/Settings/BruteForce/actions/UnBlock.php',
		'modules/Settings/BruteForce/views/Show.php',
		'modules/Settings/Notifications/actions/Delete.php',
		'modules/Settings/Notifications/models/Module.php',
		'modules/Settings/Notifications/models/Record.php',
		'modules/Settings/Notifications/views/CreateNotification.php',
		'modules/Settings/Notifications/views/List.php',
		'modules/Settings/Notifications/views/ListContent.php',
		'modules/Settings/Vtiger/models/TaxRecord.php',
		'modules/Settings/Vtiger/views/TaxAjax.php',
		'modules/Settings/Vtiger/views/TaxIndex.php',
		'modules/Users/actions/IndexAjax.php',
		'modules/Users/handlers/LogoutHandler.php',
		'modules/Vtiger/actions/TagCloud.php',
		'modules/Vtiger/dashboards/TagCloud.php',
		'modules/Vtiger/models/Tag.php',
		'modules/Vtiger/models/TrackRecord.php',
		'modules/Vtiger/uitypes/ProductTax.php',
		'modules/Vtiger/views/ShowTagCloud.php',
		'modules/Vtiger/views/ShowTagCloudTop.php',
		'modules/Vtiger/views/TagCloudSearchAjax.php',
		'modules/WSAPP/WSAPPHandler.php',
		'vendor/yii/log/DbTarget.php',
		'vendor/yii/log/EmailTarget.php',
		'vendor/yii/log/migrations/m141106_185632_log_init.php',
		'vendor/yii/log/migrations/schema-mssql.sql',
		'vendor/yii/log/migrations/schema-mysql.sql',
		'vendor/yii/log/migrations/schema-oci.sql',
		'vendor/yii/log/migrations/schema-pgsql.sql',
		'vendor/yii/log/migrations/schema-sqlite.sql',
		'vendor/yii/log/SyslogTarget.php',
		'vtlib/Vtiger/Event.php',
		'vendor/yetiforce/DB.php',
		'tests/travis.php.ini',
		'modules/Emails/class.smtp.php',
		'modules/Emails/class.phpmailer.php',
		'modules/Emails/models/Mailer.php',
		'vtlib/Vtiger/Mailer.php',
		'modules/Vtiger/helpers/TextParser.php',
		'modules/Import/models/Config.php',
		'modules/com_vtiger_workflow/VTEmailRecipientsTemplate.php',
		'modules/com_vtiger_workflow/VTSimpleTemplate.php',
		'modules/EmailTemplates/actions/Delete.php',
		'modules/EmailTemplates/actions/DeleteAjax.php',
		'modules/EmailTemplates/actions/MassDelete.phpv',
		'modules/EmailTemplates/actions/Save.php',
		'modules/EmailTemplates/models/DetailView.php',
		'modules/EmailTemplates/models/Field.php',
		'modules/EmailTemplates/models/ListView.php',
		'modules/EmailTemplates/models/Module.php',
		'modules/EmailTemplates/models/Record.php',
		'modules/EmailTemplates/views/Detail.php',
		'modules/EmailTemplates/views/Edit.php',
		'modules/EmailTemplates/views/List.php',
		'modules/EmailTemplates/views/ListAjax.php',
		'layouts/basic/modules/Settings/Mail/ListViewHeader.tpl',
		'layouts/basic/modules/Settings/MailSmtp/ListViewContents.tpl',
		'modules/Emails/actions/CheckServerInfo.php',
		'modules/Emails/actions/DownloadFile.php',
		'modules/Emails/mail.php',
		'modules/Emails/views/List.php',
		'modules/Emails/views/MassSaveAjax.php',
		'layouts/basic/modules/Settings/MailSmtp/resources/List.js',
		'layouts/basic/modules/Settings/MailSmtp/resources/List.min.js',
		'modules/Settings/MailSmtp/models/ListView.php',
		'modules/Settings/MailSmtp/views/Create.php',
		'layouts/basic/modules/Settings/MailSmtp/Create.tpl',
		'layouts/basic/modules/OSSMail/SendMailModal.tpl',
		'modules/OSSMail/views/SendMailModal.php',
		'modules/Settings/Mail/actions/GetDownload.php',
		'libraries/jquery/ZeroClipboard/ZeroClipboard.js',
		'libraries/jquery/ZeroClipboard/ZeroClipboard.swf',
		'modules/EmailTemplates/actions/MassDelete.php',
	];

	public function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	public function preupdate()
	{
		return true;
	}

	public function update()
	{
		$db = \PearDatabase::getInstance();
		$db->query('SET FOREIGN_KEY_CHECKS = 0;');
		$this->addModule('EmailTemplates');
		$this->setTablesScheme($this->getTablesAction(1));
		$this->setAlterTables($this->getAlterTables(1));
		$this->updatePack();
		$this->cron($this->getCronData(1));
		$this->updateConfigurationFiles();
		$this->updateMenu();
		$this->updateSettingMenu();
		$this->setTablesScheme($this->getTablesAction(4));
		$this->getUserEntity();
		$db->query('SET FOREIGN_KEY_CHECKS = 1;');
	}

	public function getUserEntity()
	{
		if ($this->userEntity) {
			return $this->userEntity;
		}
		if (class_exists('\includes\Modules')) {
			return $this->userEntity = \includes\Modules::getEntityInfo('Users');
		} else {
			return $this->userEntity = \App\Module::getEntityInfo('Users');
		}
	}

	public function postupdate()
	{
		$result = true;
		$modulenode = $this->modulenode;

		$db = \PearDatabase::getInstance();
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
		if (class_exists('getUserProfile')) {
			$result = $db->query('SELECT id FROM vtiger_users WHERE deleted = 0');
			while ($userId = $db->getSingleValue($result)) {
				$this->createUserPrivilegesFileTest($userId);
			}
		}
//		\App\Cache::init();
		foreach ($this->cron as $cronName) {
			$cron = \vtlib\Cron::getInstance($cronName);
			if (!empty($cron)) {
				$cron->updateStatus(\vtlib\Cron::$STATUS_ENABLED);
			}
		}
		exit(header('Location: ' . AppConfig::main('site_URL') . 'cache/updates/initFinal.php'));
	}

	private function getCronData($index)
	{
		$crons = [];
		switch ($index) {
			case 1:
				$crons = [
						['type' => 'remove', 'data' => ['Backup', 'cron/backup.service', 43200, NULL, NULL, 0, 'BackUp', 11, NULL]]
				];
				break;
			case 2:
				$crons = [
						['type' => 'add', 'data' => ['LBL_HANDLER_UPDATER', 'cron/HandlerUpdater.php', 60, null, null, 1, 'Vtiger', 2, null]],
						['type' => 'add', 'data' => ['LBL_MAILER', 'cron/Mailer.php', 300, NULL, NULL, 1, 'Vtiger', 8, NULL]]
				];
				break;
			case 3:
				$crons = [
						['type' => 'add', 'data' => ['LBL_CACHE', 'cron/Cache.php', 86400, NULL, NULL, 1, 'Vtiger', 25, NULL]]
				];
				break;
			default:
				break;
		}
		return $crons;
	}

	private function cron($crons = [])
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \PearDatabase::getInstance();
		if ($crons) {
			foreach ($crons as $cron) {
				if (empty($cron)) {
					continue;
				}
				$cronData = $cron['data'];
				$result = $db->pquery('SELECT 1 FROM `vtiger_cron_task` WHERE name = ? AND handler_file = ?;
', [$cronData[0], $cronData[1]]);
				if (!$db->getRowCount($result) && $cron['type'] === 'add') {
					\vtlib\Cron::register($cronData[0], $cronData[1], $cronData[2], $cronData[6], $cronData[5], 0, $cronData[8]);
					$this->cron[] = $cronData[0];
				} elseif ($db->getRowCount($result) && $cron['type'] === 'remove') {
					\vtlib\Cron::deregister($cronData[0]);
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function updateSettingMenu()
	{
		$db = PearDatabase::getInstance();

		$fieldsToDelete = [
				['LBL_SECURITY_MANAGEMENT', 'Backup', 'adminIcon-backup', 'LBL_BACKUP_DESCRIPTION', 'index.php?parent = Settings&module = BackUp&view = Index', '4', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_TYPE_NOTIFICATIONS', 'adminIcon-TypeNotification', 'LBL_TYPE_NOTIFICATIONS_DESCRIPTION', 'index.php?module = Notifications&view = List&parent = Settings', '12', '0', '0']
		];
		foreach ($fieldsToDelete as $row) {
			$result = $db->pquery('SELECT fieldid FROM `vtiger_settings_field` INNER JOIN vtiger_settings_blocks ON vtiger_settings_blocks.blockid = vtiger_settings_field.blockid WHERE vtiger_settings_field.`name` = ? AND vtiger_settings_blocks.label = ?', [$row[1], $row[0]]);
			if ($result->rowCount()) {
				$db->delete('vtiger_settings_field', 'fieldid = ?', [$db->getSingleValue($result)]);
			}
		}
		$menu = [
				['LBL_USER_MANAGEMENT', 'LBL_USERS', 'adminIcon-user', 'LBL_USER_DESCRIPTION', 'index.php?module=Users&parent=Settings&view=List', '1', '0', '1'],
				['LBL_USER_MANAGEMENT', 'LBL_ROLES', 'adminIcon-roles', 'LBL_ROLE_DESCRIPTION', 'index.php?module=Roles&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_PROFILES', 'adminIcon-profiles', 'LBL_PROFILE_DESCRIPTION', 'index.php?module=Profiles&parent=Settings&view=List', '3', '0', '0'],
				['LBL_USER_MANAGEMENT', 'USERGROUPLIST', 'adminIcon-groups', 'LBL_GROUP_DESCRIPTION', 'index.php?module=Groups&parent=Settings&view=List', '4', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_SHARING_ACCESS', 'adminIcon-module-access', 'LBL_SHARING_ACCESS_DESCRIPTION', 'index.php?module=SharingAccess&parent=Settings&view=Index', '5', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_FIELDS_ACCESS', 'adminIcon-special-access', 'LBL_SHARING_FIELDS_DESCRIPTION', 'index.php?module=FieldAccess&parent=Settings&view=Index', '6', '0', '0'],
				['LBL_LOGS', 'LBL_LOGIN_HISTORY_DETAILS', 'adminIcon-users-login', 'LBL_LOGIN_HISTORY_DESCRIPTION', 'index.php?module=LoginHistory&parent=Settings&view=List', '3', '0', '0'],
				['LBL_STUDIO', 'VTLIB_LBL_MODULE_MANAGER', 'adminIcon-modules-installation', 'VTLIB_LBL_MODULE_MANAGER_DESCRIPTION', 'index.php?module=ModuleManager&parent=Settings&view=List', '1', '0', '1'],
				['LBL_STUDIO', 'LBL_PICKLIST_EDITOR', 'adminIcon-fields-picklists', 'LBL_PICKLIST_DESCRIPTION', 'index.php?parent=Settings&module=Picklist&view=Index', '9', '0', '1'],
				['LBL_STUDIO', 'LBL_PICKLIST_DEPENDENCY_SETUP', 'adminIcon-fields-picklists-relations', 'LBL_PICKLIST_DEPENDENCY_DESCRIPTION', 'index.php?parent=Settings&module=PickListDependency&view=List', '10', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_ADVANCED_PERMISSION', 'glyphicon glyphicon-ice-lolly', NULL, 'index.php?module=AdvancedPermission&parent=Settings&view=List', '10', '0', '0'],
				['LBL_COMPANY', 'NOTIFICATIONSCHEDULERS', '', 'LBL_NOTIF_SCHED_DESCRIPTION', 'index.php?module=Settings&view=listnotificationschedulers&parenttab=Settings', '4', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'INVENTORYNOTIFICATION', '', 'LBL_INV_NOTIF_DESCRIPTION', 'index.php?module=Settings&view=listinventorynotifications&parenttab=Settings', '1', '0', '0'],
				['LBL_COMPANY', 'LBL_COMPANY_DETAILS', 'adminIcon-company-detlis', 'LBL_COMPANY_DESCRIPTION', 'index.php?parent=Settings&module=Vtiger&view=CompanyDetails', '2', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_MAIL_SERVER_SETTINGS', 'adminIcon-mail-configuration', 'LBL_MAIL_SERVER_DESCRIPTION', 'index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail', '5', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_CURRENCY_SETTINGS', 'adminIcon-currencies', 'LBL_CURRENCY_DESCRIPTION', 'index.php?parent=Settings&module=Currency&view=List', '4', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_SWITCH_USERS', 'adminIcon-users', 'LBL_SWITCH_USERS_DESCRIPTION', 'index.php?module=Users&view=SwitchUsers&parent=Settings', '11', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_SYSTEM_INFO', 'adminIcon-server-configuration', 'LBL_SYSTEM_DESCRIPTION', 'index.php?module=Settings&submodule=Server&view=ProxyConfig', '6', '1', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_DEFAULT_MODULE_VIEW', 'adminIcon-standard-modules', 'LBL_DEFAULT_MODULE_VIEW_DESC', 'index.php?module=Settings&action=DefModuleView&parenttab=Settings', '2', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_TERMS_AND_CONDITIONS', 'adminIcon-terms-and-conditions', 'LBL_INV_TANDC_DESCRIPTION', 'index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit', '3', '0', '0'],
				['LBL_STUDIO', 'LBL_CUSTOMIZE_RECORD_NUMBERING', 'adminIcon-recording-control', 'LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering', '6', '0', '0'],
				['LBL_AUTOMATION', 'LBL_LIST_WORKFLOWS', 'adminIcon-triggers', 'LBL_LIST_WORKFLOWS_DESCRIPTION', 'index.php?module=Workflows&parent=Settings&view=List', '1', '0', '1'],
				['LBL_SYSTEM_TOOLS', 'LBL_CONFIG_EDITOR', 'adminIcon-system-tools', 'LBL_CONFIG_EDITOR_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail', '7', '0', '0'],
				['LBL_AUTOMATION', 'Scheduler', 'adminIcon-cron', 'LBL_SCHEDULER_DESCRIPTION', 'index.php?module=CronTasks&parent=Settings&view=List', '3', '0', '0'],
				['LBL_AUTOMATION', 'LBL_WORKFLOW_LIST', 'adminIcon-workflow', 'LBL_AVAILABLE_WORKLIST_LIST', 'index.php?module=com_vtiger_workflow&action=workflowlist', '1', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'ModTracker', 'adminIcon-modules-track-chanegs', 'LBL_MODTRACKER_DESCRIPTION', 'index.php?module=ModTracker&action=BasicSettings&parenttab=Settings&formodule=ModTracker', '9', '0', '0'],
				['LBL_INTEGRATION', 'LBL_PBXMANAGER', 'adminIcon-pbx-manager', 'LBL_PBXMANAGER_DESCRIPTION', 'index.php?module=PBXManager&parent=Settings&view=Index', '22', '0', '0'],
				['LBL_INTEGRATION', 'LBL_CUSTOMER_PORTAL', 'adminIcon-customer-portal', 'PORTAL_EXTENSION_DESCRIPTION', 'index.php?module=CustomerPortal&action=index&parenttab=Settings', '3', '0', '0'],
				['LBL_INTEGRATION', 'Webforms', 'adminIcon-online-forms', 'LBL_WEBFORMS_DESCRIPTION', 'index.php?module=Webforms&action=index&parenttab=Settings', '4', '0', '0'],
				['LBL_STUDIO', 'LBL_EDIT_FIELDS', 'adminIcon-modules-fields', 'LBL_LAYOUT_EDITOR_DESCRIPTION', 'index.php?module=LayoutEditor&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_PDF', 'adminIcon-modules-pdf-templates', 'LBL_PDF_DESCRIPTION', 'index.php?module=PDF&parent=Settings&view=List', '10', '0', '0'],
				['LBL_SECURITY_MANAGEMENT', 'LBL_PASSWORD_CONF', 'adminIcon-passwords-configuration', 'LBL_PASSWORD_DESCRIPTION', 'index.php?module=Password&parent=Settings&view=Index', '1', '0', '0'],
				['LBL_STUDIO', 'LBL_MENU_BUILDER', 'adminIcon-menu-configuration', 'LBL_MENU_BUILDER_DESCRIPTION', 'index.php?module=Menu&view=Index&parent=Settings', '14', '0', '1'],
				['LBL_STUDIO', 'LBL_ARRANGE_RELATED_TABS', 'adminIcon-modules-relations', 'LBL_ARRANGE_RELATED_TABS', 'index.php?module=LayoutEditor&parent=Settings&view=Index&mode=showRelatedListLayout', '4', '0', '1'],
				['LBL_MAIL_TOOLS', 'Mail Scanner', 'adminIcon-mail-scanner', 'LBL_MAIL_SCANNER_DESCRIPTION', 'index.php?module=OSSMailScanner&parent=Settings&view=Index', '3', '0', '0'],
				['LBL_LOGS', 'Mail Logs', 'adminIcon-mail-download-history', 'LBL_MAIL_LOGS_DESCRIPTION', 'index.php?module=OSSMailScanner&parent=Settings&view=logs', '4', '0', '0'],
				['LBL_MAIL_TOOLS', 'Mail View', 'adminIcon-oss_mailview', 'LBL_MAIL_VIEW_DESCRIPTION', 'index.php?module=OSSMailView&parent=Settings&view=index', '21', '0', '0'],
				['LBL_AUTOMATION', 'Document Control', 'adminIcon-workflow', 'LBL_DOCUMENT_CONTROL_DESCRIPTION', 'index.php?module=OSSDocumentControl&parent=Settings&view=Index', '4', '0', '0'],
				['LBL_AUTOMATION', 'Project Templates', 'adminIcon-document-templates', 'LBL_PROJECT_TEMPLATES_DESCRIPTION', 'index.php?module=OSSProjectTemplates&parent=Settings&view=Index', '5', '0', '0'],
				['LBL_About_YetiForce', 'License', 'adminIcon-license', 'LBL_LICENSE_DESCRIPTION', 'index.php?module=Vtiger&parent=Settings&view=License', '4', '0', '0'],
				['LBL_SECURITY_MANAGEMENT', 'OSSPassword Configuration', 'adminIcon-passwords-encryption', 'LBL_OSSPASSWORD_CONFIGURATION_DESCRIPTION', 'index.php?module=OSSPasswords&view=ConfigurePass&parent=Settings', '3', '0', '0'],
				['LBL_AUTOMATION', 'LBL_DATAACCESS', 'adminIcon-recording-control', 'LBL_DATAACCESS_DESCRIPTION', 'index.php?module=DataAccess&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LangManagement', 'adminIcon-languages-and-translations', 'LBL_LANGMANAGEMENT_DESCRIPTION', 'index.php?module=LangManagement&parent=Settings&view=Index', '1', '0', '0'],
				['LBL_USER_MANAGEMENT', 'GlobalPermission', 'adminIcon-special-access', 'LBL_GLOBALPERMISSION_DESCRIPTION', 'index.php?module=GlobalPermission&parent=Settings&view=Index', '7', '0', '0'],
				['LBL_SEARCH_AND_FILTERS', 'Search Setup', 'adminIcon-search-configuration', 'LBL_SEARCH_SETUP_DESCRIPTION', 'index.php?module=Search&parent=Settings&view=Index', '1', '0', '0'],
				['LBL_SEARCH_AND_FILTERS', 'CustomView', 'adminIcon-filters-configuration', 'LBL_CUSTOMVIEW_DESCRIPTION', 'index.php?module=CustomView&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_STUDIO', 'Widgets', 'adminIcon-modules-widgets', 'LBL_WIDGETS_DESCRIPTION', 'index.php?module=Widgets&parent=Settings&view=Index', '3', '0', '1'],
				['LBL_About_YetiForce', 'Credits', 'adminIcon-contributors', 'LBL_CREDITS_DESCRIPTION', 'index.php?module=Vtiger&view=Credits&parent=Settings', '3', '0', '0'],
				['LBL_STUDIO', 'LBL_QUICK_CREATE_EDITOR', 'adminIcon-fields-quick-create', 'LBL_QUICK_CREATE_EDITOR_DESCRIPTION', 'index.php?module=QuickCreateEditor&parent=Settings&view=Index', '8', '0', '0'],
				['LBL_INTEGRATION', 'LBL_API_ADDRESS', 'adminIcon-address', 'LBL_API_ADDRESS_DESCRIPTION', 'index.php?module=ApiAddress&parent=Settings&view=Configuration', '5', '0', '0'],
				['LBL_SECURITY_MANAGEMENT', 'LBL_BRUTEFORCE', 'adminIcon-brute-force', 'LBL_BRUTEFORCE_DESCRIPTION', 'index.php?module=BruteForce&parent=Settings&view=Index', '2', '0', '0'],
				['LBL_LOGS', 'LBL_UPDATES_HISTORY', 'adminIcon-server-updates', 'LBL_UPDATES_HISTORY_DESCRIPTION', 'index.php?parent=Settings&module=Updates&view=Index', '2', '0', '0'],
				['LBL_LOGS', 'LBL_CONFREPORT', 'adminIcon-server-configuration', 'LBL_CONFREPORT_DESCRIPTION', 'index.php?parent=Settings&module=ConfReport&view=Index', '1', '0', '0'],
				['LBL_CALENDAR_LABELS_COLORS', 'LBL_ACTIVITY_TYPES', 'adminIcon-calendar-types', 'LBL_ACTIVITY_TYPES_DESCRIPTION', 'index.php?parent=Settings&module=Calendar&view=ActivityTypes', '1', '0', '0'],
				['LBL_STUDIO', 'LBL_WIDGETS_MANAGEMENT', 'adminIcon-widgets-configuration', 'LBL_WIDGETS_MANAGEMENT_DESCRIPTION', 'index.php?module=WidgetsManagement&parent=Settings&view=Configuration', '15', '0', '0'],
				['LBL_INTEGRATION', 'LBL_MOBILE_KEYS', 'adminIcon-mobile-applications', 'LBL_MOBILE_KEYS_DESCRIPTION', 'index.php?parent=Settings&module=MobileApps&view=MobileKeys', '6', '0', '0'],
				['LBL_STUDIO', 'LBL_TREES_MANAGER', 'adminIcon-field-folders', 'LBL_TREES_MANAGER_DESCRIPTION', 'index.php?module=TreesManager&parent=Settings&view=List', '11', '0', '0'],
				['LBL_STUDIO', 'LBL_MODTRACKER_SETTINGS', 'adminIcon-modules-track-chanegs', 'LBL_MODTRACKER_SETTINGS_DESCRIPTION', 'index.php?module=ModTracker&parent=Settings&view=List', '5', '0', '0'],
				['LBL_STUDIO', 'LBL_HIDEBLOCKS', 'adminIcon-filed-hide-bloks', 'LBL_HIDEBLOCKS_DESCRIPTION', 'index.php?module=HideBlocks&parent=Settings&view=List', '12', '0', '0'],
				['LBL_CALENDAR_LABELS_COLORS', 'LBL_PUBLIC_HOLIDAY', 'adminIcon-calendar-holidys', 'LBL_PUBLIC_HOLIDAY_DESCRIPTION', 'index.php?module=PublicHoliday&view=Configuration&parent=Settings', '3', '0', '0'],
				['LBL_CALENDAR_LABELS_COLORS', 'LBL_CALENDAR_CONFIG', 'adminIcon-calendar-configuration', 'LBL_CALENDAR_CONFIG_DESCRIPTION', 'index.php?parent=Settings&module=Calendar&view=UserColors', '2', '0', '0'],
				['LBL_PROCESSES', 'LBL_SALES_PROCESSES', 'adminIcon-sales', 'LBL_SALES_PROCESSES_DESCRIPTION', 'index.php?module=SalesProcesses&view=Index&parent=Settings', '2', '0', '0'],
				['LBL_INTEGRATION', 'LBL_DAV_KEYS', 'adminIcon-dav-applications', 'LBL_DAV_KEYS_DESCRIPTION', 'index.php?parent=Settings&module=Dav&view=Keys', '7', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_AUTOLOGIN', 'adminIcon-mail-auto-login', 'LBL_AUTOLOGIN_DESCRIPTION', 'index.php?parent=Settings&module=Mail&view=Autologin', '4', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_MAIL_GENERAL_CONFIGURATION', 'adminIcon-mail-smtp-server', 'LBL_MAIL_GENERAL_CONFIGURATION_DESCRIPTION', 'index.php?parent=Settings&module=Mail&view=Config', '1', '0', '0'],
				['LBL_PROCESSES', 'LBL_SUPPORT_PROCESSES', 'adminIcon-support ', 'LBL_SUPPORT_PROCESSES_DESCRIPTION', 'index.php?module=SupportProcesses&view=Index&parent=Settings', '6', '0', '0'],
				['LBL_CALENDAR_LABELS_COLORS', 'LBL_COLORS', 'adminIcon-colors', 'LBL_COLORS_DESCRIPTION', 'index.php?module=Users&parent=Settings&view=Colors', '4', '0', '0'],
				['LBL_PROCESSES', 'LBL_REALIZATION_PROCESSES', 'adminIcon-realization', 'LBL_REALIZATION_PROCESSES_DESCRIPTION', 'index.php?module=RealizationProcesses&view=Index&parent=Settings', '3', '0', '0'],
				['LBL_PROCESSES', 'LBL_MARKETING_PROCESSES', 'adminIcon-marketing', 'LBL_MARKETING_PROCESSES_DESCRIPTION', 'index.php?module=MarketingProcesses&view=Index&parent=Settings', '1', '0', '0'],
				['LBL_PROCESSES', 'LBL_FINANCIAL_PROCESSES', 'adminIcon-finances', 'LBL_FINANCIAL_PROCESSES_DESCRIPTION', 'index.php?module=FinancialProcesses&view=Index&parent=Settings', '5', '0', '0'],
				['LBL_INTEGRATION', 'LBL_AUTHORIZATION', 'adminIcon-automation', 'LBL_AUTHORIZATION_DESCRIPTION', 'index.php?module=Users&view=Auth&parent=Settings', '1', '0', '0'],
				['LBL_PROCESSES', 'LBL_TIMECONTROL_PROCESSES', 'adminIcon-logistics', 'LBL_TIMECONTROL_PROCESSES_DESCRIPTION', 'index.php?module=TimeControlProcesses&parent=Settings&view=Index', '7', '0', '0'],
				['LBL_STUDIO', 'LBL_CUSTOM_FIELD_MAPPING', 'adminIcon-filed-mapping', 'LBL_CUSTOM_FIELD_MAPPING_DESCRIPTION', 'index.php?parent=Settings&module=Leads&view=MappingDetail', '13', '0', '0'],
				['LBL_INTEGRATION', 'LBL_CURRENCY_UPDATE', 'adminIcon-currencies', 'LBL_CURRENCY_UPDATE_DESCRIPTION', 'index.php?module=CurrencyUpdate&view=Index&parent=Settings', '2', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_CREDITLIMITS', 'adminIcon-credit-limit-base_2', 'LBL_CREDITLIMITS_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=CreditLimits', '5', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_TAXES', 'adminIcon-taxes-rates', 'LBL_TAXES_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=Taxes', '1', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_DISCOUNTS', 'adminIcon-discount-base', 'LBL_DISCOUNTS_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=Discounts', '3', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_TAXCONFIGURATION', 'adminIcon-taxes-caonfiguration', 'LBL_TAXCONFIGURATION_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=TaxConfiguration', '4', '0', '0'],
				['LBL_ADVANCED_MODULES', 'LBL_DISCOUNTCONFIGURATION', 'adminIcon-discount-configuration', 'LBL_DISCOUNTCONFIGURATION_DESCRIPTION', 'index.php?module=Inventory&parent=Settings&view=DiscountConfiguration', '2', '0', '0'],
				['LBL_MAIL_TOOLS', 'Mail', 'adminIcon-mail-download-history', 'LBL_OSSMAIL_DESCRIPTION', 'index.php?module=OSSMail&parent=Settings&view=index', '2', '0', '0'],
				['LBL_STUDIO', 'LBL_MAPPEDFIELDS', 'adminIcon-mapped-fields', 'LBL_MAPPEDFIELDS_DESCRIPTION', 'index.php?module=MappedFields&parent=Settings&view=List', '16', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_LOCKS', 'adminIcon-locks', 'LBL_LOCKS_DESCRIPTION', 'index.php?module=Users&view=Locks&parent=Settings', '8', '0', '0'],
				['LBL_SYSTEM_TOOLS', 'LBL_NOTIFICATIONS_CONFIGURATION', 'adminIcon-NotificationConfiguration', 'LBL_TYPE_NOTIFICATIONS_DESCRIPTION', 'index.php?module=Notifications&view=Configuration&parent=Settings', '13', '0', '0'],
				['LBL_INTEGRATION', 'LBL_POS', NULL, NULL, 'index.php?module=POS&view=Index&parent=Settings', '10', '0', '0'],
				['LBL_INTEGRATION', 'LBL_WEBSERVICE_APPS', NULL, NULL, 'index.php?module=WebserviceApps&view=Index&parent=Settings', '11', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_OWNER_ALLOCATION', 'adminIcon-owner', 'LBL_OWNER_ALLOCATION_DESCRIPTION', 'index.php?module=RecordAllocation&view=Index&parent=Settings&type=owner', '9', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_MULTIOWNER_ALLOCATION', 'adminIcon-shared-owner', 'LBL_MULTIOWNER_ALLOCATION_DESCRIPTION', 'index.php?module=RecordAllocation&view=Index&parent=Settings&type=sharedOwner', '10', '0', '0'],
				['LBL_USER_MANAGEMENT', 'LBL_AUTOMATIC_ASSIGNMENT', 'adminIcon-shared-owner', 'LBL_AUTOMATICASSIGNMENT_DESCRIPTION', 'index.php?module=AutomaticAssignment&view=List&parent=Settings', '11', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_EMAILS_TO_SEND', NULL, 'LBL_EMAILS_TO_SEND_DESCRIPTION', 'index.php?module=Mail&parent=Settings&view=List', '22', '0', '0'],
				['LBL_MAIL_TOOLS', 'LBL_MAIL_SMTP', 'adminIcon-mail-configuration', 'LBL_MAILSMTP_TO_SEND_DESCRIPTION', 'index.php?module=MailSmtp&parent=Settings&view=List', '23', '0', '0']
		];
		$blocks = [];
		foreach ($menu as $row) {
			if (!array_key_exists($row[0], $blocks)) {
				$blockInstance = Settings_Vtiger_Menu_Model::getInstance($row[0]);
				$blocks[$row[0]] = $blockInstance;
			}
			$result = $db->pquery('SELECT 1 FROM `vtiger_settings_field` WHERE `name` = ?', [$row[1]]);
			if ($result->rowCount() > 0 && !empty($blocks[$row[0]])) {
				$db->update('vtiger_settings_field', ['blockid' => $blocks[$row[0]]->get('blockid'), 'name' => $row[1], 'iconpath' => $row[2], 'description' => $row[3], 'linkto' => $row[4], 'sequence' => $row[5], 'active' => $row[6], 'pinned' => $row[7]], '`name` = ?', [$row[1]]);
			} elseif (!empty($blocks[$row[0]])) {
				$fieldId = $db->getUniqueID('vtiger_settings_field');
				$db->insert('vtiger_settings_field', ['fieldid' => $fieldId, 'blockid' => $blocks[$row[0]]->get('blockid'), 'name' => $row[1], 'iconpath' => $row[2], 'description' => $row[3], 'linkto' => $row[4], 'sequence' => $row[5], 'active' => $row[6], 'pinned' => $row[7]]);
			}
		}
	}

	private function getTablesAction($index)
	{
		$tables = [];
		switch ($index) {
			case 1:
				$tables = [
						['type' => 'remove', 'name' => 'vtiger_backup_tmp'],
						['type' => 'remove', 'name' => 'vtiger_backup_settings'],
						['type' => 'remove', 'name' => 'vtiger_backup_files'],
						['type' => 'remove', 'name' => 'vtiger_backup_db'],
						['type' => 'remove', 'name' => 'vtiger_backup'],
						['type' => 'remove', 'name' => 'a_yf_notification_type'],
						['type' => 'add', 'name' => 'l_yf_profile', 'sql' => "`l_yf_profile` (
							`id` int(19) unsigned NOT NULL DEFAULT '0',
							`category` varchar(255) NOT NULL,
							`info` text,
							`log_time` varchar(20) NOT NULL,
							`trace` text,
							`level` varchar(255) DEFAULT NULL,
							`duration` decimal(3,3) NOT NULL,
							KEY `id` (`id`),
							KEY `category` (`category`)
						  )"],
						['type' => 'remove', 'name' => 'vtiger_freetagged_objects'],
						['type' => 'remove', 'name' => 'vtiger_freetags_seq'],
						['type' => 'remove', 'name' => 'vtiger_freetags'],
						['type' => 'add', 'name' => 'a_yf_bruteforce', 'sql' => "`a_yf_bruteforce` (
							`attempsnumber` tinyint(2) NOT NULL,
							`timelock` smallint(5) NOT NULL,
							`active` tinyint(1) DEFAULT '0',
							`sent` tinyint(1) DEFAULT '0'
						  )"],
						['type' => 'add', 'name' => 'a_yf_bruteforce_blocked', 'sql' => "`a_yf_bruteforce_blocked` (
							`id` int(19) NOT NULL AUTO_INCREMENT,
							`ip` varchar(50) NOT NULL,
							`time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
							`attempts` tinyint(2) DEFAULT '0',
							`blocked` tinyint(1) DEFAULT '0',
							`userid` int(11) DEFAULT NULL,
							PRIMARY KEY (`id`),
							KEY `bf1_mixed` (`ip`,`time`,`blocked`)
						  )"],
						['type' => 'add', 'name' => 'a_yf_bruteforce_users', 'sql' => "`a_yf_bruteforce_users` (
							`id` int(11) NOT NULL,
							PRIMARY KEY (`id`),
							CONSTRAINT `fk_1_bruteforce_users` FOREIGN KEY (`id`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE
						  )"],
						['type' => 'add', 'name' => 'a_yf_bruteforce', 'sql' => "`a_yf_bruteforce` (
							`attempsnumber` tinyint(2) NOT NULL,
							`timelock` smallint(5) NOT NULL,
							`active` tinyint(1) DEFAULT '0',
							`sent` tinyint(1) DEFAULT '0'
						  )"],
						['type' => 'add', 'name' => 'a_yf_bruteforce_blocked', 'sql' => "`a_yf_bruteforce_blocked` (
							`id` int(19) NOT NULL AUTO_INCREMENT,
							`ip` varchar(50) NOT NULL,
							`time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
							`attempts` tinyint(2) DEFAULT '0',
							`blocked` tinyint(1) DEFAULT '0',
							`userid` int(11) DEFAULT NULL,
							PRIMARY KEY (`id`),
							KEY `bf1_mixed` (`ip`,`time`,`blocked`)
						  )"],
						['type' => 'remove', 'name' => 'vtiger_modtracker_basic_seq'],
						['type' => 'remove', 'name' => 'vtiger_customview_seq'],
						['type' => 'remove', 'name' => 'vtiger_calendar_user_activitytypes_seq'],
						['type' => 'rename', 'name' => 'a_yf_featured_filter', 'sql' => 'RENAME TABLE `a_yf_featured_filter` TO `u_yf_featured_filter`;
'],
						['type' => 'add', 'name' => 'u_yf_dashboard_type', 'sql' => "`u_yf_dashboard_type` (
							`dashboard_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`name` varchar(255) NOT NULL,
							`system` smallint(1) DEFAULT '0',
							PRIMARY KEY (`dashboard_id`)
						  )"],
						['type' => 'add', 'name' => 's_yf_handler_updater', 'sql' => "`s_yf_handler_updater` (
							`id` int(11) NOT NULL AUTO_INCREMENT,
							`tabid` smallint(11) unsigned NOT NULL,
							`crmid` int(11) unsigned NOT NULL,
							`userid` int(11) unsigned NOT NULL,
							`handler_name` varchar(50) NOT NULL,
							`class` varchar(50) NOT NULL,
							`params` text NOT NULL,
							PRIMARY KEY (`id`)
						  )"],
						['type' => 'add', 'name' => 's_yf_automatic_assignment', 'sql' => "`s_yf_automatic_assignment` (
							`id` int(11) NOT NULL AUTO_INCREMENT,
							`tabid` int(11) unsigned NOT NULL,
							`field` varchar(30) NOT NULL,
							`value` varchar(255) DEFAULT NULL,
							`roles` text,
							`smowners` text,
							`assign` smallint(5) DEFAULT NULL,
							`active` tinyint(1) DEFAULT '1',
							`conditions` text,
							`user_limit` tinyint(1) DEFAULT NULL,
							`roleid` varchar(200) DEFAULT NULL,
							PRIMARY KEY (`id`)
						  )"],
				];
				break;
			case 2:
				$tables = [
						['type' => 'remove', 'name' => 'vtiger_bruteforce_users'],
						['type' => 'remove', 'name' => 'vtiger_bruteforce'],
				];
				break;
			case 3:
				$tables = [
						['type' => 'remove', 'name' => 'vtiger_crmentity_seq'],
						['type' => 'add', 'name' => 's_yf_mail_smtp', 'sql' => "`s_yf_mail_smtp` (
							`id` int(6) unsigned NOT NULL AUTO_INCREMENT,
							`mailer_type` varchar(10) DEFAULT 'smtp',
							`default` tinyint(1) unsigned NOT NULL DEFAULT '0',
							`name` varchar(255) NOT NULL,
							`host` varchar(255) NOT NULL,
							`port` smallint(6) unsigned DEFAULT NULL,
							`username` varchar(255) DEFAULT NULL,
							`password` varchar(255) DEFAULT NULL,
							`authentication` tinyint(1) unsigned NOT NULL DEFAULT '1',
							`secure` varchar(10) DEFAULT NULL,
							`options` text,
							`from_email` varchar(255) DEFAULT NULL,
							`from_name` varchar(255) DEFAULT NULL,
							`replay_to` varchar(255) DEFAULT NULL,
							`individual_delivery` tinyint(1) unsigned NOT NULL DEFAULT '0',
							PRIMARY KEY (`id`)
						  )"],
						['type' => 'add', 'name' => 's_yf_mail_queue', 'sql' => "`s_yf_mail_queue` (
							`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							`smtp_id` int(6) unsigned NOT NULL DEFAULT '1',
							`date` datetime NOT NULL,
							`owner` int(11) NOT NULL,
							`status` tinyint(1) unsigned NOT NULL DEFAULT '0',
							`from` text NOT NULL,
							`subject` text,
							`to` text NOT NULL,
							`content` text,
							`cc` text,
							`bcc` text,
							`attachments` text,
							`priority` tinyint(1) unsigned NOT NULL DEFAULT '1',
							PRIMARY KEY (`id`),
							KEY `smtp_id` (`smtp_id`),
							CONSTRAINT `s_yf_mail_queue_ibfk_1` FOREIGN KEY (`smtp_id`) REFERENCES `s_yf_mail_smtp` (`id`) ON DELETE CASCADE
						  )"],
						['type' => 'remove', 'name' => 'u_yf_emailtemplatescf'],
						['type' => 'add', 'name' => 'u_yf_documents_emailtemplates', 'sql' => "`u_yf_documents_emailtemplates` (
							`crmid` int(11) DEFAULT NULL,
							`relcrmid` int(11) DEFAULT NULL,
							KEY `u_yf_documents_emailtemplates_crmid_idx` (`crmid`),
							KEY `u_yf_documents_emailtemplates_relcrmid_idx` (`relcrmid`),
							CONSTRAINT `fk_1_u_yf_documents_emailtemplates` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE,
							CONSTRAINT `fk_2_u_yf_documents_emailtemplates` FOREIGN KEY (`relcrmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE
						  )"],
				];
				break;
			case 4:
				$tables = [
						['type' => 'remove', 'name' => 'vtiger_eventhandler_module_seq'],
						['type' => 'remove', 'name' => 'vtiger_eventhandler_module'],
						['type' => 'remove', 'name' => 'vtiger_eventhandlers_seq'],
						['type' => 'remove', 'name' => 'vtiger_links_seq'],
						['type' => 'remove', 'name' => 'vtiger_settings_field_seq'],
						['type' => 'remove', 'name' => 'vtiger_relatedlists_seq'],
						['type' => 'remove', 'name' => 'vtiger_relatedlists_rb'],
						['type' => 'remove', 'name' => 'vtiger_inventorytaxinfo_seq'],
						['type' => 'remove', 'name' => 'vtiger_inventorytaxinfo'],
						['type' => 'remove', 'name' => 'vtiger_producttaxrel'],
						['type' => 'remove', 'name' => 'vtiger_inventory_tandc_seq'],
						['type' => 'remove', 'name' => 'com_vtiger_workflows_seq'],
						['type' => 'remove', 'name' => 'vtiger_picklist_seq'],
						['type' => 'remove', 'name' => 'vtiger_profile_seq'],
						['type' => 'remove', 'name' => 'vtiger_def_org_share_seq'],
				];
				break;
			default:
				break;
		}
		return $tables;
	}

	private function setTablesScheme($tables)
	{
		$db = \PearDatabase::getInstance();
		foreach ($tables as $table) {
			if (empty($table)) {
				continue;
			}
			switch ($table['type']) {
				case 'add':
					$db->query('CREATE TABLE IF NOT EXISTS ' . $table['sql'] . ' ENGINE = InnoDB DEFAULT CHARSET = "utf8";
');
					break;
				case 'remove':
					$db->query('DROP TABLE IF EXISTS ' . $table['name'] . ';
');
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

	private function getLink($index)
	{
		$links = [];
		switch ($index) {
			case 1:
				$links = [
						['type' => 'remove', 'data' => [104, 'Home', 'DASHBOARDWIDGET', 'Tag Cloud', 'index.php?module = Home&view = ShowWidget&name = TagCloud', '', 17, NULL, NULL, NULL, NULL]],
				];
				break;
			case 2:
				$links = [
						['type' => 'add', 'data' => [3, 'Home', 'DASHBOARDWIDGET', 'Notifications', 'index.php?module = Notification&view = ShowWidget&name = Notifications', null, 3, null, null, null, null]],
						['type' => 'add', 'data' => [303, 'Notification', 'DASHBOARDWIDGET', 'Notifications', 'index.php?module = Notification&view = ShowWidget&name = Notifications', '', 0, null, null, null, null]],
						['type' => 'add', 'data' => [308, 'Home', 'DASHBOARDWIDGET', 'LBL_EXPIRING_SOLD_PRODUCTS', 'index.php?module = Assets&view = ShowWidget&name = ExpiringSoldProducts', '', 0, null, null, null, null]],
						['type' => 'add', 'data' => [307, 'Notification', 'DASHBOARDWIDGET', 'LBL_NOTIFICATION_BY_RECIPIENT', 'index.php?module = Notification&view = ShowWidget&name = NotificationsByRecipient', '', 0, null, null, null, null]],
						['type' => 'add', 'data' => [306, 'Home', 'DASHBOARDWIDGET', 'LBL_NOTIFICATION_BY_RECIPIENT', 'index.php?module = Notification&view = ShowWidget&name = NotificationsByRecipient', '', 0, null, null, null, null]],
						['type' => 'add', 'data' => [305, 'Notification', 'DASHBOARDWIDGET', 'LBL_NOTIFICATION_BY_SENDER', 'index.php?module = Notification&view = ShowWidget&name = NotificationsBySender', '', 0, null, null, null, null]],
						['type' => 'add', 'data' => [304, 'Home', 'DASHBOARDWIDGET', 'LBL_NOTIFICATION_BY_SENDER', 'index.php?module = Notification&view = ShowWidget&name = NotificationsBySender', '', 0, null, null, null, null]],
				];
				break;
			default:
				break;
		}
		return $links;
	}

	private function setLink($links)
	{
		$db = PearDatabase::getInstance();
		if (!empty($links)) {
			foreach ($links as $link) {
				if (empty($link)) {
					continue;
				}
				list($id, $tabid, $type, $label, $url, $iconpath, $sequence, $path, $class, $method, $params) = $link['data'];
				$tabid = \vtlib\Functions::getModuleId($tabid);
				$handlerInfo = ['path' => $path, 'class' => $class, 'method' => $method];
				if ($link['type'] === 'add') {
					$result = $db->pquery('SELECT 1 FROM vtiger_links WHERE tabid = ? AND linktype = ? AND linklabel = ? AND linkurl = ?;
', [$tabid, $type, $label, $url]);
					if (!$db->getRowCount($result))
						\vtlib\Link::addLink($tabid, $type, $label, $url, $iconpath, $sequence, $handlerInfo);
				} elseif ($link['type'] === 'remove') {
					\vtlib\Link::deleteLink($tabid, $type, $label, $url);
				} elseif ($link['type'] === 'update') {
					$db->update('vtiger_links', [
						'linktype' => $type,
						'tabid' => $tabid,
						'linklabel' => $label,
						'linkurl' => $url,
						'linkicon' => $iconpath,
						'sequence' => $sequence,
						'handler_path' => $path,
						'handler_class' => $class,
						'handler' => $method,
						'params' => $params,
						], 'linklabel = ? AND tabid = ?;
', [$label, $tabid]);
				}
			}
		}
	}

	private function checkFieldExists($moduleName, $column, $table)
	{
		$db = PearDatabase::getInstance();
		if ($moduleName == 'Settings')
			$result = $db->pquery('SELECT * FROM vtiger_settings_field WHERE name = ? AND linkto = ?;
', [$column, $table]);
		else {
			if (is_numeric($moduleName)) {
				$tabId = $moduleName;
			} else {
				$tabId = \vtlib\Functions::getModuleId($moduleName);
			}
			$result = $db->pquery("SELECT * FROM vtiger_field WHERE columnname = ? AND tablename = ? AND tabid = ?;", [$column, $table, $tabId]);
		}
		if (!$db->getRowCount($result)) {
			return false;
		}
		return true;
	}

	private function getFieldsToRemove($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'u_yf_notification' => ['relatedid', 'relatedmodule'],
					'vtiger_products' => ['taxclass'],
					'vtiger_service' => ['taxclass'],
					'vtiger_activity' => ['semodule']
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function removeFields($fields)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = PearDatabase::getInstance();
		foreach ($fields as $tableName => $columnsName) {
			if (empty($columnsName) || !\vtlib\Utils::CheckTable($tableName)) {
				continue;
			}
			foreach ($columnsName as $columnName) {
				$result = $db->pquery("SELECT fieldid FROM vtiger_field WHERE columnname = ? AND tablename = ?;", [$columnName, $tableName]);
				if ($id = $db->getSingleValue($result)) {
					$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($id);
					try {
						$fieldInstance->delete();
//						\vtlib\Profile::deleteForField($fieldInstance);
//						$db->delete('vtiger_field', 'fieldid = ?', [$id]);
//						$db->delete('vtiger_fieldmodulerel', 'fieldid = ?', [$id]);
					} catch (Exception $e) {
						\App\Log::error('ERROR' . __METHOD__ . ': code ' . $e->getCode() . " message " . $e->getMessage());
					}
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function getFields($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
						[111, 2400, 'smcreatorid', 'vtiger_crmentity', 1, 52, 'smcreatorid', 'Created By', 1, 2, '', 100, 11, 374, 2, 'V~O', 1, null, 'BAS', 1, '', 0, '', null, 'smallint(5)', 'LBL_NOTIFICATION_INFORMATION', [], [], 'Notification'],
						[111, 2399, 'shownerid', 'vtiger_crmentity', 1, 120, 'shownerid', 'Share with users', 1, 2, '', 100, 4, 374, 1, 'V~O', 2, 2, 'BAS', 1, '', 0, '', '0', 'tinyint(1)', 'LBL_NOTIFICATION_INFORMATION', [], [], 'Notification'],
						[111, 2393, 'link', 'u_yf_notification', 1, 67, 'link', 'FL_RELATION', 1, 2, '', 100, 6, 374, 1, 'I~O', 2, 5, 'BAS', 1, '', 0, '', '0', 'int(19)', 'LBL_NOTIFICATION_INFORMATION', [], [], 'Notification'],
						[111, 2397, 'process', 'u_yf_notification', 1, 66, 'process', 'FL_PROCESS', 1, 2, '', 100, 7, 374, 1, 'I~O', 2, 6, 'BAS', 1, '', 0, '', '0', 'int(19)', 'LBL_NOTIFICATION_INFORMATION', [], [], 'Notification'],
						[111, 2398, 'subprocess', 'u_yf_notification', 1, 68, 'subprocess', 'FL_SUB_PROCESS', 1, 2, '', 100, 8, 374, 1, 'I~O', 2, 7, 'BAS', 1, '', 0, '', '0', 'int(19)', 'LBL_NOTIFICATION_INFORMATION', [], [], 'Notification']
				];
				break;
			case 2:
				$fields = [
						[6, 2401, 'accounts_status', 'vtiger_account', 1, 15, 'accounts_status', 'FL_STATUS', 1, 2, '', 100, 25, 9, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, "varchar(255)", 'LBL_ACCOUNT_INFORMATION', ['PLL_PROMISING_CLIENT', 'PLL_ACTIVE_CLIENT', 'PLL_SPECIAL_CLIENT', 'PLL_LOST_CLIENT', 'PLL_UNOBTAINED_CLIENT', 'PLL_INACTIVE_CLIENT', 'PLL_CLOSED_CLIENT', 'PLL_BLACKLISTED_CLIENT'], [], 'Accounts'],
						['95', '2044', 'finvoice_paymentstatus', 'u_yf_finvoice', '1', '15', 'finvoice_paymentstatus', 'FL_PAYMENT_STATUS', '1', '2', '', '100', '10', '310', '1', 'V~M', '1', null, 'BAS', '1', '', '0', '', null, "varchar(255)", 'LBL_BASIC_DETAILS', ['PLL_AWAITING_PAYMENT', 'PLL_PARTIALLY_PAID', 'PLL_FULLY_PAID'], [], 'FInvoice'],
						['95', '2044', 'finvoice_type', 'u_yf_finvoice', '1', '15', 'finvoice_type', 'FL_INVOICE_TYPE', '1', '2', '', '100', '10', '310', '1', 'V~M', '1', null, 'BAS', '1', '', '0', '', null, "varchar(255)", 'LBL_BASIC_DETAILS', ['PLL_DOMESTIC_INVOICE', 'PLL_FOREIGN_INVOICE', 'PLL_IC_INVOICE'], [], 'FInvoice'],
						['61', '943', 'parentid', 'u_yf_ssalesprocesses', '1', '10', 'parentid', 'FL_MEMBER_OF', '1', '2', '', '100', '2', '151', '1', 'I~O', '1', null, 'BAS', '0', '', '0', '', null, "int(19) DEFAULT '0'", 'LBL_SSALESPROCESSES_INFORMATION', [], ['SSalesProcesses'], 'SSalesProcesses']
				];
				break;
			case 3:
				$db = PearDatabase::getInstance();
				$result = $db->query("SELECT `vtiger_tab`.`tabid`, `vtiger_tab`.`name`, `vtiger_blocks`.`blocklabel` FROM `vtiger_tab` LEFT JOIN `vtiger_blocks` ON vtiger_tab.tabid = vtiger_blocks.tabid WHERE ((`isentitytype`=1) AND (`vtiger_tab`.`name` NOT IN ('SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter'))) AND (`vtiger_blocks`.`iscustom`=0) GROUP BY `vtiger_tab`.`tabid` ORDER BY `vtiger_blocks`.`sequence` DESC ");
				while ($row = $db->getRow($result)) {
					$fields[] = ['61', '943', 'private', 'vtiger_crmentity', '1', '56', 'private', 'FL_IS_PRIVATE', '1', '0', '', '1', '2', '151', '1', 'C~O', '1', null, 'BAS', '0', '', '0', '', null, "tinyint(1) DEFAULT '0'", $row['blocklabel'], [], [], $row['name']];
				}
				break;
			case 4:
				$fields[] = ['14', '1759', 'taxes', 'vtiger_service', '1', '303', 'taxes', 'FL_TAXES', '1', '2', '', '100', '5', '32', '1', 'V~O', '1', null, 'BAS', '1', '', '0', '', null, "varchar(50)", 'LBL_PRICING_INFORMATION', [], [], 'Services'];
				$fields[] = ['29', '475', 'available', 'vtiger_users', '1', '56', 'available', 'FL_AVAILABLE', '1', '0', '', '3', '7', '77', '1', 'C~O', '1', null, 'BAS', '1', '', '0', '', null, "tinyint(1) DEFAULT '0'", 'LBL_MORE_INFORMATION', [], [], 'Users'];
				$fields[] = ['29', '475', 'auto_assign', 'vtiger_users', '1', '56', 'auto_assign', 'FL_AUTO_ASSIGN_RECORDS', '1', '0', '', '3', '7', '77', '1', 'C~O', '1', null, 'BAS', '1', '', '0', '', null, "tinyint(1) DEFAULT '0'", 'LBL_MORE_INFORMATION', [], [], 'Users'];
				$fields[] = ['29', '475', 'records_limit', 'vtiger_users', '1', '7', 'records_limit', 'FL_RECORD_LIMIT_IN_MODULE', '1', '0', '', '3', '7', '77', '1', 'I~O', '1', null, 'BAS', '1', '', '0', '', null, "int(11)", 'LBL_MORE_INFORMATION', [], [], 'Users'];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function setFields($fields)
	{
//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];

		foreach ($fields as $field) {
			if (!\vtlib\Functions::getModuleId($field[28]) || $this->checkFieldExists($field[28], $field[2], $field[3])) {
				continue;
			}
			$moduleInstance = \vtlib\Module::getInstance($field[28]);
			$blockInstance = \vtlib\Block::getInstance($field[25], $moduleInstance);
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
			if ($field[26] && ($field[5] == 15 || $field[5] == 16 || $field[5] == 33 ))
				$fieldInstance->setPicklistValues($field[26]);
			if ($field[27] && $field[5] == 10) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}
	}

	private function getRelations($index)
	{
		$ralations = [];
		switch ($index) {
			case 1:
				$ralations = [
						['type' => 'add', 'data' => [515, 'Partners', 'SSalesProcesses', 'getRelatedList', 13, 'SSalesProcesses', 0, 'SELECT, ADD', 0, 0, 0]],
						['type' => 'add', 'data' => [516, 'Vendors', 'SSalesProcesses', 'getRelatedList', 17, 'SSalesProcesses', 0, 'SELECT, ADD', 0, 0, 0]],
						['type' => 'add', 'data' => [517, 'Competition', 'SSalesProcesses', 'getRelatedList', 13, 'SSalesProcesses', 0, 'SELECT, ADD', 0, 0, 0]],
						['type' => 'add', 'data' => [518, 'SSalesProcesses', 'Partners', 'getRelatedList', 20, 'Partners', 0, 'SELECT, ADD', 0, 0, 0]],
						['type' => 'add', 'data' => [519, 'SSalesProcesses', 'Vendors', 'getRelatedList', 21, 'Vendors', 0, 'SELECT, ADD', 0, 0, 0]],
						['type' => 'add', 'data' => [520, 'SSalesProcesses', 'Competition', 'getRelatedList', 22, 'Competition', 0, 'SELECT, ADD', 0, 0, 0]],
						['type' => 'add', 'data' => [521, 'Partners', 'Project', 'getRelatedList', 14, 'Project', 0, 'SELECT, ADD', 0, 0, 0]],
						['type' => 'add', 'data' => [523, 'Project', 'Vendors', 'getRelatedList', 11, 'Vendors', 0, 'SELECT, ADD', 0, 0, 0]],
						['type' => 'add', 'data' => [524, 'Project', 'Partners', 'getRelatedList', 12, 'Partners', 0, 'SELECT, ADD', 0, 0, 0]]
				];
				break;
			case 2:
				$ralations = [
						['type' => 'add', 'data' => [525, 'EmailTemplates', 'Documents', 'getManyToMany', 1, 'Documents', 0, 'SELECT', 0, 0, 0]]
				];
				break;
			default:
				break;
		}
		return $ralations;
	}

	private function setRelations($data)
	{
		$db = PearDatabase::getInstance();
		if (!empty($data)) {
			foreach ($data as $relation) {
				if (empty($relation)) {
					continue;
				}
				list($id, $moduleName, $relModuleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment) = $relation['data'];
				$tabid = \vtlib\Functions::getModuleId($moduleName);
				$relTabid = \vtlib\Functions::getModuleId($relModuleName);
				$result = $db->pquery("SELECT 1 FROM `vtiger_relatedlists` WHERE tabid=? AND related_tabid = ? AND name = ? AND label = ?;", [$tabid, $relTabid, $name, $label]);
				if ($result->rowCount() === 0 && $relation['type'] === 'add') {
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
				} elseif ($result->rowCount() > 0 && $relation['type'] === 'remove') {
					$db->delete('vtiger_relatedlists', '`tabid` = ? AND `related_tabid` = ? AND `name` = ?;
', [$tabid, $relTabid, $name]);
				} elseif ($relation['type'] === 'update') {
					$keyByName = ['relation_id', 'tabid', 'related_tabid', 'name', 'sequence', 'label', 'presence', 'actions', 'favorites', 'creator_detail', 'relation_comment'];
					$updateField = [];
					foreach ($relation['updateField'] as $key => $value) {
						$relation['data'][$key] = $value;
						$updateField[$keyByName[$key]] = $value;
					}
					if ($result->rowCount() > 0) {
						if (empty($updateField)) {
							\App\Log::error('ERROR ' . __METHOD__ . ' A row in vtiger_relatedlists was not updated due to lack of data. ' . print_r($relation, true));
						} else {
							$db->update('vtiger_relatedlists', $updateField, '`tabid` = ? AND `related_tabid` = ? AND `name` = ?;
', [$tabid, $relTabid, $name]);
						}
					} else {
						$relation['type'] = 'add';
						$this->setRelations([$relation]);
					}
				}
			}
		}
	}

	private function getConfigurations()
	{
		return [
				['name' => 'config/config.inc.php', 'conditions' => [
						['type' => 'remove', 'search' => 'Pop-up window type with record list'],
						['type' => 'remove', 'search' => 'popupType'],
						['type' => 'remove', 'search' => 'Enable encrypt backup, Suppor'],
						['type' => 'remove', 'search' => 'encryptBackup'],
						['type' => 'update', 'search' => 'prod and dem', 'replace' => ['prod and demo', 'prod, demo, test']],
				],
			],
				['name' => 'config/debug.php', 'conditions' => [
						['type' => 'add', 'search' => 'LOG_TO_CONSOLE', 'checkInContents' => 'LOG_TO_PROFILE', 'addingType' => 'after', 'value' => "	// Enable saving logs profiling.  Values: false/true
	'LOG_TO_PROFILE' => false,
"],
						['type' => 'update', 'search' => 'Examples: false, 3', 'replace' => ['Examples: false, 3, ', 'Values: false = All / 3 = error and warning / ']],
						['type' => 'update', 'search' => 'Examples: false, 3', 'replace' => ['Level of saved/displayed tracerts.', 'Level of saved/displayed tracerts. // Values: int']],
						['type' => 'add', 'search' => 'DISPLAY_DEBUG_CONSOLE', 'checkInContents' => 'DEBUG_CONSOLE_ALLOWED_IPS', 'addingType' => 'after', 'value' => "	// List of IP addresses allowed to display debug console
	// Values: false = All IPS / '192.168.1.10' / ['192.168.1.10','192.168.1.11']
	'DEBUG_CONSOLE_ALLOWED_IPS' => false,
"],
				]
			],
				['name' => 'config/modules/Accounts.php', 'conditions' => [
						['type' => 'add', 'search' => '$CONFIG = [', 'checkInContents' => 'FIELD_TO_UPDATE_BY_BUTTON', 'addingType' => 'after', 'value' => "	// List of date and time fields that can be updated by current system time, via button visible in record preview.
	// [Label => Name] 
	'FIELD_TO_UPDATE_BY_BUTTON' => [
	],
"],
				]
			],
				['name' => 'config/modules/Calendar.php', 'conditions' => [
						['type' => 'add', 'search' => '];', 'checkInContents' => 'CRON_MAX_NUMERS_ACTIVITY_STATE', 'addingType' => 'before', 'value' => "	// Max number of records to update status in cron
	'CRON_MAX_NUMERS_ACTIVITY_STATE' => 5000,
"],
						['type' => 'add', 'search' => '];', 'checkInContents' => 'CRON_MAX_NUMERS_ACTIVITY_STATS', 'addingType' => 'before', 'value' => "	// Max number of records to update calendar activity fields in related modules (in cron)
	'CRON_MAX_NUMERS_ACTIVITY_STATS' => 5000,
"],
				]
			],
				['name' => 'config/modules/OSSMail.php', 'conditions' => [
						['type' => 'update', 'search' => "config['plugins']", 'checkInLine' => 'archive', 'replace' => [');', ", 'archive');"]],
						['type' => 'update', 'search' => "config['debug_level']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_DEBUG_LEVEL']", "AppConfig::debug('ROUNDCUBE_DEBUG_LEVEL')"]],
						['type' => 'update', 'search' => "config['per_user_logging']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_PER_USER_LOGGING']", "AppConfig::debug('ROUNDCUBE_PER_USER_LOGGING')"]],
						['type' => 'update', 'search' => "config['smtp_log']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_SMTP_LOG']", "AppConfig::debug('ROUNDCUBE_SMTP_LOG')"]],
						['type' => 'update', 'search' => "config['log_logins']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_LOG_LOGINS']", "AppConfig::debug('ROUNDCUBE_LOG_LOGINS')"]],
						['type' => 'update', 'search' => "config['log_session']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_LOG_SESSION']", "AppConfig::debug('ROUNDCUBE_LOG_SESSION')"]],
						['type' => 'update', 'search' => "config['sql_debug']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_SQL_DEBUG']", "AppConfig::debug('ROUNDCUBE_SQL_DEBUG')"]],
						['type' => 'update', 'search' => "config['imap_debug']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_IMAP_DEBUG']", "AppConfig::debug('ROUNDCUBE_IMAP_DEBUG')"]],
						['type' => 'update', 'search' => "config['ldap_debug']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_LDAP_DEBUG']", "AppConfig::debug('ROUNDCUBE_LDAP_DEBUG')"]],
						['type' => 'update', 'search' => "config['smtp_debug']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_SMTP_DEBUG']", "AppConfig::debug('ROUNDCUBE_SMTP_DEBUG')"]],
						['type' => 'update', 'search' => "config['devel_mode']", 'replace' => ["\$DEBUG_CONFIG['ROUNDCUBE_DEVEL_MODE']", "AppConfig::debug('ROUNDCUBE_DEVEL_MODE')"]],
						['type' => 'add', 'search' => "config['root_directory']", 'checkInContents' => 'smtp_helo_host', 'addingType' => 'before', 'value' => "\$config['smtp_helo_host'] = 'YetiForceCRM';
"],
				]
			],
				['name' => 'config/performance.php', 'conditions' => [
						['type' => 'add', 'search' => "PERFORMANCE_CONFIG = [", 'checkInContents' => 'ENABLE_CACHING_DB_CONNECTION', 'addingType' => 'after', 'value' => "	// Enable caching database instance, accelerate time database connection
	'ENABLE_CACHING_DB_CONNECTION' => false,
"],
						['type' => 'add', 'search' => "PERFORMANCE_CONFIG = [", 'checkInContents' => 'CACHING_DRIVER', 'addingType' => 'after', 'value' => "	//Data caching is about storing some PHP variables in cache and retrieving it later from cache. Drivers: Base, Apcu
	'CACHING_DRIVER' => 'Base',
"],
						['type' => 'add', 'search' => "SEARCH_OWNERS_BY_AJAX", 'checkInContents' => 'SEARCH_ROLES_BY_AJAX', 'addingType' => 'after', 'value' => "	// Search roles by AJAX
	'SEARCH_ROLES_BY_AJAX' => false,
"],
						['type' => 'add', 'search' => "OWNER_MINIMUM_INPUT_LENGTH", 'checkInContents' => 'ROLE_MINIMUM_INPUT_LENGTH', 'addingType' => 'after', 'value' => "	// Minimum number of characters to search for role
	'ROLE_MINIMUM_INPUT_LENGTH' => 2,
"],
						['type' => 'add', 'search' => "CRON_MAX_NUMERS_RECORD_PRIVILEGES_UPDATER", 'checkInContents' => 'CRON_MAX_NUMERS_RECORD_ADDRESS_BOOCK_UPDATER', 'addingType' => 'after', 'value' => "	// In how many records should the address boock be updated in cron
	'CRON_MAX_NUMERS_RECORD_ADDRESS_BOOCK_UPDATER' => 10000,
"],
						['type' => 'add', 'search' => "CRON_MAX_NUMERS_RECORD_PRIVILEGES_UPDATER", 'checkInContents' => 'CRON_MAX_NUMERS_RECORD_LABELS_UPDATER', 'addingType' => 'after', 'value' => "	// In how many records should the label be updated in cron
	'CRON_MAX_NUMERS_RECORD_LABELS_UPDATER' => 1000,
"],
						['type' => 'add', 'search' => "CRON_MAX_NUMERS_RECORD_PRIVILEGES_UPDATER", 'checkInContents' => 'CRON_MAX_NUMERS_SENDING_MAILS', 'addingType' => 'after', 'value' => "	// In how many mails should the send in cron (Mailer).
	'CRON_MAX_NUMERS_SENDING_MAILS' => 1000,
"],
						['type' => 'add', 'search' => "LOAD_CUSTOM_FILES", 'checkInContents' => 'SHOW_ADMIN_PANEL', 'addingType' => 'after', 'value' => "	//Parameter that determines whether admin panel should be available to admin by default
	'SHOW_ADMIN_PANEL' => false,
"],
						['type' => 'add', 'search' => "];", 'checkInContents' => 'GLOBAL_SEARCH', 'addingType' => 'before', 'value' => "	//Global search: true/false
	'GLOBAL_SEARCH' => true,
"],
				]
			],
				['name' => 'config/secret_keys.php', 'conditions' => [
						['type' => 'remove', 'search' => 'key will be protected backup files'],
						['type' => 'remove', 'search' => 'backupPassword'],
				]
			],
				['name' => 'config/security.php', 'conditions' => [
						['type' => 'add', 'search' => "PERMITTED_BY_ADVANCED_PERMISSION", 'checkInContents' => 'PERMITTED_BY_PRIVATE_FIELD', 'addingType' => 'after', 'value' => "	'PERMITTED_BY_PRIVATE_FIELD' => true,
"],
						['type' => 'add', 'search' => "];", 'checkInContents' => 'LOGIN_PAGE_REMEMBER_CREDENTIALS', 'addingType' => 'before', 'value' => "	// Remember user credentials
	'LOGIN_PAGE_REMEMBER_CREDENTIALS' => false,
"],
				]
			],
				['name' => 'config/modules/SSalesProcesses.php', 'conditions' => []],
				['name' => 'config/modules/Mail.php', 'conditions' => []],
				['name' => '.htaccess', 'conditions' => [
						['type' => 'remove', 'search' => 'magic_quotes_gpc'],
						['type' => 'remove', 'search' => 'magic_quotes_runtime'],
						['type' => 'add', 'search' => "session.auto_start", 'checkInContents' => 'session.cookie_httponly', 'addingType' => 'before', 'value' => "	php_flag	session.cookie_httponly		On
	#php_flag	session.cookie_secure		On
"],
						['type' => 'add', 'search' => "FcgidIdleTimeout", 'checkInContents' => 'IfModule mod_fcgid.c', 'addingType' => 'after', 'value' => "</IfModule>
<IfModule mod_fcgid.c>
	IdleTimeout 600
	ProcessLifeTime 600
	IPCConnectTimeout 600
	IPCCommTimeout 600
	BusyTimeout 600
"],
				]
			],
		];
	}

	private function updateConfigurationFiles()
	{
		$rootDirectory = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		foreach ($this->getConfigurations() as $config) {
			if (!$config) {
				continue;
			}
			$conditions = $config['conditions'];
			$fileName = $rootDirectory . $config['name'];
			if (file_exists($fileName)) {
				$baseContent = file_get_contents($fileName);
				$configContent = file($fileName);
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
									if ($condition['checkInContents'] && strpos($baseContent, $condition['checkInContents']) === false) {
										if ($condition['trim']) {
											$configContent[$key] = $this->getTrimValue($condition['trim'], $configContent[$key]);
										}
										$configContent[$key] = $condition['addingType'] === 'before' ? $condition['value'] . $configContent[$key] : $configContent[$key] . $condition['value'];
									}
									unset($addContent[$index]);
									break;
								case 'remove':
									unset($configContent[$key]);
									$emptyLine = true;
									break;
								case 'update':
									if ($condition['checkInLine'] && (strpos($condition['checkInLine'], $configContent[$key]) !== false)) {
										break;
									}
									if ($condition['replace']) {
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
				$file = fopen($fileName, "w+");
				fwrite($file, $content);
				fclose($file);
			} else {
				$dirName = 'cache/updates/' . $config['name'];
				$sourceFile = $rootDirectory . $dirName;
				if (file_exists($sourceFile)) {
					copy($sourceFile, $fileName);
				}
			}
		}
	}

	private function getAlterTables($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
						['type' => ['change', 'Null'], 'validType' => 'YES', 'name' => 'doc_folder', 'table' => 'vtiger_ossdocumentcontrol', 'sql' => "ALTER TABLE `vtiger_ossdocumentcontrol` CHANGE `doc_folder` `doc_folder` int(19)   NULL after `summary`;"],
						['type' => ['remove'], 'name' => 'unblock', 'table' => 'vtiger_loginhistory', 'sql' => "ALTER TABLE `vtiger_loginhistory` 
							CHANGE `user_name` `user_name` varchar(32) NULL after `login_id` , 
							CHANGE `user_ip` `user_ip` varchar(50) NOT NULL after `user_name` , 
							CHANGE `logout_time` `logout_time` timestamp   NULL after `user_ip` , 
							CHANGE `login_time` `login_time` timestamp   NOT NULL after `logout_time` , 
							DROP COLUMN `unblock` , 
							DROP KEY `user_ip`, ADD KEY `user_ip`(`user_ip`,`login_time`,`status`) ;"],
						['type' => ['add'], 'name' => 'date', 'table' => 'vtiger_module_dashboard', 'sql' => "ALTER TABLE `vtiger_module_dashboard` 
							ADD COLUMN `date` varchar(20) NULL after `cache` ;"],
						['type' => ['add'], 'name' => 'dashboard_id', 'table' => 'vtiger_module_dashboard_blocks', 'sql' => "ALTER TABLE `vtiger_module_dashboard_blocks` 
							ADD COLUMN `dashboard_id` int(11) NULL after `tabid` ;"],
						['type' => ['add'], 'name' => 'date', 'table' => 'vtiger_module_dashboard_widgets', 'sql' => "ALTER TABLE `vtiger_module_dashboard_widgets` 
							ADD COLUMN `date` varchar(20) NULL after `cache`;"],
						['type' => ['add'], 'name' => 'dashboardid', 'table' => 'vtiger_module_dashboard_widgets', 'sql' => "ALTER TABLE `vtiger_module_dashboard_widgets` 
							ADD COLUMN `dashboardid` int(11) NULL after `date` ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'id', 'table' => 'vtiger_modtracker_basic', 'sql' => "ALTER TABLE `vtiger_modtracker_basic` 
							CHANGE `id` `id` int(20) NOT NULL auto_increment first ;"],
						['type' => ['change', 'Type'], 'validType' => 'int', 'name' => 'linkto', 'table' => 'vtiger_osspasswords', 'sql' => "ALTER TABLE `vtiger_osspasswords` 
							CHANGE `linkto` `linkto` int(11)   NULL after `link_adres` ;"],
						['type' => ['change', 'Type'], 'validType' => 'int', 'name' => 'customer', 'table' => 'vtiger_pbxmanager', 'sql' => "ALTER TABLE `vtiger_pbxmanager` 
							CHANGE `customer` `customer` int(11)   NULL after `gateway` ;"],
						['type' => ['change', 'Type'], 'validType' => 'int', 'name' => 'bean_id', 'table' => 'vtiger_users_last_import', 'sql' => "ALTER TABLE `vtiger_users_last_import` 
							CHANGE `bean_id` `bean_id` int(11)   NULL after `bean_type` ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'cvid', 'table' => 'vtiger_customview', 'sql' => "ALTER TABLE `vtiger_customview` 
							CHANGE `cvid` `cvid` int(19)   NOT NULL auto_increment first ;"],
						['type' => ['change', 'Null'], 'validType' => 'YES', 'name' => 'userid', 'table' => 'u_yf_crmentity_search_label', 'sql' => "ALTER TABLE `u_yf_crmentity_search_label` 	CHANGE `userid` `userid` text NULL after `setype` ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'id', 'table' => 'vtiger_calendar_user_activitytypes', 'sql' => "ALTER TABLE `vtiger_calendar_user_activitytypes` 
							CHANGE `id` `id` int(19)   NOT NULL auto_increment first , 
							ADD KEY `userid`(`userid`) ;"],
						['type' => ['change', 'Null'], 'validType' => 'YES', 'name' => 'lastname', 'table' => 'vtiger_leaddetails', 'sql' => "ALTER TABLE `vtiger_leaddetails` 
							CHANGE `lastname` `lastname` varchar(80) NULL after `salutation`;"],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'noapprovalcalls', 'table' => 'vtiger_leaddetails', 'sql' => "ALTER TABLE `vtiger_leaddetails` 
							CHANGE `noapprovalcalls` `noapprovalcalls` smallint(1)   NULL after `assignleadchk` , 
							CHANGE `noapprovalemails` `noapprovalemails` smallint(1)   NULL after `noapprovalcalls` ;"],
						['type' => ['add'], 'name' => 'private', 'table' => 'vtiger_crmentity', 'sql' => "ALTER TABLE `vtiger_crmentity`
							ADD COLUMN `private` tinyint(1)   NULL DEFAULT 0 after `was_read`;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'crmid', 'table' => 'vtiger_crmentity', 'sql' => "ALTER TABLE `vtiger_crmentity` 
							CHANGE `crmid` `crmid` int(19)   NOT NULL auto_increment first;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'relation_id', 'table' => 'vtiger_relatedlists', 'sql' => "ALTER TABLE `vtiger_relatedlists` 
							CHANGE `relation_id` `relation_id` smallint(19) unsigned NOT NULL auto_increment first ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'id', 'table' => 'vtiger_inventory_tandc', 'sql' => "ALTER TABLE `vtiger_inventory_tandc` 
							CHANGE `id` `id` int(11)   NOT NULL auto_increment first ;"],
				];
				break;
			case 2:
				$fields = [
						['type' => ['change', 'Type'], 'validType' => 'tinyint', 'name' => 'status', 'table' => 'a_yf_mapped_config', 'sql' => "ALTER TABLE `a_yf_mapped_config` 
							CHANGE `tabid` `tabid` smallint(6) unsigned   NOT NULL after `id` , 
							CHANGE `reltabid` `reltabid` smallint(6) unsigned   NOT NULL after `tabid` , 
							CHANGE `status` `status` tinyint(1) unsigned   NULL DEFAULT 0 after `reltabid` ;"],
				];
				break;
			case 3:
				$fields = [
						['type' => ['change', 'Type'], 'validType' => 'tinyint', 'name' => 'status', 'table' => 'a_yf_pdf', 'sql' => "ALTER TABLE `a_yf_pdf` 
							CHANGE `status` `status` tinyint(1) NOT NULL DEFAULT 0 after `footer_content` , 
							CHANGE `page_orientation` `page_orientation` varchar(30) NOT NULL after `footer_height` , 
							CHANGE `visibility` `visibility` varchar(200) NOT NULL after `filename` , 
							CHANGE `watermark_type` `watermark_type` tinyint(1)   NOT NULL DEFAULT 0 after `conditions` ;"],
				];
				break;
			case 4:
				$fields = [
//						['type' => ['change', 'exception'], 'name' => '', 'table' => 'vtiger_webforms_field', 'sql' => "ALTER TABLE `vtiger_webforms_field` DROP FOREIGN KEY `fk_3_vtiger_webforms_field`  ;"],
						['type' => ['add'], 'name' => 'fieldid', 'table' => 'vtiger_webforms_field', 'sql' => "ALTER TABLE `vtiger_webforms_field` 
							ADD COLUMN `fieldid` int(11)   NULL after `fieldname` , 
							CHANGE `neutralizedfield` `neutralizedfield` varchar(50) NOT NULL after `fieldid` , 
							CHANGE `defaultvalue` `defaultvalue` varchar(200) NULL after `neutralizedfield` , 
							CHANGE `required` `required` int(10) NOT NULL DEFAULT 0 after `defaultvalue` , 
							CHANGE `sequence` `sequence` int(10) NULL after `required` , 
							CHANGE `hidden` `hidden` int(10) NULL after `sequence` , 
							ADD KEY `fk_3_vtiger_webforms_field`(`fieldid`);"],
//						['type' => ['change', 'exception'], 'name' => '', 'table' => 'vtiger_webforms_field', 'sql' => "ALTER TABLE `vtiger_webforms_field`
//							ADD CONSTRAINT `fk_3_vtiger_webforms_field` 
//							FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE ;"],
				];
				break;
			case 5:
				$fields = [
						['type' => ['add'], 'name' => 'modules', 'table' => 'u_yf_watchdog_schedule', 'sql' => "ALTER TABLE `u_yf_watchdog_schedule` 
							ADD COLUMN `modules` text NULL after `last_execution` ;"],
				];
				break;
			case 6:
				$fields = [
						['type' => ['add'], 'name' => 'include_modules', 'table' => 'vtiger_eventhandlers', 'sql' => "ALTER TABLE `vtiger_eventhandlers` 
							CHANGE `event_name` `event_name` varchar(50) NOT NULL after `eventhandler_id` , 
							CHANGE `handler_class` `handler_class` varchar(100) NOT NULL after `event_name` , 
							CHANGE `is_active` `is_active` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `handler_class` , 
							ADD COLUMN `include_modules` varchar(255) NOT NULL DEFAULT '' after `is_active` , 
							ADD COLUMN `exclude_modules` varchar(255) NOT NULL DEFAULT '' after `include_modules` , 
							ADD COLUMN `priority` tinyint(1) unsigned   NOT NULL DEFAULT 5 after `exclude_modules` , 
							ADD COLUMN `owner_id` smallint(5) unsigned   NOT NULL DEFAULT 0 after `priority` , 
							DROP COLUMN `cond` , 
							DROP COLUMN `handler_path` , 
							DROP COLUMN `dependent_on` ;"],
				];
				break;
			case 7:
				$fields = [
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'uitype', 'table' => 'vtiger_ws_fieldtype', 'sql' => "ALTER TABLE `vtiger_ws_fieldtype` 
							CHANGE `uitype` `uitype` smallint(3)   NOT NULL after `fieldtypeid` ; "],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'duration_hours', 'table' => 'vtiger_activity', 'sql' => "ALTER TABLE `vtiger_activity` 
							CHANGE `duration_hours` `duration_hours` smallint(6)   NULL after `sendnotification` , 
							CHANGE `duration_minutes` `duration_minutes` smallint(3)   NULL after `duration_hours`;"],
						['type' => ['change', 'Null'], 'validType' => 'YES', 'name' => 'conditions', 'table' => 'a_yf_adv_permission', 'sql' => "ALTER TABLE `a_yf_adv_permission` 
							CHANGE `conditions` `conditions` text NULL after `action` ;"],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'emailoptout', 'table' => 'vtiger_account', 'sql' => "ALTER TABLE `vtiger_account` 
							CHANGE `emailoptout` `emailoptout` smallint(1)   NULL DEFAULT 0 after `employees` , 
							CHANGE `isconvertedfromlead` `isconvertedfromlead` smallint(3)   NULL DEFAULT 0 after `emailoptout` , 
							CHANGE `no_approval` `no_approval` smallint(1)   NULL DEFAULT 0 after `verification`;"],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'sendnotification', 'table' => 'vtiger_activity', 'sql' => "ALTER TABLE `vtiger_activity` 
							CHANGE `sendnotification` `sendnotification` smallint(1)   NOT NULL DEFAULT 0 after `time_end` ,  
							CHANGE `notime` `notime` smallint(1)   NOT NULL DEFAULT 0 after `location`;"],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'donotcall', 'table' => 'vtiger_contactdetails', 'sql' => "ALTER TABLE `vtiger_contactdetails` 
							CHANGE `donotcall` `donotcall` smallint(1)   NULL after `otheremail` , 
							CHANGE `emailoptout` `emailoptout` smallint(1)   NULL DEFAULT 0 after `donotcall` , 
							CHANGE `isconvertedfromlead` `isconvertedfromlead` smallint(1)   NULL DEFAULT 0 after `imagename` ;"],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'portal', 'table' => 'vtiger_customerdetails', 'sql' => "ALTER TABLE `vtiger_customerdetails` 
							CHANGE `portal` `portal` smallint(1)   NULL after `customerid` ;"],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'filestatus', 'table' => 'vtiger_notes', 'sql' => "ALTER TABLE `vtiger_notes` 
							CHANGE `filestatus` `filestatus` smallint(1)   NULL after `filedownloadcount` ;"],
						['type' => ['change', 'Null'], 'validType' => 'YES', 'name' => 'folderid', 'table' => 'vtiger_notes', 'sql' => "ALTER TABLE `vtiger_notes` 
							CHANGE `folderid` `folderid` varchar(255) NULL after `notecontent`;"],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'verify', 'table' => 'vtiger_ossmailview', 'sql' => "ALTER TABLE `vtiger_ossmailview` 
							CHANGE `attachments_exist` `attachments_exist` smallint(1)   NULL DEFAULT 0 after `ossmailview_sendtype` , 
							CHANGE `verify` `verify` smallint(1)   NULL DEFAULT 0 after `orginal_mail` "],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'active', 'table' => 'vtiger_pricebook', 'sql' => "ALTER TABLE `vtiger_pricebook` 
							CHANGE `active` `active` smallint(1)   NULL after `bookname` ;"],
						['type' => ['change', 'Type'], 'validType' => 'smallint', 'name' => 'from_portal', 'table' => 'vtiger_troubletickets', 'sql' => "ALTER TABLE `vtiger_troubletickets` 
							CHANGE `from_portal` `from_portal` smallint(1)   NULL after `ordertime` ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'shareid', 'table' => 'vtiger_datashare_module_rel', 'sql' => "ALTER TABLE `vtiger_datashare_module_rel` 
							CHANGE `shareid` `shareid` int(19) NOT NULL auto_increment first ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'blockid', 'table' => 'vtiger_blocks', 'sql' => "ALTER TABLE `vtiger_blocks` 
							CHANGE `blockid` `blockid` int(19)   NOT NULL auto_increment first ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'linkid', 'table' => 'vtiger_links', 'sql' => "ALTER TABLE `vtiger_links` 
							CHANGE `linkid` `linkid` int(11) NOT NULL auto_increment first ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'fieldid', 'table' => 'vtiger_settings_field', 'sql' => "ALTER TABLE `vtiger_settings_field` 
							CHANGE `fieldid` `fieldid` int(19)   NOT NULL auto_increment first;"],
						['type' => ['add'], 'name' => 'admin_access', 'table' => 'vtiger_settings_field', 'sql' => "ALTER TABLE `vtiger_settings_field` 
							ADD COLUMN `admin_access` text NULL after `pinned` ;"],
						['type' => ['change', 'Type'], 'validType' => 'varchar(255', 'name' => 'servicename', 'table' => 'vtiger_service', 'sql' => "ALTER TABLE `vtiger_service` 
							CHANGE `servicename` `servicename` varchar(255) NOT NULL after `service_no` ;"],
						['type' => ['add'], 'name' => 'auto_assign', 'table' => 'vtiger_role', 'sql' => "ALTER TABLE `vtiger_role` 
							ADD COLUMN `auto_assign` tinyint(1) unsigned   NOT NULL DEFAULT 0;"],
						['type' => ['add'], 'name' => 'admin_access', 'table' => 'vtiger_settings_blocks', 'sql' => "ALTER TABLE `vtiger_settings_blocks` 
							ADD COLUMN `admin_access` text NULL after `linkto` ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'crmid', 'table' => 'vtiger_crmentity', 'sql' => "ALTER TABLE `vtiger_crmentity` 
							CHANGE `crmid` `crmid` int(19) NOT NULL auto_increment first;"],
						['type' => ['add'], 'name' => 'id', 'table' => 's_yf_handler_updater', 'sql' => "ALTER TABLE `s_yf_handler_updater`(
							ADD COLUMN `id` int(11) NOT NULL  auto_increment , 
							ADD PRIMARY KEY (`id`) ;"],
						['type' => ['change', 'Type'], 'validType' => 'varchar(100', 'name' => 'email', 'table' => 'u_yf_competition', 'sql' => "ALTER TABLE `u_yf_competition` 
							CHANGE `email` `email` varchar(100) NULL DEFAULT '';"],
						['type' => ['change', 'Type'], 'validType' => 'varchar(100', 'name' => 'email', 'table' => 'u_yf_partners', 'sql' => "ALTER TABLE `u_yf_partners` 
							CHANGE `email` `email` varchar(100) NULL DEFAULT '';"],
						['type' => ['change', 'Type'], 'validType' => 'varchar(100', 'name' => 'secondary_email', 'table' => 'vtiger_contactdetails', 'sql' => "ALTER TABLE `vtiger_contactdetails` 
							CHANGE `secondary_email` `secondary_email` varchar(100) NULL DEFAULT '' after `verification`;"],
						['type' => ['change', 'Type'], 'validType' => 'varchar(100', 'name' => 'business_mail', 'table' => 'vtiger_ossemployees', 'sql' => "ALTER TABLE `vtiger_ossemployees` 
							CHANGE `business_mail` `business_mail` varchar(100) NULL  , 
							CHANGE `private_mail` `private_mail` varchar(100) NULL ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'portalid', 'table' => 'vtiger_portal', 'sql' => "ALTER TABLE `vtiger_portal` 
							CHANGE `portalid` `portalid` int(19) NOT NULL auto_increment first ;"],
						['type' => ['change', 'Extra'], 'validType' => 'auto_increment', 'name' => 'rssid', 'table' => 'vtiger_rss', 'sql' => "ALTER TABLE `vtiger_rss` 
							CHANGE `rssid` `rssid` int(19) NOT NULL auto_increment first ;"],
						['type' => ['add'], 'name' => 'failed_login', 'table' => 'roundcube_users', 'sql' => "ALTER TABLE `roundcube_users` 
							ADD COLUMN `failed_login` datetime   NULL after `last_login` , 
							ADD COLUMN `failed_login_counter` int(10) unsigned   NULL after `failed_login`;"],
						['type' => ['remove', 'Key_name'], 'name' => 'relatedid', 'table' => 'u_yf_notification', 'sql' => "ALTER TABLE `u_yf_notification` 
							DROP KEY `relatedid`;"],
//						['type' => ['add'], 'name' => 'notificationid', 'table' => 'u_yf_notification', 'sql' => "ALTER TABLE `u_yf_notification`
//							DROP FOREIGN KEY `fk_1_notification`;"],
					['type' => ['add'], 'name' => 'notificationid', 'table' => 'u_yf_notification', 'sql' => "ALTER TABLE `u_yf_notification` 
							ADD COLUMN `notificationid` int(11)   NOT NULL first , 
							CHANGE `title` `title` varchar(255)  NULL after `notificationid` ,
							DROP COLUMN `id` ,
							DROP KEY `PRIMARY`, ADD PRIMARY KEY(`notificationid`);"],
//						['type' => ['change', 'exception'], 'name' => 'fk_1_notification', 'table' => 'u_yf_notification', 'sql' => "ALTER TABLE `u_yf_notification`
//							ADD CONSTRAINT `fk_1_notification` FOREIGN KEY (`notificationid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;"],
				];
				break;
			case 8:
				$fields = [
						['type' => ['add'], 'name' => 'params', 'table' => 's_yf_mail_smtp', 'sql' => "ALTER TABLE `s_yf_mail_smtp` 
							ADD COLUMN `params` text;"],
						['type' => ['remove'], 'name' => 'type', 'table' => 'vtiger_import_queue', 'sql' => "ALTER TABLE `vtiger_import_queue` 
							CHANGE `importid` `importid` int(11)   NOT NULL auto_increment first , 
							CHANGE `tabid` `tabid` smallint(11) unsigned   NOT NULL after `userid` , 
							CHANGE `temp_status` `temp_status` tinyint(1)   NULL DEFAULT 0 after `merge_fields` , 
							DROP COLUMN `type` ;"],
						['type' => ['add', 'Key_name'], 'name' => 'link', 'table' => 'u_yf_notification', 'sql' => "ALTER TABLE `u_yf_notification` ADD KEY `link`(`link`) ;"],
						['type' => ['add', 'Key_name'], 'name' => 'process', 'table' => 'u_yf_notification', 'sql' => "ALTER TABLE `u_yf_notification` ADD KEY `process`(`process`) ;"],
						['type' => ['add', 'Key_name'], 'name' => 'subprocess', 'table' => 'u_yf_notification', 'sql' => "ALTER TABLE `u_yf_notification` ADD KEY `subprocess`(`subprocess`) ;"],
						['type' => ['add', 'exception'], 'name' => 'private', 'table' => 'vtiger_crmentity', 'sql' => "ALTER TABLE `vtiger_crmentity` 
							CHANGE `private` `private` tinyint(1)   NULL DEFAULT 0  after `was_read`; "],
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function setAlterTables($data)
	{
		$db = PearDatabase::getInstance();
		$db->query('SET FOREIGN_KEY_CHECKS = 0;');
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
				if (!$checkSql) {
					continue;
				}
				$result = $db->query($checkSql);
				$num = $result->rowCount();
				if (( $num === 0 && $alter['type'][0] === 'add') || ($num > 0 && $alter['type'][0] === 'remove')) {
					$db->query($alter['sql']);
				} elseif ($num == 1 && in_array($alter['type'][0], ['change', 'changeTable'])) {
					$row = $db->getRow($result);
					if (strpos($row[$alter['type'][1]], $alter['validType']) === false) {
						$db->query($alter['sql']);
					}
				}
			}
		}
	}

	private function getActionMapp($index)
	{
		$actions = [];
		switch ($index) {
			case 1:
				$actions = [
						['type' => 'add', 'name' => 'DuplicateRecord', 'tabsData' => [\vtlib\Functions::getModuleId('SSalesProcesses')]],
						['type' => 'add', 'name' => 'MassComposeEmail', 'tabsData' => [\vtlib\Functions::getModuleId('Competition'), \vtlib\Functions::getModuleId('Partners')]],
				];
				break;
			default:
				break;
		}
		return $actions;
	}

	private function actionMapp($actions)
	{
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
			$permission = 1;
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
						$db->insert('vtiger_profile2utility', [
							'profileid' => $profileId, 'tabid' => $tabid, 'activityid' => $key, 'permission' => $permission
						]);
					}
				}
			}
		}
	}

	private function updatePack()
	{
		$db = \PearDatabase::getInstance();
		$db->update('vtiger_field', ['typeofdata' => 'DT~O'], 'tablename = ? AND columnname IN (?,?) AND typeofdata = ?', ['vtiger_callhistory', 'start_time', 'end_time', 'V~O']);
//		$db->update('yetiforce_menu', ['icon' => 'fa fa-ticket'], 'role = ? AND icon = ? AND module = ?', [0, '', \vtlib\Functions::getModuleId('HelpDesk')]);
//		$db->update('yetiforce_menu', ['icon' => 'fa fa-ticket'], 'role = ? AND icon = ? AND label = ? AND type = ?', [0, 'userIcon-Support', 'MEN_SUPPORT', 2]);
		$db->update('vtiger_cvcolumnlist', ['columnindex' => 'u_yf_notification:title:title:Notification_FL_TITLE:V'], 'columnindex = ?', ['vtiger_notification:title:title:Notification_FL_TITLE:V']);
		$db->update('vtiger_cvcolumnlist', ['columnindex' => 'u_yf_notification:number:number:Notification_FL_NUMBER:V'], 'columnindex = ?', ['vtiger_notification:number:number:Notification_FL_NUMBER:V']);
		$db->update('vtiger_picklist', ['name' => 'notification_type'], 'name = ?', ['notificationtype']);
		$noticeTabId = \vtlib\Functions::getModuleId('Notification');

		$db->delete('vtiger_homestuff', 'stufftitle = ?', ['Tag Cloud']);
		$this->setLink($this->getLink(1));
		$resultC = $db->query('SHOW COLUMNS FROM `u_yf_notification` LIKE "link"');
		if (!$db->getRowCount($resultC)) {
			$db->delete('vtiger_crmentity', 'setype = ?', ['Notification']);
			$db->delete('u_yf_notification');
			$this->removeFields($this->getFieldsToRemove(1));
			$this->setFields($this->getFields(1));
			$db->update('vtiger_field', ['defaultvalue' => 'PLL_USERS'], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'notification_status']);
			$db->update('vtiger_field', ['quickcreatesequence' => 1, 'quickcreate' => 1], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'title']);
			$db->update('vtiger_field', ['quickcreatesequence' => 2, 'quickcreate' => 2], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'shownerid']);
			$db->update('vtiger_field', ['quickcreatesequence' => 3, 'quickcreate' => 1], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'smownerid']);
			$db->update('vtiger_field', ['quickcreatesequence' => 4, 'quickcreate' => 2], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'notification_type']);
			$db->update('vtiger_field', ['quickcreatesequence' => 5, 'quickcreate' => 2], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'notification_status']);
			$db->update('vtiger_field', ['quickcreatesequence' => 6, 'quickcreate' => 2], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'process']);
			$db->update('vtiger_field', ['quickcreatesequence' => 7, 'quickcreate' => 2], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'subprocess']);
			$db->update('vtiger_field', ['quickcreatesequence' => 8, 'quickcreate' => 2], 'tablename = ? AND columnname = ?', ['u_yf_notification', 'description']);
		}
		$db->update('vtiger_blocks', ['blocklabel' => 'LBL_NOTIFICATION_CONTENTS'], 'tabid = ? AND blocklabel = ?', [\vtlib\Functions::getModuleId('Notification'), 'LBL_CUSTOM_INFORMATION']);
		$db->update('vtiger_field', ['displaytype' => 10], 'tablename = ? AND columnname IN (?,?)', ['u_yf_notification', 'notification_type', 'notification_status']);
		$db->update('vtiger_field', ['displaytype' => 2], 'tabid = ? AND columnname = ?', [\vtlib\Functions::getModuleId('Notification'), 'smcreatorid']);
		$this->setLink($this->getLink(2));
		$resultC = $db->query('SHOW TABLES LIKE "vtiger_bruteforce"');
		if ($db->getRowCount($resultC)) {
			$result = $db->query('SELECT * FROM vtiger_bruteforce');
			$data = $db->getRow($result);
			$db->insert('a_yf_bruteforce', $data);
			$result = $db->query('SELECT id FROM vtiger_bruteforce_users');
			while ($row = $db->getRow($result)) {
				$db->insert('vtiger_bruteforce_users', $row);
			}
			$this->setTablesScheme($this->getTablesAction(2));
		}
		$this->setFields($this->getFields(2));
		$resultC = $db->query("SHOW COLUMNS FROM `a_yf_mapped_config` LIKE 'status'");
		$columnData = $db->getRow($resultC);
		if (strpos($columnData['Type'], 'tinyint') === false) {
			$this->setAlterTables($this->getAlterTables(2));
			$db->update('a_yf_mapped_config', ['status' => 0], 'status = ?', [2]);
		}
		$this->picklists($this->getPicklistsToAction(1));
		$db->update('vtiger_finvoice_formpayment', ['presence' => 0], 'finvoice_formpayment IN (?,?,?)', ['PLL_TRANSFER', 'PLL_CASH', 'PLL_WIRE_TRANSFER']);
		$db->update('vtiger_finvoice_paymentstatus', ['presence' => 0], 'finvoice_paymentstatus IN (?,?,?)', ['PLL_AWAITING_PAYMENT', 'PLL_PARTIALLY_PAID', 'PLL_FULLY_PAID']);
		$db->update('vtiger_finvoice_type', ['presence' => 0], 'finvoice_type IN (?,?,?)', ['PLL_DOMESTIC_INVOICE', 'PLL_FOREIGN_INVOICE', 'PLL_IC_INVOICE']);
		$resultC = $db->query("SHOW COLUMNS FROM `a_yf_pdf` LIKE 'status'");
		$columnData = $db->getRow($resultC);
		if (strpos($columnData['Type'], 'tinyint') === false) {
			$this->setAlterTables($this->getAlterTables(3));
			$db->update('a_yf_pdf', ['status' => 0], 'status = ?', [2]);
			$db->update('a_yf_pdf', ['watermark_type' => 0], 'watermark_type = ?', [1]);
			$db->update('a_yf_pdf', ['watermark_type' => 1], 'watermark_type = ?', [2]);
		}
		$db->update('vtiger_leadstatus', ['leadstatus' => 'PLL_CONTACTS_IN_THE_FUTURE'], 'leadstatus = ?', ['LBL_CONTACTS_IN_THE_FUTURE']);
		$db->update('vtiger_leaddetails', ['leadstatus' => 'PLL_CONTACTS_IN_THE_FUTURE'], 'leadstatus = ?', ['LBL_CONTACTS_IN_THE_FUTURE']);
		$db->update('vtiger_leadstatus', ['leadstatus' => 'PLL_TO_REALIZE'], 'leadstatus = ?', ['LBL_TO_REALIZE']);
		$db->update('vtiger_leaddetails', ['leadstatus' => 'PLL_TO_REALIZE'], 'leadstatus = ?', ['LBL_TO_REALIZE']);
		$db->update('vtiger_leadstatus', ['leadstatus' => 'PLL_LEAD_UNTAPPED'], 'leadstatus = ?', ['LBL_LEAD_UNTAPPED']);
		$db->update('vtiger_leaddetails', ['leadstatus' => 'PLL_LEAD_UNTAPPED'], 'leadstatus = ?', ['LBL_LEAD_UNTAPPED']);
		$db->update('vtiger_leadstatus', ['leadstatus' => 'PLL_LEAD_ACQUIRED'], 'leadstatus = ?', ['LBL_LEAD_ACQUIRED']);
		$db->update('vtiger_leaddetails', ['leadstatus' => 'PLL_LEAD_ACQUIRED'], 'leadstatus = ?', ['LBL_LEAD_ACQUIRED']);
		$db->update('yetiforce_proc_marketing', ['value' => 'PLL_LEAD_ACQUIRED'], 'value = ?', ['LBL_LEAD_ACQUIRED']);
		$this->setRelations($this->getRelations(1));
		$resultC = $db->query("SHOW COLUMNS FROM `vtiger_webforms_field` LIKE 'fieldid'");
		if (!$db->getRowCount($resultC)) {
			$this->setAlterTables($this->getAlterTables(4));
		}
		$resultC = $db->query('SELECT 1 FROM u_yf_dashboard_type;');
		if (!$db->getRowCount($resultC)) {
			$db->insert('u_yf_dashboard_type', [
				'name' => 'LBL_MAIN_PAGE', 'system' => 1
			]);
			$db->update('vtiger_module_dashboard_blocks', ['dashboard_id' => 1]);
			$db->update('vtiger_module_dashboard_widgets', ['dashboardid' => 1]);
		}
		$resultC = $db->query("SHOW COLUMNS FROM `vtiger_activity_reminder_popup` LIKE 'datetime'");
		if (!$db->getRowCount($resultC)) {
			$db->query("ALTER TABLE `vtiger_activity_reminder_popup` ADD COLUMN `datetime` datetime NOT NULL after `recordid`;");
			$db->query("UPDATE `vtiger_activity_reminder_popup` SET `datetime` = CONCAT(date_start,' ',time_start)");
			$db->query("ALTER TABLE `vtiger_activity_reminder_popup` DROP COLUMN `date_start`, DROP COLUMN `time_start` ;");
		}
		$resultC = $db->query("SHOW COLUMNS FROM `u_yf_watchdog_module` LIKE 'member'");
		if (!$db->getRowCount($resultC)) {
			$db->query("ALTER TABLE `u_yf_watchdog_module` 
				ADD COLUMN `member` varchar(50) NOT NULL first , 
				CHANGE `module` `module` int(11) unsigned   NOT NULL after `member` , 
				ADD COLUMN `lock` tinyint(1)  NULL DEFAULT 0 after `module` , 
				ADD COLUMN `exceptions` text NULL after `lock`;");
			$db->query("UPDATE `u_yf_watchdog_module` SET `member` = CONCAT('Users:',userid)");
			$db->query("ALTER TABLE `u_yf_watchdog_module` DROP COLUMN `userid` , 
				DROP KEY `PRIMARY`, ADD PRIMARY KEY(`member`,`module`) , 
				DROP KEY `userid`, ADD KEY `userid`(`member`) ;");
			$db->delete('u_yf_watchdog_schedule');
			$this->setAlterTables($this->getAlterTables(5));
		}
		$db->update('vtiger_notification_type', ['presence' => 0], 'notification_type IN (?,?)', ['PLL_USERS', 'PLL_SYSTEM']);
		$this->setFields($this->getFields(3));
		$db->update('vtiger_field', ['typeofdata' => 'V~M'], 'tablename = ? AND columnname = ? AND typeofdata = ?', ['vtiger_pricebook', 'currency_id', 'I~M']);
		$db->update('vtiger_field', ['typeofdata' => 'V~O'], 'tablename = ? AND columnname = ? AND typeofdata = ?', ['vtiger_pricebook', 'currency_id', 'I~O']);
		$db->update('vtiger_field', ['typeofdata' => 'V~O'], 'tablename = ? AND columnname = ? AND typeofdata = ?', ['vtiger_users', 'currency_id', 'I~O']);
		$db->update('vtiger_field', ['typeofdata' => 'V~M'], 'tablename = ? AND columnname = ? AND typeofdata = ?', ['vtiger_paymentsin', 'paymentscurrency', 'I~M']);
		$db->update('vtiger_field', ['typeofdata' => 'V~O'], 'tablename = ? AND columnname = ? AND typeofdata = ?', ['vtiger_paymentsin', 'paymentscurrency', 'I~O']);
		$db->update('vtiger_field', ['typeofdata' => 'V~M'], 'tablename = ? AND columnname = ? AND typeofdata = ?', ['vtiger_paymentsout', 'paymentscurrency', 'I~M']);
		$db->update('vtiger_field', ['typeofdata' => 'V~O'], 'tablename = ? AND columnname = ? AND typeofdata = ?', ['vtiger_paymentsout', 'paymentscurrency', 'I~O']);
		$db->delete('vtiger_ws_entity', '`name` IN (?,?,?)', ['ProductTaxes', 'LineItem', 'Tax']);
		$db->delete('vtiger_ws_operation', '`name` = "retrieve_inventory"');
		$resultC = $db->pquery('SELECT fieldtypeid FROM vtiger_ws_entity_fieldtype WHERE table_name = ?;', ['vtiger_producttaxrel']);
		$fieldTypeIds = $db->getArrayColumn($resultC);
		if ($fieldTypeIds) {
			$db->delete('vtiger_ws_entity_fieldtype', '`table_name` = ?', ['vtiger_producttaxrel']);
			$db->delete('vtiger_ws_entity_referencetype', '`fieldtypeid` IN (' . $db->generateQuestionMarks($fieldTypeIds) . ')', $fieldTypeIds);
		}
		$db->delete('vtiger_ws_entity_name', '`table_name` = ?', ['vtiger_inventorytaxinfo']);
		$db->delete('vtiger_ws_entity_tables', '`table_name` IN (?, ?)', ['vtiger_inventorytaxinfo', 'vtiger_producttaxrel']);
		$resultC = $db->pquery('SELECT fieldid FROM vtiger_field WHERE tablename = ? AND columnname in (?,?,?);', ['vtiger_users', 'is_admin', 'roleid', 'status']);
		$fieldTypeIds = $db->getArrayColumn($resultC);
		if ($fieldTypeIds) {
			$resultP = $db->query('SELECT profileid FROM vtiger_profile;');
			$profileIds = $db->getArrayColumn($resultP);
			$tabId = \vtlib\Functions::getModuleId('Users');
			foreach ($fieldTypeIds as $fieldId) {
				$resultC = $db->pquery('SELECT 1 FROM vtiger_def_org_field WHERE fieldid=? ;', [$fieldId]);
				if (!$db->getRowCount($resultC)) {
					$db->insert('vtiger_def_org_field', ['tabid' => $tabId, 'fieldid' => $fieldId, 'visible' => 0, 'readonly' => 0]);
				}
				$resultC = $db->pquery('SELECT profileid FROM vtiger_profile2field WHERE vtiger_profile2field.`fieldid` = ?', [$fieldId]);
				$undoProfileIds = $db->getArrayColumn($resultC);
				$profiles = array_diff($profileIds, $undoProfileIds);
				foreach ($profiles as $profile) {
					$db->insert('vtiger_profile2field', ['profileid' => $profile, 'tabid' => $tabId, 'fieldid' => $fieldId, 'visible' => 0, 'readonly' => 0]);
				}
			}
		}

		$this->setFields($this->getFields(4));
		$db->update('vtiger_field', ['typeofdata' => 'V~O'], 'tablename = ? AND columnname = ? AND typeofdata = ?', ['vtiger_products', 'taxes', 'V~I']);
		$this->updateRelations();

		$db->update('vtiger_field', ['presence' => 1], 'tablename = ? AND columnname = ? AND presence = ?', ['vtiger_products', 'category_multipicklist', 2]);
		$db->update('vtiger_field', ['presence' => 1], 'tablename = ? AND columnname = ? AND presence = ?', ['vtiger_activity', 'recurringtype', 0]);
		$data = ['vtiger_osssoldservices:invoiceid:invoiceid:OSSSoldServices_Invoice:V', 'vtiger_seactivityrel:crmid:parent_id:Calendar_Related_to:V', 'vtiger_activity:status:taskstatus:Calendar_Status:V', 'vtiger_accountbillads:bill_city:bill_city:Accounts_City:V'];
		$db->delete('vtiger_cvcolumnlist', '`columnindex` IN (' . $db->generateQuestionMarks($data) . ')', $data);
		$this->updateFileExtensionInDB();
		$resultC = $db->pquery('SELECT actionid FROM vtiger_actionmapping WHERE actionname = ?;', ['TagCloud']);
		$actionId = $db->getSingleValue($resultC);
		if ($actionId) {
			$db->delete('vtiger_actionmapping', '`actionname` = ?', ['TagCloud']);
			$db->delete('vtiger_profile2utility', '`activityid` = ?', [$actionId]);
		}
		$db->update('com_vtiger_workflows', ['nexttrigger_time' => null], '`nexttrigger_time` = ?', ['0000-00-00 00:00:00']);
		$db->delete('vtiger_ws_entity_tables', '`table_name` = ?', ['vtiger_inventoryproductrel']);
		$this->changeInHandlers();
		$db->update('vtiger_ws_fieldtype', ['uitype' => 'userCreator'], '`uitype` = ?', [52]);
		$this->setAlterTables($this->getAlterTables(7));
		$resultC = $db->pquery('SELECT 1 FROM vtiger_ws_fieldtype WHERE fieldtype = ?;', ['recordNumber']);
		if (!$db->getRowCount($resultC)) {
			$db->insert('vtiger_ws_fieldtype', ['fieldtype' => 'recordNumber', 'uitype' => 4]);
		}
		$db->update('vtiger_field', ['typeofdata' => 'I~O'], 'tablename = ? AND columnname IN (?,?)', ['vtiger_activity', 'duration_hours', 'duration_minutes']);
		$db->update('vtiger_users', ['available' => 1]);
		$db->query('UPDATE vtiger_no_of_currency_decimals SET `no_of_currency_decimalsid` = no_of_currency_decimalsid + 1 ORDER BY no_of_currency_decimalsid DESC');
		$db->getUniqueID('vtiger_no_of_currency_decimals');
		$db->update('vtiger_field', ['displaytype' => 10], 'tablename = ? AND columnname IN (?)', ['vtiger_reservations', 'sum_time']);
		$resultC = $db->pquery('SELECT 1 FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?;', ['VTAutoAssign']);
		if (!$db->getRowCount($resultC)) {
			$db->insert('com_vtiger_workflow_tasktypes', [
				'id' => $db->getUniqueID("com_vtiger_workflow_tasktypes"),
				'tasktypename' => 'VTAutoAssign',
				'label' => 'LBL_AUTO_ASSIGN',
				'classname' => 'VTAutoAssign',
				'classpath' => 'modules/com_vtiger_workflow/tasks/VTAutoAssign.php',
				'templatepath' => 'com_vtiger_workflow/taskforms/VTAutoAssign.tpl',
				'modules' => '{"include":[],"exclude":[]}',
				'sourcemodule' => null
			]);
		}
		$this->tablesInventory();
		$this->setTablesScheme($this->getTablesAction(3));
		$this->setAlterTables($this->getAlterTables(8));
		$searchColumn = [
			'PaymentsIn' => ['new' => 'paymentsno,paymentsname', 'old' => 'paymentsinid'],
			'PaymentsOut' => ['new' => 'paymentsno,paymentsname', 'old' => 'paymentsoutid'],
			'LettersIn' => ['new' => 'title', 'old' => 'lettersinid'],
			'LettersOut' => ['new' => 'title', 'old' => 'lettersoutid'],
			'Reservations' => ['new' => 'title', 'old' => 'reservationsid']
		];
		foreach ($searchColumn as $moduleName => $data) {
			$result = $db->update('vtiger_entityname', ['searchcolumn' => $data['new']], 'modulename = ? AND searchcolumn = ?', [$moduleName, $data['old']]);
			if ($result) {
				$db->update('u_yf_crmentity_search_label', ['searchlabel' => ''], 'setype = ?', [$moduleName]);
				$db->query("DELETE FROM u_yf_crmentity_label WHERE u_yf_crmentity_label.crmid IN (SELECT vtiger_crmentity.crmid FROM vtiger_crmentity WHERE vtiger_crmentity.setype ='$moduleName')");
			}
		}
		$this->actionMapp($this->getActionMapp(1));
		$db->update('vtiger_calendar_config', ['name' => 'notworkingdays'], '`name` = ?', ['notworkingdays ']);
		$relationsCombine = ['Activity state' => 'LBL_ACTIVITY_STATE',
			'Assets Renewal' => 'LBL_ASSETS_RENEWAL',
			'CalDav' => 'LBL_CAL_DAV',
			'CardDav' => 'LBL_CARD_DAV',
			'LBL_ADDRESS_BOOCK' => 'LBL_ADDRESS_BOOK',
			'MailScannerAction' => 'LBL_MAIL_SCANNER_ACTION',
			'MailScannerBind' => 'LBL_MAIL_SCANNER_BIND',
			'MailScannerVerification' => 'LBL_MAIL_SCANNER_VERIFICATION',
			'PrivilegesUpdater' => 'LBL_PRIVILEGES_UPDATER',
			'RecordLabelUpdater' => 'LBL_RECORD_LABEL_UPDATER',
			'Scheduled Import' => 'LBL_SCHEDULED_IMPORT',
			'ScheduleReports' => 'LBL_SCHEDULE_REPORTS',
			'SendReminder' => 'LBL_SEND_REMINDER',
			'SoldServices Renewal' => 'LBL_SOLD_SERVICES_RENEWAL',
			'UpdaterCoordinates' => 'LBL_UPDATER_COORDINATES',
			'UpdaterRecordsCoordinates' => 'LBL_UPDATER_RECORDS_COORDINATES',
			'Workflow' => 'LBL_WORKFLOW'
		];
		$query = 'UPDATE vtiger_cron_task SET `name` = CASE ';
		foreach ($relationsCombine as $oldname => $newName) {
			$query .= " WHEN `name`='$oldname'  THEN '$newName' ";
		}
		$query .= ' ELSE `name` END WHERE `name` IN (' . $db->generateQuestionMarks($relationsCombine) . ')';
		$db->pquery($query, array_keys($relationsCombine));
		$this->deleteWorkflows();
		$this->updateLeadPicklist();
		$patch = 'config/modules/Mail.php';
		$patch2 = 'config/modules/Email.php';
		if (file_exists($patch2) && !file_exists($patch)) {
			rename($patch2, $patch);
		}
		$db->update('vtiger_entityname', ['entityidfield' => 'notificationid', 'entityidcolumn' => 'notificationid'], 'entityidfield = ?', ['id']);
		$this->cron($this->getCronData(3));
		$this->setRelations($this->getRelations(2));
		$resultC = $db->query('SHOW CREATE TABLE u_yf_notification');
		$table = $db->getRow($resultC);
		if (strpos($table['Create Table'], 'fk_1_notification') === false) {
			$db->query('ALTER TABLE `u_yf_notification` ADD CONSTRAINT `fk_1_notification` FOREIGN KEY (`notificationid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE ;');
		}
		$resultC = $db->query('SHOW CREATE TABLE vtiger_webforms_field');
		$table = $db->getRow($resultC);
		if (strpos($table['Create Table'], 'CONSTRAINT `fk_3_vtiger_webforms_field`') === false) {
			$db->query('ALTER TABLE `vtiger_webforms_field`	ADD CONSTRAINT `fk_3_vtiger_webforms_field` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE ;');
		}
		$db->delete('vtiger_eventhandlers', 'handler_class = ?', ['']);
	}

	private function updateRelations()
	{
		$db = PearDatabase::getInstance();
		$relationsCombine = ['get_contacts' => 'getContacts',
			'get_activities' => 'getActivities',
			'get_emails' => 'getEmails',
			'get_dependents_list' => 'getDependentsList',
			'get_attachments' => 'getAttachments',
			'get_tickets' => 'getDependentsList',
			'get_products' => 'getProducts',
			'get_campaigns' => 'getCampaigns',
			'get_product_pricebooks' => 'getProductPricebooks',
			'get_leads' => 'getLeads',
			'get_accounts' => 'getAccounts',
			'get_parent_products' => 'getParentProducts',
			'get_ticket_history' => 'getTicketHistory',
			'get_pricebook_products' => 'getPricebookProducts',
			'get_users' => 'getUsers',
			'get_campaigns_records' => 'getCampaignsRecords',
			'get_related_list' => 'getRelatedList',
			'get_pricebook_services' => 'getPricebookServices',
			'get_service_pricebooks' => 'getServicePricebooks',
			'get_gantt_chart' => 'getGanttChart',
			'get_record2mails' => 'getRecordToMails',
			'get_osstimecontrol' => 'getOsstimecontrol',
			'get_many_to_many' => 'getManyToMany'
		];
		$query = 'UPDATE vtiger_relatedlists SET `name` = CASE ';
		foreach ($relationsCombine as $oldname => $newName) {
			$query .= " WHEN `name`='$oldname'  THEN '$newName' ";
		}
		$query .= ' ELSE `name` END WHERE `name` IN (' . $db->generateQuestionMarks($relationsCombine) . ')';
		$db->pquery($query, array_keys($relationsCombine));
		$db->update('vtiger_relatedlists', ['name' => 'getDependentsList'], 'name = ?', ['getTickets']);
		$db->update('vtiger_relatedlists', ['name' => 'getDependentsList'], '`tabid` = ? AND `related_tabid` = ? AND name = ?', [\vtlib\Functions::getModuleId('Accounts'), \vtlib\Functions::getModuleId('Contacts'), 'getContacts']);
		$db->update('vtiger_relatedlists', ['name' => 'getDependentsList'], '`tabid` = ? AND `related_tabid` = ? AND name = ?', [\vtlib\Functions::getModuleId('Vendors'), \vtlib\Functions::getModuleId('Project'), 'getRelatedList']);
		$db->delete('vtiger_relatedlists', '`name` IN (?,?,?);', ['getGanttChart', 'getTicketHistory', 'getUsers']);
		$db->delete('vtiger_relatedlists', '`tabid` = ?;', [\vtlib\Functions::getModuleId('Emails')]);
	}

	private function updateFileExtensionInDB()
	{
		$db = PearDatabase::getInstance();
		$cron = [
			'cron/modules/com_vtiger_workflow/com_vtiger_workflow.service' => 'cron/modules/com_vtiger_workflow/com_vtiger_workflow.php',
			'cron/SendReminder.service' => 'cron/SendReminder.php',
			'cron/modules/Import/ScheduledImport.service' => 'cron/modules/Import/ScheduledImport.php',
			'cron/modules/Reports/ScheduleReports.service' => 'cron/modules/Reports/ScheduleReports.php',
			'cron/AddressBoock.php' => 'cron/AddressBook.php',
			'cron/MultiReference.service' => 'cron/MultiReference.php'
		];
		$query = 'UPDATE vtiger_cron_task SET `handler_file` = CASE ';
		foreach ($cron as $oldName => $newName) {
			$query .= " WHEN `handler_file`='$oldName'  THEN '$newName' ";
		}
		$query .= ' ELSE `handler_file` END WHERE `handler_file` IN (' . $db->generateQuestionMarks($relationsCombine) . ')';
		$db->pquery($query, array_keys($cron));
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTEmailTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTEmailTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTCreateTodoTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTCreateTodoTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTCreateEventTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTCreateEventTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTCreateEntityTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTCreateEntityTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTSMSTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTSMSTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTSendPdf.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTSendPdf.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTUpdateClosedTime.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTUpdateClosedTime.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTSendNotificationTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTSendNotificationTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTAddressBookTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTAddressBookTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTUpdateCalendarDates.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTUpdateCalendarDates.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTUpdateWorkTime.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTUpdateWorkTime.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTUpdateRelatedFieldTask.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTUpdateRelatedFieldTask.inc']);
		$db->update('com_vtiger_workflow_tasktypes', ['classpath' => 'modules/com_vtiger_workflow/tasks/VTWatchdog.php'], 'classpath = ?', ['modules/com_vtiger_workflow/tasks/VTWatchdog.inc']);
	}

	private function getMax($table, $field, $filter = '')
	{
		$db = PearDatabase::getInstance();
		$result = $db->query("SELECT MAX($field) AS max_seq  FROM $table $filter;");
		$id = (int) $db->getSingleValue($result) + 1;
		return $id;
	}

	private function isModuleActive($name)
	{

		if (class_exists('\includes\Modules')) {
			return \includes\Modules::isModuleActive($name);
		} elseif (class_exists('\App\Module')) {
			return \App\Module::isModuleActive($name);
		}
		\App\Log::error('ERROR' . __METHOD__ . ' | Class not found: ' . print_r($name, true));
		return false;
	}

	private function getPicklistsToAction($index)
	{
		$fields = [];
		switch ($index) {
			case 1:
				$fields = [
					'FInvoice' => [
							['name' => 'finvoice_formpayment', 'uitype' => '15', 'add_values' => ['PLL_WIRE_TRANSFER'], 'remove_values' => []],
					],
					'Leads' => [
							['name' => 'leadstatus', 'uitype' => '15', 'add_values' => ['PLL_PENDING', 'PLL_IN_REALIZATION', 'PLL_INCORRECT'], 'remove_values' => ['LBL_VERIFICATION_OF_DATA', 'LBL_PRELIMINARY_ANALYSIS_OF', 'LBL_ADVANCED_ANALYSIS', 'LBL_INITIAL_ACQUISITION', 'LBL_REQUIRES_VERIFICATION']]
					]
				];
				break;
			default:
				break;
		}
		return $fields;
	}

	private function picklists($addPicklists)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \PearDatabase::getInstance();

		$rolesSelected = [];
		if (empty($this->roles)) {
			$roleRecordList = Settings_Roles_Record_Model::getAll();
			$rolesSelected = array_keys($roleRecordList);
			$this->roles = $rolesSelected;
		} else {
			$rolesSelected = $this->roles;
		}

		foreach ($addPicklists as $moduleName => $picklists) {
			$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
			if (!$moduleModel)
				continue;
			foreach ($picklists as $picklist) {
				$fieldModel = Settings_Picklist_Field_Model::getInstance($picklist['name'], $moduleModel);
				if (!$fieldModel)
					continue;
				if (method_exists('Vtiger_Util_Helper', 'getPickListValues')) {
					$pickListValues = Vtiger_Util_Helper::getPickListValues($picklist['name']);
				} else {
					$pickListValues = App\Fields\Picklist::getPickListValues($picklist['name']);
				}

				foreach ($picklist['add_values'] as $newValue) {
					if (!in_array($newValue, $pickListValues)) {
						$moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
					}
				}
				foreach ($picklist['remove_values'] as $newValue) {
					if (!in_array($newValue, $pickListValues))
						continue;
					if ($picklist['uitype'] != '16') {
						$deletePicklistValueId = self::getPicklistId($picklist['name'], $newValue);
						if ($deletePicklistValueId)
							$db->delete('vtiger_role2picklist', 'picklistvalueid = ?', [$deletePicklistValueId]);
					}
					$db->delete('vtiger_' . $picklist['name'], $picklist['name'] . ' = ? ', [$newValue]);
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private static function getPicklistId($fieldName, $value)
	{
		$db = PearDatabase::getInstance();
		if (\vtlib\Utils::CheckTable('vtiger_' . $fieldName)) {
			$sql = 'SELECT picklist_valueid FROM vtiger_' . $fieldName . ' WHERE ' . $fieldName . ' = ? ;';
			$result = $db->pquery($sql, [$value]);
			if ($db->getRowCount($result) > 0) {
				return $db->getSingleValue($result);
			}
		}
		return false;
	}

	private function changeInHandlers()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \PearDatabase::getInstance();
		$db->delete('vtiger_eventhandlers', 'handler_class = ? AND event_name = ? ', ['Vtiger_SharingPrivileges_Handler', 'vtiger.entity.link.after']);
		$db->delete('vtiger_eventhandlers', 'handler_class = ? AND event_name = ? ', ['ModCommentsHandler', 'vtiger.entity.aftersave']);
		$db->delete('vtiger_eventhandlers', 'handler_class = ? AND event_name = ? ', ['Vtiger_MultiReferenceUpdater_Handler', 'vtiger.entity.aftersave.final']);
		$db->delete('vtiger_eventhandlers', 'handler_class = ?', ['VTEntityDelta']);
		$db->delete('vtiger_eventhandlers', 'handler_class = ? AND event_name IN (?,?) ', ['PBXManagerHandler', 'vtiger.batchevent.save', 'vtiger.batchevent.delete']);
		$db->delete('vtiger_eventhandlers', 'handler_class = ? AND event_name IN (?) ', ['ServiceContractsHandler', 'vtiger.entity.beforesave']);
		$db->delete('vtiger_eventhandlers', 'handler_class = ?', ['LogoutHandler']);
		$this->setAlterTables($this->getAlterTables(6));
		$this->cron($this->getCronData(2));
		$relationsCombine = ['vtiger.entity.beforesave' => 'EntityBeforeSave',
			'vtiger.entity.aftersave' => 'EntityAfterSave',
			'vtiger.entity.afterrestore' => 'EntityAfterRestore',
//			'vtiger.entity.unlink.before' => '',
			'vtiger.entity.unlink.after' => 'EntityAfterUnLink',
			'vtiger.entity.aftersave.final' => 'EntityAfterSave',
			'vtiger.entity.beforedelete' => 'EntityBeforeDelete',
			'vtiger.entity.afterdelete' => 'EntityAfterDelete',
//			'vtiger.batchevent.save' => '',
//			'vtiger.batchevent.delete' => '',
			'vtiger.view.detail.before' => 'DetailViewBefore',
			'vtiger.picklist.afterrename' => 'PicklistAfterRename',
			'vtiger.picklist.afterdelete' => 'PicklistAfterDelete',
			'vtiger.entity.link.after' => 'EntityAfterLink'
//			'user.logout.before'
		];
		$query = 'UPDATE vtiger_eventhandlers SET `event_name` = CASE ';
		foreach ($relationsCombine as $oldname => $newName) {
			$query .= " WHEN `event_name`='$oldname'  THEN '$newName' ";
		}
		$query .= ' ELSE `event_name` END WHERE `event_name` IN (' . $db->generateQuestionMarks($relationsCombine) . ')';
		$db->pquery($query, array_keys($relationsCombine));
		$relationsCombine = [
//			'VTEntityDelta' => '',
			'VTWorkflowEventHandler' => 'Vtiger_Workflow_Handler',
			'ModTrackerHandler' => 'ModTracker_ModTrackerHandler_Handler',
			'PBXManagerHandler' => 'PBXManager_PBXManagerHandler_Handler',
//			'PBXManagerBatchHandler' => '',
			'ServiceContractsHandler' => 'ServiceContracts_ServiceContractsHandler_Handler',
			'WSAPPAssignToTracker' => '',
//			'Vtiger_RecordLabelUpdater_Handler' => 'Vtiger_RecordLabelUpdater_Handler',
			'ModCommentsHandler' => 'ModTracker_ModTrackerHandler_Handler',
			'PickListHandler' => 'Settings_Picklist_PickListHandler_Handler',
			'SECURE' => 'OSSPasswords_Secure_Handler',
			'TimeControlHandler' => 'OSSTimeControl_TimeControl_Handler',
//			'Vtiger_SharingPrivileges_Handler' => '',
			'HelpDeskHandler' => 'HelpDesk_TicketRangeTime_Handler',
			'API_CardDAV_Handler' => 'API_CardDAV_Handler',
			'API_CalDAV_Handler' => 'API_CalDAV_Handler',
			'ProjectTaskHandler' => 'ProjectTask_ProjectTaskHandler_Handler',
//			'LogoutHandler' => '',
			'CalendarHandler' => 'Calendar_CalendarHandler_Handler',
			'Vtiger_MultiReferenceUpdater_Handler' => 'Vtiger_MultiReferenceUpdater_Handler',
			'RecalculateStockHandler' => 'IStorages_RecalculateStockHandler_Handler',
			'SaveChanges' => 'Accounts_SaveChanges_Handler',
			'OpenStreetMapHandler' => 'OpenStreetMap_OpenStreetMapHandler_Handler'
		];
		$query = 'UPDATE vtiger_eventhandlers SET `handler_class` = CASE ';
		foreach ($relationsCombine as $oldname => $newName) {
			$query .= " WHEN `handler_class`='$oldname'  THEN '$newName' ";
		}
		$query .= ' ELSE `handler_class` END WHERE `handler_class` IN (' . $db->generateQuestionMarks($relationsCombine) . ')';
		$db->pquery($query, array_keys($relationsCombine));
		$relationsCombine = [
			'Vtiger_Workflow_Handler' => ['include_modules' => '', 'owner_id' => 0],
			'ModTracker_ModTrackerHandler_Handler' => ['include_modules' => '', 'owner_id' => \vtlib\Functions::getModuleId('ModTracker')],
			'PBXManager_PBXManagerHandler_Handler' => ['include_modules' => 'Contacts,Accounts,Leads', 'owner_id' => \vtlib\Functions::getModuleId('PBXManager')],
			'ServiceContracts_ServiceContractsHandler_Handler' => ['include_modules' => 'HelpDesk,ServiceContracts', 'owner_id' => \vtlib\Functions::getModuleId('ServiceContracts')],
			'Vtiger_RecordLabelUpdater_Handler' => ['include_modules' => '', 'owner_id' => 0],
			'Settings_Picklist_PickListHandler_Handler' => ['include_modules' => '', 'owner_id' => 0],
			'OSSPasswords_Secure_Handler' => ['include_modules' => 'OSSPasswords', 'owner_id' => \vtlib\Functions::getModuleId('OSSPasswords')],
			'OSSTimeControl_TimeControl_Handler' => ['include_modules' => 'OSSPasswords', 'owner_id' => \vtlib\Functions::getModuleId('OSSTimeControl')],
			'Vtiger_SharingPrivileges_Handler' => ['include_modules' => '', 'owner_id' => 0],
			'HelpDesk_TicketRangeTime_Handler' => ['include_modules' => 'HelpDesk', 'owner_id' => \vtlib\Functions::getModuleId('HelpDesk')],
			'API_CardDAV_Handler' => ['include_modules' => 'Contacts,OSSEmployees', 'owner_id' => 0],
			'API_CalDAV_Handler' => ['include_modules' => 'Events,Calendar', 'owner_id' => 0],
			'ProjectTask_ProjectTaskHandler_Handler' => ['include_modules' => 'ProjectTask', 'owner_id' => \vtlib\Functions::getModuleId('ProjectTask')],
			'Calendar_CalendarHandler_Handler' => ['include_modules' => 'Calendar,Events,Activity', 'owner_id' => \vtlib\Functions::getModuleId('Calendar')],
			'Vtiger_MultiReferenceUpdater_Handler' => ['include_modules' => '', 'owner_id' => 0],
			'IStorages_RecalculateStockHandler_Handler' => ['include_modules' => 'IGRN,IIDN,IGDN,IGIN,IPreOrder,ISTDN,ISTRN,IGRNC,IGDNC', 'owner_id' => \vtlib\Functions::getModuleId('IStorages')],
			'Accounts_SaveChanges_Handler' => ['include_modules' => 'Accounts', 'owner_id' => \vtlib\Functions::getModuleId('Accounts')],
			'OpenStreetMap_OpenStreetMapHandler_Handler' => ['include_modules' => 'Accounts,Leads,Partners,Vendors,Competition,Contacts', 'owner_id' => \vtlib\Functions::getModuleId('OpenStreetMap')]
		];
		$query = 'UPDATE vtiger_eventhandlers SET `owner_id` = CASE ';
		$set = ', include_modules = CASE ';
		foreach ($relationsCombine as $oldname => $newName) {
			$include = $newName['include_modules'];
			$owner = $newName['owner_id'];
			$query .= " WHEN `handler_class`='$oldname'  THEN $owner ";
			$set .= " WHEN `handler_class`='$oldname'  THEN '$include' ";
		}
		$query .= ' ELSE `owner_id` END ';
		$query .= $set . ' ELSE `include_modules` END '
			. " , priority = CASE "
			. " WHEN `event_name`='EntityAfterSave' AND handler_class NOT IN('ModTracker_ModTrackerHandler_Handler','Vtiger_Workflow_Handler','Vtiger_RecordLabelUpdater_Handler','PBXManager_PBXManagerHandler_Handler','ServiceContracts_ServiceContractsHandler_Handler') THEN 3 "
			. " WHEN `event_name`='EntityAfterDelete' AND handler_class = 'OSSTimeControl_TimeControl_Handler' THEN 3 ELSE 5 END, is_active=1 ";
		$query .= ' WHERE `handler_class` IN (' . $db->generateQuestionMarks($relationsCombine) . ')';
		$db->pquery($query, array_keys($relationsCombine));

		$resultC = $db->pquery('SELECT 1 FROM vtiger_eventhandlers WHERE handler_class = ?;', ['Vtiger_AutomaticAssignment_Handler']);
		if (!$db->getRowCount($resultC)) {
			$db->insert('vtiger_eventhandlers', ['event_name' => 'EntitySystemAfterCreate', 'handler_class' => 'Vtiger_AutomaticAssignment_Handler', 'is_active' => 0, 'include_modules' => '', 'exclude_modules' => '', 'priority' => 5, 'owner_id' => 0]);
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function tablesInventory()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \PearDatabase::getInstance();
		$tables = ['u_yf_fcorectinginvoice_inventory', 'u_yf_finvoice_inventory', 'u_yf_finvoiceproforma_inventory', 'u_yf_igdn_inventory', 'u_yf_igdnc_inventory', 'u_yf_igin_inventory', 'u_yf_igrn_inventory', 'u_yf_igrnc_inventory', 'u_yf_iidn_inventory', 'u_yf_ipreorder_inventory', 'u_yf_istdn_inventory', 'u_yf_istrn_inventory', 'u_yf_scalculations_inventory', 'u_yf_squoteenquiries_inventory', 'u_yf_squotes_inventory', 'u_yf_srecurringorders_inventory', 'u_yf_srequirementscards_inventory', 'u_yf_ssingleorders_inventory'];
		$result = $db->pquery('SELECT `name` FROM `vtiger_tab` WHERE `type` = ? ;', [1]);
		while ($moduleName = $db->getSingleValue($result)) {
			$focus = CRMEntity::getInstance($moduleName);
			$table = $focus->table_name . '_inventory';
			$this->setNullInventoryFields($table);
			unset($tables[$table]);
		}
		foreach ($tables as $table) {
			$this->setNullInventoryFields($table);
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function setNullInventoryFields($table)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \PearDatabase::getInstance();
		$fields = ['discountparam' => "ALTER TABLE `$table` CHANGE `discountparam` `discountparam` varchar(255) NULL; ",
			'qtyparam' => "ALTER TABLE `$table` CHANGE `qtyparam` `qtyparam` tinyint(1) NULL DEFAULT 0; ",
			'comment1' => "ALTER TABLE `$table` CHANGE `comment1` `comment1` text NULL;"];
		$result = $db->query("SHOW TABLES LIKE '$table';");
		if ($result->rowCount()) {
			foreach ($fields as $fieldName => $sql) {
				$result = $db->query("SHOW COLUMNS FROM `$table` LIKE '$fieldName';");
				if ($result->rowCount()) {
					$db->query($sql);
				}
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function deleteWorkflows()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \PearDatabase::getInstance();
		$workflows = ['Marketing process - Data Verification', 'Marketing process - Preliminary analysis', 'Marketing process - Advanced Analysis', 'Marketing process - Initial acquisition', 'Proces marketingowy - Kontakt w przyszłości'];
		$result = $db->pquery('SELECT workflow_id FROM com_vtiger_workflows WHERE module_name = ? AND summary IN (' . $db->generateQuestionMarks($workflows) . ')', ['Leads', $workflows]);
		while ($recordId = $db->getSingleValue($result)) {
			$recordModel = Settings_Workflows_Record_Model::getInstance($recordId);
			$recordModel->delete();
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	private function updateLeadPicklist()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \PearDatabase::getInstance();
		$values = ['LBL_TO_REALIZE' => 'PLL_TO_REALIZE',
			'LBL_REQUIRES_VERIFICATION' => 'PLL_IN_REALIZATION',
			'LBL_PRELIMINARY_ANALYSIS_OF' => 'PLL_IN_REALIZATION',
			'LBL_ADVANCED_ANALYSIS' => 'PLL_IN_REALIZATION',
			'LBL_INITIAL_ACQUISITION' => 'PLL_CONTACTS_IN_THE_FUTURE',
			'LBL_CONTACTS_IN_THE_FUTURE' => 'PLL_CONTACTS_IN_THE_FUTURE',
			'LBL_LEAD_UNTAPPED' => 'PLL_LEAD_UNTAPPED',
			'LBL_LEAD_ACQUIRED' => 'PLL_LEAD_ACQUIRED'];
		foreach ($values as $old => $new) {
			$db->update('vtiger_leadstatus', ['leadstatus' => $new], '`leadstatus` = ?', [$old]);
			$db->update('vtiger_leaddetails', ['leadstatus' => $new], '`leadstatus` = ?', [$old]);
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	public function createUserPrivilegesFileTest($userId)
	{
		require_once('include/utils/UserInfoUtil.php');
		$file = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges' . DIRECTORY_SEPARATOR . "user_privileges_$userId.php";
		$user = [];
		$userInstance = \CRMEntity::getInstance('Users');
		$userInstance->retrieve_entity_info($userId, 'Users');
		$userInstance->column_fields['is_admin'] = $userInstance->is_admin === 'on';
		$entityData = $this->getUserEntity();
		$displayName = '';
		foreach ($entityData['fieldnameArr'] as &$field) {
			$displayName .= ' ' . $userInstance->column_fields[$field];
		}
		$userRoleInfo = \App\PrivilegeUtil::getRoleDetail($userInstance->column_fields['roleid']);
		$user['details'] = $userInstance->column_fields;
		$user['displayName'] = trim($displayName);
		$user['profiles'] = getUserProfile($userId);
		$user['groups'] = Users_Record_Model::getUserGroups($userId);
		$user['parent_roles'] = $userRoleInfo['parentRoles'];
		$user['parent_role_seq'] = $userRoleInfo['parentrole'];
		file_put_contents($file, 'return ' . \vtlib\Functions::varExportMin($user) . ';', FILE_APPEND);
	}

	private function addModule($module)
	{
		$db = PearDatabase::getInstance();
		$rootDir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		$dirName = 'cache/updates/files/';
		if (file_exists('cache/updates/' . $module . '.xml') && !\vtlib\Module::getInstance($module)) {
			$locations = ['modules/' . $module];
			foreach ($locations as $loc) {
				if (is_dir($dirName . $loc) && !file_exists($rootDir . $loc)) {
					mkdir($rootDir . $loc);
				}
				\vtlib\Functions::recurseCopy($dirName . $loc, $loc, true);
				\vtlib\Functions::recurseDelete($dirName . $loc);
			}

			$importInstance = new \vtlib\PackageImport();
			$importInstance->_modulexml = simplexml_load_file('cache/updates/' . $module . '.xml');
			$importInstance->import_Module();
			unlink('cache/updates/' . $module . '.xml');
			$this->postInstalModule($module);
		}
	}

	private function postInstalModule($moduleName)
	{
		$db = PearDatabase::getInstance();
		$db->update('vtiger_tab', ['customized' => 0], '`name` = ?', [$moduleName]);
		$prefix = $this->getPrefix($moduleName);
		$moduleData = \vtlib\Functions::getModuleData($moduleName);
		if (class_exists('\includes\fields\RecordNumber')) {
			\includes\fields\RecordNumber::setNumber($moduleData['tabid'], $prefix, '1');
		} else {
			\App\Fields\RecordNumber::setNumber($moduleData['tabid'], $prefix, '1');
		}
	}

	private function getPrefix($moduleName)
	{
		$prefixes = [
			'EmailTemplates' => 'N'
		];
		return $prefixes[$moduleName];
	}

	public function updateMenu()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$adb = PearDatabase::getInstance();
		$columns = ['id', 'parentid', 'type', 'sequence', 'module', 'label', 'icon'];
		$menu = [
				[44, 0, 2, 0, NULL, 'MEN_VIRTUAL_DESK', 'userIcon-VirtualDesk'],
				[45, 44, 0, 0, 'Home', 'Home page', 'userIcon-Home'],
				[46, 44, 0, 1, 'Calendar', NULL, ''],
				[47, 0, 2, 1, NULL, 'MEN_COMPANIES_CONTACTS', 'userIcon-CompaniesAndContact'],
				[48, 47, 0, 0, 'Leads', NULL, ''],
				[49, 47, 0, 5, 'Contacts', NULL, ''],
				[50, 47, 0, 3, 'Vendors', NULL, ''],
				[51, 47, 0, 1, 'Accounts', NULL, ''],
				[52, 0, 2, 2, NULL, 'MEN_MARKETING', 'userIcon-Campaigns'],
				[54, 52, 0, 0, 'Campaigns', NULL, ''],
				[62, 118, 0, 7, 'PriceBooks', NULL, ''],
				[63, 0, 2, 5, NULL, 'MEN_SUPPORT', 'userIcon-Support'],
				[64, 63, 0, 0, 'HelpDesk', NULL, ''],
				[65, 63, 0, 1, 'ServiceContracts', NULL, ''],
				[66, 63, 0, 2, 'Faq', NULL, ''],
				[67, 0, 2, 4, NULL, 'MEN_PROJECTS', 'userIcon-Project'],
				[68, 67, 0, 0, 'Project', NULL, ''],
				[69, 67, 0, 1, 'ProjectMilestone', NULL, ''],
				[70, 67, 0, 2, 'ProjectTask', NULL, ''],
				[71, 0, 2, 6, NULL, 'MEN_ACCOUNTING', 'userIcon-Bookkeeping'],
				[72, 71, 0, 5, 'PaymentsIn', NULL, ''],
				[73, 71, 0, 4, 'PaymentsOut', NULL, ''],
				[76, 0, 2, 8, NULL, 'MEN_HUMAN_RESOURCES', 'userIcon-HumanResources'],
				[77, 76, 0, 0, 'OSSEmployees', NULL, ''],
				[78, 76, 0, 1, 'OSSTimeControl', NULL, ''],
				[79, 76, 0, 2, 'HolidaysEntitlement', NULL, ''],
				[80, 0, 2, 9, NULL, 'MEN_SECRETARY', 'userIcon-Secretary'],
				[81, 80, 0, 0, 'LettersIn', NULL, ''],
				[82, 80, 0, 1, 'LettersOut', NULL, ''],
				[83, 80, 0, 2, 'Reservations', NULL, ''],
				[84, 0, 2, 10, NULL, 'MEN_DATABESES', 'userIcon-Database'],
				[85, 84, 2, 1, NULL, 'MEN_PRODUCTBASE', NULL],
				[86, 84, 0, 2, 'Products', NULL, ''],
				[87, 84, 0, 3, 'OutsourcedProducts', NULL, ''],
				[88, 84, 0, 4, 'Assets', NULL, ''],
				[89, 84, 3, 5, NULL, NULL, NULL],
				[90, 84, 2, 6, NULL, 'MEN_SERVICESBASE', NULL],
				[91, 84, 0, 7, 'Services', NULL, ''],
				[92, 84, 0, 8, 'OSSOutsourcedServices', NULL, ''],
				[93, 84, 0, 9, 'OSSSoldServices', NULL, ''],
				[94, 84, 3, 10, NULL, NULL, NULL],
				[95, 84, 2, 11, NULL, 'MEN_LISTS', NULL],
				[96, 84, 0, 12, 'OSSMailView', NULL, ''],
				[97, 84, 0, 13, 'SMSNotifier', NULL, ''],
				[98, 84, 0, 14, 'PBXManager', NULL, ''],
				[99, 84, 0, 15, 'OSSMailTemplates', NULL, ''],
				[100, 84, 0, 17, 'Documents', NULL, ''],
				[106, 84, 0, 19, 'CallHistory', NULL, ''],
				[107, 84, 3, 20, NULL, NULL, NULL],
				[108, 84, 0, 25, 'Announcements', NULL, ''],
				[109, 84, 0, 18, 'OSSPasswords', NULL, ''],
				[111, 44, 0, 3, 'Ideas', NULL, ''],
				[113, 44, 0, 2, 'OSSMail', NULL, ''],
				[114, 84, 0, 24, 'Reports', NULL, ''],
				[115, 84, 0, 21, 'Rss', NULL, ''],
				[116, 84, 0, 22, 'Portal', NULL, ''],
				[117, 84, 3, 23, NULL, NULL, NULL],
				[118, 0, 2, 3, NULL, 'MEN_SALES', 'userIcon-Sales'],
				[119, 118, 0, 0, 'SSalesProcesses', NULL, ''],
				[120, 118, 0, 1, 'SQuoteEnquiries', NULL, ''],
				[121, 118, 0, 2, 'SRequirementsCards', NULL, ''],
				[122, 118, 0, 3, 'SCalculations', NULL, ''],
				[123, 118, 0, 4, 'SQuotes', NULL, ''],
				[124, 118, 0, 5, 'SSingleOrders', NULL, ''],
				[125, 118, 0, 6, 'SRecurringOrders', NULL, ''],
				[126, 47, 0, 2, 'Partners', NULL, ''],
				[127, 47, 0, 4, 'Competition', NULL, ''],
				[128, 71, 0, 0, 'FBookkeeping', NULL, ''],
				[129, 71, 0, 1, 'FInvoice', NULL, ''],
				[130, 63, 0, 3, 'KnowledgeBase', NULL, ''],
				[131, 0, 2, 7, NULL, 'MEN_LOGISTICS', 'userIcon-VendorsAccounts'],
				[132, 131, 0, 10, 'IStorages', NULL, ''],
				[133, 131, 0, 1, 'IGRN', NULL, ''],
				[134, 71, 0, 2, 'FInvoiceProforma', NULL, ''],
				[135, 131, 0, 4, 'IGDN', NULL, ''],
				[136, 131, 0, 5, 'IIDN', NULL, ''],
				[137, 131, 0, 6, 'IGIN', NULL, ''],
				[138, 131, 0, 7, 'IPreOrder', NULL, ''],
				[139, 131, 0, 9, 'ISTDN', NULL, ''],
				[140, 131, 0, 0, 'ISTN', NULL, ''],
				[141, 131, 0, 8, 'ISTRN', NULL, ''],
				[142, 71, 0, 3, 'FCorectingInvoice', NULL, ''],
				[143, 131, 0, 2, 'IGRNC', 'IGRNC', ''],
				[144, 131, 0, 3, 'IGDNC', 'IGDNC', ''],
				[145, 84, 0, 25, 'RecycleBin', '', ''],
				[146, 84, 0, 16, 'EmailTemplates', '', '']
		];
		$adb->delete('yetiforce_menu', '`role` = ? ', [0]);
		$parents = [];
		foreach ($menu as $row) {
			$parent = $row[1] !== 0 ? $parents[$row[1]] : 0;
			$module = $row[2] === 0 ? \vtlib\Functions::getModuleId($row[4]) : $row[4];
			if ($row[2] === 0 && !$this->isModuleActive($row[4])) {
				continue;
			}
			$result = $adb->insert('yetiforce_menu', ['role' => 0, 'parentid' => $parent, 'type' => $row[2], 'sequence' => $row[3], 'module' => $module, 'label' => $row[5], 'icon' => $row[6]]);
			if (is_array($result) && $row[1] == 0) {
				$parents[$row[0]] = $result['id'];
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}
}
