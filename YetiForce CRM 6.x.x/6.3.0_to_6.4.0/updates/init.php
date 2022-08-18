<?php
/**
 * YetiForce system update package file.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

//  SHA-1: e32d5353852580fd3354ca3776a7879212aada93

/**
 * YetiForce system update package class.
 */
class YetiForceUpdate
{
	/** @var \vtlib\PackageImport */
	public $package;

	/** @var string[] Fields to delete. */
	public $filesToDelete = [];

	/** @var string */
	private $logFile = 'cache/logs/updateLogsTrace.log';

	/** @var object Module Meta XML File (Parsed). */
	private $moduleNode;

	/** @var DbImporter */
	private $importer;

	/** @var string[] Errors. */
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
	 * Log.
	 *
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

		if (version_compare(PHP_VERSION, '7.4', '<')) {
			$this->package->_errorText = 'The server configuration is not compatible with the requirements of the upgrade package.' . PHP_EOL;
			$this->package->_errorText .= 'Please have a look at the list of errors:' . PHP_EOL . PHP_EOL;
			$this->package->_errorText .= 'Wrong PHP version, recommended version >= 7.4';
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
		copy(__DIR__ . '/files/app/Db/Importers/Base.php', ROOT_DIRECTORY . '/app/Db/Importers/Base.php');
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
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->checkIntegrity(false);
			$this->roundcubeUpdateTable();
			$this->updateTargetField();
			$this->importer->dropForeignKeys(['u_yf_users_pinned_fk_1' => 'u_yf_users_pinned', 'module' => 'vtiger_trees_templates']);
			$this->importer->updateScheme();
			$this->importer->dropTable(['vtiger_ws_entity', 'vtiger_ws_fieldinfo', 'vtiger_ws_operation', 'vtiger_ws_operation_parameters', 'vtiger_ws_userauthtoken']);

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
		$this->addFields($this->getFields(1));
		$this->removeModules('OSSPasswords');
		$this->addModules(['SMSTemplates']);
		$this->smsNotifier();
		$this->updateData();
		$this->picklistDependency();
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function removeModules($moduleName)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " {$moduleName} | " . date('Y-m-d H:i:s'));

