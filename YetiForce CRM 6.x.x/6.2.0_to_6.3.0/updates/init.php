<?php
/**
 * YetiForceUpdate Class.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * YetiForceUpdate Class.
 */
class YetiForceUpdate
{
	/**
	 * @var \vtlib\PackageImport
	 */
	public $package;
	/**
	 * @var string[] Fields to delete.
	 */
	public $filesToDelete = [];
	/**
	 * @var string
	 */
	private $logFile = 'cache/logs/updateLogsTrace.log';
	/**
	 * @var object Module Meta XML File (Parsed).
	 */
	private $moduleNode;
	/**
	 * @var DbImporter
	 */
	private $importer;

	private $error = [];

	/**
	 * Constructor.
	 *
	 * @param object $moduleNode
	 */
	public function __construct($moduleNode)
	{
		$this->moduleNode = $moduleNode;
		$this->filesToDelete = require_once 'deleteFiles.php';
	}

	/**
	 * @param string $message Logs.
	 */
	private function log(string $message): void
	{
		$fp = fopen($this->logFile, 'a+');
		fwrite($fp, $message . PHP_EOL);
		fclose($fp);
		if (false !== stripos($message, '[ERROR]')) {
			$this->error[] = $message;
		}
	}

	/**
	 * Pre update.
	 */
	public function preupdate(): bool
	{
		$minTime = 600;
		$maxExecutionTime = ini_get('max_execution_time');
		$maxInputTime = ini_get('max_input_time');
		if (version_compare(PHP_VERSION, '7.3', '<')) {
			$error = 'Wrong PHP version, recommended version >= 7.3';
		}
		if ($error) {
			$this->package->_errorText = 'The server configuration is not compatible with the requirements of the upgrade package.' . PHP_EOL;
			$this->package->_errorText .= 'Please have a look at the list of errors:' . PHP_EOL . PHP_EOL;
			$this->package->_errorText .= $error;
			return false;
		}
		if ((0 != $maxExecutionTime && $maxExecutionTime < $minTime) || ($maxInputTime > 0 && $maxInputTime < $minTime)) {
			$this->package->_errorText = 'The server configuration is not compatible with the requirements of the upgrade package.' . PHP_EOL;
			$this->package->_errorText .= 'Please have a look at the list of errors:';
			if (0 != $maxExecutionTime && $maxExecutionTime < $minTime) {
				$this->package->_errorText .= PHP_EOL . 'max_execution_time = ' . $maxExecutionTime . ' < ' . $minTime;
			}
			if ($maxInputTime > 0 && $maxInputTime < $minTime) {
				$this->package->_errorText .= PHP_EOL . 'max_input_time = ' . $maxInputTime . ' < ' . $minTime;
			}
			return false;
		}

		copy(__DIR__ . '/files/app/Db/Importer.php', ROOT_DIRECTORY . '/app/Db/Importer.php');
		copy(__DIR__ . '/files/modules/Settings/Menu/models/Record.php', ROOT_DIRECTORY . '/modules/Settings/Menu/models/Record.php');

		return true;
	}

