<?php
/**
 * YetiForceUpdate Class.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		copy(__DIR__ . '/files/app/Db/Updater.php', ROOT_DIRECTORY . '/app/Db/Updater.php');
		copy(__DIR__ . '/files/app/Db/Fixer.php', ROOT_DIRECTORY . '/app/Db/Fixer.php');
		copy(__DIR__ . '/files/app/Db/Importer.php', ROOT_DIRECTORY . '/app/Db/Importer.php');
		copy(__DIR__ . '/files/app/ConfigFile.php', ROOT_DIRECTORY . '/app/ConfigFile.php');
		copy(__DIR__ . '/files/app/YetiForce/Shop/AbstractBaseProduct.php', ROOT_DIRECTORY . '/app/YetiForce/Shop/AbstractBaseProduct.php');
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
			$this->updateBeforeImporter();
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->checkIntegrity(false);
			$this->importer->updateScheme();
			$this->searchTable();

			$this->importer->importData();
			$this->updateDataImporter();
			$this->addModules(['Queue']);

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
		$this->updateData();
		$this->addFields();
		$this->setRelations();
		$this->addMissingRelations();
		$this->addRecordListFilterValues();
		$this->dropInvTable();
		$this->addAnonymizationFields();
		$this->recalculateWorkingTime();
		$this->relatedAttachmentsInPdf();
		$this->updateMask();
		$this->workflow();
		$this->importer->dropTable(['u_yf_crmentity_last_changes', 'vtiger_shorturls']);
		$this->importer->dropColumns([['w_yf_portal_user', 'logout_time'], ['w_yf_portal_user', 'language']]);
		$this->addKeysToPicklist();
		$this->dropColumns();
		$this->createConfigFiles();
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function updateBeforeImporter(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->delete('u_#__finvoiceproforma_address', ['not in', 'finvoiceproformaaddressid', (new \App\Db\Query())->select(['finvoiceproformaid'])->from('u_#__finvoiceproforma')])->execute();
		$dbCommand->delete('u_#__recurring_info', ['not in', 'srecurringordersid', (new \App\Db\Query())->select(['srecurringordersid'])->from('u_#__srecurringorders')])->execute();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateDataImporter(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		// \App\Db\Updater::batchUpdate([
		// 	['vtiger_cron_task', ['frequency' => 7200], ['name' => 'LBL_MAIL_RBL']]
		// ]);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateData(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$tableSchema = $db->getTableSchema('w_yf_portal_user', true);
		$column = $tableSchema->getColumn('password_t');
		if ($column) {
			$db->createCommand('ALTER TABLE `w_yf_portal_user` CHANGE `password_t` `password` VARCHAR(500) NULL;')->execute();
		}
		$tableSchema = $db->getTableSchema('w_yf_servers', true);
		$column = $tableSchema->getColumn('acceptable_url');
		if ($column) {
			$db->createCommand('ALTER TABLE `w_yf_servers` CHANGE `acceptable_url` `ips` VARCHAR(255) NULL;')->execute();
		}
		$importerType = new \App\Db\Importers\Base();
		$db->createCommand()->alterColumn('a_yf_record_list_filter', 'id', $importerType->smallInteger(5)->unsigned()->autoIncrement()->notNull())->execute();

		$batchInsert = \App\Db\Updater::batchInsert([
			['a_yf_settings_modules',	['name' => 'Proxy', 'status' => 1, 'created_time' => date('Y-m-d H:i:s')], ['name' => 'Proxy']],
			['vtiger_settings_field',	['blockid' => vtlib\Deprecated::getSettingsBlockId('LBL_MAIL_TOOLS'), 'name' => 'LBL_CONFIG_PROXY', 'iconpath' => 'yfi yfi-server-configuration', 'description' => 'LBL_CONFIG_PROXY_DESCRIPTION', 'linkto' => 'index.php?parent=Settings&module=Proxy&view=Index', 'sequence' => 8, 'active' => 0, 'pinned' => 0, 'admin_access' => null], ['name' => 'LBL_CONFIG_PROXY']],
			['com_vtiger_workflow_tasktypes', ['tasktypename' => 'Webhook', 'label' => 'Webhook', 'classname' => 'Webhook', 'classpath' => 'modules/com_vtiger_workflow/tasks/Webhook.php', 'templatepath' => 'com_vtiger_workflow/taskforms/Webhook.tpl', 'modules' => '{"include":[],"exclude":[]}'], ['tasktypename' => 'Webhook']],
			['vtiger_links', ['tabid' => 3, 'linktype' => 'DASHBOARDWIDGET', 'linklabel' => 'Upcoming events', 'linkurl' => 'index.php?module=Home&view=ShowWidget&name=UpcomingEvents'], ['linkurl' => 'index.php?module=Home&view=ShowWidget&name=UpcomingEvents']],
			['vtiger_eventhandlers', ['event_name' => 'EntityAfterSave', 'handler_class' => 'Queue_Queue_Handler', 'include_modules' => 'Queue'], ['handler_class' => 'Queue_Queue_Handler']],
			['vtiger_eventhandlers', ['event_name' => 'EntityBeforeSave', 'handler_class' => 'Vtiger_AutoFillIban_Handler'], ['handler_class' => 'Vtiger_AutoFillIban_Handler']],
		]);
		$this->log('[INFO] batchInsert: ' . \App\Utils::varExport($batchInsert));

		$batchDelete = \App\Db\Updater::batchDelete([
			['a_yf_settings_modules', ['name' => 'HideBlocks']],
			['vtiger_settings_field', ['name' => 'LBL_HIDEBLOCKS']],
			['vtiger_eventhandlers', ['handler_class' => ['Vtiger_RecordLabelUpdater_Handler', 'Accounts_SaveChanges_Handler', 'Vtiger_SharingPrivileges_Handler']]],
			['vtiger_ws_fieldtype', ['fieldtype' => ['companySelect']]],
		]);
		$this->log('[INFO] batchDelete: ' . \App\Utils::varExport($batchDelete));

		$batchUpdate = \App\Db\Updater::batchUpdate([
			['vtiger_blocks', ['icon' => 'fas fa-money-check-alt'], ['blocklabel' => 'LBL_CURRENCY_CONFIGURATION', 'tabid' => \App\Module::getModuleId('Users')]],
			['vtiger_blocks', ['icon' => 'fas fa-info-circle'], ['blocklabel' => 'LBL_MORE_INFORMATION', 'tabid' => \App\Module::getModuleId('Users')]],
			['vtiger_blocks', ['icon' => 'fas fa-address-book'], ['blocklabel' => 'LBL_USER_CONTACT_INFORMATION', 'tabid' => \App\Module::getModuleId('Users')]],
			['vtiger_blocks', ['icon' => 'yfm-OSSTimeControl'], ['blocklabel' => 'LBL_USER_CONFIGURATION_WORKING_TIME', 'tabid' => \App\Module::getModuleId('Users')]],
			['vtiger_field', ['header_field' => '{"type":"highlights","class":"badge-info"}'], ['columnname' => 'mulcomp_status', 'tablename' => 'u_yf_multicompany', 'header_field' => [null, '']]],
			['vtiger_field', ['fieldparams' => '{"isProcessStatusField":true}'], ['columnname' => 'ssalesprocesses_status', 'tablename' => 'u_yf_ssalesprocesses', 'header_field' => [null, '']]],
			['vtiger_field', ['defaultvalue' => null], ['columnname' => 'ssingleorders_status', 'tablename' => 'u_yf_ssingleorders', 'defaultvalue' => 'PLL_DRAFT']],
			['vtiger_field', ['fieldparams' => '{"editWidth":"col-sm-3"}'], ['columnname' => 'reapeat', 'tablename' => 'vtiger_activity', 'fieldparams' => [null, '']]],
			['vtiger_field', ['fieldparams' => '{"editWidth":"col-sm-9"}'], ['columnname' => 'recurrence', 'tablename' => 'vtiger_activity', 'fieldparams' => [null, '']]],
			['vtiger_field', ['header_field' => '{"type":"value"}'],
				[
					'and',
					['header_field' => [null, '']],
					['or',
						['columnname' => 'parentid', 'tablename' => 'vtiger_contactdetails'],
						['columnname' => ['process', 'link'], 'tablename' => 'vtiger_activity'],
						['columnname' => 'parent_id', 'tablename' => 'u_yf_multicompany'],
						['columnname' => 'related_to', 'tablename' => 'u_yf_ssalesprocesses'],
						['columnname' => 'linktoaccountscontacts', 'tablename' => 'vtiger_project'],
						['columnname' => ['parent_id', 'status'], 'tablename' => 'vtiger_troubletickets'],
					]
				]
			],
			['vtiger_field', ['header_field' => '{"type":"value"}'], ['fieldname' => ['shownerid', 'assigned_user_id'], 'tablename' => 'vtiger_crmentity', 'header_field' => [null, ''], 'tabid' => [
				\App\Module::getModuleId('Contacts'), \App\Module::getModuleId('Calendar'), \App\Module::getModuleId('HelpDesk'), \App\Module::getModuleId('Project'), \App\Module::getModuleId('SSalesProcesses'), \App\Module::getModuleId('MultiCompany')
			]]],
			['vtiger_field', ['fieldparams' => '{"mask":"9999999999999"}'], ['columnname' => 'ean', 'tablename' => 'vtiger_products', 'fieldparams' => '9999999999999']],
			['vtiger_field', ['displaytype' => 2], ['fieldlabel' => ['FL_MAGENTO_SERVER', 'FL_MAGENTO_ID', 'FL_MAGENTO_STATUS']]],
			['vtiger_ssalesprocesses_status', ['record_state' => 2], ['ssalesprocesses_status' => ['PLL_SALE_COMPLETED', 'PLL_SALE_FAILED', 'PLL_SALE_CANCELLED']]],
			['vtiger_crmentity', ['private' => 0], ['private' => null]],
			['vtiger_relatedlists', ['label' => 'Occurrences'], ['tabid' => \App\Module::getModuleId('Contacts'), 'name' => 'getRelatedMembers', 'label' => 'LBL_PARTICIPANT']],
			['vtiger_settings_field', ['premium' => 1], ['name' => ['LBL_MAIL_INTEGRATION', 'LBL_MAIL_RBL', 'LBL_MAGENTO', 'LBL_VULNERABILITIES', 'LBL_DAV_KEYS']]],
			['vtiger_eventhandlers', ['privileges' => 0], ['not', ['privileges' => 0]]],
			['vtiger_eventhandlers', ['privileges' => 1], ['or', ['event_name' => 'EditViewPreSave'], ['handler_class' => ['PaymentsIn_PaymentsInHandler_Handler', 'Vtiger_RecordFlowUpdater_Handler', 'Contacts_DuplicateEmail_Handler', 'Accounts_DuplicateVatId_Handler', 'Products_DuplicateEan_Handler', 'IGDNC_IgdnExist_Handler', 'App\Extension\PwnedPassword', 'Vtiger_AutoFillIban_Handler']]]],
			['vtiger_cron_task', ['max_exe_time' => 5], ['and',
				['handler_class' => 'Calendar_SetCrmActivity_Cron'],
				['or', ['max_exe_time' => null], ['max_exe_time' => 0]]
			]],
			['vtiger_field', ['defaultvalue' => null], ['fieldname' => 'crmactivity', 'tablename' => 'vtiger_entity_stats']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-map'], ['name' => ['LBL_MAP']]],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-twitter'], ['name' => ['LBL_SOCIAL_MEDIA']]],
			['vtiger_queue_status', ['presence' => 0], ['queue_status' => ['PLL_ACCEPTED', 'PLL_COMPLETED', 'PLL_CANCELLED']]],
			['vtiger_relatedlists', ['presence' => 1], ['related_tabid' => (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['name' => 'Queue'])->scalar()]],
		]);
		$this->log('[INFO] batchUpdate: ' . \App\Utils::varExport($batchUpdate));
		$this->emailTemplates();
		$this->addPicklistValue();

		include_once 'modules/ModComments/ModComments.php';
		if (class_exists('ModComments')) {
			\ModComments::addWidgetTo(['Queue']);
		}

		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->delete('u_#__crmentity_label', ['label' => ''])->execute();
		$dbCommand->delete('u_#__crmentity_search_label', ['searchlabel' => ''])->execute();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function setRelations()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();

		$ralations = [
			['type' => 'add', 'data' => [659, 'ServiceContracts', 'Contacts', 'getRelatedList', 1, 'Contacts', 0, 'SELECT', 0, 0, 0, 'RelatedTab', null, null]],
			['type' => 'add', 'data' => [660, 'Contacts', 'ServiceContracts', 'getRelatedList', 13, 'ServiceContracts', 0, 'SELECT', 0, 0, 0, 'RelatedTab', null, null]],
			// ['type' => 'add', 'data' => [661,'SSalesProcesses','SSalesProcesses','getDependentsList',25,'SSalesProcesses',1,'',0,0,0,'RelatedTab','parentid',null]],
		];

		foreach ($ralations as $relation) {
			[, $moduleName, $relModuleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment, $viewType, $fieldName,$customView] = $relation['data'];
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
				if ('ModComments' === $relatedModule || ($relatedModule === $moduleModel->getName() && 'SSalesProcesses' !== $relatedModule)) {
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
							'actions' => $relatedModule === $moduleModel->getName() ? '' : 'ADD',
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

	public function emailTemplates()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$data['YetiPortalRegister'] = <<<'STR'
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
								<h1>Welcome to the YetiForce Client Portal!</h1>
								<p>
									Dear $(params : login)$,<br />
									Your account has been created successfully. Below are your username and password:<br /><br />
									Portal address: $(params : acceptable_url)$<br />
									Your username: $(params : login)$<br />
									Your password: $(params : password)$<br /><br />

									Please log in to access all of the Portal features.<br /><br />
									If you have any questions or need any assistance, please send us an email to help@yetiforce.com.
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
								We received a password change request from you regarding your account in $(params : siteUrl)$<br />
								In order to change your password click the following link (valid until $(params : expirationDate)$):<br /><br />
								<a href="$(params : url)$">$(params : url)$</a><br />
								$(params : token)$
								<br /><br />
								If you didn't request the passwords change please report it to the administrator; your password won't be changed.
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
	 * Drop column.
	 */
	public function dropColumns()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$modules = [
			'vtiger_customerdetails' => ['fields' => ['support_start_date', 'support_end_date'], 'moduleName' => 'Contacts'],
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
						$this->log('    [Warning] Field exists and is in use, deactivated: ' . $fieldModel->getName() . ' ' . $fieldModel->getModuleName());
					}
				} else {
					$this->log("    [INFO] Skip removing {$moduleName}:{$fieldName}, field not exists");
				}
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
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

	private function removeField($fieldModel)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " | {$fieldModel->getName()},{$fieldModel->getModuleName()} | " . date('Y-m-d H:i:s'));
		try {
			$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldModel->getId());
			$fieldInstance->delete();
		} catch (\Throwable $e) {
			$message = '    [ERROR] ' . __METHOD__ . ': ' . $e->__toString();
			$this->log($message);
			\App\Log::error($message);
		}
		\App\Cache::delete('ModuleFields', $fieldModel->getModuleId());
		\App\Cache::staticDelete('ModuleFields', $fieldModel->getModuleId());
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function searchTable()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$tableSchema = $db->getTableSchema('u_yf_crmentity_search_label', true);
		$column = $tableSchema->getColumn('tabid');
		if (!$column) {
			$db->createCommand()->truncateTable('u_yf_crmentity_search_label')->execute();
			$db->createCommand('ALTER TABLE `u_yf_crmentity_search_label` ADD COLUMN `tabid` SMALLINT(5) NOT NULL AFTER `searchlabel`;')->execute();
			$db->createCommand('ALTER TABLE `u_yf_crmentity_search_label` DROP INDEX `crmentity_searchlabel_setype`, ADD  KEY `crmentity_tabid_searchlabel` (`tabid`, `searchlabel`);')->execute();
			$db->createCommand('ALTER TABLE `u_yf_crmentity_search_label` DROP COLUMN `setype`;')->execute();
			$db->createCommand('ALTER TABLE `vtiger_entityname` CHANGE `turn_off` `turn_off` TINYINT(1) UNSIGNED DEFAULT 0 NOT NULL;')->execute();
			$db->createCommand('ALTER TABLE `u_yf_crmentity_search_label` CHANGE `crmid` `crmid` INT (10) NOT NULL, ADD CONSTRAINT `fk_u_yf_crmentity_search_label` FOREIGN KEY (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE;')->execute();
		}

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
				if ('Queue' === $moduleName && ($tabId = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['name' => $moduleName])->scalar())) {
					\CRMEntity::getInstance('ModTracker')->enableTrackingForModule($tabId);
				}
			} else {
				$this->log('    [INFO] Module exist: ' . $moduleName);
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	public function addPicklistValue()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$fields = ['legal_form' => ['PLL_COMPANY'], 'login_method' => ['PLL_LDAP_2FA']];
		foreach ($fields as $fieldName => $values) {
			if ('legal_form' === $fieldName) {
				$langues = \App\Language::getAll();
				foreach (\App\Fields\Picklist::getValuesName($fieldName) as $value) {
					if (false !== strpos($value, 'PLL_')) {
						foreach ($langues as $langKey => $langLabel) {
							\App\Language::translationModify($langKey, 'Accounts', 'php', $value, \App\Language::translate($value, 'Accounts', $langKey, false));
							\App\Language::translationModify($langKey, 'Leads', 'php', $value, \App\Language::translate($value, 'Leads', $langKey, false));
						}
					}
				}
			}
			$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['fieldname' => $fieldName, 'uitype' => [16, 33]])->scalar();
			$this->log("[INFO] PICKLIST VALUES: {$fieldName} " . print_r($fieldName, array_diff($values, \App\Fields\Picklist::getValuesName($fieldName)), $values, \App\Fields\Picklist::getValuesName($fieldName)));
			if ($fieldId && ($diffVal = array_diff($values, \App\Fields\Picklist::getValuesName($fieldName)))) {
				$this->log("[INFO] PICKLIST VALUES SET IN: {$fieldName}");
				$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
				$fieldModel->setPicklistValues($diffVal);
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function addKeysToPicklist()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$dataReader = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['uitype' => [15, 33]])->distinct()->createCommand()->query();
		while ($name = $dataReader->readColumn(0)) {
			$tableName = "vtiger_{$name}";
			if (!$db->isTableExists($tableName)) {
				$this->log('    [Warninig] Table not exists: ' . $tableName);
				continue;
			}
			$tableSchema = $db->getTableSchema($tableName, true);
			$column = $tableSchema->getColumn('picklist_valueid');
			if (!$column) {
				$this->log('    [Warninig] NO column picklist_valueid: ' . $tableName);
				continue;
			}
			$keyExists = false;
			$indexes = $db->getTableKeys($tableName);
			foreach ($indexes as $index) {
				if (isset($index['picklist_valueid'])) {
					$keyExists = true;
				}
			}
			if (!$keyExists) {
				try {
					$db->createCommand()->createIndex($name . '_valueid_idx', $tableName, 'picklist_valueid', true)->execute();
				} catch (\Throwable $e) {
					$this->log("    [ERROR] {$tableName} - " . $e->__toString());
				}
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function workflow()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$workflows = ['HelpDesk' => [
			'Ticket change: Send Email to Record Owner',
			'Ticket change: Send Email to Record Account',
			'Ticket Closed: Send Email to Record Owner',
			'Ticket Closed: Send Email to Record Account',
			'Ticket Creation: Send Email to Record Account'
		]
		];
		foreach ($workflows as $moduleName => $names) {
			$dataReader = (new \App\Db\Query())->select(['workflow_id'])->from('com_vtiger_workflows')->where(['module_name' => $moduleName, 'summary' => $names])->createCommand()->query();
			while ($workflowId = $dataReader->readColumn(0)) {
				$workflowModel = \Settings_Workflows_Record_Model::getInstance($workflowId);
				if ($workflowModel->isDefault()) {
					continue;
				}
				$active = false;
				foreach ($workflowModel->getTasks() as $task) {
					if ($task->getTaskObject()->active) {
						$active = true;
						break;
					}
				}
				if (!$active) {
					$workflowModel->delete();
				}
			}
		}

		\Vtiger_Loader::includeOnce('~modules/com_vtiger_workflow/VTTask.php');
		require_once 'modules/com_vtiger_workflow/tasks/SumFieldFromDependent.php';
		$query = (new \App\Db\Query())->select(['task_id', 'task'])->from('com_vtiger_workflowtasks')->where(['summary' => 'It sums up all open sales orders']);
		$dataReader = $query->createCommand()->query();
		$fieldName = 'ssingleorders_status';
		$singleOrderStatuses = \App\Fields\Picklist::getValuesName($fieldName);
		while ($row = $dataReader->read()) {
			$update = false;
			$unserializeTask = unserialize($row['task']);
			if (!empty($unserializeTask->conditions['rules'])) {
				foreach ($unserializeTask->conditions['rules'] as &$value) {
					$field = explode(':', $value['fieldname']);
					$picklistValues = explode('##', $value['value']);
					if ($fieldName === $field[0]
						&& !array_diff($picklistValues, ['PLL_DRAFT', 'PLL_IN_REALIZATION', 'PLL_FOR_VERIFICATION', 'PLL_AWAITING_SIGNATURES']) && array_diff($picklistValues, $singleOrderStatuses)
						&& !array_diff(['PLL_NEW', 'PLL_PAYMENT_REVIEW', 'PLL_PROCESSING'], $singleOrderStatuses)
					) {
						$value['value'] = implode('##', ['PLL_NEW', 'PLL_PAYMENT_REVIEW', 'PLL_PROCESSING']);
						$update = true;
					}
				}
				if ($update) {
					$dbCommand->update('com_vtiger_workflowtasks', ['task' => serialize($unserializeTask)], ['task_id' => $row['task_id']])->execute();
				}
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateMask()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$dataReader = (new \App\Db\Query())->from('vtiger_field')->where(['not', ['fieldparams' => [null, '']]])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$params = $row['fieldparams'];
			if (!\App\Json::isEmpty($params) && '{' !== $params[0] && '[' !== $params[0]) {
				$fieldModel = new \Vtiger_Field_Model();
				$fieldModel->initialize($row);
				if (\in_array($fieldModel->getFieldDataType(), ['string', 'currency', 'url', 'integer', 'double'])) {
					$fieldParams['mask'] = $params;
					$db->createCommand()->update('vtiger_field', ['fieldparams' => \App\Json::encode($fieldParams)], ['fieldid' => $fieldModel->getId()])->execute();
					echo 'update: ' . \App\Module::getModuleName($row['tabid']) . " - {$row['fieldname']}<br>";
				}
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function dropInvTable(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		if ($db->isTableExists('u_#__squoteenquiries_inventory')) {
			$count = (new \App\Db\Query())->from('u_#__squoteenquiries_inventory')->count();
			if (0 === $count) {
				$dbCommand->dropTable('u_#__squoteenquiries_inventory')->execute();
				$dbCommand->dropTable('u_#__squoteenquiries_invfield')->execute();
				$dbCommand->dropTable('u_#__squoteenquiries_invmap')->execute();
				// $dbCommand->update('vtiger_tab', ['type' => 0], ['name' => 'SQuoteEnquiries'])->execute();
				(new \App\BatchMethod(['method' => '\App\Module::changeType', 'params' => ['module' => 'SQuoteEnquiries', 'type' => \Vtiger_Module_Model::STANDARD_TYPE]]))->save();
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function addAnonymizationFields(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$fields = [
			// tablename , fieldname , anonymization_target
			['vtiger_users', 'user_password', '["logs"]'],
			['vtiger_users', 'confirm_password', '["logs"]'],
			['vtiger_osspasswords', 'password', '["logs","modTrackerDisplay"]'],
		];
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($fields as $field) {
			$fileId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tablename' => $field[0], 'fieldname' => $field[1]])->scalar();
			if ($fileId && !(new \App\Db\Query())->from('s_yf_fields_anonymization')->where(['field_id' => $fileId])->exists()) {
				$dbCommand->insert('s_yf_fields_anonymization', ['field_id' => $fileId, 'anonymization_target' => $field[2]])->execute();
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function recalculateWorkingTime(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$db->createCommand("UPDATE vtiger_osstimecontrol SET sum_time=TIMESTAMPDIFF(MINUTE,CONCAT(date_start,' ',time_start),CONCAT(due_date,' ',time_end))")->execute();
		$db->createCommand()->update('vtiger_field', ['uitype' => 8], ['fieldname' => ['sum_time', 'sum_time_subordinate'], 'uitype' => 7])->execute();

		$log = '';
		foreach (['link', 'process', 'subprocess', 'linkextend', 'subprocess_sl'] as $field) {
			$queryGenerator = (new \App\QueryGenerator('OSSTimeControl'));
			$queryGenerator->permissions = false;
			$query = $queryGenerator->createQuery();
			$query->select(['id' => $field, 'module' => 'rel_crmentity.setype'])->andWhere(['<>', $field, 0])->distinct($field);
			$query->innerJoin(['rel_crmentity' => 'vtiger_crmentity'], "vtiger_osstimecontrol.{$field} = rel_crmentity.crmid");
			$dataReader = $query->createCommand()->query();
			$i = 0;
			while ($row = $dataReader->read()) {
				(new App\BatchMethod(['method' => 'OSSTimeControl_TimeCounting_Model::recalculate', 'params' => [$row['module'], $row['id'], $field]]))->save();
				++$i;
			}
			$log .= "{$field}: $i  ";
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . " | $log | " . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function relatedAttachmentsInPdf(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$dbCommand = \App\Db::getInstance()->createCommand();
		$columns = ['header_content', 'body_content', 'footer_content'];
		$i = 0;
		foreach ($columns as $column) {
			$query = (new \App\Db\Query())
				->select(['pdfid', $column])
				->from('a_#__pdf')
				->where(['LIKE', $column, '$(custom : RelatedAttachments']);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$content = $row[$column];
				$content = str_replace('$(custom : RelatedAttachments', '$(custom : RelatedAttachments|', $content);
				$dbCommand->update('a_#__pdf', [$column => $content], ['pdfid' => $row['pdfid']])->execute();
				++$i;
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . " | Updated: $i |" . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function addRecordListFilterValues(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$dbCommand = \App\Db::getInstance()->createCommand();
		if (!(new \App\Db\Query())->from('a_yf_record_list_filter')->exists()) {
			$tabRel = [
				// The module from which we list the record in the modal window
				'Contacts' => [
					// source module [Edit, Detail] => ['fieldName' => name of the related field in the source module, 'moduleName' => module name of the related field in the source module]
					'HelpDesk' => ['fieldName' => 'parent_id', 'moduleName' => 'Accounts'],
					'Project' => ['fieldName' => 'linktoaccountscontacts', 'moduleName' => 'Accounts'],
					'SSalesProcesses' => ['fieldName' => 'related_to', 'moduleName' => 'Accounts'],
					'SQuoteEnquiries' => ['fieldName' => 'accountid', 'moduleName' => 'Accounts'],
				],
				'ServiceContracts' => [
					'Assets' => ['fieldName' => 'parent_id', 'moduleName' => 'Accounts'],
					'OSSSoldServices' => ['fieldName' => 'parent_id', 'moduleName' => 'Accounts'],
				],
				'Assets' => [
					'ServiceContracts' => ['fieldName' => 'sc_related_to', 'moduleName' => 'Accounts']
				],
				'OSSSoldServices' => [
					'ServiceContracts' => ['fieldName' => 'sc_related_to', 'moduleName' => 'Accounts']
				],
				'SSalesProcesses' => [
					'SSalesProcesses' => ['fieldName' => 'related_to', 'moduleName' => 'Accounts']
				]
			];
			foreach ($tabRel as $relModule => $relData) {
				foreach ($relData as $sourceModule => $fieldsData) {
					$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
					$sourceModuleId = \App\Module::getModuleId($sourceModule);
					$relModuleId = \App\Module::getModuleId($relModule);
					$query = (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $sourceModuleId, 'related_tabid' => $relModuleId])->all();
					if (!$query) {
						$query = (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $relModuleId, 'related_tabid' => $sourceModuleId])->all();
					}
					$count = \count($query);
					if (1 === $count) {
						$currentData = current($query);
						$sourceRelationId = $currentData['relation_id'];
						$relField = $sourceModuleModel->getFieldByName($fieldsData['fieldName']);
						if ($relField && \in_array($fieldsData['moduleName'], $relField->getReferenceList())) {
							$relModuleName = $fieldsData['moduleName'];
							$query = (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => \App\Module::getModuleId($relModuleName), 'related_tabid' => $sourceModuleId, 'field_name' => $relField->getName()])->one();
							$relRelationId = $query['relation_id'];

							$query = (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => \App\Module::getModuleId($relModuleName), 'related_tabid' => \App\Module::getModuleId($relModule)])->one();
							$desRelationId = $query['relation_id'];

							if (!(new \App\Db\Query())->from('a_yf_record_list_filter')->where(['relationid' => $sourceRelationId, 'rel_relationid' => $relRelationId, 'dest_relationid' => $desRelationId])->exists()) {
								$dbCommand->insert('a_yf_record_list_filter',
								['relationid' => $sourceRelationId, 'rel_relationid' => $relRelationId, 'dest_relationid' => $desRelationId]
								)->execute();
							}
						} else {
							$this->log("[WARNING] The module does not exist in the relationship field: {$relModule} >> {$sourceModule} | relationid: {$sourceRelationId} | " . PHP_EOL . print_r($fieldsData, true));
						}
					} else {
						$this->log("[INFO] No relationship was found ($count): {$relModule} >> {$sourceModule} | " . PHP_EOL . print_r($fieldsData, true));
					}
				}
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
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
				[112, 3065, 'sys_name', 'u_yf_emailtemplates', 1, 1, 'sys_name', 'FL_SYS_NAME', 0, 0, '', '50', 8, 378, 2, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_CUSTOM_INFORMATION', 'blockData' => ['label' => 'LBL_CUSTOM_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 0, 'iscustom' => 0, 'icon' => null], 'moduleName' => 'EmailTemplates'],
				[60, 3079, 'multicompanyid', 'vtiger_osspasswords', 1, 10, 'multicompanyid', 'FL_MULTICOMPANY', 0, 2, '', '4294967295', 16, 147, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_OSSPASSWORD_INFORMATION', 'blockData' => ['label' => 'LBL_OSSPASSWORD_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['MultiCompany'], 'moduleName' => 'OSSPasswords']
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
			if (!empty($field['relatedModules']) && 10 == $field[5]) {
				$fieldInstance->setRelatedModules($field['relatedModules']);
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
		foreach (['OSSMail', 'OSSMailScanner', 'Calendar', 'IStorages', 'Notification'] as $module) {
			$configTemplates = "modules/{$module}/ConfigTemplate.php";
			copy(__DIR__ . '/files/' . $configTemplates, \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $configTemplates);
		}

		\App\Cache::resetOpcache();
		clearstatcache();

		$changeConfiguration = [
			'debug' => [
				'apiShowExceptionMessages' => \App\Config::debug('WEBSERVICE_SHOW_ERROR', \App\Config::debug('apiShowExceptionMessages')),
				'apiShowExceptionReasonPhrase' => \App\Config::debug('WEBSERVICE_SHOW_ERROR', \App\Config::debug('apiShowExceptionReasonPhrase')),
				'apiShowExceptionBacktrace' => \App\Config::debug('WEBSERVICE_SHOW_EXCEPTION_BACKTRACE', \App\Config::debug('apiShowExceptionBacktrace')),
				'apiLogException' => \App\Config::debug('WEBSERVICE_LOG_ERRORS', \App\Config::debug('apiLogException')),
				'apiLogAllRequests' => \App\Config::debug('WEBSERVICE_LOG_REQUESTS', \App\Config::debug('apiLogAllRequests')),
			],
			'performance' => [
				'CRON_MAX_NUMBERS_RECORD_LABELS_UPDATER' => 1000 == \App\Config::performance('CRON_MAX_NUMBERS_RECORD_LABELS_UPDATER') ? 10000 : \App\Config::performance('CRON_MAX_NUMBERS_RECORD_LABELS_UPDATER'),
			]
		];

		$skip = ['module', 'component'];
		foreach (array_diff(\App\ConfigFile::TYPES, $skip) as $type) {
			(new \App\ConfigFile($type))->create();
		}
		$dirPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Modules';
		if (!is_dir($dirPath)) {
			mkdir($dirPath);
		}
		$dataReader = (new \App\Db\Query())->select(['name'])->from('vtiger_tab')->createCommand()->query();
		while ($moduleName = $dataReader->readColumn(0)) {
			$filePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
			if (file_exists($filePath)) {
				(new \App\ConfigFile('module', $moduleName))->create();
			}
		}
		$path = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
		$componentsData = require_once "$path";
		foreach ($componentsData as $component => $data) {
			(new \App\ConfigFile('component', $component))->create();
		}

		foreach ($changeConfiguration as $type => $data) {
			$configFile = (new \App\ConfigFile($type));
			foreach ($data as $key => $value) {
				$configFile->set($key, $value);
			}
			$configFile->create();
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
				'time' => date('Y-m-d H:i:s')
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

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		\App\Module::createModuleMetaFile();
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
