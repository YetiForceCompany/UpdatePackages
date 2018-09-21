<?php
/**
 * YetiForceUpdate Class
 * @package   YetiForce.UpdatePackages
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * YetiForceUpdate Class
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
	 * Fields to delete
	 * @var string[]
	 */
	public $filesToDelete = [];

	/**
	 * DbImporter
	 * @var DbImporter
	 */
	private $importer;


	public $modules = [];
	public $moduleName = '';
	public $uitypes = [69, 311];

	/**
	 * Constructor
	 * @param object $modulenode
	 */
	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
		$this->filesToDelete = require_once('deleteFiles.php');
	}


	/**
	 * Logs.
	 *
	 * @param string $message
	 */
	public function log($message)
	{
		$fp = fopen($this->logFile, 'a+');
		fwrite($fp, $message . PHP_EOL);
		fclose($fp);
	}

	/**
	 * Preupdate
	 */
	public function preupdate()
	{
		$minTime = 600;
		if (ini_get('max_execution_time') < $minTime || ini_get('max_input_time') < $minTime) {
			$this->package->_errorText = 'The server configuration is not compatible with the requirements of the upgrade package.' . PHP_EOL;
			$this->package->_errorText .= 'Please have a look at the list of errors:';
			if (ini_get('max_execution_time') < $minTime) {
				$this->package->_errorText .= PHP_EOL . 'max_execution_time = ' . ini_get('max_execution_time') . ' < ' . $minTime;
			}
			if (ini_get('max_input_time') < $minTime) {
				$this->package->_errorText .= PHP_EOL . 'max_input_time = ' . ini_get('max_input_time') . ' < ' . $minTime;
			}
			$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
			return false;
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return true;
	}

	private function removeForeignKey()
	{
		$data = ['a_#__record_converter_fk_tab' => 'a_#__record_converter'];
		$db = App\Db::getInstance('admin');
		foreach ($data as $keyName => $tableName) {
			$tableSchema = $db->getTableSchema($tableName, true);
			$keyName = str_replace('#__', $db->tablePrefix, $keyName);
			if (isset($tableSchema->foreignKeys[$keyName])) {
				$db->createCommand()->dropForeignKey($keyName, $tableName)->execute();
			}
		}
	}

	/**
	 * Update
	 */
	public function update()
	{
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		try {
			$this->importer = new \App\Db\Importer();
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->updateScheme();
			$this->removeForeignKey();
			$this->importer->refreshSchema();
			$this->importer->postUpdate();
			$this->importer->dropTable(['com_vtiger_workflowtasks_seq']);
			$this->importer->logs(false);
		} catch (\Throwable $ex) {
			$this->log($ex->getMessage() . '|' . $ex->getTraceAsString());
			$this->importer->logs(false);
			throw $ex;
		}
		$this->importer->refreshSchema();
		$db->createCommand()->checkIntegrity(true)->execute();
		$this->updateData();
		$this->addPicklistValues();
		$this->removeEventsModule();
		$this->updateCron();
		$this->addFields();
		$this->updateHeaderField();
		$this->addFilter();
		$this->addRelations();
		$this->updateRecords();
		$this->imageFix();
		$this->attachmentsFix();
		$this->updateConfigurationFiles();
	}

	private function attachmentsFix()
	{
		$db = \App\Db::getInstance();
		$query = (new \App\Db\Query())->select(['attachmentsid', 'path'])->from('vtiger_attachments');
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$path = $row['path'];
			if (substr(str_replace('\\', '/', $path), 0, 7) !== 'storage') {
				$path .= 'X';
				$db->createCommand()->update('vtiger_attachments', ['path' => substr($path, strpos($path, 'storage'), -1)], ['attachmentsid' => $row['attachmentsid']])->execute();
			}
		}
	}

	/**
	 * Migrate image field.
	 *
	 * @param string $field
	 */
	private function migrateImage(string $field = null)
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$entityModel = $queryGenerator->getEntityModel();
		if ($field) {
			$field = $queryGenerator->getModuleModel()->getField($field);
			$fields = $field ? [$field] : [];
		} else {
			$fields = $queryGenerator->getModuleModel()->getFieldsByUiType(69);
		}
		$field = reset($fields);
		if (empty($fields) || count($fields) !== 1 || !isset($entityModel->tab_name_index[$field->getTableName()])) {
			\App\Log::error('MIGRATE FILES ID:' . implode(',', array_keys($fields)) . "|{$this->moduleName} - Incorrect data");
			return;
		}
		$field->set('primaryColumn', $entityModel->tab_name_index[$field->getTableName()]);
		$queryGenerator->permissions = false;
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id', $field->getName()]);
		$queryGenerator->setCustomColumn(['vtiger_attachments.*']);
		if ($field->getModuleName() !== 'Users') {
			$relTable = 'vtiger_seattachmentsrel';
			$queryGenerator->addJoin(['INNER JOIN', $relTable, $queryGenerator->getColumnName('id') . "=$relTable.crmid"]);
			$queryGenerator->addJoin(['INNER JOIN', 'vtiger_attachments', "$relTable.attachmentsid = vtiger_attachments.attachmentsid"]);
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				$this->updateRow($row, $field);
			}
			$dataReader->close();
		}
	}

	/**
	 * Migrate MultiImage field.
	 */
	private function migrateMultiImage()
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$entityModel = $queryGenerator->getEntityModel();
		$fields = $queryGenerator->getModuleModel()->getFieldsByUiType(311);
		foreach ($fields as $field) {
			if (empty($field) || !isset($entityModel->tab_name_index[$field->getTableName()])) {
				\App\Log::error("MIGRATE FILES ID:{$field->getName()}|{$this->moduleName} - Incorrect data");
				continue;
			}
			if ($field->getModuleName() === 'Contacts' || $field->getModuleName() === 'Products') {
				$this->migrateImage($field->getName());
				continue;
			}
		}
	}

	/**
	 * Update data.
	 *
	 * @param array               $row
	 * @param \Vtiger_Field_Model $field
	 * @param bool                $isMulti
	 */
	private function updateRow(array $row, \Vtiger_Field_Model $field, bool $isMulti = false)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$path = strpos($row['path'], ROOT_DIRECTORY) === 0 ? $row['path'] : ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $row['path'];
		$file = \App\Fields\File::loadFromInfo([
			'path' => $path . DIRECTORY_SEPARATOR . $row['attachmentsid'],
			'name' => $row['name'],
		]);
		if (!file_exists($file->getPath())) {
			\App\Log::error("MIGRATE FILES ID:{$row['id']}|{$row['attachmentsid']} - No file");
			return;
		}
		if ($file->validate()) {
			$image = [];
			$image['key'] = $file->generateHash(true);
			$image['size'] = \vtlib\Functions::showBytes($file->getSize());
			$image['name'] = $file->getName();
			$image['path'] = \App\Fields\File::getLocalPath($file->getPath());

			$oldValue = (new \App\Db\Query())->select([$field->getColumnName()])->from($field->getTableName())->where([$field->get('primaryColumn') => $row['id']])->scalar();
			$value = \App\Json::decode($oldValue);
			if (!is_array($value)) {
				$value = [];
			}
			$value[] = $image;
			if ($dbCommand->update($field->getTableName(), [$field->getColumnName() => \App\Json::encode($value)], [$field->get('primaryColumn') => $row['id']])->execute()) {
				if ($isMulti) {
					$dbCommand->delete('u_#__attachments', ['and', ['attachmentid' => $id], ['fieldid' => $field->getId()]])->execute();
				} else {
					$dbCommand->delete('vtiger_crmentity', ['and', ['crmid' => $row['attachmentsid']], ['not in', 'setype', $this->modules]])->execute();
					$dbCommand->delete('vtiger_attachments', ['attachmentsid' => $row['attachmentsid']])->execute();
					if ($field->getModuleName() === 'Users') {
						$dbCommand->delete('vtiger_salesmanattachmentsrel', ['attachmentsid' => $row['attachmentsid']])->execute();
						\App\UserPrivilegesFile::createUserPrivilegesfile($row['id']);
					}
				}
			}
		} else {
			\App\Log::error("MIGRATE FILES ID:{$row['id']}|{$row['attachmentsid']} - " . $file->validateError);
		}
	}

	private function updateRecords()
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		$dbCommand->update('vtiger_ossmails_logs', ['status' => '2'], ['status' => '1'])->execute();

		$subQuery = (new \App\Db\Query())->select(['vtiger_crmentity.setype'])
			->from('vtiger_crmentity')->where(['vtiger_crmentity.crmid' => new \yii\db\Expression('vtiger_crmentityrel.crmid')]);
		$dbCommand->update('vtiger_crmentityrel', ['module' => $subQuery])->execute();

		$subQuery = (new \App\Db\Query())->select(['vtiger_crmentity.setype'])
			->from('vtiger_crmentity')->where(['vtiger_crmentity.crmid' => new \yii\db\Expression('vtiger_crmentityrel.relcrmid')]);
		$dbCommand->update('vtiger_crmentityrel', ['relmodule' => $subQuery])->execute();

		$subQuery = (new \App\Db\Query())->select(['emailtemplatesid'])->from('u_#__emailtemplates')
			->where(['sys_name' => 'ActivityReminderNotificationEvents']);
		$dbCommand->delete('vtiger_crmentity', ['crmid' => $subQuery ])->execute();
	}

	private function imageFix()
	{
		$db = \App\Db::getInstance();
		$modules = array_column(\vtlib\Functions::getAllModules(), 'name');
		$fields = (new \App\Db\Query())->select(['tabid', 'uitype'])->from('vtiger_field')->where(['uitype' => $this->uitypes])->orderBy(['tabid' => SORT_ASC])->createCommand()->queryAllByGroup(2);
		foreach ($fields as $tabId => $uitypes) {
			try {
				if ($tabId) {
					$this->moduleName = \App\Module::getModuleName($tabId);
					$this->modules = $modules;
					$this->migrateImage();
					$this->migrateMultiImage();
				}
			} catch (\Throwable $ex) {
				\App\Log::error('MIGRATE FILES:' . $ex->getMessage());
			}
		}
	}

	private function addRelations()
	{
		$moduleModel = Vtiger_Module_Model::getInstance('Competition');
		if (!(new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $moduleModel->getId(), 'related_tabid' => $moduleModel->getId(), 'name' => 'getDependentsList'])->exists()) {
			$moduleModel->setRelatedList($moduleModel, '', 'ADD', 'getDependentsList');
		}
	}

	private function addFilter()
	{
		$moduleName = 'CInternalTickets';
		$customViewModel = CustomView_Record_Model::getCleanInstance();
		$customViewModel->setModule($moduleName);
		$customViewData = [
			'cvid' => null,
			'viewname' => 'Open',
			'setdefault' => 0,
			'setmetrics' => 0,
			'status' => 1,
			'featured' => 0,
			'color' => '',
			'description' => ''
		];
		$fields = ['cinternaltickets_no', 'subject', 'internal_tickets_status', 'assigned_user_id', 'shownerid', 'modifiedtime', 'createdtime'];
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$selectedColumnsList = [];
		foreach ($fields as $fieldName) {
			$fieldModel = $moduleModel->getField($fieldName);
			$selectedColumnsList [] = $fieldModel->getCustomViewColumnName();
		}
		$customViewData['columnslist'] = $selectedColumnsList;
		$statusFieldModel = $moduleModel->getField('internal_tickets_status');
		$advFilterList = [
			'1' => [
				'columns' => [
					[
						'columnname' => $statusFieldModel->getCustomViewColumnName(),
						'comparator' => 'n',
						'value' => 'PLL_CANCELLED,PLL_ACCEPTED',
						'column_condition' => ''
					]
				],
				'condition' => 'and'
			]
		];
		if (!empty($advFilterList)) {
			$customViewData['advfilterlist'] = $advFilterList;
		}
		$customViewModel->setData($customViewData);
		if (!$customViewModel->checkDuplicate()) {
			$customViewModel->save();
		}
		// ------------------------------------
		$cvId = (new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->where(['viewname' => 'All', 'entitytype' => $moduleName])->scalar();
		if ($cvId) {
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
			$customViewModel->setModule($moduleName);
			$customViewModel->set('columnslist', $selectedColumnsList);
			$customViewModel->save();
			App\Db::getInstance()->createCommand()->delete('vtiger_user_module_preferences',
				['userid' => 'Users:' . \App\User::getCurrentUserId(), 'tabid' => App\Module::getModuleId($moduleName)])
				->execute();
		}
	}

	private function updateHeaderField()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$query = (new \App\Db\Query())->select(['header_field', 'fieldid', 'fieldname', 'tabid'])->from('vtiger_field')->where([
			'and', [
				'NOT', ['header_field' => null]
			], ['<>', 'header_field', ''], ['<>', 'header_field', '0']
		]);
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (strpos($row['header_field'], '{') !== 0) {
				$class = $row['header_field'];
				if ($row['fieldname'] === 'mulcomp_status' && $row['tabid'] === \App\Module::getModuleId('MultiCompany')) {
					$class = 'badge-info';
				}
				$dbCommand->update('vtiger_field', ['header_field' => \App\Json::encode(['type' => 'value', 'class' => $class])], ['fieldid' => $row['fieldid']])->execute();
			}
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function addPicklistValues()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$fields = [
			'finvoiceproforma_formpayment' => ['FInvoiceProforma', 'PLL_WIRE_TRANSFER'],
			'fcorectinginvoice_formpayment' => ['FCorectingInvoice', 'PLL_WIRE_TRANSFER']
		];
		foreach ($fields as $fieldName => $info) {
			$moduleModel = Settings_Picklist_Module_Model::getInstance($info[0]);
			$fieldModel = Settings_Picklist_Field_Model::getInstance($fieldName, $moduleModel);
			$picklistValues = $fieldModel->getPicklistValues(true);
			if (isset($picklistValues[$info[1]])) {
				continue;
			}
			$roleRecordList = Settings_Roles_Record_Model::getAll();
			$rolesSelected = [];
			foreach ($roleRecordList as $roleRecord) {
				$id = $roleRecord->getId();
				if ($id !== 'H1') {
					$rolesSelected[] = $id;
				}
			}
			$moduleModel->addPickListValues($fieldModel, $info[1], $rolesSelected, '');
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Add fields.
	 */
	public function addFields()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$fields = [
			[93, 2769, 'parent_id', 'u_yf_competition', 2, 10, 'parent_id', 'LBL_PARENT_ID', 1, 2, '', '4294967295', 8, 303, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 'integer', 'LBL_COMPETITION_INFORMATION', [], ['Competition'], 'Competition']
		];
		foreach ($fields as $field) {
			$moduleId = App\Module::getModuleId($field[28]);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists) {
				continue;
			}

			$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => $field[25], 'tabid' => $moduleId])->scalar();
			$cacheName = $field[25] . '|';
			$cacheName1 = $cacheName . $moduleId;
			$cacheName2 = $blockId . '|';
			\App\Cache::delete('BlockInstance', $cacheName);
			\App\Cache::delete('BlockInstance', $cacheName1);
			\App\Cache::delete('BlockInstance', $cacheName2);
			if (!$blockId) {
				$module = \Vtiger_Module_Model::getInstance($field[28]);
				$blockInstance = new \vtlib\Block();
				$blockInstance->label = $field[25];
				$module->addBlock($blockInstance);
				$blockId = $blockInstance->id;
			}

			if ((!$blockId || !($blockInstance = \vtlib\Block::getInstance($blockId))) && !($blockInstance = reset(Vtiger_Module_Model::getInstance($field[28])->getBlocks()))) {
				\App\Log::error("No block found ({$field[25]}) to create a field, you will need to create a field manually. Module: {$field[28]}, field name: {$field[6]}, field label: {$field[7]}");
				$this->log("[ERROR] No block found to create a field, you will need to create a field manually. Module: {$field[28]}, field name: {$field[6]}, field label: {$field[7]}");
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
			if ($field[26] && ($field[5] == 15 || $field[5] == 16 || $field[5] == 33)) {
				$fieldInstance->setPicklistValues($field[26]);
			}
			if ($field[27] && $field[5] == 10) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function removeEventsModule()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$eventTabId = App\Module::getModuleId('Events');
		$calendarTabId = App\Module::getModuleId('Calendar');
		$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => 'LBL_RECURRENCE_INFORMATION', 'tabid' => $eventTabId])->scalar();
		$blockIdReminder = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => 'LBL_REMINDER_INFORMATION'])->scalar();
		$dbCommand->update('vtiger_blocks', ['tabid' => $calendarTabId], ['blockid' => [$blockId, $blockIdReminder]])->execute();
		$field = [
			'activitytype' => ['displaytype' => 1, 'quickcreate' => 0, 'quickcreatesequence' => 5, 'typeofdata' => 'V~M'],
			'reapeat' => ['displaytype' => 1, 'block' => $blockId, 'presence' => 0],
			'recurrence' => ['displaytype' => 1, 'block' => $blockId, 'presence' => 0],
			'reminder_time' => ['displaytype' => 1, 'block' => $blockIdReminder],
			'visibility' => ['displaytype' => 1, 'presence' => 0, 'defaultvalue' => 'Private'],
			'linkextend' => ['presence' => 0],
			'followup' => ['presence' => 0],
			'state' => ['presence' => 0],
			'allday' => ['presence' => 0],
			'was_read' => ['presence' => 0],
			'closedtime' => ['presence' => 0],
			'shownerid' => ['presence' => 0],
			'smcreatorid' => ['presence' => 0],
			'created_user_id' => ['presence' => 0],
		];
		foreach ($field as $name => $set) {
			$dbCommand->update('vtiger_field', $set, ['fieldname' => $name, 'tabid' => 9])->execute();
		}
		$activityFieldInfo = (new \App\Db\Query())->select(['quickcreate', 'quickcreatesequence'])
			->from('vtiger_field')
			->where(['fieldname' => 'activitytype', 'tabid' => $calendarTabId])->one();
		if ($activityFieldInfo && (int)$activityFieldInfo['quickcreate'] !== 0) {
			$dbCommand->update('vtiger_field', ['displaytype' => 1, 'quickcreate' => 0, 'quickcreatesequence' => (new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $calendarTabId])->max('quickcreatesequence') + 1, 'typeofdata' => 'V~M'], ['fieldname' => 'activitytype', 'tabid' => $calendarTabId])
				->execute();
		}
		$values = ['Task'];
		$moduleModel = Settings_Picklist_Module_Model::getInstance('Calendar');
		$fieldModel = Settings_Picklist_Field_Model::getInstance('activitytype', $moduleModel);
		$roleRecordList = Settings_Roles_Record_Model::getAll();
		$rolesSelected = array_keys($roleRecordList);
		$tableName = 'vtiger_' . $fieldModel->getName();
		$db = \App\Db::getInstance();
		if ($db->isTableExists($tableName)) {
			$id = $db->getUniqueId($tableName, $fieldModel->getName() . 'id', false);
			$db->createCommand()->update("{$tableName}_seq", ['id' => --$id])->execute();
		}
		$picklistValues = \App\Fields\Picklist::getValuesName('activitytype');
		$values = array_diff($values, $picklistValues);
		foreach ($values as $newValue) {
			$moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
		}
		$dbCommand->update($tableName, ['presence' => 0], ['activitytype' => 'Task'])->execute();
		$this->removeWorkflowTask();
		// remove
		$moduleName = 'Events';
		$moduleInstance = \Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleInstance) {
			\vtlib\Access::deleteTools($moduleInstance);
			\vtlib\Filter::deleteForModule($moduleInstance);
			\vtlib\Block::deleteForModule($moduleInstance);
			$dbCommand->update('vtiger_crmentity', ['setype' => 'Calendar'], ['setype' => 'Events'])->execute();
			$dataReader = (new \App\Db\Query())->select(['columnname', 'tablename'])->from('vtiger_field')->where(['uitype' => 301])->createCommand()->query();
			while($row = $dataReader->read()) {
				$dbCommand->update($row['tablename'], [$row['columnname'] => 'Calendar'], [$row['columnname'] => 'Events'])->execute();
			}
			$moduleInstance->deleteIcons();
			$moduleInstance->unsetAllRelatedList();
			\vtlib\Language::deleteForModule($moduleInstance);
			\vtlib\Access::deleteSharing($moduleInstance);
			$moduleInstance->deleteFromModentityNum();
			$moduleInstance->deleteGroup2Modules();
			\vtlib\Cron::deleteForModule($moduleInstance);
			\vtlib\Profile::deleteForModule($moduleInstance);
			\Settings_Workflows_Module_Model::deleteForModule($moduleInstance);
			\vtlib\Menu::deleteForModule($moduleInstance);
			// Aktualizacja grup
			\vtlib\Profile::deleteForModule($moduleInstance);
			\App\Fields\Tree::deleteForModule($moduleInstance->id);
			\vtlib\Link::deleteAll($moduleInstance->id);
			\Settings_Vtiger_Module_Model::deleteSettingsFieldBymodule($moduleInstance->name);
			$moduleInstance->__delete();
			$moduleInstance->deleteDir($moduleInstance);
			\vtlib\Module::syncfile();
			\App\Cache::clear();
			$dbCommand->delete('vtiger_links', ['like', 'linkurl', "module={$moduleName}&"])->execute();
			$dbCommand->delete('vtiger_profile2utility', ['tabid' => $eventTabId])->execute();
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function removeWorkflowTask()
	{
		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		$tasks = [
			['moduleName' => 'Events', 'summary' => 'Notify Contact On Ticket Change', 'changes' => ['workflow_id' => 14, 'workflowId' => 14]]
		];
		foreach ($tasks as $taskData) {
			if (empty($taskData)) {
				continue;
			}
			$task = (new \App\Db\Query())->select(['com_vtiger_workflowtasks.task'])->from('com_vtiger_workflowtasks')->innerJoin('com_vtiger_workflows', 'com_vtiger_workflowtasks.workflow_id = com_vtiger_workflows.workflow_id')->where(['com_vtiger_workflowtasks.summary' => $taskData['summary'], 'com_vtiger_workflows.module_name' => $taskData['moduleName']])->scalar();
			if ($task) {
				$tm = new VTTaskManager();
				$task = $tm->unserializeTask($task);
				$save = false;
				foreach ($taskData['changes'] as $name => $value) {
					if (!isset($task->$name) || $task->$name != $value) {
						$task->$name = $value;
						$save = true;
					}
				}
				if ($save) {
					$tm->saveTask($task);
				}
			}
		}
	}

	public function updateData()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = App\Db::getInstance();

		$data = [
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('DataSetRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('ActivityRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('LocationRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('IncidentRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('AuditRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_blocks', ['tabid' => \App\Module::getModuleId('Calendar'), 'blocklabel' => 'LBL_CUSTOM_INFORMATION']],
		];
		\App\Db\Updater::batchDelete($data);
		$data = [
			['vtiger_cron_task', ['handler_file' => 'modules/Calendar/cron/RecurringEvents.php', 'module' => 'Calendar'], ['handler_file' => 'modules/Events/cron/RecurringEvents.php']],
			['vtiger_field', ['defaultvalue' => 'twilight'], ['fieldname' => 'theme', 'tabid' => \App\Module::getModuleId('Users')]],
			['vtiger_field', ['presence' => 0, 'displaytype' => 10], ['fieldname' => 'sum_time', 'tabid' => \App\Module::getModuleId('OSSTimeControl')]],
			['vtiger_field', ['maximumlength' => null], ['fieldname' => 'from_id', 'tabid' => \App\Module::getModuleId('OSSMailView')]],
			['vtiger_field', ['maximumlength' => null], ['fieldname' => 'to_id', 'tabid' => \App\Module::getModuleId('OSSMailView')]],
			['vtiger_field', ['uitype' => 312], ['fieldname' => 'authy_secret_totp', 'tabid' => \App\Module::getModuleId('Users')]],
			['vtiger_field', ['fieldlabel' => 'Due Date & Time'], ['fieldname' => 'due_date', 'tabid' => \App\Module::getModuleId('Calendar')]],
		];
		\App\Db\Updater::batchUpdate($data);
		$tables = ['u_yf_social_media_config'];
		foreach ($tables as $table) {
			if ($db->isTableExists($table)) {
				\App\Db\Updater::batchInsert([
					[$table, ['name' => 'archiving_records_number_of_days', 'value' => '365', 'type' => 'twitter'], ['name' => 'archiving_records_number_of_days']],
					[$table, ['name' => 'twitter_api_key', 'value' => null, 'type' => 'twitter'], ['name' => 'twitter_api_key']],
					[$table, ['name' => 'twitter_api_secret', 'value' => null, 'type' => 'twitter'], ['name' => 'twitter_api_secret']],
					[$table, ['name' => 'oauth_token', 'value' => null, 'type' => 'twitter'], ['name' => 'oauth_token']],
					[$table, ['name' => 'oauth_token_secret', 'value' => null, 'type' => 'twitter'], ['name' => 'oauth_token_secret']],
				]);
			}
		}
		$data = [
			['vtiger_eventhandlers', [
				'event_name' => 'EntityBeforeSave',
				'handler_class' => 'OSSTimeControl_TimeControl_Handler',
				'is_active' => 1,
				'include_modules' => 'OSSTimeControl',
				'exclude_modules' => '',
				'priority' => 5,
				'owner_id' => 51,
			], [
				'event_name' => 'EntityBeforeSave',
				'handler_class' => 'OSSTimeControl_TimeControl_Handler'
			]
			],
			['vtiger_eventhandlers', [
				'event_name' => 'EntityBeforeSave',
				'handler_class' => 'Vtiger_SocialMedia_Handler',
				'is_active' => 1,
				'include_modules' => '',
				'exclude_modules' => '',
				'priority' => 6,
				'owner_id' => 0,
			], [
				'event_name' => 'EntityBeforeSave',
				'handler_class' => 'Vtiger_SocialMedia_Handler'
			]
			],
			['vtiger_eventhandlers', [
				'event_name' => 'EntityBeforeDelete',
				'handler_class' => 'Vtiger_SocialMedia_Handler',
				'is_active' => 1,
				'include_modules' => '',
				'exclude_modules' => '',
				'priority' => 6,
				'owner_id' => 0,
			], [
				'event_name' => 'EntityBeforeDelete',
				'handler_class' => 'Vtiger_SocialMedia_Handler'
			]
			],
			['vtiger_links', [
				'tabid' => \App\Module::getModuleId('Home'),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'Multifilter',
				'linkurl' => 'index.php?module=Home&view=ShowWidget&name=Multifilter',
				'linkicon' => '',
				'sequence' => 25,
			], [
				'tabid' => \App\Module::getModuleId('Home'),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'Multifilter'
			]
			],
			['vtiger_settings_field', [
				'blockid' => \Settings_Vtiger_Menu_Model::getInstance('LBL_INTEGRATION')->get('blockid'),
				'name' => 'LBL_SOCIAL_MEDIA',
				'iconpath' => 'fab fa-twitter-square',
				'description' => 'LBL_SOCIAL_MEDIA_DESC',
				'linkto' => 'index.php?module=SocialMedia&parent=Settings&view=Index',
				'sequence' => null,
				'active' => 0,
				'pinned' => 0,
				'admin_access' => null,
			], ['name' => 'LBL_SOCIAL_MEDIA']
			],
			['vtiger_settings_field', [
				'blockid' => \Settings_Vtiger_Menu_Model::getInstance('LBL_About_YetiForce')->get('blockid'),
				'name' => 'LBL_YETIFORCE_STATUS_HEADER',
				'iconpath' => 'fas fa-thermometer-half',
				'description' => 'LBL_YETIFORCE_STATUS_DESC',
				'linkto' => 'index.php?module=YetiForce&parent=Settings&view=Status',
				'sequence' => null,
				'active' => 0,
				'pinned' => 0,
				'admin_access' => null,
			], ['name' => 'LBL_YETIFORCE_STATUS_HEADER']
			],
			['vtiger_settings_field', [
				'blockid' => \Settings_Vtiger_Menu_Model::getInstance('LBL_SECURITY_MANAGEMENT')->get('blockid'),
				'name' => 'LBL_LOGS',
				'iconpath' => 'fa fa-exclamation-triangle',
				'description' => 'LBL_LOGS_DESC',
				'linkto' => 'index.php?module=Log&parent=Settings&view=Index',
				'sequence' => 6,
				'active' => 0,
				'pinned' => 0,
				'admin_access' => null,
			], ['name' => 'LBL_LOGS']
			],
		];
		\App\Db\Updater::batchInsert($data);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Changes in configuration files.
	 *
	 * @return array
	 */
	private function getConfigurations()
	{
		return [
			['name' => 'config/config.inc.php', 'conditions' => [
				['type' => 'update', 'search' => '$default_theme = \'softed\';', 'replace' => ['$default_theme = \'softed\';', '$default_theme = \'twilight\';']],
			],
			],
			['name' => 'config/modules/Calendar.php', 'conditions' => [
				['type' => 'update', 'search' => 'Event/To Do', 'replace' => ['Event/To Do', 'Calendar']],
				['type' => 'add', 'search' => '];', 'checkInContents' => '\'CALENDAR_VIEW\'', 'addingType' => 'before', 'value' => "	//Calendar view - allowed values: Extended, Standard
	'CALENDAR_VIEW' => 'Standard',
"]
			]
			],
			['name' => 'config/modules/Users.php', 'conditions' => [
				['type' => 'add', 'search' => "];", 'checkInContents' => 'SHOW_ROLE_NAME', 'addingType' => 'before', 'value' => "	// Show role name
	'SHOW_ROLE_NAME' => true,
"],
			]
			],
			['name' => 'config/performance.php', 'conditions' => [
				['type' => 'add', 'search' => "];", 'checkInContents' => 'INVENTORY_EDIT_VIEW_LAYOUT', 'addingType' => 'before', 'value' => "	//Is divided layout style on edit view in modules with products
	'INVENTORY_EDIT_VIEW_LAYOUT' => true,
"],
			],
			],
			['name' => 'config/modules/OSSMail.php', 'conditions' => [
				['type' => 'add', 'search' => '$config[\'site_URL\']', 'checkInContents' => 'if (isset($site_URL)) {', 'addingType' => 'before', 'value' => 'if (isset($site_URL)) {
'],
				['type' => 'add', 'search' => '$config[\'imap_open_add_connection_type\']', 'checkInContents' => '}
$config[\'imap_open_add_connection_type\']', 'addingType' => 'before', 'value' => '}
'],
				['type' => 'add', 'search' => '$config[\'site_URL\']', 'checkInContents' => 'if (isset($site_URL)) {', 'addingType' => 'before', 'value' => 'if (isset($site_URL)) {
'],
				['type' => 'add', 'search' => '$config[\'db_dsnw\']', 'checkInContents' => 'if (isset($dbconfig)) {', 'addingType' => 'before', 'value' => 'if (isset($dbconfig)) {
	'],
				['type' => 'add', 'search' => '$config[\'db_prefix\']', 'checkInContents' => 'defined(\'RCUBE_INSTALL_PATH\')', 'addingType' => 'before', 'value' => '}
if (!defined(\'RCUBE_INSTALL_PATH\')) {
	define(\'RCUBE_INSTALL_PATH\', realpath(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . \'public_html\' . DIRECTORY_SEPARATOR . \'modules\' . DIRECTORY_SEPARATOR . \'OSSMail\' . DIRECTORY_SEPARATOR . \'roundcube\'));
}
']
			],
			],
		];
	}
	/**
	 * Configuration files.
	 */
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
				$configContent = $configContentClone = file($fileName);
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
									if (isset($condition['checkInContents']) && strpos($baseContent, $condition['checkInContents']) === false) {
										$configContent[$key] = $condition['addingType'] === 'before' ? $condition['value'] . $configContent[$key] : $configContent[$key] . $condition['value'];
									}
									unset($addContent[$index]);
									break;
								case 'remove':
									if (!empty($condition['before'])) {
										if (strpos($configContentClone[$key - 1], $condition['before']) !== false) {
											unset($configContent[$key]);
											$emptyLine = true;
										}
									} else {
										unset($configContent[$key]);
										$emptyLine = true;
									}
									break;
								case 'removeTo':
									unset($configContent[$key]);
									$while = 0;
									while ($while !== false) {
										$while++;
										unset($configContent[$key + $while]);
										if (strpos($configContent[$key + $while], $condition['end']) === false) {
											$while = false;
										}
									}
									$emptyLine = true;
									break;
								case 'update':
									if (isset($condition['checkInLine']) && (strpos($condition['checkInLine'], $configContent[$key]) !== false)) {
										break;
									}
									if (isset($condition['replace'])) {
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
				$content = implode('', $configContent);
				if ($addContent) {
					$addContentString = implode('', $addContent);
					$content .= $addContentString;
				}
				file_put_contents($fileName, $content);
			}
		}
	}
	/**
	 * Cron data.
	 */
	private function updateCron()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		\App\Db\Updater::cron([
			['type' => 'add', 'data' => ['LBL_ARCHIVE_OLD_RECORDS', 'cron/Social.php', 86400, null, null, 0, 'Vtiger', 32, null]],
			['type' => 'add', 'data' => ['LBL_GET_SOCIAL_MEDIA_MESSAGES', 'cron/SocialGet.php', 1800, null, null, 0, 'Vtiger', 33, null]],
		]);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Postupdate
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return true;
	}
}