	/**
	 * Update.
	 */
	public function update(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$this->importer = new \App\Db\Importer();
		try {
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->checkIntegrity(false);
			$this->roundcubeUpdateTable();
			$this->importer->dropIndexes([
				'w_yf_api_user' => ['user_name_status', 'server_id', 'user_name']
			]);
			$this->importer->updateScheme();
			$this->importer->dropTable(['b_#__social_media_twitter', 's_#__automatic_assignment', 'u_#__social_media_config', 'u_#__social_media_twitter', 'yetiforce_mail_quantities', 'l_#__social_media_logs']);
			$this->importer->importData();

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
		$this->removeModules('Password');
		$this->addModules(['Passwords']);
		$this->updateData();
		$this->addFields();
		$this->createConfigFiles();
		$this->updateProfileData();
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function removeModules($moduleName)
	{
		$moduleInstance = \Vtiger_Module_Model::getInstance($moduleName);
		if (!$moduleInstance) {
			$this->log('[INFO] Module not exists: ' . $moduleName);
			return;
		}
		$focus = \CRMEntity::getInstance($moduleName);
		if (isset($focus->table_name)) {
			$this->log('[INFO] Module exists: ' . $moduleName);
			return;
		}

		$moduleInstance->deleteIcons();
		$moduleInstance->unsetAllRelatedList();
		\vtlib\Access::deleteSharing($moduleInstance);
		$moduleInstance->deleteFromModentityNum();
		\vtlib\Cron::deleteForModule($moduleInstance);
		\vtlib\Profile::deleteForModule($moduleInstance);
		\Settings_Workflows_Module_Model::deleteForModule($moduleInstance);
		\vtlib\Menu::deleteForModule($moduleInstance);
		$moduleInstance->deleteGroup2Modules();
		$moduleInstance->deleteCRMEntityRel();
		\App\Fields\Tree::deleteForModule($moduleInstance->id);
		\vtlib\Link::deleteAll($moduleInstance->id);
		$moduleInstance->__delete();
		\vtlib\Functions::recurseDelete(ROOT_DIRECTORY . "/config/Modules/{$moduleInstance->name}.php");
		\vtlib\Functions::recurseDelete(ROOT_DIRECTORY . '/modules/' . $moduleInstance->name);
		foreach (\App\Layout::getAllLayouts() as $name => $label) {
			\vtlib\Functions::recurseDelete(ROOT_DIRECTORY . "/layouts/$name/modules/{$moduleInstance->name}");
			\vtlib\Functions::recurseDelete(ROOT_DIRECTORY . "/public_html/layouts/$name/modules/{$moduleInstance->name}");
		}
	}

	private function roundcubeUpdateTable()
	{
		$db = \App\Db::getInstance();

		$tableSchema = $db->getTableSchema('roundcube_users');
		$column = $tableSchema->getColumn('crm_status');
		if (!$column) {
			$db->createCommand("ALTER TABLE `roundcube_cache` CHANGE `data` `data` longtext  COLLATE utf8mb4_unicode_ci NOT NULL after `expires` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_cache_index` CHANGE `data` `data` longtext  COLLATE utf8mb4_unicode_ci NOT NULL after `valid` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_cache_messages` CHANGE `data` `data` longtext  COLLATE utf8mb4_unicode_ci NOT NULL after `expires` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_cache_shared` CHANGE `data` `data` longtext  COLLATE utf8mb4_unicode_ci NOT NULL after `expires` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_cache_thread` CHANGE `data` `data` longtext  COLLATE utf8mb4_unicode_ci NOT NULL after `expires` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_contactgroups` CHANGE `name` `name` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' after `del` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_contacts`
			CHANGE `name` `name` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' after `del` ,
			CHANGE `email` `email` text  COLLATE utf8mb4_unicode_ci NOT NULL after `name` ,
			CHANGE `firstname` `firstname` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' after `email` ,
			CHANGE `surname` `surname` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' after `firstname` ,
			CHANGE `vcard` `vcard` longtext  COLLATE utf8mb4_unicode_ci NULL after `surname` ,
			CHANGE `words` `words` text  COLLATE utf8mb4_unicode_ci NULL after `vcard` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_dictionary`
			CHANGE `language` `language` varchar(16)  COLLATE utf8mb4_unicode_ci NOT NULL after `user_id` ,
			CHANGE `data` `data` longtext  COLLATE utf8mb4_unicode_ci NOT NULL after `language` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_filestore`
			CHANGE `context` `context` varchar(32)  COLLATE utf8mb4_unicode_ci NOT NULL after `user_id` ,
			CHANGE `filename` `filename` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL after `context` ,
			CHANGE `data` `data` longtext  COLLATE utf8mb4_unicode_ci NOT NULL after `mtime` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_identities`
			CHANGE `name` `name` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL after `standard` ,
			CHANGE `organization` `organization` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' after `name` ,
			CHANGE `email` `email` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL after `organization` ,
			CHANGE `reply-to` `reply-to` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' after `email` ,
			CHANGE `bcc` `bcc` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' after `reply-to` ,
			CHANGE `signature` `signature` longtext  COLLATE utf8mb4_unicode_ci NULL after `bcc` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_searches`
			CHANGE `name` `name` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL after `type` ,
			CHANGE `data` `data` text  COLLATE utf8mb4_unicode_ci NULL after `name` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_session`
			CHANGE `sess_id` `sess_id` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL first ,
			CHANGE `ip` `ip` varchar(40)  COLLATE utf8mb4_unicode_ci NOT NULL after `changed` ,
			CHANGE `vars` `vars` mediumtext  COLLATE utf8mb4_unicode_ci NOT NULL after `ip` , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand("ALTER TABLE `roundcube_users`
			CHANGE `mail_host` `mail_host` varchar(128)  COLLATE utf8mb4_unicode_ci NOT NULL after `username` ,
			CHANGE `language` `language` varchar(16)  COLLATE utf8mb4_unicode_ci NULL after `failed_login_counter` ,
			CHANGE `preferences` `preferences` longtext  COLLATE utf8mb4_unicode_ci NULL after `language` ,
			CHANGE `actions` `actions` text  COLLATE utf8mb4_unicode_ci NULL after `preferences` ,
			CHANGE `password` `password` varchar(500)  COLLATE utf8mb4_unicode_ci NULL after `actions` ,
			ADD COLUMN `crm_status` tinyint(1)   NULL DEFAULT 0 after `crm_user_id` ,
			ADD COLUMN `crm_error` varchar(255)  COLLATE utf8mb4_unicode_ci NULL after `crm_status` ,
			ADD KEY `crm_status`(`crm_status`) , DEFAULT CHARSET='utf8mb4', COLLATE ='utf8mb4_unicode_ci' ;")->execute();
			$db->createCommand('ALTER TABLE `roundcube_users_autologin`	ADD KEY `crmuser_id`(`crmuser_id`) ;')->execute();
		}
	}

	private function updateData(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();

		$this->emailTemplates();
		$this->actionMapp();
		$batchInsert = \App\Db\Updater::batchInsert([
			['a_yf_settings_modules',	['name' => 'Kanban', 'status' => 1, 'created_time' => date('Y-m-d H:i:s')], ['name' => 'Kanban']],
			['a_yf_settings_modules',	['name' => 'MeetingServices', 'status' => 1, 'created_time' => date('Y-m-d H:i:s')], ['name' => 'MeetingServices']],
			['vtiger_settings_field',	['blockid' => \vtlib\Deprecated::getSettingsBlockId('LBL_STUDIO'), 'name' => 'LBL_KANBAN', 'iconpath' => 'yfi yfi-kanban', 'description' => 'LBL_KANBAN_DESCRIPTION', 'linkto' => 'index.php?parent=Settings&module=Kanban&view=Index', 'sequence' => 17, 'active' => 0, 'pinned' => 0, 'premium' => 1, 'admin_access' => null], ['name' => 'LBL_KANBAN']],
		]);
		$this->log('[INFO] batchInsert: ' . \App\Utils::varExport($batchInsert));

		$batchUpdate = \App\Db\Updater::batchUpdate([
			['vtiger_links', ['handler_class' => 'FInvoice_SummationByMonthsModel_Dashboard'], ['linklabel' => 'DW_SUMMATION_BY_MONTHS']],
			['vtiger_links', ['handler_class' => 'FInvoice_SummationByUserModel_Dashboard'], ['linklabel' => 'DW_SUMMATION_BY_USER']],
			['vtiger_links', ['handler_class' => 'Vtiger_MultifilterModel_Dashboard'], ['linklabel' => 'Multifilter']],
			['vtiger_links', ['handler_class' => 'Vtiger_CalendarModel_Dashboard'], ['linklabel' => 'Calendar']],
			['vtiger_links', ['handler_class' => 'Vtiger_UpcomingEventsModel_Dashboard'], ['linklabel' => 'Upcoming events']],
			['vtiger_links', ['handler_class' => 'Vtiger_CreatedNotMineActivitiesModel_Dashboard'], ['linklabel' => 'LBL_CREATED_BY_ME_BUT_NOT_MINE_ACTIVITIES']],
			['vtiger_links', ['handler_class' => 'Vtiger_CreatedNotMineActivitiesModel_Dashboard'], ['linklabel' => 'LBL_CREATED_BY_ME_BUT_NOT_MINE_OVERDUE_ACTIVITIES']],
			['vtiger_links', ['handler_class' => 'Vtiger_MiniListModel_Dashboard'], ['linklabel' => 'Mini List']],
			['vtiger_links', ['handler_class' => 'Vtiger_RssModel_Dashboard'], ['linklabel' => 'Rss']],
			['vtiger_links', ['handler_class' => 'Vtiger_ChartFilterModel_Dashboard'], ['linklabel' => 'ChartFilter']],
			['vtiger_field',	['displaytype' => 2], ['fieldname' => 'sum_time', 'uitype' => 8, 'displaytype' => 10]],
			['vtiger_field',	['displaytype' => 2], ['fieldname' => 'payment_status', 'tablename' => ['u_yf_finvoice', 'u_yf_ssingleorders'], 'displaytype' => 10]],
			['vtiger_blocks', ['icon' => 'fas fa-business-time'], ['tabid' => \App\Module::getModuleId('HelpDesk'), 'blocklabel' => 'BL_RECORD_STATUS_TIMES']],
			['vtiger_field',	['uitype' => 300], ['fieldname' => 'commentcontent', 'tablename' => ['vtiger_modcomments'], 'uitype' => 19]],
			['vtiger_customview',	['status' => 3], ['status' => 0, 'presence' => 1]],
			['vtiger_eventhandlers',	['privileges' => 1], ['handler_class' => 'ServiceContracts_ServiceContractsHandler_Handler', 'event_name' => 'EntityAfterSave']],
			['vtiger_relatedlists',	['label' => 'LBL_HELPDESK_RELATED'], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('HelpDesk'), 'name' => 'getRelatedList', 'label' => 'HelpDesk']],
			['vtiger_settings_field',	['premium' => 1], ['name' => 'LBL_AUTOMATIC_ASSIGNMENT']],
		]);
		$this->log('[INFO] batchUpdate: ' . \App\Utils::varExport($batchUpdate));

		$dataReader = (new \App\Db\Query())->from('s_yf_fields_anonymization')->createCommand()->query();
		while ($row = $dataReader->read()) {
			$target = str_replace(['"logs"', '"modTrackerDisplay"'], [0, 1], $row['anonymization_target']);
			if ($target !== $row['anonymization_target']) {
				$db->createCommand()->update('s_yf_fields_anonymization', ['anonymization_target' => $target], ['field_id' => $row['field_id']])->execute();
			}
		}
		$dataReader->close();

		$dataReader = (new \App\Db\Query())->select(['fieldid', 'columnname', 'fieldname', 'defaultvalue', 'tablename'])->from('vtiger_field')->where(['columnname' => ['projectpriority', 'projecttaskpriority'], 'tablename' => ['vtiger_project', 'vtiger_projecttask'], 'defaultvalue' => 'Low'])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$picklist = \App\Fields\Picklist::getValuesName($row['fieldname']);
			if ('Low' === $row['defaultvalue'] && !\in_array('Low', $picklist) && \in_array('low', $picklist)) {
				$db->createCommand()->update('vtiger_field', ['defaultvalue' => 'low'], ['fieldid' => $row['fieldid']])->execute();
				$db->createCommand()->update($row['tablename'], [$row['columnname'] => 'low'], [$row['columnname'] => 'Low'])->execute();
			} elseif (!\in_array($row['defaultvalue'], $picklist)) {
				$db->createCommand()->update('vtiger_field', ['defaultvalue' => ''], ['fieldid' => $row['fieldid']])->execute();
			}
		}
		$dataReader->close();

		$batchDelete = \App\Db\Updater::batchDelete([
			['a_yf_settings_modules', ['name' => 'SocialMedia']],
			['vtiger_cron_task', ['handler_class' => ['Vtiger_Social_Cron', 'Vtiger_SocialGet_Cron']]],
			['vtiger_eventhandlers', ['handler_class' => ['Vtiger_SocialMedia_Handler']]],
			['vtiger_settings_field', ['name' => 'LBL_SOCIAL_MEDIA']],
		]);
		$this->log('[INFO] batchDelete: ' . \App\Utils::varExport($batchDelete));

		$moduleName = 'OSSPasswords';
		if (\App\Module::isModuleActive($moduleName)) {
			$queryGenerator = new \App\QueryGenerator($moduleName);
			$queryGenerator->permission = false;
			$queryGenerator->setStateCondition('All');
			$queryGenerator->addNativeCondition(['<>', 'vtiger_crmentity.deleted', [0]]);
			if (!$queryGenerator->createQuery()->exists()) {
				\vtlib\Module::toggleModuleAccess($moduleName, false);
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function actionMapp()
	{
		$start = microtime(true);
		$db = \App\Db::getInstance();
		$modules = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['and', ['isentitytype' => 1], ['not', ['name' => ['OSSMailView', 'CallHistory']]]])->column();
		$actions = [
			['type' => 'add', 'name' => 'Kanban', 'tabsData' => $modules, 'permission' => 1],
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

			if ($tabsData = $action['tabsData']) {
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
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function setRelations(array $relation)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();

		[, $moduleName, $relModuleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment, $viewType, $fieldName,$customView] = $relation;
		$tabid = \App\Module::getModuleId($moduleName);
		$relTabid = \App\Module::getModuleId($relModuleName);
		$where = ['tabid' => $tabid, 'related_tabid' => $relTabid, 'name' => $name];
		$isExists = (new \App\Db\Query())->from('vtiger_relatedlists')->where($where)->exists();
		if (!$isExists) {
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
				'field_name' => $fieldName,
			])->execute();
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function emailTemplates()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$data['UsersResetPassword'] = <<<'STR'
		<table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" class="table_full editable-bg-color bg_color_ffffff editable-bg-image" style="max-width:1024px;min-width:320px;">
	<tbody>
		<tr>
			<td height="20">&nbsp;</td>
		</tr>
		<tr>
			<td>
			<table align="center" border="0" cellpadding="0" cellspacing="0" class="table1" style="width:100%;">
				<tbody>
					<tr>
						<td bgcolor="#fcfcfc" style="border:1px solid #f2f2f2;border-radius:5px;padding:10px;">
						<table align="center" border="0" cellpadding="0" cellspacing="0" class="no_float">
							<tbody>
								<tr>
									<td align="center" class="editable-img">$(organization : logo)$</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td height="25">&nbsp;</td>
		</tr>
		<tr>
			<td>
			<table align="center" border="0" cellpadding="0" cellspacing="0" class="table1" style="width:100%;">
				<tbody>
					<tr style="text-align:left;">
						<td bgcolor="#fcfcfc" style="padding:30px 20px 30px 20px;border:1px solid #f2f2f2;border-radius:5px;">
							<p>Dear user,<br />
								We received a request to change the password to your account.<br />
								In order to set a new password click the following link (valid until $(params : expirationDate)$):<br /><br />
								<a href="$(params : url)$" target="_blank">$(params : url)$</a><br />
								$(params : token)$
								<br /><br />
								If you didn't request the passwords change please report it to the administrator or use the password change option available on the login page.
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td height="40">&nbsp;</td>
		</tr>
		<tr>
			<td>
			<table align="center" border="0" cellpadding="0" cellspacing="0" class="table1" style="width:100%;">
				<tbody>
					<tr>
						<td align="center" class="text_color_c6c6c6" style="line-height:1;font-size:14px;font-weight:400;font-family:'Open Sans', Helvetica, sans-serif;">
						<div class="editable-text"><span class="text_container">&copy; 2021 YetiForce Sp. z o.o. All Rights Reserved.</span></div>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td height="20">&nbsp;</td>
		</tr>
	</tbody>
</table>
STR;
		foreach ($data as $sysName => $content) {
			$dbCommand->update('u_yf_emailtemplates', ['content' => $content], ['sys_name' => $sysName])->execute();
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
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
				if ('Passwords' === $moduleName && ($tabId = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['name' => $moduleName])->scalar())) {
					\CRMEntity::getInstance('ModTracker')->enableTrackingForModule($tabId);
				}
			} else {
				$this->log('    [INFO] Module exist: ' . $moduleName);
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
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
			$fields = [
				[92, 3080, 'parentid', 'u_yf_partners', 1, 10, 'parentid', 'FL_MEMBER_OF', 0, 2, '', '4294967295', 10, 299, 1, 'V~O', 2, 3, 'BAS', 1, '', 1, '', null, 0, 0, 0, 0, '', '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_PARTNERS_INFORMATION', 'blockData' => ['label' => 'LBL_PARTNERS_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['Partners'], 'moduleName' => 'Partners'],
				[13, 3097, 'contact_id', 'vtiger_troubletickets', 1, 10, 'contact_id', 'FL_CONTACT', 0, 2, '', '4294967295', 7, 25, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_TICKET_INFORMATION', 'blockData' => ['label' => 'LBL_TICKET_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['Contacts'], 'moduleName' => 'HelpDesk', 'relationData' => [669, 'Contacts', 'HelpDesk', 'getDependentsList', 4, 'LBL_HELPDESK_DEPENDENTS', 0, 'ADD', 0, 0, 0, 'RelatedTab', 'contact_id', null]],
				[111, 3098, 'subprocess_sl', 'u_yf_notification', 2, 64, 'subprocess_sl', 'FL_SUBPROCESS_SECOND_LEVEL', 0, 0, '', '4294967295', 10, 374, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_NOTIFICATION_INFORMATION', 'blockData' => ['label' => 'LBL_NOTIFICATION_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['ProjectTask'], 'moduleName' => 'Notification', 'relationData' => [670, 'ProjectTask', 'Notification', 'getDependentsList', 5, 'Notification', 0, 'ADD', 0, 0, 0, 'RelatedTab', 'subprocess_sl', null]]
			];
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
			$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => ($field['blockData']['label'] ?? $field['blockLabel']), 'tabid' => $moduleId])->scalar();
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
			if (!$blockInstance
			&& !($blockInstance = reset(Vtiger_Module_Model::getInstance($moduleName)->getBlocks()))) {
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
			$fieldInstance->fieldparams = $field[22];
			$blockInstance->addField($fieldInstance);
			if (!empty($field['picklistValues']) && (15 == $field[5] || 16 == $field[5] || 33 == $field[5])) {
				$fieldInstance->setPicklistValues($field['picklistValues']);
			}
			if (!empty($field['relatedModules']) && \in_array($field[5], [10, 64])) {
				if (10 == $field[5]) {
					$fieldInstance->setRelatedModules($field['relatedModules']);
				}
				if (!empty($field['relationData'])) {
					$this->setRelations($field['relationData']);
				}
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Post update.
	 */
	public function createConfigFiles(): bool
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$configTemplates = 'config/ConfigTemplates.php';
		copy(__DIR__ . '/files/' . $configTemplates, ROOT_DIRECTORY . '/' . $configTemplates);
		$configTemplates = 'config/Components/ConfigTemplates.php';
		copy(__DIR__ . '/files/' . $configTemplates, \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $configTemplates);

		\App\Cache::resetOpcache();
		clearstatcache();

		(new \App\ConfigFile('relation'))->create();

		$path = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
		$componentsData = require_once "$path";
		foreach ($componentsData as $component => $data) {
			(new \App\ConfigFile('component', $component))->create();
		}

		(new \App\BatchMethod(['method' => '\App\UserPrivilegesFile::recalculateAll', 'params' => []]))->save();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return true;
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
				'name' => (string) $this->moduleNode->label,
				'from_version' => (string) $this->moduleNode->from_version,
				'to_version' => (string) $this->moduleNode->to_version,
				'result' => false,
				'time' => date('Y-m-d H:i:s'),
			])->execute();
			$dbCommand->update('vtiger_version', ['current_version' => (string) $this->moduleNode->to_version])->execute();
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

	public function updateProfileData()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		\App\Db\Fixer::baseModuleTools();
		\App\Db\Fixer::baseModuleActions();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		\App\Module::createModuleMetaFile();
		(new \Settings_Menu_Record_Model())->refreshMenuFiles();
		(new \App\BatchMethod(['method' => '\App\Db\Fixer::baseModuleTools', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\Db\Fixer::baseModuleActions', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\Db\Fixer::profileField', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\UserPrivilegesFile::recalculateAll', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => 'Settings_SharingAccess_Module_Model::recalculateSharingRules', 'params' => []]))->save();
		\App\Cache::clear();
		\App\Cache::resetOpcache();
		if ($this->error || false !== strpos($this->importer->logs, 'Error')) {
			$this->stopProcess();
			$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
			exit;
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
		return true;
	}

	public function finishUpdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();

		(new \App\BatchMethod(['method' => '\App\Db\Fixer::baseModuleTools', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\Db\Fixer::baseModuleActions', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\Db\Fixer::profileField', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\UserPrivilegesFile::recalculateAll', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => 'Settings_SharingAccess_Module_Model::recalculateSharingRules', 'params' => []]))->save();

		$db->createCommand()->insert('yetiforce_updates', [
			'user' => \Users_Record_Model::getCurrentUserModel()->get('user_name'),
			'name' => (string) $this->moduleNode->label,
			'from_version' => (string) $this->moduleNode->from_version,
			'to_version' => (string) $this->moduleNode->to_version,
			'result' => 1,
			'time' => date('Y-m-d H:i:s'),
		])->execute();
		$db->createCommand()->update('vtiger_version', ['current_version' => (string) $this->moduleNode->to_version])->execute();
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
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		header('location: ' . \App\Config::main('site_URL') . 'index.php?module=Companies&parent=Settings&view=List&displayModal=online');
	}
}