		$moduleInstance = \Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleInstance) {
			$moduleInstance->delete();
		} else {
			$this->log('  [INFO] Module not exists: ' . $moduleName);
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
				if ($tabId = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['name' => $moduleName])->scalar()) {
					\CRMEntity::getInstance('ModTracker')->enableTrackingForModule($tabId);
				}
				$this->log('  [INFO] Add module:' . $moduleName);
			} else {
				$this->log('  [INFO] Module exist: ' . $moduleName);
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function smsNotifier()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$moduleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		$fieldName = 'smsnotifier_status';
		$values = ['PLL_SENT', 'PLL_QUEUE', 'PLL_REPLY'];
		$fieldModel = $moduleModel->getFieldByName($fieldName);
		if ($diffVal = array_diff($values, \App\Fields\Picklist::getValuesName($fieldName))) {
			$fieldModel->setPicklistValues($diffVal);
			$this->log('  [INFO] Add picklist values:' . implode(',', $diffVal));
		}
		$fieldModel
			->set('displaytype', 2)
			->set('presence', 0)
			->set('defaultvalue', 'PLL_QUEUE')
			->save();
		$block = $moduleModel->getBlocks()['StatusInformation'] ?? null;
		if ($block && null == ($moduleModel->getFieldsByBlocks()['StatusInformation'] ?? null)) {
			$block->delete(false);
			$this->log('  [INFO] Delete block: StatusInformation');
		} elseif ($block) {
			$this->log('  [WARNING] cannot delete a block(StatusInformation) because it has fields');
		}
		if (!($moduleModel->getBlocks()['BL_SMS_CONTENT'] ?? null)) {
			$blockInstance = new \vtlib\Block();
			$data = ['label' => 'BL_SMS_CONTENT', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null, 'sequence' => 2];
			foreach ($data as $key => $value) {
				$blockInstance->{$key} = $value;
			}
			$moduleModel->addBlock($blockInstance);
			$this->log('  [INFO] Added new block: BL_SMS_CONTENT');

			$messageField = $moduleModel->getFieldByName('message');
			Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber([[
				'block' => $blockInstance->id,
				'fieldid' => $messageField->getId(),
				'sequence' => 1,
			]]);
		}

		$this->addFields($this->getFields(0));

		$updateSeq = [];
		$systemBlock = $moduleModel->getBlocks()['LBL_CUSTOM_INFORMATION'];
		foreach ([1 => 'assigned_user_id', 3 => 'createdtime', 4 => 'modifiedtime', 5 => 'created_user_id', 6 => 'modifiedby'] as $key => $fieldName) {
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			if ('LBL_SMSNOTIFIER_INFORMATION' === $fieldModel->getBlockName()) {
				$updateSeq[] = [
					'block' => $systemBlock->id,
					'fieldid' => $fieldModel->getId(),
					'sequence' => $key,
				];
			}
		}

		if ($updateSeq) {
			Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($updateSeq);
		}

		\App\EventHandler::registerHandler('EntityBeforeSave', 'SMSNotifier_Parser_Handler', 'SMSNotifier', '', 5, true, \App\Module::getModuleId('SMSNotifier'));
		$messageField = $moduleModel->getFieldByName('message');
		$db->createCommand()->update('vtiger_field', ['fieldlabel' => 'FL_MESSAGE'], ['fieldlabel' => 'message', 'fieldid' => $messageField->getId()])->execute();
		$db->createCommand()->update('vtiger_smsnotifier_status', ['presence' => 0], ['smsnotifier_status' => ['PLL_QUEUE', 'PLL_SENT', 'PLL_REPLY']])->execute();
		\App\Db\Updater::addRoleToPicklist(['smsnotifier_status']);

		// remove relation M-M
		$db->createCommand()->delete('vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('SMSNotifier'), 'name' => 'getRelatedList'])->execute();

		// add block record by picklist value
		$moduleModel = Settings_Picklist_Module_Model::getInstance('SMSNotifier');
		$fieldModel = Settings_Picklist_Field_Model::getInstance('smsnotifier_status', $moduleModel);
		if ($fieldModel) {
			$fieldName = $fieldModel->getName();
			$values = array_column(App\Fields\Picklist::getValues($fieldName), 'picklist_valueid', 'smsnotifier_status');
			$fieldModel->updateCloseState($values['PLL_DELIVERED'], 'PLL_DELIVERED', true);
			$fieldModel->updateCloseState($values['PLL_REPLY'], 'PLL_REPLY', true);
		}
		$relatedModules = array_merge(array_keys(\App\ModuleHierarchy::getModulesByLevel(0)), ['Contacts']);

		// Add action MassSMS to some modules
		$this->actionMapp([
			['type' => 'add', 'name' => 'MassSendSMS', 'tabsData' => array_map('\App\Module::getModuleId', $relatedModules), 'permission' => 0]
		]);

		// add restriction to sms workflow task
		$task = (new \App\Db\Query())->select(['id', 'modules'])->from('com_vtiger_workflow_tasktypes')->where(['tasktypename' => 'VTSMSTask'])->one();
		$taskModules = \App\Json::decode($task['modules']);
		$taskModules['include'] = $relatedModules;
		$task['modules'] = \App\Json::encode($taskModules);
		$db->createCommand()->update('com_vtiger_workflow_tasktypes', ['modules' => $task['modules']], ['id' => $task['id']])->execute();

		$this->importer->dropTable(['vtiger_passwords_config']);

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function actionMapp(array $actions)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();

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
							'profileid' => $profileId, 'tabid' => $tabId, 'activityid' => $key, 'permission' => $permission,
						])->execute();
					}
				}
			}
		}
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function getFields(int $index)
	{
		$fields = [];
		$importerType = new \App\Db\Importers\Base();
		switch ($index) {
			case 0:
				$fields = [
					[119, 2653, 'phone', 'vtiger_smsnotifier', 1, 11, 'phone', 'FL_PHONE', 0, 0, '', '30', 3, 407, 10, 'V~M', 1, 0, 'BAS', 1, '', 0, '', '', 0, 0, 0, 0, '', '', 'type' => $importerType->stringType(30), 'blockLabel' => 'LBL_SMSNOTIFIER_INFORMATION', 'blockData' => null, 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'SMSNotifier'],
					[60, 929, 'related_to', 'vtiger_smsnotifier', 1, 10, 'related_to', 'FL_RELATED_TO', 0, 0, '', '4294967295', 3, 147, 1, 'I~M', 1, null, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', null, 'type' => $importerType->integer(10)->unsigned(), 'blockLabel' => 'LBL_SMSNOTIFIER_INFORMATION', 'picklistValues' => [], 'relatedModules' => array_merge(array_keys(\App\ModuleHierarchy::getModulesByLevel(0)), ['Contacts']), 'moduleName' => 'SMSNotifier'],
					[119, 2653, 'msgid', 'vtiger_smsnotifier', 1, 1, 'msgid', 'FL_MESSAGE_ID', 0, 0, '', '50', 3, 407, 2, 'V~O', 1, 0, 'BAS', 1, '', 0, '', '', 0, 0, 0, 0, '', '', 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_CUSTOM_INFORMATION', 'blockData' => null, 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'SMSNotifier'],
					[60, 929, 'parentid', 'vtiger_smsnotifier', 1, 10, 'parentid', 'FL_PARENT', 0, 0, '', '4294967295', 3, 147, 2, 'I~O', 1, null, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', null, 'type' => $importerType->integer(10)->unsigned(), 'blockLabel' => 'LBL_SMSNOTIFIER_INFORMATION', 'picklistValues' => [], 'relatedModules' => ['SMSNotifier'], 'moduleName' => 'SMSNotifier'],
					[75, 3176, 'image', 'vtiger_smsnotifier', 1, 69, 'image', 'FL_IMAGE', 0, 2, '', '65535', 20, 236, 1, 'V~O', 2, 0, 'BAS', 1, '', 0, '{"maxFileSize":512000}', null, 0, 0, 0, 0, '', '', 'type' => $importerType->text(), 'blockLabel' => 'BL_SMS_CONTENT', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'SMSNotifier'],
				];
				break;
			case 1:
				$fields = [
					[29, 3100, 'calendar_all_users_by_default', 'vtiger_users', 1, 56, 'calendar_all_users_by_default', 'FL_CALENDAR_ALL_USERS_BY_DEFAULT', 0, 2, '', '-128,127', 12, 118, 1, 'C~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', '', 'type' => $importerType->tinyInteger(1), 'blockLabel' => 'LBL_CALENDAR_SETTINGS', 'blockData' => ['label' => 'LBL_CALENDAR_SETTINGS', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => 'far fa-calendar-alt'], 'moduleName' => 'Users'],
				];
				break;
			default:
				break;
		}

		return $fields;
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
				[60, 3079, 'multicompanyid', 'vtiger_osspasswords', 1, 10, 'multicompanyid', 'FL_MULTICOMPANY', 0, 2, '', '4294967295', 16, 147, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 0, '', 'type' => $importerType->integer(10)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_OSSPASSWORD_INFORMATION', 'blockData' => ['label' => 'LBL_OSSPASSWORD_INFORMATION', 'showtitle' => 0, 'visible' => 0, 'increateview' => 0, 'ineditview' => 0, 'indetailview' => 0, 'display_status' => 2, 'iscustom' => 0, 'icon' => null], 'relatedModules' => ['MultiCompany'], 'moduleName' => 'OSSPasswords'],
			];
		}

		foreach ($fields as $field) {
			$moduleName = $field['moduleName'];
			$moduleId = \App\Module::getModuleId($moduleName);
			if (!$moduleId) {
				$this->log("  [ERROR] Module not exists: {$moduleName}");
				continue;
			}
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if ($isExists) {
				$this->log("  [INFO] Skip adding field. Module: {$moduleName}({$moduleId}); field name: {$field[2]}, field exists: {$isExists}");
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
				$this->log("  [ERROR] No block found to create a field, you will need to create a field manually.
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

	private function roundcubeUpdateTable()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		foreach (['roundcube_cache', 'roundcube_cache_index', 'roundcube_cache_messages', 'roundcube_cache_shared', 'roundcube_cache_thread', 'roundcube_dictionary'] as $tableName) {
			$db->createCommand()->delete($tableName)->execute();
		}
		$importerBase = new \App\Db\Importers\Base();
		$importerBase->dropColumns = [
			['roundcube_cache', 'created'],
			['roundcube_cache_shared', 'created'],
			['roundcube_session', 'created']
		];
		$importerBase->dropIndexes = [
			'roundcube_cache' => ['user_cache_index'],
			'roundcube_cache_shared' => ['cache_key_index'],
		];
		$importerBase->tables = [
			'roundcube_cache' => [
				'primaryKeys' => [
					['roundcube_cache_pk', ['user_id', 'cache_key']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'roundcube_cache_messages' => [
				'columns' => [
					'uid' => $importerBase->integer()->unsigned()->notNull()->defaultValue(0),
					'flags' => $importerBase->integer()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'roundcube_cache_shared' => [
				'primaryKeys' => [
					['roundcube_cache_shared_pk', 'cache_key']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'roundcube_dictionary' => [
				'columns' => [
					'id' => $importerBase->primaryKeyUnsigned(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
		];

		if (!$db->isTableExists('roundcube_responses')) {
			$importerBase->tables['roundcube_responses'] = [
				'columns' => [
					'response_id' => $importerBase->primaryKeyUnsigned(10),
					'user_id' => $importerBase->integer(10)->unsigned()->notNull(),
					'name' => $importerBase->stringType()->notNull(),
					'data' => $importerBase->text()->notNull(),
					'is_html' => $importerBase->smallInteger(1)->notNull()->defaultValue(0),
					'changed' => $importerBase->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'del' => $importerBase->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'is_html' => $importerBase->tinyInteger(1)->notNull()->defaultValue(0),
					'del' => $importerBase->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['user_responses_index', ['user_id', 'del']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			];
			$importerBase->foreignKey = [
				['user_id_fk_responses', 'roundcube_responses', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE']
			];
		}

		$this->importer->drop($importerBase);
		$this->importer->updateTables($importerBase);
		$this->importer->updateForeignKey($importerBase);

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function updateTargetField()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$column = $db->getSchema()->getTableSchema('vtiger_project')->getColumn('targetbudget');
		$i = 0;
		if ($column && 'integer' !== $column->type) {
			$dbCommand = $db->createCommand();
			$dataReader = (new \App\db\Query())->select(['projectid', 'targetbudget'])->from('vtiger_project')->where(['not', ['targetbudget' => null]])->createCommand()->query();
			while ($row = $dataReader->read()) {
				$value = $row['targetbudget'];
				$value = is_numeric($value) ? (float) $value : 0;
				if ($value < 0) {
					$value = 0;
				}
				$i += $dbCommand->update('vtiger_project', ['targetbudget' => $value], ['projectid' => $row['projectid']])->execute();
			}
			$dataReader->close();
			$this->log('  [INFO] update vtiger_project.targetbudget | count:' . $i);

			$importerBase = new \App\Db\Importers\Base();
			$importerBase->tables['vtiger_project'] = [
				'columns' => [
					'targetbudget' => ['type' => $importerBase->integer(10)->unsigned(), 'mode' => 1]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			];
			$this->importer->updateScheme($importerBase);
		}

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function updateData(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$batchInsert = \App\Db\Updater::batchInsert([
			['a_yf_discounts_config', ['param' => 'default_mode', 'value' => 1], ['param' => 'default_mode']],
			['a_yf_taxes_config', ['param' => 'default_mode', 'value' => 1], ['param' => 'default_mode']],
		]);
		$this->log('  [INFO] batchInsert: ' . \App\Utils::varExport($batchInsert));
		unset($batchInsert);

		$updates = [
			['vtiger_trees_templates_data', ['icon' => ''], ['icon' => '1']],
			['vtiger_trees_templates_data', ['icon' => new \yii\db\Expression("REPLACE(icon,'public_html/', '')")], ['like', 'icon', 'public_html/%', false]],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'timing_change', 'tablename' => 'u_yf_cfixedassets', 'maximumlength' => '0,2147483647']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'fuel_consumption', 'tablename' => 'u_yf_cfixedassets', 'maximumlength' => '0,2147483647']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'oil_change', 'tablename' => 'u_yf_cfixedassets', 'maximumlength' => '0,2147483647']],
			// ['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'current_odometer_reading', 'tablename' => 'u_yf_cfixedassets', 'maximumlength' => '0,2147483647']],
			['vtiger_field', ['maximumlength' => '0,65535'], ['fieldname' => 'number_repair', 'tablename' => 'u_yf_cfixedassets', 'maximumlength' => '0,32767']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'peoplne_number', 'tablename' => 'u_yf_incidentregister', 'maximumlength' => '0,2147483647']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'capacity', 'tablename' => 'u_yf_locations', 'maximumlength' => '0,2147483647']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'employees', 'tablename' => 'vtiger_account', 'maximumlength' => '0,2147483647']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'duration', 'tablename' => 'vtiger_callhistory', 'maximumlength' => '0,2147483647']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'prodcount', 'tablename' => 'vtiger_outsourcedproducts', 'maximumlength' => '0,2147483647']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['fieldname' => 'records_limit', 'tablename' => 'vtiger_users']],
			['vtiger_field', ['maximumlength' => '0,999'], ['fieldname' => 'total_units', 'tablename' => 'vtiger_servicecontracts', 'maximumlength' => '999']],
			['vtiger_field', ['maximumlength' => '0,999999'], ['fieldname' => 'estimated_work_time', 'tablename' => 'vtiger_projecttask', 'maximumlength' => '999999']],
			['vtiger_field', ['maximumlength' => '0,9999999999999'], ['fieldname' => 'estimated_work_time', 'tablename' => 'vtiger_projectmilestone', 'maximumlength' => '9999999999999']],
			['vtiger_field', ['maximumlength' => '0,9999999999999'], ['fieldname' => 'estimated_work_time', 'tablename' => 'vtiger_project', 'maximumlength' => '9999999999999']],

			['vtiger_field', ['maximumlength' => '0,1.0E+20'], ['tablename' => 'u_yf_cfixedassets', 'fieldname' => 'actual_price']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'u_yf_cfixedassets', 'fieldname' => 'current_odometer_reading']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'u_yf_cfixedassets', 'fieldname' => 'fuel_consumption']],
			['vtiger_field', ['maximumlength' => '0,65535'], ['tablename' => 'u_yf_cfixedassets', 'fieldname' => 'number_repair']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'u_yf_cfixedassets', 'fieldname' => 'oil_change']],
			['vtiger_field', ['maximumlength' => '0,1.0E+20'], ['tablename' => 'u_yf_cfixedassets', 'fieldname' => 'purchase_price']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'u_yf_cfixedassets', 'fieldname' => 'timing_change']],
			['vtiger_field', ['maximumlength' => '0,99999999999'], ['tablename' => 'u_yf_cmileagelogbook', 'fieldname' => 'number_kilometers']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'u_yf_incidentregister', 'fieldname' => 'peoplne_number']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'u_yf_locations', 'fieldname' => 'capacity']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'u_yf_occurrences', 'fieldname' => 'participants']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_account', 'fieldname' => 'employees']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_callhistory', 'fieldname' => 'duration']],
			['vtiger_field', ['maximumlength' => '0,1.0E+20'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'actualcost']],
			['vtiger_field', ['maximumlength' => '0,1.0E+20'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'actualroi']],
			['vtiger_field', ['maximumlength' => '0,1.0E+20'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'budgetcost']],
			['vtiger_field', ['maximumlength' => '0,1.0E+20'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'expectedrevenue']],
			['vtiger_field', ['maximumlength' => '0,1.0E+20'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'expectedroi']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'actualsalescount']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'actualresponsecount']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'expectedresponsecount']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'expectedsalescount']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'targetsize']],
			['vtiger_field', ['maximumlength' => '0,99999999999'], ['tablename' => 'vtiger_campaign', 'fieldname' => 'numsent']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_leaddetails', 'fieldname' => 'noofemployees']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_outsourcedproducts', 'fieldname' => 'prodcount']],
			['vtiger_field', ['maximumlength' => '0,99999999'], ['tablename' => 'vtiger_products', 'fieldname' => 'purchase']],
			['vtiger_field', ['maximumlength' => '0,99999999'], ['tablename' => 'vtiger_products', 'fieldname' => 'unit_price']],
			['vtiger_field', ['maximumlength' => '0,99999999'], ['tablename' => 'vtiger_products', 'fieldname' => 'weight']],
			['vtiger_field', ['maximumlength' => '0,9999999999999'], ['tablename' => 'vtiger_project', 'fieldname' => 'estimated_work_time']],
			['vtiger_project', ['targetbudget' => null], ['targetbudget' => '']],
			['vtiger_field', ['maximumlength' => '0,4294967295', 'typeofdata' => 'I~O'], ['tablename' => 'vtiger_project', 'fieldname' => 'targetbudget']],
			['vtiger_field', ['maximumlength' => '0,9999999999999'], ['tablename' => 'vtiger_projectmilestone', 'fieldname' => 'estimated_work_time']],
			['vtiger_field', ['maximumlength' => '0,999999'], ['tablename' => 'vtiger_projecttask', 'fieldname' => 'estimated_work_time']],
			['vtiger_field', ['maximumlength' => '0,99999999'], ['tablename' => 'vtiger_service', 'fieldname' => 'purchase']],
			['vtiger_field', ['maximumlength' => '0,99999999'], ['tablename' => 'vtiger_service', 'fieldname' => 'unit_price']],
			['vtiger_field', ['maximumlength' => '0,999'], ['tablename' => 'vtiger_servicecontracts', 'fieldname' => 'total_units']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_users', 'fieldname' => 'records_limit']],
			['vtiger_field', ['maximumlength' => '0,255'], ['tablename' => 'vtiger_ossmailview', 'fieldname' => 'type']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_ossmailview', 'fieldname' => 'mid']],
			['vtiger_field', ['maximumlength' => '0,4294967295'], ['tablename' => 'vtiger_ossmailview', 'fieldname' => 'rc_user']],
			['com_vtiger_workflow_tasktypes', ['templatepath' => ''], []],
		];
		$links = (new \App\db\Query())->select(['linkid', 'tabid'])->from('vtiger_links')->where(['linktype' => 'DASHBOARDWIDGET'])->createCommand()->queryAllByGroup(0);
		foreach ($links as $linkId => $tabId) {
			$updates[] = ['vtiger_module_dashboard_widgets', ['module' => $tabId], ['linkid' => $linkId]];
		}
		$batchUpdate = \App\Db\Updater::batchUpdate($updates);
		// ['u_yf_users_pinned', ['tabid' => \App\Module::getModuleId('Calendar')], ''],
		$this->log('  [INFO] batchUpdate: ' . \App\Utils::varExport($batchUpdate));
		unset($batchUpdate);

		$importerBase = new \App\Db\Importers\Base();
		$importerBase->tables = [
			'u_#__users_pinned' => [
				'columns' => [
					'tabid' => $importerBase->smallInteger(5)->notNull()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->importer->updateTables($importerBase);

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function picklistDependency(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		if (!$db->isTableExists('vtiger_picklist_dependency')) {
			$this->log('  [INFO] skip update dependency: table not exists vtiger_picklist_dependency');
			$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
			return;
		}
		$dbCommand = $db->createCommand();

		$dependencies = [];
		$dataReader = (new \App\db\Query())->from('vtiger_picklist_dependency')->createCommand()->query();
		while ($row = $dataReader->read()) {
			$dependencies[$row['tabid']][$row['targetfield']][$row['sourcefield']][] = $row;
		}
		$dataReader->close();

		try {
			$isEmptyDefaultValue = \App\Config::performance('PICKLIST_DEPENDENCY_DEFAULT_EMPTY', true);
			foreach ($dependencies as $tabId => $data) {
				$moduleName = \App\Module::getModuleName($tabId);
				if ($moduleName && \App\Module::isModuleActive($moduleName)) {
					$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
					foreach ($data as $targetField => $dependency) {
						$fieldModel = $moduleModel->getFieldByName($targetField);
						foreach ($dependency as $sourceField => $values) {
							$fieldModelSource = $moduleModel->getFieldByName($sourceField);
							$conditionFieldName = "{$sourceField}:{$moduleName}";
							if ($fieldModel && $fieldModel->isActiveField() && 'picklist' === $fieldModel->getFieldDataType()
							&& $fieldModelSource && $fieldModelSource->isActiveField() && 'picklist' === $fieldModelSource->getFieldDataType()
							&& !(new \App\db\Query())->from('s_yf_picklist_dependency')->where(['tabid' => $tabId, 'source_field' => $fieldModel->getId()])->exists()
						) {
								$dbCommand->insert('s_yf_picklist_dependency', ['tabid' => $tabId, 'source_field' => $fieldModel->getId()])->execute();
								$dependencyId = $db->getLastInsertID('s_yf_picklist_dependency_id_seq');
								$targetPicklistValues = \App\Fields\Picklist::getValuesName($fieldModel->getName());
								$sourcePicklistValues = \App\Fields\Picklist::getValuesName($fieldModelSource->getName());
								foreach ($targetPicklistValues as $key => $value) {
									$sourceValues = array_filter($values, function ($row) use ($value){
										return \in_array($value, \App\Json::decode($row['targetvalues'] ?: '[]'));
									});
									$sourceValues = array_column($sourceValues, 'sourcevalue');
									$sourceValues = array_intersect($sourceValues, $sourcePicklistValues);
									$rules = [];
									if (!$sourceValues && $isEmptyDefaultValue) {
										$rules[] = ['fieldname' => $conditionFieldName, 'operator' => 'y', 'value' => ''];
										$rules[] = ['fieldname' => $conditionFieldName, 'operator' => 'ny', 'value' => ''];
									} elseif ($sourceValues) {
										$rules[] = ['fieldname' => $conditionFieldName, 'operator' => 'e', 'value' => implode('##', $sourceValues)];
									}
									if ($rules) {
										$conditions = ['condition' => 'AND', 'rules' => $rules];
										$dbCommand->insert('s_yf_picklist_dependency_data', ['id' => $dependencyId, 'source_id' => $key, 'conditions' => \App\Json::encode($conditions)])->execute();
									}
								}
							}
						}
					}
				}
			}
			$this->importer->dropTable(['vtiger_picklist_dependency']);
		} catch (\Throwable $th) {
			$this->log("  [ERROR]: {$th->__toString()}");
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

		foreach (['config/ConfigTemplates.php', 'config/Components/ConfigTemplates.php', 'modules/OSSMail/ConfigTemplate.php', 'modules/OpenStreetMap/ConfigTemplate.php', 'modules/ModComments/ConfigTemplate.php'] as $configTemplates) {
			$path = ROOT_DIRECTORY . '/' . $configTemplates;
			copy(__DIR__ . '/files/' . $configTemplates, $path);
			\App\Cache::resetFileCache($path);
		}

		\App\Cache::resetOpcache();
		clearstatcache();

		(new \App\ConfigFile('security'))->create();
		(new \App\ConfigFile('performance'))->create();
		$debug = (new \App\ConfigFile('debug'));
		if (null !== \App\Config::debug('DAV_DEBUG_EXCEPTIONS', null)) {
			$debug->set('davDebugExceptions', \App\Config::debug('DAV_DEBUG_EXCEPTIONS'));
			$debug->set('davDebugPlugin', \App\Config::debug('DAV_DEBUG_PLUGIN'));
		}
		$debug->create();

		$ossMail = new \App\ConfigFile('module', 'OSSMail');
		if (null !== \App\Config::module('OSSMail', 'default_host', null)) {
			$imaps = \App\Config::module('OSSMail', 'default_host');
			$newImaps = [];
			$port = \App\Config::module('OSSMail', 'default_port', 993);
			foreach ($imaps as $key => $name) {
				if (!parse_url($key, PHP_URL_PORT)) {
					if ($name === $key) {
						$name .= ":{$port}";
					}
					$key .= ":{$port}";
				}
				$newImaps[$key] = $name;
			}
			$ossMail->set('imap_host', $newImaps);

			$smtp = \App\Config::module('OSSMail', 'smtp_server');
			$smtpPort = \App\Config::module('OSSMail', 'smtp_port');
			$ossMail->set('smtp_host', $smtp ? "{$smtp}:{$smtpPort}" : '');
		}
		$ossMail->create();

		$openStreetMap = new \App\ConfigFile('module', 'OpenStreetMap');
		if (null !== \App\Config::module('OpenStreetMap', 'CRON_MAX_UPDATED_ADDRESSES', null)) {
			$openStreetMap->set('cronMaxUpdatedAddresses', \App\Config::module('OpenStreetMap', 'CRON_MAX_UPDATED_ADDRESSES', 1000));
			$openStreetMap->set('mapModules', \App\Config::module('OpenStreetMap', 'ALLOW_MODULES', ['Accounts', 'Contacts', 'Competition', 'Vendors', 'Partners', 'Leads', 'Locations']));
			$openStreetMap->set('mapPinFields', \App\Config::module('OpenStreetMap', 'FIELDS_IN_POPUP', [
				'Accounts' => ['accountname', 'email1', 'phone'],
				'Leads' => ['company', 'firstname', 'lastname', 'email'],
				'Partners' => ['subject', 'email'],
				'Competition' => ['subject', 'email'],
				'Vendors' => ['vendorname', 'email', 'website'],
				'Contacts' => ['firstname', 'lastname', 'email', 'phone'],
				'Locations' => ['subject', 'email']
			]));
		}
		$openStreetMap->create();

		$configFile = new \App\ConfigFile('module', 'ModComments');
		if (null !== \App\Config::module('ModComments', 'DEFAULT_SOURCE', null)) {
			$configFile->set('defaultSource', \App\Config::module('ModComments', 'DEFAULT_SOURCE', ['current']));
		}
		$configFile->create();

		// $addressFinder = new \App\ConfigFile('component', 'AddressFinder');
		// if ('no data' !== \App\Config::component('AddressFinder', 'REMAPPING_OPENCAGE', 'no data')) {
		// 	$addressFinder->set('remappingOpenCage', \App\Config::component('AddressFinder', 'REMAPPING_OPENCAGE'));
		// 	$addressFinder->set('remappingOpenCageForCountry', \App\Config::component('AddressFinder', 'REMAPPING_OPENCAGE_FOR_COUNTRY'));
		// }
		// $addressFinder->create();

		$skip = ['main', 'db', 'performance', 'debug', 'security', 'module', 'component'];
		foreach (array_diff(\App\ConfigFile::TYPES, $skip) as $type) {
			(new \App\ConfigFile($type))->create();
		}

		$dataReader = (new \App\Db\Query())->select(['name'])->from('vtiger_tab')->createCommand()->query();
		while ($moduleName = $dataReader->readColumn(0)) {
			$filePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
			if (!\in_array($moduleName, ['OpenStreetMap', 'OSSMail', 'ModComments']) && file_exists($filePath)) {
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

	/**
	 * Post update .
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$this->createConfigFiles();

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

	/**
	 * Stop process when an error occurs.
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
}
