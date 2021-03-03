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

			$this->importer->importData();
			$this->updateDataImporter();
			// $this->addModules(['x', 'x']);

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
		$this->addRecordListFilterValues();
		$this->dropInvTable();
		$this->addAnonymizationFields();
		$this->recalculateWorkingTime();
		$this->relatedAttachmentsInPdf();
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

		\App\Db\Updater::batchInsert([
			['a_yf_settings_modules',	['name' => 'Proxy', 'status' => 1, 'created_time' => date('Y-m-d H:i:s')], ['name' => 'Proxy']],
			['vtiger_settings_field',	['blockid' => vtlib\Deprecated::getSettingsBlockId('LBL_MAIL_TOOLS'), 'name' => 'LBL_CONFIG_PROXY', 'iconpath' => 'yfi yfi-server-configuration', 'description' => 'LBL_CONFIG_PROXY_DESCRIPTION', 'linkto' => 'index.php?parent=Settings&module=Proxy&view=Index', 'sequence', 'active' => 0, 'pinned' => 0, 'admin_access' => null], ['name' => 'LBL_CONFIG_PROXY']],
		]);

		\App\Db\Updater::batchDelete([
			['a_yf_settings_modules', ['name' => 'HideBlocks']],
			['vtiger_settings_field', ['name' => 'LBL_HIDEBLOCKS']],
		]);

		\App\Db\Updater::batchUpdate([
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
					'header_field' => [null, ''],
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
			['vtiger_field', ['record_state' => 2], ['ssalesprocesses_status' => ['PLL_SALE_COMPLETED', 'PLL_SALE_FAILED', 'PLL_SALE_CANCELLED']]],
			['vtiger_field', ['displaytype' => 2], ['fieldlabel' => ['FL_MAGENTO_SERVER', 'FL_MAGENTO_ID', 'FL_MAGENTO_STATUS']]],
			['vtiger_ssalesprocesses_status', ['record_state' => 2], ['ssalesprocesses_status' => ['PLL_SALE_COMPLETED', 'PLL_SALE_FAILED', 'PLL_SALE_CANCELLED']]],
			['vtiger_crmentity', ['private' => 0], ['private' => null]],
		]);

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
				$dbCommand->update('vtiger_tab', ['type' => 0], ['name' => 'SQuoteEnquiries'])->execute();
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
			}
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function addRecordListFilterValues(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$dbCommand = \App\Db::getInstance()->createCommand();
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
				'Project' => ['fieldName' => 'linktoaccountscontacts', 'moduleName' => 'Accounts']
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
					if (\in_array($fieldsData['moduleName'], $relField->getReferenceList())) {
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
				[
					112, 3065, 'sys_name', 'u_yf_emailtemplates', 1, 1, 'sys_name', 'FL_SYS_NAME', 0, 0, '', '50', 8, 378, 2, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '',
					'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_CUSTOM_INFORMATION', 'moduleName' => 'FInvoiceProforma'
				],
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
	public function postupdate(): bool
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

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

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return true;
	}
}
