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

	/**
	 * Constructor.
	 *
	 * @param object $modulenode
	 */
	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
		$this->filesToDelete = require_once 'deleteFiles.php';
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
	 * Preupdate.
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

	private function migrateCvColumnList()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = App\Db::getInstance();
		$tableSchema = $db->getTableSchema('vtiger_cvcolumnlist', true);
		if (!$tableSchema->getColumn('columnname')) {
			$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
			return;
		}
		if (!$tableSchema->getColumn('field_name')) {
			$db->createCommand()->addColumn('vtiger_cvcolumnlist', 'field_name', 'string(50)')->execute();
		}
		if (!$tableSchema->getColumn('module_name')) {
			$db->createCommand()->addColumn('vtiger_cvcolumnlist', 'module_name', 'string(25)')->execute();
		}
		if (!$tableSchema->getColumn('source_field_name')) {
			$db->createCommand()->addColumn('vtiger_cvcolumnlist', 'source_field_name', 'string(50)')->execute();
		}
		$dataReader = (new \App\Db\Query())->from('vtiger_cvcolumnlist')
			->innerJoin('vtiger_customview', 'vtiger_customview.cvid = vtiger_cvcolumnlist.cvid')->createCommand()->query();
		while ($row = $dataReader->read()) {
			[$tableName, $columnName, $fieldName, $moduleFieldLabel, $fieldTypeOfData] = explode(':', $row['columnname']);
			if ((new \App\Db\Query())->from('vtiger_field')->where(['fieldname' => $fieldName, 'tabid' => App\Module::getModuleId($row['entitytype'])])->exists()) {
				$db->createCommand()->update('vtiger_cvcolumnlist', ['field_name' => $fieldName, 'module_name' => $row['entitytype']], ['cvid' => $row['cvid'], 'columnindex' => $row['columnindex']])
					->execute();
			} else {
				$db->createCommand()->delete('vtiger_cvcolumnlist', ['cvid' => $row['cvid'], 'columnindex' => $row['columnindex']])
					->execute();
			}
		}
		if ($tableSchema->getColumn('columnname')) {
			$db->createCommand()->dropColumn('vtiger_cvcolumnlist', 'columnname')->execute();
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
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

	private function migrateCalendarDefaultTime()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$tableName = 'vtiger_users';
		$columnName = 'othereventduration';

		$tableSchema = $db->getSchema()->getTableSchema($tableName, true);
		$columnSchema = $tableSchema->getColumn($columnName);
		if ($columnSchema->dbType !== 'text') {
			$column = \yii\db\Schema::TYPE_TEXT;
			$db->createCommand()->alterColumn($tableName, $columnName, $column)->execute();
			$picklist = \App\Fields\Picklist::getValuesName('activitytype');
			if (!in_array('Task', $picklist)) {
				$picklist[] = 'Task';
			}
			$db->createCommand()->update('vtiger_field', ['uitype' => 315, 'defaultvalue' => ''], ['tabid' => \App\Module::getModuleId('Users'), 'fieldname' => 'othereventduration'])->execute();
			$usersData = (new \App\Db\Query())->select(['id', 'callduration', 'othereventduration'])->from('vtiger_users')->where(['or',
				['and', ['not', ['callduration' => null]], ['<>', 'callduration', '']],
				['and', ['not', ['othereventduration' => null]], ['<>', 'othereventduration', '']]
			])->createCommand()->queryAllByGroup(1);
			foreach ($usersData as $userId => $data) {
				$value = [];
				if (!empty($data['callduration'])) {
					$value[] = ['activitytype' => 'Call', 'duration' => $data['callduration']];
				}
				if (!empty($data['othereventduration'])) {
					foreach ($picklist as $label) {
						if ($label === 'Call') {
							continue;
						}
						$value[] = ['activitytype' => $label, 'duration' => $data['othereventduration']];
					}
				}
				$value = $value ? \App\Json::encode($value) : '';
				$db->createCommand()->update($tableName, [$columnName => $value], ['id' => $userId])->execute();
			}
		}
		// =========================
		$fieldname = 'othereventduration';
		$query = (new \App\Db\Query())->from('vtiger_field')
			->where(['fieldname' => $fieldname])
			->andWhere(['in', 'uitype', [15, 16, 33]]);
		$dataReader = $query->createCommand()->query();
		if (!$dataReader->count()) {
			$db->createCommand()->delete('vtiger_picklist', ['name' => $fieldname])->execute();
		}
		$db->createCommand()->delete('vtiger_picklist_dependency', ['and', ['tabid' => App\Module::getModuleId('Users')], ['or', ['sourcefield' => $fieldname], ['targetfield' => $fieldname]]])->execute();
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$this->addFilter();
		$this->migrateCalendarDefaultTime();
		$db->createCommand()->checkIntegrity(false)->execute();
		try {
			$this->importer = new \App\Db\Importer();
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->updateScheme();
			$this->removeForeignKey();
			$this->importer->refreshSchema();
			$this->importer->postUpdate();
			$this->importer->dropTable([
				'com_vtiger_workflowtasks_seq',
				'vtiger_othereventduration',
				'vtiger_othereventduration_seq'
			]);
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
		$this->removeFields();
		$this->migrateCvColumnList();
		$this->updateHeaderField();
		$this->addRelations();
		$this->updateRecords();
		$this->imageFix();
		$this->attachmentsFix();
		$this->updateConfigurationFiles();
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function removeFields()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$fields = ['Users' => ['callduration']];
		foreach ($fields as $moduleName => $columns) {
			$ids = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['columnname' => $columns, 'tabid' => App\Module::getModuleId($moduleName)])->column();
			foreach ($ids as $id) {
				try {
					$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($id);
					$fieldInstance->delete();
				} catch (Exception $e) {
					\App\Log::error('RemoveFields' . __METHOD__ . ': code ' . $e->getCode() . ' message ' . $e->getMessage());
				}
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function attachmentsFix()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$query = (new \App\Db\Query())->select(['attachmentsid', 'path'])->from('vtiger_attachments')->where(['not like', 'path', 'storage%', false]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$path = $row['path'];
			if (substr(str_replace('\\', '/', $path), 0, 7) !== 'storage') {
				$path .= 'X';
				$db->createCommand()->update('vtiger_attachments', ['path' => substr($path, strpos($path, 'storage'), -1)], ['attachmentsid' => $row['attachmentsid']])->execute();
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateRecords()
	{
		$dbCommand = App\Db::getInstance()->createCommand();

		$subQuery = (new \App\Db\Query())->select(['vtiger_crmentity.setype'])
			->from('vtiger_crmentity')->where(['vtiger_crmentity.crmid' => new \yii\db\Expression('vtiger_crmentityrel.crmid')]);
		$dbCommand->update('vtiger_crmentityrel', ['module' => $subQuery])->execute();

		$subQuery = (new \App\Db\Query())->select(['vtiger_crmentity.setype'])
			->from('vtiger_crmentity')->where(['vtiger_crmentity.crmid' => new \yii\db\Expression('vtiger_crmentityrel.relcrmid')]);
		$dbCommand->update('vtiger_crmentityrel', ['relmodule' => $subQuery])->execute();

		$subQuery = (new \App\Db\Query())->select(['emailtemplatesid'])->from('u_#__emailtemplates')
			->where(['sys_name' => 'ActivityReminderNotificationEvents']);
		$dbCommand->delete('vtiger_crmentity', ['crmid' => $subQuery])->execute();
		$dataReader = (new \App\Db\Query())->select(['id', 'data'])->from('vtiger_widgets')->where(['and', ['type' => 'EmailList'], ['not like', 'data', 'OSSMailView']])->createCommand()->query();
		$dbCommand = \App\Db::getInstance()->createCommand();
		while ($row = $dataReader->read()) {
			$data = \App\Json::decode($row['data']);
			$data['relatedmodule'] = 'OSSMailView';
			$data = \App\Json::encode($data);
			$dbCommand->update('vtiger_widgets', ['data' => $data], ['id' => $row['id']])->execute();
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
			if (method_exists($fieldModel, 'getCustomViewSelectColumnName')) {
				$selectedColumnsList[] = $fieldModel->getCustomViewSelectColumnName();
			} else {
				$selectedColumnsList[] = $fieldModel->getCustomViewColumnName();
			}
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
			\App\Db::getInstance()->createCommand()->delete(
				'vtiger_user_module_preferences',
				['userid' => 'Users:' . \App\User::getCurrentUserId(), 'tabid' => App\Module::getModuleId($moduleName)]
			)
				->execute();
		}
	}

	private function updateHeaderField()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$query = (new \App\Db\Query())->select(['header_field', 'fieldid', 'fieldname', 'tabid'])->from('vtiger_field')->where([
			'and', ['NOT', ['header_field' => null]], ['<>', 'header_field', ''], ['<>', 'header_field', '0']
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
			[93, 2769, 'parent_id', 'u_yf_competition', 2, 10, 'parent_id', 'LBL_PARENT_ID', 1, 2, '', '4294967295', 8, 303, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 'integer', 'LBL_COMPETITION_INFORMATION', [], ['Competition'], 'Competition'],
			[40, 2770, 'parents', 'vtiger_modcomments', 1, 1, 'parents', 'FL_PARENTS', 1, 2, '', null, 9, 98, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 'text', 'LBL_COMPETITION_INFORMATION', [], [], 'ModComments']
		];
		foreach ($fields as $field) {
			$moduleId = App\Module::getModuleId($field[28]);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists) {
				$this->log("[INFO] Skip adding field. Module: {$moduleId}-{$field[28]}; field name: {$field[2]}, field exists: {$isExists}");
				continue;
			}
			$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => $field[25], 'tabid' => $moduleId])->scalar();
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

	/**
	 * Remove Events module.
	 *
	 * @throws \yii\db\Exception
	 */
	public function removeEventsModule()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$eventTabId = \App\Module::getModuleId('Events');
		if (!$eventTabId) {
			$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
			return;
		}
		$dbCommand = \App\Db::getInstance()->createCommand();
		$calendarTabId = \App\Module::getModuleId('Calendar');
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
			while ($row = $dataReader->read()) {
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

	/**
	 * Remove workflow tasks.
	 *
	 * @todo remove all tasks by Events module
	 */
	private function removeWorkflowTask()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		$workflowId = (new \App\Db\Query())->select(['workflow_id'])->from('com_vtiger_workflows')->where(['summary' => 'Workflow for Calendar Todos when Send Notification is True', 'module_name' => 'Calendar'])->scalar();
		$tasks = [
			['moduleName' => 'Events', 'summary' => 'Notify Contact On Ticket Change', 'changes' => ['workflow_id' => $workflowId, 'workflowId' => $workflowId]]
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
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function updateData()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = App\Db::getInstance();
		$taskColor = (new \App\Db\Query())->select(['value'])->from('vtiger_calendar_config')->where(['type' => 'colors', 'name' => 'Task'])->scalar();
		if ($taskColor) {
			$db->createCommand()->update('vtiger_activitytype', ['presence' => 0, 'color' => $taskColor], ['activitytype' => 'Task'])->execute();
		}

		$data = [
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('DataSetRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('ActivityRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('LocationRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('IncidentRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_relatedlists', ['tabid' => \App\Module::getModuleId('AuditRegister'), 'name' => 'getActivities', 'label' => 'Activities']],
			['vtiger_blocks', ['tabid' => \App\Module::getModuleId('Calendar'), 'blocklabel' => 'LBL_CUSTOM_INFORMATION']],
			['vtiger_calendar_config', ['type' => 'colors', 'name' => 'Task']],
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
			['vtiger_field', ['typeofdata' => 'V~O'], ['fieldname' => 'salesprocessid', 'tabid' => \App\Module::getModuleId('SQuoteEnquiries')]],
			['vtiger_field', ['uitype' => '315', 'maximumlength' => ''], ['fieldname' => 'othereventduration', 'tabid' => \App\Module::getModuleId('Users')]],
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
			['u_yf_chat_rooms', [
				'room_id' => 0,
				'name' => 'LBL_GENERAL',
			], ['name' => 'LBL_GENERAL']
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
				['type' => 'update', 'search' => 'SHOW_TIMELINE_WEEK', 'replace' => ['false', 'true']],
				['type' => 'update', 'search' => 'SHOW_TIMELINE_DAY', 'replace' => ['false', 'true']],
				['type' => 'update', 'search' => 'Event/To Do', 'replace' => ['Event/To Do', 'Calendar']],
				['type' => 'add', 'search' => '];', 'checkInContents' => '\'CALENDAR_VIEW\'', 'addingType' => 'before', 'value' => "	//Calendar view - allowed values: Extended, 'Standard'
	'CALENDAR_VIEW' => 'Extended',
"],
				['type' => 'add', 'search' => '];', 'checkInContents' => '\'SHOW_EDIT_FORM\'', 'addingType' => 'before', 'value' => "	//Show default edit form
	'SHOW_EDIT_FORM' => false,
"],
				['type' => 'add', 'search' => '];', 'checkInContents' => '\'AUTOFILL_TIME\'', 'addingType' => 'before', 'value' => "	//Select event free time automatically
	'AUTOFILL_TIME' => false
"],
				['type' => 'add', 'search' => 'return [', 'checkInContents' => '\'WEEK_COUNT\'', 'addingType' => 'after', 'value' => "	// Shows number of the week in the year view
	// true - show, false - hide
	'WEEK_COUNT' => true, //Boolean"]
			]
			],
			['name' => 'config/modules/Users.php', 'conditions' => [
				['type' => 'add', 'search' => '];', 'checkInContents' => 'SHOW_ROLE_NAME', 'addingType' => 'before', 'value' => "	// Show role name
	'SHOW_ROLE_NAME' => true,
"],
			]
			],
			['name' => 'config/modules/Chat.php', 'conditions' => [
				['type' => 'update', 'search' => 'REFRESH_TIME', 'replace' => [AppConfig::module('Chat', 'REFRESH_TIME'), AppConfig::module('Chat', 'REFRESH_TIME') * 1000]],
			]
			],
			['name' => 'config/modules/OpenStreetMap.php', 'conditions' => [
				['type' => 'add', 'search' => '];', 'checkInContents' => '\'COORDINATE_CONNECTOR\'', 'addingType' => 'before', 'value' => "	// Name of connector to get coordinates
	'COORDINATE_CONNECTOR' => 'OpenStreetMap',"],
				['type' => 'add', 'search' => '];', 'checkInContents' => '\'ROUTE_CONNECTOR\'', 'addingType' => 'before', 'value' => "// Name of connector to calculate of route
	'ROUTE_CONNECTOR' => 'Yours',"]
			]
			],
			['name' => 'config/performance.php', 'conditions' => [
				['type' => 'add', 'search' => '];', 'checkInContents' => 'INVENTORY_EDIT_VIEW_LAYOUT', 'addingType' => 'before', 'value' => "	//Is divided layout style on edit view in modules with products
	'INVENTORY_EDIT_VIEW_LAYOUT' => true,
"],
				['type' => 'add', 'search' => '];', 'checkInContents' => 'LIMITED_INFO_IN_FOOTER', 'addingType' => 'before', 'value' => "	// Any modifications of this parameter require the vendor's consent.
	// Any unauthorised modification breaches the terms and conditions of YetiForce Public License.
	'LIMITED_INFO_IN_FOOTER' => false,
"],
			],
			],
			['name' => 'config/modules/OSSMail.php', 'conditions' => [
				['type' => 'update', 'search' => '$config[\'db_dsnw\']', 'checkInContents' => 'isset($dbconfig)', 'value' => "if (isset(\$dbconfig)) {
	\$config['db_dsnw'] = 'mysql://' . \$dbconfig['db_username'] . ':' . \$dbconfig['db_password'] . '@' . \$dbconfig['db_server'] . ':' . \$dbconfig['db_port'] . '/' . \$dbconfig['db_name'];
}
"],
				['type' => 'add', 'search' => '$config[\'db_prefix\']', 'checkInContents' => 'defined(\'RCUBE_INSTALL_PATH\')', 'addingType' => 'before', 'value' => "if (!defined('RCUBE_INSTALL_PATH')) {
	define('RCUBE_INSTALL_PATH', realpath(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'OSSMail' . DIRECTORY_SEPARATOR . 'roundcube'));
}
"],
				['type' => 'update', 'search' => '$config[\'site_URL\']', 'checkInContents' => 'isset($site_URL)', 'value' => "if (isset(\$site_URL)) {
	\$config['site_URL'] = \$config['public_URL'] = \$site_URL;
	\$config['public_URL'] .= strpos(\$_SERVER['SCRIPT_NAME'], 'public_html/modules/OSSMail') === false ? '' : 'public_html/';
}
"],
				['type' => 'remove', 'search' => '$config[\'public_URL\'] .=', 'checkInContents' => 'isset($site_URL)']
			],
			],
			['name' => 'config/modules/OpenStreetMap.php', 'conditions' => [
				['type' => 'update', 'search' => 'seaching', 'replace' => ['seaching', 'searching']],
				['type' => 'add', 'search' => '];', 'checkInContents' => 'ROUTE_CONNECTOR', 'addingType' => 'before', 'value' => "	// Name of connector to calculate of route
	'ROUTE_CONNECTOR' => 'Yours'
"],
				['type' => 'add', 'search' => '];', 'checkInContents' => 'COORDINATE_CONNECTOR', 'addingType' => 'before', 'value' => "	// Name of connector to get coordinates
	'COORDINATE_CONNECTOR' => 'OpenStreetMap',
"],
			]
			],
		];
	}

	/**
	 * Configuration files.
	 */
	private function updateConfigurationFiles()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
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
									if (isset($condition['checkInContents']) && strpos($baseContent, $condition['checkInContents']) !== false) {
										break;
									}
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
									} elseif (isset($condition['checkInContents']) && strpos($baseContent, $condition['checkInContents']) !== false) {
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
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
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
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		\App\UserPrivilegesFile::recalculateAll();
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return true;
	}

	/**
	 * Migrate image field.
	 */
	public function imageFix()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		try {
			$imageInstance = new MigrateImages();
			$imageInstance->preProcess();
			$imageInstance->update();
			$imageInstance->clean();
		} catch (\Throwable $ex) {
			$this->log('MIGRATE imageFix: ' . $ex->getMessage() . '|' . $ex->getTraceAsString());
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}
}

/**
 * Class MigrateImages.
 */
class MigrateImages
{
	public $modules = [];
	public $tables = ['u_#__attachments', 'vtiger_salesmanattachmentsrel'];
	public $moduleName = '';
	public $uitypes = [69, 311];

	/**
	 * Preprocess.
	 *
	 * @return bool
	 */
	public function preProcess()
	{
		$db = \App\Db::getInstance();
		foreach ($this->tables as $key => $table) {
			if (!$db->isTableExists($table)) {
				unset($this->tables[$key]);
			}
		}
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$modules = array_column(\vtlib\Functions::getAllModules(), 'name');
		$fields = (new \App\Db\Query())->select(['tabid', 'uitype'])->from('vtiger_field')->where(['uitype' => $this->uitypes])->orderBy(['tabid' => SORT_ASC])->createCommand()->queryAllByGroup(2);
		foreach ($fields as $tabId => $uitypes) {
			try {
				if ($tabId) {
					$this->moduleName = \App\Module::getModuleName($tabId);
					$this->modules = $modules;
					if (in_array(69, $uitypes)) {
						$this->migrateImage();
					}
					if (in_array(311, $uitypes)) {
						$this->migrateMultiImage();
					}
				}
			} catch (\Throwable $ex) {
				\App\Log::error('MIGRATE FILES:' . $ex->getMessage() . $ex->getTraceAsString());
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
		if ($field->getModuleName() === 'Users') {
			$relTable = 'vtiger_salesmanattachmentsrel';
			if (!in_array($relTable, $this->tables)) {
				return;
			}
			$queryGenerator->addJoin(['INNER JOIN', $relTable, $queryGenerator->getColumnName('id') . "=$relTable.smid"]);
		} else {
			$relTable = 'vtiger_seattachmentsrel';
			$queryGenerator->addJoin(['INNER JOIN', $relTable, $queryGenerator->getColumnName('id') . "=$relTable.crmid"]);
		}
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_attachments', "$relTable.attachmentsid = vtiger_attachments.attachmentsid"]);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$this->updateRow($row, $field);
		}
		$dataReader->close();
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
			if (!in_array('u_#__attachments', $this->tables)) {
				continue;
			}
			$field->set('primaryColumn', $entityModel->tab_name_index[$field->getTableName()]);
			$queryGenerator->permissions = false;
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id', $field->getName()]);
			$queryGenerator->setCustomColumn(['attachmentsid' => 'u_#__attachments.attachmentid', 'path' => 'u_#__attachments.path', 'name' => 'u_#__attachments.name']);
			$queryGenerator->addJoin(['INNER JOIN', 'u_#__attachments', $queryGenerator->getColumnName('id') . '=u_#__attachments.crmid']);
			$queryGenerator->addNativeCondition(['and', ['u_#__attachments.fieldid' => $field->getId()], ['u_#__attachments.status' => 1]]);
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				$this->updateRow($row, $field, true);
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
		if (substr(str_replace('\\', '/', $row['path']), 0, 7) !== 'storage') {
			$path = $row['path'] . 'X';
			$row['path'] = substr($path, strpos($path, 'storage'), -1);
		}
		$path = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $row['path'];
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
			$image['key'] = hash('sha1', $file->getContents()) . $this->generatePassword(10);
			$image['size'] = \vtlib\Functions::showBytes($file->getSize());
			$image['name'] = $file->getName();
			$image['path'] = $this->getLocalPath($file->getPath());

			$oldValue = (new \App\Db\Query())->select([$field->getColumnName()])->from($field->getTableName())->where([$field->get('primaryColumn') => $row['id']])->scalar();
			$value = \App\Json::decode($oldValue);
			if (!is_array($value)) {
				$value = [];
			}
			$value[] = $image;
			if ($dbCommand->update($field->getTableName(), [$field->getColumnName() => \App\Json::encode($value)], [$field->get('primaryColumn') => $row['id']])->execute()) {
				if ($isMulti) {
					$dbCommand->delete('u_#__attachments', ['and', ['attachmentid' => $row['attachmentsid']], ['fieldid' => $field->getId()]])->execute();
				} else {
					$dbCommand->delete('vtiger_crmentity', ['and', ['crmid' => $row['attachmentsid']], ['not in', 'setype', $this->modules]])->execute();
					$dbCommand->delete('vtiger_attachments', ['attachmentsid' => $row['attachmentsid']])->execute();
					if ($field->getModuleName() === 'Users') {
						$dbCommand->delete('vtiger_salesmanattachmentsrel', ['attachmentsid' => $row['attachmentsid']])->execute();
					}
				}
			}
		} else {
			\App\Log::error("MIGRATE FILES ID:{$row['id']}|{$row['attachmentsid']} - " . $row['path']);
		}
	}

	/**
	 * Generate random password.
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	private function generatePassword($length = 10, $type = 'lbd')
	{
		$chars = [];
		if (strpos($type, 'l') !== false) {
			$chars[] = 'abcdefghjkmnpqrstuvwxyz';
		}
		if (strpos($type, 'b') !== false) {
			$chars[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		}
		if (strpos($type, 'd') !== false) {
			$chars[] = '0123456789';
		}
		if (strpos($type, 's') !== false) {
			$chars[] = '!"#$%&\'()*+,-./:;<=>?@[\]^_{|}';
		}
		$password = $allChars = '';
		foreach ($chars as $char) {
			$allChars .= $char;
			$password .= $char[array_rand(str_split($char))];
		}
		$allChars = str_split($allChars);
		$missing = $length - count($chars);
		for ($i = 0; $i < $missing; ++$i) {
			$password .= $allChars[array_rand($allChars)];
		}
		return str_shuffle($password);
	}

	/**
	 * Get crm pathname.
	 *
	 * @param string $path Absolute pathname
	 *
	 * @return string Local pathname
	 */
	public function getLocalPath($path)
	{
		if (strpos($path, ROOT_DIRECTORY) === 0) {
			$index = strlen(ROOT_DIRECTORY) + 1;
			if (strrpos(ROOT_DIRECTORY, '/') === strlen(ROOT_DIRECTORY) - 1 || strrpos(ROOT_DIRECTORY, '\\') === strlen(ROOT_DIRECTORY) - 1) {
				$index -= 1;
			}
			$path = substr($path, $index);
		}
		return $path;
	}

	/**
	 * Drop tables.
	 *
	 * @param array $tables
	 */
	public function clean()
	{
		$db = \App\Db::getInstance();
		foreach ($this->tables as $table) {
			if (!$db->isTableExists($table)) {
				continue;
			}
			if ($table === 'u_#__attachments') {
				$db->createCommand()->delete('u_#__attachments', ['status' => 0])->execute();
			}
			if (!(new \App\Db\Query())->from($table)->exists()) {
				$db->createCommand()->dropTable($table)->execute();
			} else {
				\App\Log::error("MIGRATE FILES - $table can not be deleted. There is data.");
			}
		}
	}
}
