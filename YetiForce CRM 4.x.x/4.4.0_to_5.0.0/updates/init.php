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

	private function migrateCvConditions(){
		$db = App\Db::getInstance();
		$tableName = 'u_yf_cv_condition';
		if (!$db->getTableSchema($tableName, true)) {
			$importer = new \App\Db\Importers\Base();
			$db->createTable($tableName, [
				'id' => $importer->primaryKeyUnsigned(10),
				'group_id' => $importer->integer(10)->unsigned(),
				'field_name' => $importer->stringType(50),
				'module_name' => $importer->stringType(25),
				'source_field_name' => $importer->stringType(50),
				'operator' => $importer->stringType(20),
				'value' => $importer->text(),
				'index' => $importer->tinyInteger(5),
			]);
			$tableNameGroup = 'u_yf_cv_condition_group';
			$db->createTable($tableNameGroup, [
				'id' => $importer->primaryKeyUnsigned(10),
				'cvid' => $importer->integer(10),
				'condition' => $importer->stringType(3),
				'parent_id' => $importer->integer(10),
				'index' => $importer->tinyInteger(5),
			]);
			$dataReader = (new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->createCommand()->query();
			while ($row = $dataReader->read()) {
				$model = CustomView_Record_Model::getInstanceById($row['cvid']);
				$advfilterlist = [];
				$dataReaderCondition = (new \App\Db\Query())
					->from('vtiger_cvadvfilter')
					->where(['cvid' => $row['cvid'], 'groupid' => 1])
					->createCommand()->query();
				if ($dataReaderCondition->count()) {
					$advfilterlist = [
						'condition' => 'AND',
						'rules' => []
					];
					while ($condition = $dataReaderCondition->read()) {
						$moduleName = $model->get('entitytype');
						[$tableName, $columnName, $fieldName] = array_pad(explode(':', $condition['columnname']), 3, false);
						$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($moduleName));
						$value = $condition['value'];
						if (in_array($fieldModel->getFieldDataType(), [
							'userCreator', 'userReference', 'picklist', 'tree',
							'categoryMultipicklist', 'country', 'multipicklist', 'theme',
							'modules', 'sharedOwner', 'owner', 'taxes'
						])) {
							$value = str_replace(',', '##', $value);
						}
						$advfilterlist['rules'][$condition['columnindex']] = [
							'fieldname' => "{$fieldModel->getModuleName()}:{$fieldModel->get('name')}",
							'operator' => $condition['comparator'],
							'value' => $value
						];
					}
				}
				$dataReaderCondition = (new \App\Db\Query())
					->from('vtiger_cvadvfilter')
					->where(['cvid' => $row['cvid'], 'groupid' => 2])
					->createCommand()->query();
				if ($dataReaderCondition->count()) {
					$ors = [
						'condition' => 'OR',
						'rules' => []
					];
					while ($condition = $dataReaderCondition->read()) {
						$moduleName = $model->get('entitytype');
						[$tableName, $columnName, $fieldName] = array_pad(explode(':', $condition['columnname']), 3, false);
						$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($moduleName));

						$value = $condition['value'];
						if (in_array($fieldModel->getFieldDataType(), [
							'userCreator', 'userReference', 'picklist', 'tree',
							'categoryMultipicklist', 'country', 'multipicklist', 'theme',
							'modules', 'sharedOwner', 'owner', 'taxes'
						])) {
							$value = str_replace(',', '##', $value);
						}
						$ors['rules'][$condition['columnindex']] = [
							'fieldname' => "{$fieldModel->getModuleName()}:{$fieldModel->get('name')}",
							'operator' => $condition['comparator'],
							'value' => $value
						];
					}
					if (count($ors['rules'])) {
						if (isset($advfilterlist['condition'])) {
							$advfilterlist['rules'][] = $ors;
						} else {
							$advfilterlist = $ors;
						}
					}
				}
				$this->addGroup($row['cvid'], $advfilterlist, 0, 0);
			}
		}
	}

	/**
	 * Add condition to database.
	 *
	 * @param array $rule
	 * @param int   $parentId
	 * @param int   $index
	 *
	 * @throws \App\Exceptions\Security
	 * @throws \yii\db\Exception
	 *
	 * @return void
	 */
	private function addCondition(array $rule, int $parentId, int $index)
	{
		[$fieldModuleName, $fieldName, $sourceFieldName] = array_pad(explode(':', $rule['fieldname']), 3, false);
		$operator = $rule['operator'];
		$value = $rule['value'] ?? '';
		\App\Db::getInstance()->createCommand()->insert('u_#__cv_condition', [
			'group_id' => $parentId,
			'field_name' => $fieldName,
			'module_name' => $fieldModuleName,
			'source_field_name' => $sourceFieldName,
			'operator' => $operator,
			'value' => $value,
			'index' => $index
		])->execute();
	}

	/**
	 * Add group to database.
	 *
	 * @param array|null $rule
	 * @param int        $parentId
	 * @param int        $index
	 *
	 * @throws \App\Exceptions\Security
	 * @throws \yii\db\Exception
	 *
	 * @return void
	 */
	private function addGroup(int $cvId , ?array $rule, int $parentId, int $index)
	{
		if (empty($rule)) {
			return;
		}
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('u_#__cv_condition_group', [
			'cvid' => $cvId,
			'condition' => $rule['condition'] === 'AND' ? 'AND' : 'OR',
			'parent_id' => $parentId,
			'index' => $index
		])->execute();
		$index = 0;
		$parentId = $db->getLastInsertID('u_#__cv_condition_group_id_seq');
		foreach ($rule['rules'] as $ruleInfo) {
			if (isset($ruleInfo['condition'])) {
				$this->addGroup($cvId, $ruleInfo, $parentId, $index);
			} else {
				$this->addCondition($ruleInfo, $parentId, $index);
			}
			$index++;
		}
	}
	private function migrateCompanies()
	{
		$dataReader = (new \App\Db\Query())->from('s_#__companies')->createCommand()->query();
		$db = App\Db::getInstance();
		$companyId = 0;
		while ($row = $dataReader->read()) {
			if (!isset($row['short_name'])) {
				return;
			}
			$recordModel = Vtiger_Record_Model::getCleanInstance('MultiCompany');
			$recordModel->set('company_name', $row['name']);
			$recordModel->set('mulcomp_status', 'PLL_ACTIVE');
			$recordModel->set('addresslevel8a', $row['street']);
			$recordModel->set('addresslevel5a', $row['city']);
			$recordModel->set('poboxa', $row['code']);
			$recordModel->set('addresslevel2a', $row['state']);
			$recordModel->set('addresslevel1a', $row['country']);
			$recordModel->set('phone', $row['phone']);
			$recordModel->set('fax', $row['fax']);
			$recordModel->set('website', $row['website']);
			$recordModel->set('vat', $row['vatid']);
			$recordModel->set('companyid1', $row['id1']);
			$recordModel->set('companyid2', $row['id2']);
			$recordModel->set('email1', $row['email']);
			if (file_exists('public_html/layouts/resources/Logo/' . $row['logo_main'])) {
				$filePath = 'public_html/layouts/resources/Logo/' . 'backup' . $row['logo_main'];
				copy('public_html/layouts/resources/Logo/' . $row['logo_main'], $filePath);
				$file = \App\Fields\File::loadFromPath($filePath);
				$savePath = App\Fields\File::initStorageFileDirectory('MultiImage/MultiCompany/logo');
				$key = $file->generateHash(true, $savePath);
				$size = $file->getSize();
				if ($file->moveFile($savePath . $key)) {
					$recordModel->set('logo', \App\Json::encode([[
						'name' => substr($file->getName(), 6),
						'size' => \vtlib\Functions::showBytes($size),
						'key' => $key,
						'path' => $savePath . $key
					]]));
				}
			}
			\App\Cache::clear();
			$recordModel->save();
			$companyId = $recordModel->getId();
			$db->createCommand()->update('s_#__companies', ['logo' => $row['logo_main']], ['id' => $row['id']])->execute();
			$filePath = 'public_html/layouts/resources/Logo/' . $row['logo_login'];
			if (file_exists($filePath)) {
				unlink($filePath);
			}
			$filePath = 'public_html/layouts/resources/Logo/' . $row['logo_mail'];
			if (file_exists($filePath)) {
				unlink($filePath);
			}
		}
		$db->createCommand()->update('vtiger_role', ['company' => $companyId])->execute();
		$this->importer->dropColumns([
			['s_#__companies', 'short_name'],
			['s_#__companies', 'default'],
			['s_#__companies', 'street'],
			['s_#__companies', 'code'],
			['s_#__companies', 'state'],
			['s_#__companies', 'phone'],
			['s_#__companies', 'fax'],
			['s_#__companies', 'vatid'],
			['s_#__companies', 'id1'],
			['s_#__companies', 'id2'],
			['s_#__companies', 'logo_login'],
			['s_#__companies', 'logo_login_height'],
			['s_#__companies', 'logo_main'],
			['s_#__companies', 'logo_main_height'],
			['s_#__companies', 'logo_mail'],
			['s_#__companies', 'logo_mail_height'],
		]);
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
		$this->migrateCvConditions();
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
				'vtiger_othereventduration_seq',
				'u_#__chat_messages',
				'u_#__chat_rooms',
				'u_#__chat_users',
				'vtiger_pbxmanager_gateway',
				'vtiger_pbxmanager_phonelookup',
			]);
		} catch (\Throwable $ex) {
			$this->log($ex->getMessage() . '|' . $ex->getTraceAsString());
			$this->importer->logs(false);
			throw $ex;
		}
		$this->importer->refreshSchema();
		$db->createCommand()->checkIntegrity(true)->execute();
		$this->imageFix();
		$this->attachmentsFix();
		$this->migrateDefOrgField();
		$this->addNotificationForNewUser();
		$this->addFields();
		$this->changeLengthFields();
		$this->migrateCompanies();
		$this->updateData();
		$this->addPicklistValues();
		$this->removeEventsModule();
		$this->updateCron();
		$this->removeFields();
		$this->migrateCvColumnList();
		$this->updateHeaderField();
		$this->addRelations();
		$this->updateRecords();
		$this->removePbxManager();
		$this->actionMapp();
		$this->addModules(['RecycleBin']);
		$this->updateInventory();
		$this->migrateLanguages();
		$this->importer->dropTable([
			'vtiger_cvadvfilter',
			'vtiger_cvadvfilter_grouping',
			'vtiger_def_org_field',
			'vtiger_cvstdfilter',
			'vtiger_callduration',
			'vtiger_callduration_seq',
			'vtiger_language_seq'
		]);
		$this->importer->dropColumns([
			['a_yf_pdf', 'watermark_size'],
			['vtiger_language', 'label'],
			['a_yf_pdf', 'meta_creator'],
		]);
		$this->importer->logs(false);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function addNotificationForNewUser()
	{
		if ((new \App\Db\Query())->from('com_vtiger_workflows')->where(['module_name' => 'Users', 'summary' => 'LBL_NEW_USER_CREATED'])->exists()) {
			return;
		}
		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		require_once 'modules/com_vtiger_workflow/VTWorkflowManager.php';
		require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php';
		$workflowManager = new VTWorkflowManager();
		$workflow = new Workflow();
		$workflow->test = '[]';
		$workflow->moduleName = 'Users';
		$workflow->description = 'LBL_NEW_USER_CREATED';
		$workflow->executionCondition = VTWorkflowManager::$ON_FIRST_SAVE;
		$workflow->type = 'basic';
		$workflow->filtersavedinnew = 6;
		$workflowManager->save($workflow);
		$tm = new VTTaskManager();
		$task = new VTEntityMethodTask();

		$task->executeImmediately = 1;
		$task->workflowId = $workflow->id;
		$task->summary = 'New user created';
		$task->active = 0;
		$task->methodName = 'newUser';
		$tm->saveTask($task);
		(new VTEntityMethodManager())->addEntityMethod('Users', 'newUser', 'modules/Users/workflows/UsersWorkflow.php', 'UsersWorkflow');
		$recordModel = Vtiger_Record_Model::getCleanInstance('EmailTemplates');
		$recordModel->set('name', 'New user');
		$recordModel->set('emial_template_type', 'PLL_RECORD');
		$recordModel->set('module_name', 'Users');
		$recordModel->set('subject', 'A new user has been created');
		$recordModel->set('content', "<table border=\"0\" style=\"width:100%;font-family:Arial, 'Sans-serif';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;\">
	<tbody>
		<tr>
			<td style=\"background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, 'Sans-serif';font-size:11px;\">
			<h3 style=\"padding:0 0 6px 0;margin:0;font-family:Arial, 'Sans-serif';font-size:16px;font-weight:bold;color:#222;\"><span>A new user has been created</span></h3>
			</td>
		</tr>
		<tr>
			<td>
			<div style=\"padding:2px;\">
			<table border=\"0\">
				<tbody>
					<tr>
						<td style=\"padding:0 1em 10px 0;font-family:Arial, 'Sans-serif';font-size:13px;color:#888;white-space:nowrap;\">Dear user,<br>
						A new user has been created. Below you can find your password and access data to your account.<br>
						<br>
						$(translate : LBL_SITE_URL)$: $(general : SiteUrl)$<br>
						$(translate : Users|User Name)$: $(record : user_name)$<br>
						$(translate : Users|Password)$: $(params : password)$</td>
					</tr>
				</tbody>
			</table>
			</div>
			</td>
		</tr>
		<tr>
			<td style=\"background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, 'Sans-serif';font-size:11px;\">
			<div style=\"float:right;\">$(organization : mailLogo)$</div>
			 
			<p><span style=\"font-size:12px;\">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr>
	</tbody>
</table>
");
		$recordModel->setHandlerExceptions(['disableHandlers' => true]);
		$recordModel->set('email_template_priority', 9);
		$recordModel->save();
		App\Db::getInstance()->createCommand()->update('u_#__emailtemplates', ['sys_name' => 'NewUser'], ['emailtemplatesid' => $recordModel->getId()])->execute();

	}

	private function getNewPrefixLang($prefix)
	{
		if (strpos($prefix, '-') !== false) {
			return $prefix;
		}
		$oldPrefix = explode('_', $prefix, 2);
		return $oldPrefix[0] . '-' . strtoupper($oldPrefix[1]);
	}
	private function migrateLanguages()
	{
		$db = App\Db::getInstance();
		$allLanguages = (new \App\Db\Query())->from('vtiger_language')->all();
		foreach ($allLanguages as $row) {
			$prefix = $row['prefix'];
			if (strpos($prefix, '-') !== false) {
				continue;
			}
			$newPrefix = $this->getNewPrefixLang($prefix);
			$db->createCommand()->update('vtiger_language', ['prefix' => $newPrefix], ['id' => $row['id']])->execute();
		}
		$dataReader = (new \App\Db\Query())->select(['tablename', 'columnname'])->from('vtiger_field')->where(['uitype' => 32])->createCommand()->query();
		while ($row = $dataReader->read()) {
			foreach ($allLanguages as $lang) {
				$db->createCommand()->update($row['tablename'], [$row['columnname'] => $this->getNewPrefixLang($lang['prefix'])], [$row['columnname'] => $lang['prefix']])->execute();
			}
		}
		$dirs = ['languages', 'custom/languages'];
		foreach ($dirs as $directory) {
			if (!is_dir($directory)) {
				continue;
			}
			$dir = new \DirectoryIterator($directory);
			foreach ($dir as $fileinfo) {
				if ($fileinfo->getType() === 'dir') {
					if (in_array($fileinfo->getFilename(), ['.', '..'])) {
						continue;
					}
					rename($fileinfo->getPath() . DIRECTORY_SEPARATOR . $fileinfo->getFilename(), $fileinfo->getPath() . DIRECTORY_SEPARATOR . $this->getNewPrefixLang($fileinfo->getFilename()));
				}
			}
		}
	}
	private function updateInventory()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$modules = \Vtiger_Module_Model::getAll([], [], true);
		foreach ($modules as $moduleModel) {
			if (!$moduleModel->isInventory()) {
				continue;
			}
			$moduleName = $moduleModel->getName();
			$tableNameBase = CRMEntity::getInstance($moduleName)->table_name;
			$index = CRMEntity::getInstance($moduleName)->table_index;
			$tableName = $tableNameBase . '_inventory';
			try {
				if ($db->isTableExists($tableName) && $schema->getTableSchema($tableName)->getColumn('qtyparam')) {
					$db->createCommand()->dropColumn($tableName, 'qtyparam')->execute();
				}
				if ($db->isTableExists($tableName) && !$schema->getTableSchema($tableName)->getColumn('crmid')) {
					if (!in_array($tableName, ['u_yf_finvoicecost_inventory', 'u_yf_svendorenquiries_inventory'])) {
						$db->createCommand()->dropForeignKey("fk_1_{$tableName}", $tableName)->execute();
						$db->createCommand()->dropIndex("id", $tableName)->execute();
					} elseif($tableName === 'u_yf_finvoicecost_inventory') {
						$db->createCommand()->dropIndex("finvoicecost_inventory_idx", $tableName)->execute();
					} elseif ($tableName === 'u_yf_svendorenquiries_inventory') {
						$db->createCommand()->dropIndex("svendorenquiries_inventory_idx", $tableName)->execute();
					}
					$db->createCommand()->renameColumn($tableName, 'id', 'crmid')->execute();
					$db->createCommand()->addColumn($tableName, 'id', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_PK, 10))->execute();
					$db->createCommand()->createIndex("{$tableName}_crmid_idx", $tableName, ['crmid'])->execute();
					$db->createCommand()->addForeignKey("fk_1_{$tableName}", $tableName, 'crmid', $tableNameBase, $index, 'CASCADE')->execute();
					$db->createCommand("ALTER TABLE $tableName  CHANGE `id` `id` INT(10) NOT NULL AUTO_INCREMENT FIRST")->execute();
				}
			} catch (\Throwable $e) {
				$this->log(__METHOD__ . '| Error: ' . $moduleName . ', tablename: ' . $tableName);
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}
	/**
	 * Add modules.
	 *
	 * @param string[] $modules
	 */
	private function addModules(array $modules)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$command = \App\Db::getInstance()->createCommand();
		foreach ($modules as $moduleName) {
			if (file_exists(__DIR__ . '/' . $moduleName . '.xml') && !\vtlib\Module::getInstance($moduleName)) {
				$importInstance = new \vtlib\PackageImport();
				$importInstance->_modulexml = simplexml_load_file('cache/updates/updates/' . $moduleName . '.xml');
				$importInstance->importModule();
				$command->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			} else {
				\App\Log::warning('Module exists: ' . $moduleName);
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function migrateDefOrgField(){
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = App\Db::getInstance();
		if (!$db->getTableSchema('vtiger_def_org_field')) {
			$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
			return;
		}
		$subQuery = (new \App\Db\Query())->select(['vtiger_def_org_field.readonly'])
			->from('vtiger_def_org_field')->where(['vtiger_def_org_field.fieldid' => new \yii\db\Expression('vtiger_field.fieldid')]);
		$db->createCommand()->update('vtiger_field', ['readonly' => $subQuery])->execute();
		$subQuery = (new \App\Db\Query())->select(['vtiger_def_org_field.visible'])
			->from('vtiger_def_org_field')->where(['vtiger_def_org_field.fieldid' => new \yii\db\Expression('vtiger_field.fieldid')]);
		$db->createCommand()->update('vtiger_field', ['visible' => $subQuery])->execute();
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function removePbxManager(){
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$pbxmanagerId = \App\Module::getModuleId('PBXManager');
		if (!$pbxmanagerId) {
			$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
			return;
		}
		$moduleInstance = \vtlib\Module::getInstance($pbxmanagerId);
		$moduleInstance->delete();
		App\Db::getInstance()->createCommand()->delete('vtiger_ws_entity', ['name' => 'PBXManager'])->execute();

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

		$db = App\Db::getInstance();
		$dataReader = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['uitype' => [15, 16, 33]])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tableName = 'vtiger_' . $row['fieldname'];
			$tableSchema = $db->getTableSchema($tableName);
			if ($tableSchema && $tableSchema->getColumn('color')) {
				$idName = \App\Fields\Picklist::getPickListId($row['fieldname']);
				$dataReaderColor = (new \App\Db\Query())->select(['color', $idName])->from($tableName)->createCommand()->query();
				while ($rowColor = $dataReaderColor->read()) {
					$color = $rowColor['color'];
					if (strpos($color, '#') !== false) {
						$color = ltrim($color, '#');
						$db->createCommand()->update($tableName, ['color' => $color], [$idName => $rowColor[$idName]])->execute();
					}
				}
			}
		}
		$db->createCommand()->update('vtiger_field', ['maximumlength' => 65535], ['uitype' => 300])->execute();
		$this->updateVtEmailTemplates();
		$db->createCommand()->delete('vtiger_relatedlists', ['name' => 'getContacts'])->execute();
		$db->createCommand()->update('vtiger_blocks', ['display_status' => 2], ['display_status' => 1])->execute();
		$db->createCommand()->update('s_#__companies', ['type' => 2])->execute();
		if ($db->getTableSchema('vtiger_trees_templates_data')->getColumn('parenttrre')){
			$db->createCommand()->renameColumn('vtiger_trees_templates_data', 'parenttrre', 'parentTree')->execute();
		}
	}

	private function updateVtEmailTemplates()
	{
		$db = App\Db::getInstance();
		$dataReader = (new \App\Db\Query())
			->from('com_vtiger_workflowtasks')
			->where(['like', 'task', 'VTEmailTemplateTask'])
			->createCommand()->query();
		require_once 'modules/com_vtiger_workflow/include.php';
		require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php';
		require_once 'modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.php';
		while ($row = $dataReader->read()) {
			$taskObject = unserialize($row['task']);
			if (is_array($taskObject->email)) {
				$newemail = [];
				foreach ($taskObject->email as $email) {
					if (strpos($email, '$') !== false) {
						continue 2;
					}
					[$parentFieldName, $moduleName, $fieldName] = array_pad(explode('=', $email), 3, false);
					if ($fieldName) {
						$newemail = "$(relatedRecord : $parentFieldName|$fieldName|$moduleName)$";
					} else {
						$newemail = "$(record : $parentFieldName)$";
					}
					$newemail[] = $newemail;
				}
				$taskObject->email = $newemail;
				$db->createCommand()->update('com_vtiger_workflowtasks', ['task' => serialize($taskObject)], ['task_id' => $row['task_id']])->execute();
			} else {
				if (strpos($taskObject->email, '$') !== false) {
					continue;
				}
				[$parentFieldName, $moduleName, $fieldName] = array_pad(explode('=', $taskObject->email), 3, false);
				if ($fieldName) {
					$newemail = "$(relatedRecord : $parentFieldName|$fieldName|$moduleName)$";
				} else {
					$newemail = "$(record : $parentFieldName)$";
				}
				$taskObject->email = $newemail;
				$db->createCommand()->update('com_vtiger_workflowtasks', ['task' => serialize($taskObject)], ['task_id' => $row['task_id']])->execute();
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

	private function changeLengthFields()
	{
		$fields = [
			['Accounts', 'website', 255],
			['Products', 'website', 255],
			['Vendors', 'website', 255],
			['Services', 'website', 255],
		];
		$db = App\Db::getInstance();
		foreach ($fields as $fieldInfo) {
			$moduleModel = Vtiger_Module_Model::getInstance($fieldInfo[0]);
			if(!$moduleModel) {
				$this->log('Can not found module' . $fieldInfo[0]);
				continue;
			}
			$fieldModel = $moduleModel->getField($fieldInfo[1]);
			if(!$fieldModel) {
				$this->log('Can not found field' . $fieldInfo[1] . ' in module ' . $fieldInfo[0]);
				continue;
			}
			$db->createCommand()->alterColumn($fieldModel->getTableName(), $fieldModel->getColumnName(), 'string('. $fieldInfo[2].')')->execute();
			$db->createCommand()->update('vtiger_field', ['maximumlength' => $fieldInfo[2]], [
				'tabid' => App\Module::getModuleId($fieldInfo[0]),
				'fieldname' => $fieldInfo[1]
			])->execute();
		}
	}
	/**
	 * Add fields.
	 */
	public function addFields()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$importerType = new \App\Db\Importers\Base();
		$fields = [
			[93, 2769, 'parent_id', 'u_yf_competition', 2, 10, 'parent_id', 'LBL_PARENT_ID', 0, 2, '', '4294967295', 8, 303, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 'integer', 'LBL_COMPETITION_INFORMATION', [], ['Competition'], 'Competition'],
			[40, 2770, 'parents', 'vtiger_modcomments', 1, 1, 'parents', 'FL_PARENTS', 0, 2, '', null, 9, 98, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 'text', 'LBL_COMPETITION_INFORMATION', [], [], 'ModComments'],
			[95, 2771, 'issue_time','u_yf_finvoice',1,5,'issue_time','FL_ISSUE_TIME',0,2,'',NULL,4,310,1,'D~O',1,0,'BAS',1,'',0,'',NULL, 'date', 'LBL_BASIC_DETAILS', [], [], 'FInvoice'],
			[61, 2772, 'multicompanyid', 'vtiger_ossemployees', 1, 10, 'multicompanyid', 'FL_ORGANIZATION_STRUCTURE', 0, 0, '', '-2147483648,2147483647', 20, 151, 1, 'I~M', 2, 0, 'BAS', 1, '', 0, '', '', 'integer(10)', 'LBL_INFORMATION', [], ['MultiCompany'], 'OSSEmployees'],
			[119, 2773, 'website', 'u_yf_multicompany', 2, 17, 'website', 'FL_WEBSITE', 0, 2, '', '255', 9, 407, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 'string(255)', 'LBL_CONTACT_INFORMATION', [], [], 'MultiCompany'],
			[119, 2774, 'logo', 'u_yf_multicompany', 2, 69, 'logo', 'FL_LOGO', 0, 2, '', NULL, 0, 406, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 'text', 'LBL_ADDITIONAL_INFORMATION', [], [], 'MultiCompany'],
			[85,2775,'campaign_id','u_yf_squoteenquiries',1,10,'campaign_id','FL_CAMPAIGN_ID',0,2,'','-2147483648,2147483647',9,265,1,'I~O',1,0,'BAS',1,'',0,'',NULL, 'integer', 'LBL_QUOTESENQUIRES_INFORMATION', [], ['Campaigns'], 'SQuoteEnquiries'],
			[29,2776,'sync_carddav','vtiger_users',1,16,'sync_carddav','LBL_CARDDAV_SYNCHRONIZATION_CONTACT',0,2,'','255',17,83,1,'V~O',1,0,'BAS',1,'',0,'',NULL, 'string(100)', 'LBL_USER_ADV_OPTIONS', ['PLL_OWNER', 'PLL_OWNER_PERSON', 'PLL_OWNER_PERSON_GROUP', 'PLL_BASED_CREDENTIALS'], [], 'Users'],
			[29,2777,'sync_caldav','vtiger_users',1,16,'sync_caldav','LBL_CALDAV_SYNCHRONIZATION_CALENDAR',0,2,'','255',18,83,1,'V~O',1,0,'BAS',1,'',0,'',NULL,'string(100)', 'LBL_USER_ADV_OPTIONS', ['PLL_OWNER', 'PLL_OWNER_PERSON', 'PLL_OWNER_PERSON_GROUP'], [], 'Users'],
			[112, 2778, 'smtp_id', 'u_yf_emailtemplates', 2, 316, 'smtp_id', 'SMTP', 0, 2, '', '4294967295', 0, 376, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, $importerType->integer(11)->unsigned()->null(), 'LBL_BASIC_DETAILS', [], [], 'EmailTemplates']
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
			['vtiger_links', ['linktype' => 'HEADERSCRIPT', 'linklabel' => 'OSSMailJScheckmails']],
			['vtiger_links', ['linktype' => 'HEADERSCRIPT', 'linklabel' => 'ModCommentsCommonHeaderScript']],
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
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_NOTICE_CREATE)$ [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 43]],
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_NOTICE_CLOSE)$ [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 42]],
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_NOTICE_MODIFICATION)$ [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 41]],
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_NOTICE_CREATE)$ [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 40]],
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_NOTICE_CREATE)$ [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 39]],
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_NOTICE_CLOSE)$ [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 38]],
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_NOTICE_CLOSE)$ [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 37]],
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_COPY_BILLING_ADDRESS)$  [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 36]],
			['u_yf_emailtemplates', ['subject' => '$(translate : HelpDesk|LBL_NOTICE_MODIFICATION)$ [$(record : ticket_no)$]:$(record : ticket_title)$'], ['emailtemplatesid' => 35]],
			['vtiger_field', ['uitype' => '9', 'typeofdata' => 'N~O', 'maximumlength' => '999', 'readonly' => 1], ['fieldname' => 'projecttaskprogress', 'tabid' => \App\Module::getModuleId('ProjectTask')]],
			['vtiger_field', ['displaytype' => '9'], ['fieldname' => 'projectid', 'tabid' => \App\Module::getModuleId('ProjectTask')]],
			['vtiger_field', ['uitype' => '9', 'typeofdata' => 'N~O', 'maximumlength' => '999', 'readonly' => 1, 'displaytype' => 10], ['fieldname' => 'progress', 'tabid' => \App\Module::getModuleId('Project')]],
			['vtiger_field', ['uitype' => '9', 'typeofdata' => 'N~O', 'maximumlength' => '999', 'readonly' => 1], ['fieldname' => 'projectmilestone_progress', 'tabid' => \App\Module::getModuleId('ProjectMilestone')]],
			['vtiger_eventhandlers', ['include_modules' => 'Calendar'], ['handler_class' => 'API_CalDAV_Handler', 'event_name' => 'EntityAfterSave']],
			['vtiger_eventhandlers', ['priority' => '3'], ['handler_class' => 'Accounts_SaveChanges_Handler']],
			['vtiger_field', ['uitype' => '14'], ['fieldname' => 'time_start', 'tabid' => \App\Module::getModuleId('Calendar')]],
			['vtiger_field', ['uitype' => '14'], ['fieldname' => 'time_end', 'tabid' => \App\Module::getModuleId('Calendar')]],
			['vtiger_field', ['uitype' => '19', 'maximumlength' => '65535'], ['fieldname' => 'description', 'tabid' => \App\Module::getModuleId('Calendar')]],
		];
		$db->createCommand()->alterColumn('vtiger_project', 'progress', 'decimal(5,2)')->execute();
		$db->createCommand()->alterColumn('vtiger_projectmilestone', 'projectmilestone_progress', 'decimal(5,2)')->execute();
		$db->createCommand()->alterColumn('vtiger_projecttask', 'projecttaskprogress', 'decimal(5,2)')->execute();
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
				'iconpath' => 'fas fa-exclamation-triangle',
				'description' => 'LBL_LOGS_DESC',
				'linkto' => 'index.php?module=Log&parent=Settings&view=Index',
				'sequence' => 6,
				'active' => 0,
				'pinned' => 0,
				'admin_access' => null,
			], ['name' => 'LBL_LOGS']
			],
			['vtiger_settings_field', [
				'blockid' => \Settings_Vtiger_Menu_Model::getInstance('LBL_SYSTEM_TOOLS')->get('blockid'),
				'name' => 'LBL_BACKUP_MANAGER',
				'iconpath' => 'fas fa-file-archive',
				'description' => 'LBL_BACKUP_MANAGER_DESCRIPTION',
				'linkto' => 'index.php?module=Backup&parent=Settings&view=Index',
				'sequence' => 14,
				'active' => 0,
				'pinned' => 0,
				'admin_access' => null,
			], ['name' => 'LBL_BACKUP_MANAGER']
			],
			['u_#__chat_global', [
				'name' => 'LBL_GENERAL'
			], ['name' => 'LBL_GENERAL']
			],
			['vtiger_links', [
				'tabid' => App\Module::getModuleId('OSSTimeControl'),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'Employees Time Control',
				'linkurl' => 'index.php?module=OSSTimeControl&view=ShowWidget&name=TimeControl',
				'linkicon' => '',
				'sequence' => 0
			], ['tabid' => App\Module::getModuleId('OSSTimeControl'), 'linklabel' => 'Employees Time Control']
			],
			['vtiger_links', [
				'tabid' => App\Module::getModuleId('OSSTimeControl'),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'LBL_ALL_TIME_CONTROL',
				'linkurl' => 'index.php?module=OSSTimeControl&view=ShowWidget&name=AllTimeControl',
				'linkicon' => '',
				'sequence' => 0
			], ['tabid' => App\Module::getModuleId('OSSTimeControl'), 'linklabel' => 'LBL_ALL_TIME_CONTROL']
			],
			['vtiger_links', [
				'tabid' => App\Module::getModuleId('OSSTimeControl'),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'Mini List',
				'linkurl' => 'index.php?module=Home&view=ShowWidget&name=MiniList',
				'linkicon' => '',
				'sequence' => 0
			], ['tabid' => App\Module::getModuleId('OSSTimeControl'), 'linklabel' => 'Mini List']
			],
			['vtiger_links', [
				'tabid' => App\Module::getModuleId('OSSTimeControl'),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'ChartFilter',
				'linkurl' => 'index.php?module=Home&view=ShowWidget&name=ChartFilter',
				'linkicon' => '',
				'sequence' => 0
			], ['tabid' => App\Module::getModuleId('OSSTimeControl'), 'linklabel' => 'ChartFilter']
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('SRecurringOrders'),
				'related_tabid' => App\Module::getModuleId('Contacts'),
				'name' => 'getRelatedList',
				'sequence' => 4,
				'label' => 'Contacts',
				'presence' => 0,
				'actions' => 'ADD,SELECT',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('SRecurringOrders'), 'related_tabid' => App\Module::getModuleId('Contacts')]
			],
			['vtiger_widgets', [
				'tabid' => App\Module::getModuleId('SRecurringOrders'),
				'type' => 'RelatedModule',
				'label' => '',
				'wcol' => '1',
				'sequence' => '6',
				'data' => '{"relatedmodule":"4","relatedfields":["4::firstname","4::lastname","4::assigned_user_id"],"viewtype":"List","limit":"5","action":"1","actionSelect":"1","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}'
			], ['tabid' => App\Module::getModuleId('SRecurringOrders'), 'data' => '{"relatedmodule":"4","relatedfields":["4::firstname","4::lastname","4::assigned_user_id"],"viewtype":"List","limit":"5","action":"1","actionSelect":"1","no_result_text":"0","switchHeader":"-","filter":"-","checkbox":"-"}']
			],
			['vtiger_eventhandlers', [
				'event_name' => 'EntityAfterSave',
				'handler_class' => 'Project_ProjectHandler_Handler',
				'is_active' => 1,
				'include_modules' => 'Project,ProjectMilestone',
				'exclude_modules' => '',
				'priority' => 3,
				'owner_id' => 42,
			], [
				'event_name' => 'EntityAfterSave',
				'handler_class' => 'Project_ProjectHandler_Handler'
			]
			],
			['vtiger_eventhandlers', [
				'event_name' => 'EntityChangeState',
				'handler_class' => 'Project_ProjectHandler_Handler',
				'is_active' => 1,
				'include_modules' => 'Project,ProjectMilestone',
				'exclude_modules' => '',
				'priority' => 3,
				'owner_id' => 42,
			], [
				'event_name' => 'EntityChangeState',
				'handler_class' => 'Project_ProjectHandler_Handler'
			]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('MultiCompany'),
				'related_tabid' => App\Module::getModuleId('OSSEmployees'),
				'name' => 'getRelatedList',
				'sequence' => 1,
				'label' => 'OSSEmployees',
				'presence' => 0,
				'actions' => 'ADD,SELECT',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('MultiCompany'), 'related_tabid' => App\Module::getModuleId('OSSEmployees')]
			],
			['vtiger_eventhandlers', [
				'event_name' => 'UserAfterSave',
				'handler_class' => 'Vtiger_Workflow_Handler',
				'is_active' => 1,
				'include_modules' => 'Users',
				'exclude_modules' => '',
				'priority' => 4,
				'owner_id' => 0,
			], [
				'event_name' => 'UserAfterSave',
				'handler_class' => 'Vtiger_Workflow_Handler'
			]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('Accounts'),
				'related_tabid' => App\Module::getModuleId('SQuoteEnquiries'),
				'name' => 'getDependentsList',
				'sequence' => 30,
				'label' => 'SQuoteEnquiries',
				'presence' => 1,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('Accounts'), 'related_tabid' => App\Module::getModuleId('SQuoteEnquiries')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('Accounts'),
				'related_tabid' => App\Module::getModuleId('SQuotes'),
				'name' => 'getDependentsList',
				'sequence' => 31,
				'label' => 'SQuotes',
				'presence' => 1,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('Accounts'), 'related_tabid' => App\Module::getModuleId('SQuotes')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('Accounts'),
				'related_tabid' => App\Module::getModuleId('SRequirementsCards'),
				'name' => 'getDependentsList',
				'sequence' => 31,
				'label' => 'SRequirementsCards',
				'presence' => 1,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('Accounts'), 'related_tabid' => App\Module::getModuleId('SRequirementsCards')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('Accounts'),
				'related_tabid' => App\Module::getModuleId('SSingleOrders'),
				'name' => 'getDependentsList',
				'sequence' => 31,
				'label' => 'SSingleOrders',
				'presence' => 1,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('Accounts'), 'related_tabid' => App\Module::getModuleId('SSingleOrders')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('Accounts'),
				'related_tabid' => App\Module::getModuleId('SRecurringOrders'),
				'name' => 'getDependentsList',
				'sequence' => 31,
				'label' => 'SRecurringOrders',
				'presence' => 1,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('Accounts'), 'related_tabid' => App\Module::getModuleId('SRecurringOrders')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('Accounts'),
				'related_tabid' => App\Module::getModuleId('SVendorEnquiries'),
				'name' => 'getDependentsList',
				'sequence' => 31,
				'label' => 'SVendorEnquiries',
				'presence' => 1,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('Accounts'), 'related_tabid' => App\Module::getModuleId('SVendorEnquiries')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('Accounts'),
				'related_tabid' => App\Module::getModuleId('SCalculations'),
				'name' => 'getDependentsList',
				'sequence' => 31,
				'label' => 'SCalculations',
				'presence' => 1,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('Accounts'), 'related_tabid' => App\Module::getModuleId('SCalculations')]
			],
			['vtiger_eventhandlers', [
				'event_name' => 'EntityAfterDelete',
				'handler_class' => 'API_CardDAV_Handler',
				'is_active' => 1,
				'include_modules' => 'Contacts,OSSEmployees',
				'exclude_modules' => '',
				'priority' => 3,
				'owner_id' => 0,
			], [
				'event_name' => 'EntityAfterDelete',
				'handler_class' => 'API_CardDAV_Handler'
			]
			],
			['vtiger_eventhandlers', [
				'event_name' => 'EntityChangeState',
				'handler_class' => 'API_CardDAV_Handler',
				'is_active' => 1,
				'include_modules' => 'Contacts,OSSEmployees',
				'exclude_modules' => '',
				'priority' => 3,
				'owner_id' => 0,
			], [
				'event_name' => 'EntityChangeState',
				'handler_class' => 'API_CardDAV_Handler'
			]
			],
			['vtiger_eventhandlers', [
				'event_name' => 'EntityAfterDelete',
				'handler_class' => 'API_CalDAV_Handler',
				'is_active' => 1,
				'include_modules' => 'Calendar',
				'exclude_modules' => '',
				'priority' => 3,
				'owner_id' => 0,
			], [
				'event_name' => 'EntityAfterDelete',
				'handler_class' => 'API_CalDAV_Handler'
			]
			],
			['vtiger_eventhandlers', [
				'event_name' => 'EntityChangeState',
				'handler_class' => 'API_CalDAV_Handler',
				'is_active' => 1,
				'include_modules' => 'Calendar',
				'exclude_modules' => '',
				'priority' => 3,
				'owner_id' => 0,
			], [
				'event_name' => 'EntityChangeState',
				'handler_class' => 'API_CalDAV_Handler'
			]
			],
			['vtiger_fieldmodulerel', [
				'fieldid' => Vtiger_Module_Model::getInstance('SQuoteEnquiries')->getField('accountid')->getId(),
				'module' => 'SQuoteEnquiries',
				'relmodule' => 'Leads',
				'status' => null,
				'sequence' => 0,
			], [
				'fieldid' => Vtiger_Module_Model::getInstance('SQuoteEnquiries')->getField('accountid')->getId(),
				'relmodule' => 'Leads'
			]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('Leads'),
				'related_tabid' => App\Module::getModuleId('SQuoteEnquiries'),
				'name' => 'getDependentsList',
				'sequence' => 23,
				'label' => 'SQuoteEnquiries',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('Leads'), 'related_tabid' => App\Module::getModuleId('SQuoteEnquiries')]
			],
		];
		foreach(['Vendors', 'Partners', 'Competition','SSalesProcesses','Project', 'ServiceContracts', 'Campaigns', 'FBookkeeping', 'ProjectTask', 'ProjectMilestone', 'SQuoteEnquiries', 'SRequirementsCards', 'SCalculations', 'SQuotes', 'SSingleOrders',
					'SRecurringOrders', 'FInvoice', 'SVendorEnquiries'] as $moduleName) {
			$data[] = ['vtiger_links', [
				'tabid' => App\Module::getModuleId($moduleName),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'ChartFilter',
				'linkurl' => 'index.php?module=Home&view=ShowWidget&name=ChartFilter',
				'linkicon' => '',
				'sequence' => 0
			], ['tabid' => App\Module::getModuleId($moduleName), 'linklabel' => 'ChartFilter']
			];
			$data[] = ['vtiger_links', [
				'tabid' => App\Module::getModuleId($moduleName),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'Mini List',
				'linkurl' => 'index.php?module=Home&view=ShowWidget&name=MiniList',
				'linkicon' => '',
				'sequence' => 0
			], ['tabid' => App\Module::getModuleId($moduleName), 'linklabel' => 'Mini List']
			];
		}
		\App\Db\Updater::batchInsert($data);

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function actionMapp()
	{
		$allModules = array_keys(vtlib\Functions::getAllModules());
		$actions = [
  			['type' => 'add', 'name' => 'OpenRecord', 'tabsData' => $allModules],
			['type' => 'remove', 'name' => 'DuplicatesHandling'],
			['type' => 'add', 'name' => 'AutoAssignRecord', 'tabsData' => $allModules],
			['type' => 'add', 'name' => 'AssignToYourself', 'tabsData' => $allModules]
		];
		$db = \App\Db::getInstance();
		foreach ($actions as $action) {
			$key = (new \App\Db\Query())->select(['actionid'])->from('vtiger_actionmapping')->where(['actionname' => $action['name']])->limit(1)->scalar();
			if ($action['type'] === 'remove') {
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
			while (($profileId = $dataReader->readColumn(0)) !== false) {
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

	private function createConfigFiles()
	{
		if (class_exists('Config\\Main')) {
			return;
		}
		require_once 'vendor/nette/php-generator/src/PhpGenerator/PhpLiteral.php';
		require_once 'vendor/nette/utils/src/Utils/SmartObject.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/Traits/CommentAware.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/ClassType.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/PhpNamespace.php';
		require_once 'vendor/nette/utils/src/Utils/StaticClass.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/Helpers.php';
		require_once 'vendor/nette/utils/src/Utils/Callback.php';
		require_once 'vendor/nette/utils/src/Utils/Strings.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/Traits/VisibilityAware.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/Traits/NameAware.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/Property.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/Printer.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/Traits/FunctionLike.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/Method.php';
		require_once 'vendor/nette/php-generator/src/PhpGenerator/PhpFile.php';
		require 'config/api.php';
		(new UpdateConfig('api'))
			->set('enabledServices', $enabledServices)
			->set('enableBrowser', $enableBrowser)
			->set('enableCardDAV', $enableCardDAV)
			->set('enableCalDAV', $enableCalDAV)
			->set('enableWebDAV', $enableWebDAV)
			->create();
		require 'config/sounds.php';
		(new UpdateConfig('sounds'))
			->set('IS_ENABLED', $SOUNDS_CONFIG['IS_ENABLED'])
			->set('REMINDERS', $SOUNDS_CONFIG['REMINDERS'])
			->set('CHAT', $SOUNDS_CONFIG['REMINDERS'])
			->set('MAILS', $SOUNDS_CONFIG['REMINDERS'])
			->create();
		$dbConfig = AppConfig::main('dbconfig');
		(new UpdateConfig('db'))
			->set('db_server', $dbConfig['db_server'])
			->set('db_port', $dbConfig['db_port'])
			->set('db_username', $dbConfig['db_username'])
			->set('db_password', $dbConfig['db_password'])
			->set('db_name', $dbConfig['db_name'])
			->set('db_type', $dbConfig['db_type'])
			->create();

		$skip = ['module', 'component', 'db', 'api', 'sounds'];
		foreach (array_diff(UpdateConfig::TYPES, $skip) as $type) {
			(new UpdateConfig($type))->create();
		}
		$allConfig = [];
		foreach ((new \DirectoryIterator('config/modules/')) as $item) {
			if ($item->isFile() && !in_array($item->getBasename(), ['.', '..'])) {
				$moduleName = $item->getBasename();
				$filePath = 'config/modules' . \DIRECTORY_SEPARATOR . $moduleName;
				$fileName = current(explode('.', $moduleName));
				$allConfig[$fileName] = require $filePath;
			}
		}
		$allConfig['OSSMail'] = $config;
		AppConfig::load('modules', $allConfig);
		rename('config/modules', 'config/Modules');
		foreach ((new \DirectoryIterator('modules/')) as $item) {
			if ($item->isDir() && !in_array($item->getBasename(), ['.', '..'])) {
				$moduleName = $item->getBasename();
				$filePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
				if (file_exists($filePath)) {
					(new UpdateConfig('module', $moduleName))->create($allConfig[$moduleName]);
					unset($allConfig[$moduleName]);
				}
			}
		}


		$path = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
		$componentsData = require_once "$path";
		$skip = ['Dav'];
		foreach ($componentsData as $component => $data) {
			if (!in_array($component, $skip)) {
				(new UpdateConfig('component', $component))->create($allConfig[$component]);
				if (isset($allConfig[$component])) {
					unset($allConfig[$component]);
				}
			}
		}
		(new UpdateConfig('component', 'Dav'))
			->set('CALDAV_DEFAULT_VISIBILITY_FROM_DAV', AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV'))
			->set('CALDAV_EXCLUSION_FROM_DAV', AppConfig::module('API', 'CALDAV_EXCLUSION_FROM_DAV'))
			->set('CALDAV_EXCLUSION_TO_DAV', AppConfig::module('API', 'CALDAV_EXCLUSION_TO_DAV'))
			->create();
		unset($allConfig['API']);
		foreach ($allConfig as $file => $badConfig) {
			$this->log('ERROR: Can not create config file for '. $file . '| ' . date('Y-m-d H:i:s'));
		}
		$files = [
			'config/Modules/API.php',
			'config/Modules/Export.php',
			'config/Modules/Mail.php',
			'config/config.db.php',
			'config/config.inc.php',
			'config/config.php',
			'config/config.template.php',
			'config/secret_keys.php'
		];
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			$files = array_merge($files, [
				'config/api.php',
				'config/debug.php',
				'config/developer.php',
				'config/performance.php',
				'config/relation.php',
				'config/search.php',
				'config/security.php',
				'config/sounds.php',
			]);
		}
		foreach($files as $file) {
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}
	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$userIds = (new App\Db\Query())->select(['id'])->from('vtiger_users')->where(['deleted' => 0])->column();
		foreach ($userIds as $userid) {
			$handle = fopen(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges/user_privileges_' . $userid . '.php', 'w+');
			if ($handle) {
				$newBuf = '';
				$newBuf .= "<?php\n";
				$userFocus = \CRMEntity::getInstance('Users');
				$userFocus->retrieveEntityInfo($userid, 'Users');
				$userInfo = [];
				$userFocus->column_fields['id'] = '';
				$userFocus->id = $userid;
				foreach ($userFocus->column_fields as $field => $value) {
					if (isset($userFocus->$field)) {
						if ($field === 'currency_symbol') {
							$userInfo[$field] = $userFocus->$field;
						} else {
							$userInfo[$field] = is_numeric($userFocus->$field) ? $userFocus->$field : \App\Purifier::encodeHtml($userFocus->$field);
						}
					}
				}
				if ($userFocus->is_admin == 'on') {
					$newBuf .= "\$is_admin=true;\n";
					$newBuf .= '$user_info=' . App\Utils::varExport($userInfo) . ";\n";
				} else {
					$newBuf .= "\$is_admin=false;\n";
					$globalPermissionArr = App\PrivilegeUtil::getCombinedUserGlobalPermissions($userid);
					$tabsPermissionArr = App\PrivilegeUtil::getCombinedUserModulesPermissions($userid);
					$actionPermissionArr = App\PrivilegeUtil::getCombinedUserActionsPermissions($userid);
					$userRole = App\PrivilegeUtil::getRoleByUsers($userid);
					$userRoleParent = App\PrivilegeUtil::getRoleDetail($userRole)['parentrole'];
					$subRoles = App\PrivilegeUtil::getRoleSubordinates($userRole);
					$subRoleAndUsers = [];
					foreach ($subRoles as $subRoleId) {
						$subRoleAndUsers[$subRoleId] = \App\PrivilegeUtil::getUsersNameByRole($subRoleId);
					}
					$parentRoles = \App\PrivilegeUtil::getParentRole($userRole);
					$newBuf .= "\$current_user_roles='" . $userRole . "';\n";
					$newBuf .= "\$current_user_parent_role_seq='" . $userRoleParent . "';\n";
					$newBuf .= '$current_user_profiles=' . App\Utils::varExport(App\PrivilegeUtil::getProfilesByRole($userRole)) . ";\n";
					$newBuf .= '$profileGlobalPermission=' . App\Utils::varExport($globalPermissionArr) . ";\n";
					$newBuf .= '$profileTabsPermission=' . App\Utils::varExport($tabsPermissionArr) . ";\n";
					$newBuf .= '$profileActionPermission=' . App\Utils::varExport($actionPermissionArr) . ";\n";
					$newBuf .= '$current_user_groups=' . App\Utils::varExport(App\PrivilegeUtil::getAllGroupsByUser($userid)) . ";\n";
					$newBuf .= '$subordinate_roles=' . App\Utils::varExport($subRoles) . ";\n";
					$newBuf .= '$parent_roles=' . App\Utils::varExport($parentRoles) . ";\n";
					$newBuf .= '$subordinate_roles_users=' . App\Utils::varExport($subRoleAndUsers) . ";\n";
					$newBuf .= '$user_info=' . App\Utils::varExport($userInfo) . ";\n";
				}
				fwrite($handle, $newBuf);
				fclose($handle);
				$file = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges' . DIRECTORY_SEPARATOR . "user_privileges_$userid.php";
				$user = [];
				$userInstance = \CRMEntity::getInstance('Users');
				$userInstance->retrieveEntityInfo($userid, 'Users');
				$userInstance->column_fields['is_admin'] = $userInstance->is_admin === 'on';
				$exclusionEncodeHtml = ['currency_symbol', 'date_format', 'currency_id', 'currency_decimal_separator', 'currency_grouping_separator', 'othereventduration', 'imagename'];
				foreach ($userInstance->column_fields as $field => $value) {
					if (!\in_array($field, $exclusionEncodeHtml)) {
						$userInstance->column_fields[$field] = is_numeric($value) ? $value : \App\Purifier::encodeHtml($value);
					}
				}
				$displayName = '';
				foreach (App\Module::getEntityInfo('Users')['fieldnameArr'] as $field) {
					$displayName .= ' ' . $userInstance->column_fields[$field];
				}
				$userRoleInfo = App\PrivilegeUtil::getRoleDetail($userInstance->column_fields['roleid']);
				$user['details'] = $userInstance->column_fields;
				$user['displayName'] = trim($displayName);
				$user['profiles'] = App\PrivilegeUtil::getProfilesByRole($userInstance->column_fields['roleid']);
				$user['groups'] = App\PrivilegeUtil::getAllGroupsByUser($userid);
				$user['parent_roles'] = $userRoleInfo['parentRoles'];
				$user['parent_role_seq'] = $userRoleInfo['parentrole'];
				$user['roleName'] = $userRoleInfo['rolename'];
				$multiCompany = (new App\Db\Query())->select(['u_#__multicompany.*'])->from('u_#__multicompany')
					->innerJoin('vtiger_role', 'u_#__multicompany.multicompanyid = vtiger_role.company')
					->innerJoin('vtiger_user2role', 'vtiger_role.roleid = vtiger_user2role.roleid')
					->where(['vtiger_user2role.userid' => $userid])->limit(1)->one() ?: [];
				if ($multiCompany) {
					if (!(empty($multiCompany['logo']) || $multiCompany['logo'] === '[]' || $multiCompany['logo'] === '""') && ($logo = App\Json::decode($multiCompany['logo']))) {
						$multiCompany['logo'] = $logo[0] ?? [];
					} else {
						$multiCompany['logo'] = [];
					}
				}
				$user['multiCompanyId'] = $multiCompany['multicompanyid'];
				$user['multiCompanyLogo'] = $multiCompany['logo'] ?? '';
				$user['multiCompanyLogoUrl'] = $multiCompany['logo'] ? "file.php?module=MultiCompany&action=Logo&record={$userid}&key={$multiCompany['logo']['key']}" : '';
				file_put_contents($file, 'return ' . App\Utils::varExport($user) . ';' . PHP_EOL, FILE_APPEND);
				\Users_Privileges_Model::clearCache($userid);
				App\User::clearCache($userid);
			}

			\App\UserPrivilegesFile::createUserSharingPrivilegesfile($userid);
		}
		$menuRecordModel = new \Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		$this->createConfigFiles();
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

class UpdateConfig extends \App\Base
{
	/** Types of configuration files */
	public const TYPES = [
		'main',
		'db',
		'performance',
		'module',
		'api',
		'debug',
		'developer',
		'security',
		'securityKeys',
		'relation',
		'sounds',
		'search',
		'component',
	];

	/** @var string Type of configuration file */
	private $type;
	/** @var string|null Component name */
	private $component;
	/** @var string Path to the configuration file */
	private $path;
	/** @var string Path to the configuration template file */
	private $templatePath;
	/** @var array Template data */
	private $template = [];

	/** @var string License */
	private $license = 'Configuration file.
This file is auto-generated.

@package Config

@copyright YetiForce Sp. z o.o
@license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
';

	/**
	 * ConfigFile constructor.
	 *
	 * @param string      $type
	 * @param string|null $component
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function __construct(string $type, ?string $component = '')
	{
		parent::__construct();
		if (!in_array($type, self::TYPES)) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $type, 406);
		}
		$this->type = $type;
		if ($component) {
			$this->component = $component;
		}
		if ($this->type === 'module') {
			$this->templatePath = 'modules' . \DIRECTORY_SEPARATOR . $component . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
			$this->path = 'config' . \DIRECTORY_SEPARATOR . 'Modules' . \DIRECTORY_SEPARATOR . "{$component}.php";
		} elseif ($this->type === 'component') {
			$this->templatePath = 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
			$this->path = 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . "{$component}.php";
		} else {
			$this->templatePath = 'config' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
			$this->path = 'config' . \DIRECTORY_SEPARATOR . \ucfirst($this->type) . '.php';
		}
		$this->loadTemplate();
	}

	/**
	 * Load configuration template.
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	private function loadTemplate()
	{
		if (!\file_exists($this->templatePath)) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $this->templatePath, 406);
		}
		$data = require "{$this->templatePath}";
		if ('component' === $this->type) {
			if (!isset($data[$this->component])) {
				throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||{$this->type}:{$this->component}", 406);
			}
			$data = $data[$this->component];
		} elseif ('module' !== $this->type) {
			if (!isset($data[$this->type])) {
				throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $this->type, 406);
			}
			$data = $data[$this->type];
		}
		$this->template = $data;
	}

	/**
	 * Gets class name.
	 *
	 * @return string
	 */
	private function getClassName()
	{
		$className = 'Config\\';
		if ($this->type === 'module') {
			$className .= 'Modules\\' . $this->component;
		} elseif ($this->type === 'component') {
			$className .= 'Components\\' . $this->component;
		} else {
			$className .= ucfirst($this->type);
		}
		return $className;
	}

	/**
	 * Gets template data.
	 *
	 * @param string|null $key
	 *
	 * @return mixed
	 */
	public function getTemplate(?string $key = null)
	{
		return $key ? ($this->template[$key] ?? null) : $this->template;
	}


	/**
	 * Create configuration file.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function create($config = [])
	{
		if (\array_diff_key($this->getData(), $this->template)) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
		}
		$className = $this->getClassName();
		$file = new \Nette\PhpGenerator\PhpFile();
		$file->addComment($this->license);
		$class = $file->addClass($className);
		$class->addComment('Configuration Class.');
		foreach ($this->template as $parameterName => $parameter) {
			if (isset($parameter['type']) && 'function' === $parameter['type']) {
				$class->addMethod($parameterName)->setStatic()->setBody($parameter['default'])->addComment($parameter['description']);
			} else {

				$value = '';
				if ($this->has($parameterName)) {
					$value = $this->get($parameterName);
				} else {
					$method = $this->type;
					if ($method === 'module' || $method === 'component') {
						$value = AppConfig::module($this->component, $parameterName);
						if (!isset($config[$parameterName])) {
							$value = $parameter['default'];
						}
					} else {
						$value = AppConfig::$method($parameterName, $parameter['default']);
					}
				}
				$class->addProperty($parameterName, $value)->setStatic()->addComment($parameter['description']);
			}
		}
		if (file_exists($this->path)) {
			unlink($this->path);
		}
		if (false === file_put_contents($this->path, $file, LOCK_EX)) {
			throw new Exceptions\AppException("ERR_CREATE_FILE_FAILURE||{$this->path}");
		}
	}
}