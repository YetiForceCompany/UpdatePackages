<?php
/**
 * YetiForceUpdate Class.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
// last check: 7fb498e44be26b17ba150584fd4da6330e075cec
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

	private $error = false;

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
		if (false !== strpos($message, '[ERROR]')) {
			$this->error = true;
		}
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
			return false;
		}
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		try {
			$this->importer = new \App\Db\Importer();
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->updateScheme();
			$this->importer->dropColumns([
				['w_#__servers', 'accounts_id'], ['a_yf_record_converter', 'field_mappging'], ['a_yf_record_converter', 'change_view'],
				['vtiger_tab', 'modifiedby'], ['vtiger_tab', 'modifiedtime']
			]);
			$this->importer->refreshSchema();
			$this->importer->postUpdate();
			$this->updateScheme();
			$this->importer->dropTable(['vtiger_vendorcontactrel', 'vtiger_seticketsrel']);
			$this->importer->logs(false);
		} catch (\Throwable $ex) {
			$this->log($ex->getMessage() . '|' . $ex->getTraceAsString());
			$this->importer->logs(false);
			throw $ex;
		}
		$this->importer->refreshSchema();
		$db->createCommand()->checkIntegrity(true)->execute();
		$this->updateData();
		$this->updateFields();
		$this->addFields();
		$this->setRelations();
		$this->actionMapp();
		$this->updateFaq();
		$this->updateKnowledgeBase();
		$this->updateNotification();
		$this->updatePayments();
		$this->updatePaymentsOut();
		$this->updateInvoice();
		$this->updateProducts();
		$this->updateServices();
		$this->updateNextData();
		$this->importer->logs(false);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function records()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$id = (new \App\Db\Query())->select(['emailtemplatesid'])->from('u_yf_emailtemplates')->where(['sys_name' => 'YetiPortalForgotPassword'])->scalar();
		if ($id) {
			$record = \Vtiger_Record_Model::getInstanceById($id, 'EmailTemplates');
			$record->set('name', 'Notify the contact about the new account in the portal');
			$record->set('email_template_type', 'PLL_RECORD');
			$record->set('module_name', 'Contacts');
			$record->set('subject', 'Welcome to the YetiForce Client Portal!');
			$record->set('content', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;">
			<tr>
				<td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
					<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span> Welcome to the YetiForce Client Portal! </span></h3>
				</td>
			</tr>
			<tr>
				<td>
					<div style="padding:2px;">
						<table border="0">
							<tr>
								<td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear $(params : login)$,<br />
									Your account has been created successfully. Below are your username and password:<br /><br />
				Portal address:	$(params : acceptable_url)$<br />
							Your username: $(params : login)$<br />
									Your password: $(params : password)$
								</td>
							</tr>
							<tr>
								<td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
									Please log in to access all of the Portal features.<br /><br />
									If you have any questions or need any assistance, please send us an email to help@yetiforce.com.
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
			<tr>
				<td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
					<div style="float:right;">
						$(organization : 1|logo|40)$
					</div>
					<p>
						<span style="font-size:12px;">
							$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$
						</span>
					</p>
				</td>
			</tr>
		</table>');
			$record->set('email_template_priority', 1);
			$record->setHandlerExceptions(['disableHandlers' => true]);
			$record->save();
			$db->createCommand()
				->update('u_yf_emailtemplates', [
					'sys_name' => 'YetiPortalRegister',
				], ['emailtemplatesid' => $record->getId()])
				->execute();
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function removeRoleFromPicklist($fieldsName)
	{
		$start = microtime(true);
		$cache = implode(',', $fieldsName);
		$this->log(__METHOD__ . " | {$cache} |" . date('Y-m-d H:i:s'));
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		foreach ($fieldsName as $fieldName) {
			$count = (new \App\Db\Query())->from('vtiger_field')->where(['uitype' => 15, 'fieldname' => $fieldName])->count();
			if (1 === $count) {
				$tableName = "vtiger_{$fieldName}";
				$tableScheme = $db->getTableSchema($tableName);
				if ($tableScheme && $tableScheme->getColumn('picklist_valueid')) {
					try {
						$picklistId = (new \App\Db\Query())->from('vtiger_picklist')->select(['picklistid'])->where(['name' => $fieldName])->scalar();
						$this->importer->dropColumns([[$tableName, 'picklist_valueid']]);
						$dbCommand->update('vtiger_field', ['uitype' => 16], ['uitype' => 15, 'fieldname' => $fieldName])->execute();
						$dbCommand->delete('vtiger_picklist', ['picklistid' => $picklistId])->execute();
						$dbCommand->delete('vtiger_role2picklist', ['picklistid' => $picklistId])->execute();
					} catch (\Throwable $ex) {
						$this->log('[ERROR] ' . $ex->__toString());
					}
				} else {
					$this->log("[ERROR] data inconsistency {$tableName}");
				}
			} elseif ($count > 1) {
				$this->log("[ERROR] the field type cannot be changed because the item is shared {$tableName}");
			} else {
				$this->log("[INFO] No data to modify {$fieldName}");
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function dropTableIfNotUse(string $tableName, array $conditions, bool $drop = false)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " | {$tableName} |" . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$dropTable = [];
		if (!(new \App\Db\Query())->from('vtiger_field')->where($conditions)->exists() && $db->isTableExists($tableName)) {
			$dropTable[] = $tableName;
			if ($db->isTableExists($tableName . '_seq')) {
				$dropTable[] = $tableName . '_seq';
			}
		}
		if ($drop && $dropTable) {
			$this->importer->dropTable($dropTable);
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return $dropTable;
	}

	private function updateNotification()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$importerType = new \App\Db\Importers\Base();
		$moduleInstance = \Vtiger_Module_Model::getInstance('Notification');

		try {
			$dataToUpdate = [];
			$blockId = $this->getBlockId($moduleInstance->getId(), 'LBL_CUSTOM_INFORMATION', ['label' => 'LBL_CUSTOM_INFORMATION']);
			if ($blockId) {
				$dataToUpdate[] = ['vtiger_field', ['block' => $blockId], ['fieldname' => ['private', 'smcreatorid', 'shownerid', 'modifiedtime', 'createdtime', 'number'], 'block' => $this->getBlockId($moduleInstance->getId(), 'LBL_NOTIFICATION_INFORMATION')]];
			}
			\App\Db\Updater::batchUpdate($dataToUpdate);

			$fields = [
				[111, 2801, 'category', 'u_yf_notification', 1, 302, 'category', 'FL_CATEGORY', 0, 2, '', '30', 7, 374, 1, 'V~O', 2, 3, 'BAS', 1, '', 0, '30', '', 0, 0, 0, 'type' => $importerType->stringType(30)->defaultValue(''), 'blockLabel' => 'LBL_NOTIFICATION_INFORMATION', 'picklistValues' => [
					'column' => 'category',
					'base' => [30, 'Category', \App\Module::getModuleId('Notification'), 0],
					'data' => [[30, 'Base', 'T1', 'T1', 0, 'Base', '{"loaded":"1","opened":false,"selected":false,"disabled":false}', '']]
				], 'relatedModules' => [], 'moduleName' => 'Notification'],
			];
			$this->addFields($fields);
		} catch (\Throwable $ex) {
			$this->log('[ERROR] ' . $ex->__toString());
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateKnowledgeBase()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$importerType = new \App\Db\Importers\Base();
		$moduleInstance = \Vtiger_Module_Model::getInstance('KnowledgeBase');
		$tableName = $moduleInstance->basetable;

		// FULLTEXT
		$this->importer->logs .= "> start updateScheme()\n";
		$dbIndexes = $db->getTableKeys($tableName);
		try {
			if (!isset($dbIndexes['search']) && 'mysql' === $db->getDriverName()) {
				$this->importer->logs .= "  > create index: {$tableName} search ... ";
				$db->createCommand("ALTER TABLE {$tableName} ADD FULLTEXT KEY `search` (`subject`,`content`,`introduction`);")->execute();
				$this->importer->logs .= "done\n";
			}
		} catch (\Throwable $e) {
			$this->importer->logs .= " | ERROR (8) [{$e->getMessage()}] in  \n{$e->getTraceAsString()} !!!\n";
		}

		$dataToUpdate[] = ['vtiger_field', ['maximumlength' => '16777215'], ['fieldname' => 'content', 'tablename' => $tableName]];
		$blockId = $this->getBlockId($moduleInstance->getId(), 'LBL_CUSTOM_INFORMATION', ['label' => 'LBL_CUSTOM_INFORMATION', 'display_status' => 2]);
		if ($blockId) {
			$dataToUpdate[] = ['vtiger_field', ['block' => $blockId], ['fieldname' => ['modifiedtime', 'private', 'shownerid', 'createdtime', 'number', 'smcreatorid'], 'block' => $this->getBlockId($moduleInstance->getId(), 'LBL_KNOWLEDGEBASE_INFORMATION')]];
		}
		\App\Db\Updater::batchUpdate($dataToUpdate);

		$fields = [
			[96, 2789, 'featured', 'u_yf_knowledgebase', 1, 56, 'featured', 'FL_FEATURED', 0, 2, '', '-128,127', 6, 314, 1, 'C~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->tinyInteger(1), 'blockLabel' => 'LBL_KNOWLEDGEBASE_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'KnowledgeBase'],
			[96, 2790, 'introduction', 'u_yf_knowledgebase', 1, 300, 'introduction', 'FL_INTRODUCTION', 0, 2, '', '65535', 2, 315, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_ARTICLE', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'KnowledgeBase'],
		];

		try {
			$query = (new \App\Db\Query())->select(['columnname', 'tablename'])->from('vtiger_field')
				->where(['fieldname' => 'introduction', 'tabid' => $moduleInstance->getId()]);
			$fieldExists = $query->exists();
			$this->addFields($fields);
			$fieldModel = $moduleInstance->getFieldByName('description');
			if ($fieldModel && !$fieldExists && ($fieldInfo = $query->one())) {
				$descTableName = $fieldModel->getTableName();
				$descTableColumn = $fieldModel->getColumnName();
				$newFieldTable = $fieldInfo['tablename'];
				$newFieldColumn = $fieldInfo['columnname'];
				$query = "UPDATE {$newFieldTable}
					INNER JOIN {$descTableName} ON {$descTableName}.crmid = {$newFieldTable}.knowledgebaseid
					SET {$newFieldTable}.{$newFieldColumn} = {$descTableName}.{$descTableColumn}
					WHERE {$descTableName}.{$descTableColumn} is not null and {$descTableName}.{$descTableColumn} <> ''";
				$result = $db->createCommand($query)->execute();
				if ($result) {
					$this->log("[Info] Copy data from {$descTableName}.{$descTableColumn} to {$newFieldTable}.{$newFieldColumn} | {$result}");
				}
				$this->removeField($fieldModel);
				$this->removeBlock($moduleInstance->getId(), 'LBL_DESCRIPTION_BLOCK');
				$this->removeBlock($moduleInstance->getId(), 'LBL_ATTENTION_BLOCK');

				$moduleInstance->setRelatedList($moduleInstance, 'LBL_RELATED_KNOWLEDGE_BASES', 'SELECT', 'getManyToMany');
				$this->setRelations([
					['type' => 'add', 'data' => [614, 'HelpDesk', 'KnowledgeBase', 'getRelatedList', 24, 'KnowledgeBase', 0, 'ADD,SELECT', 0, 0, 0, 'RelatedTab']],
					['type' => 'add', 'data' => [611, 'KnowledgeBase', 'HelpDesk', 'getRelatedList', 3, 'HelpDesk', 0, 'SELECT', 0, 0, 0, 'RelatedTab,DetailBottom']],
					['type' => 'add', 'data' => [612, 'KnowledgeBase', 'Project', 'getRelatedList', 4, 'Project', 0, 'SELECT', 0, 0, 0, 'RelatedTab,DetailBottom']],
					['type' => 'add', 'data' => [616, 'Project', 'KnowledgeBase', 'getRelatedList', 15, 'KnowledgeBase', 0, 'ADD,SELECT', 0, 0, 0, 'RelatedTab']],
					['type' => 'update', 'data' => [485, 'KnowledgeBase', 'Documents', 'getAttachments', 2, 'Documents', 0, 'ADD,SELECT', 0, 0, 0, 'RelatedTab,DetailBottom']],
					['type' => 'update', 'data' => [605, 'KnowledgeBase', 'KnowledgeBase', 'getManyToMany', 1, 'LBL_RELATED_KNOWLEDGE_BASES', 0, 'SELECT', 0, 0, 0, 'RelatedTab,DetailBottom']],
				]);
			}
		} catch (\Throwable $ex) {
			$this->log('[ERROR] ' . $ex->__toString());
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateFaq()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$importerType = new \App\Db\Importers\Base();
		$queryBuilder = $db->getSchema()->getQueryBuilder();
		$moduleInstance = \Vtiger_Module_Model::getInstance('Faq');
		$mapp = ['question' => 'introduction', 'faq_answer' => 'content', 'faqcategories' => 'category'];
		$templateId = null;
		$fieldModel = $moduleInstance->getFieldByName('faqcategories');
		if ($fieldModel && 'picklist' === $fieldModel->getFieldDataType()) {
			$oldValue = $fieldModel->getPicklistValues(true);
			$treeData = [];
			foreach (array_keys($oldValue) as $value) {
				$treeData[] = [0, $value, 'T1', 'T1', 0, $value, '{"loaded":"1","opened":false,"selected":false,"disabled":false}', ''];
			}
			$templateId = $this->setTree([
				'column' => 'category',
				'base' => [30, 'Category', $moduleInstance->getId(), 0],
				'data' => $treeData
			]);
		}

		$fields = [
			'subject' => [15, 2794, 'subject', 'vtiger_faq', 2, 1, 'subject', 'FL_SUBJECT', 0, 2, '', '255', 1, 37, 1, 'V~O~LE~255', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0,
				'type' => $importerType->stringType(255)->defaultValue(''), 'blockLabel' => 'LBL_FAQ_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Faq'],

			'content' => [15, 2795, 'content', 'vtiger_faq', 1, 300, 'content', 'FL_CONTENT', 0, 2, '', '16777215', 1, 442, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0,
				'type' => $importerType->db->getSchema()->createColumnSchemaBuilder('MEDIUMTEXT'), 'blockLabel' => 'LBL_ARTICLE', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Faq', 'blockID' => $this->getBlockId($moduleInstance->getId(), 'LBL_ARTICLE', ['label' => 'LBL_ARTICLE', 'display_status' => 2])],

			'category' => [15, 2796, 'category', 'vtiger_faq', 1, 302, 'category', 'FL_CATEGORY', 0, 2, '', '30', 3, 37, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, $templateId, null, 0, 0, 0,
				'type' => $importerType->stringType(30)->defaultValue(''), 'blockLabel' => 'LBL_FAQ_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Faq'],

			'featured' => [15, 2797, 'featured', 'vtiger_faq', 1, 56, 'featured', 'FL_FEATURED', 0, 2, '', '-128,127', 6, 37, 1, 'C~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0,
				'type' => $importerType->tinyInteger(1)->defaultValue(0), 'blockLabel' => 'LBL_FAQ_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Faq'],

			'introduction' => [15, 2798, 'introduction', 'vtiger_faq', 1, 300, 'introduction', 'FL_INTRODUCTION', 0, 2, '', '65535', 2, 442, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0,	'type' => $importerType->text(), 'blockLabel' => 'LBL_ARTICLE', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Faq', 'blockID' => $this->getBlockId($moduleInstance->getId(), 'LBL_ARTICLE')],

			'knowledgebase_view' => [15, 2799, 'knowledgebase_view', 'vtiger_faq', 1, 16, 'knowledgebase_view', 'FL_VIEWS', 0, 2, '', '255', 4, 37, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->stringType(255)->defaultValue(''), 'blockLabel' => 'LBL_FAQ_INFORMATION', 'picklistValues' => ['PLL_PAGE', 'PLL_PRESENTATION'], 'relatedModules' => [], 'moduleName' => 'Faq'],

			'accountid' => [15, 2821, 'accountid', 'vtiger_faq', 1, 10, 'accountid', 'FL_ACCOUNT', 0, 2, '', '4294967295', 8, 37, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(11)->unsigned()->defaultValue(0), 'blockLabel' => 'LBL_FAQ_INFORMATION', 'picklistValues' => [], 'relatedModules' => ['Accounts'], 'moduleName' => 'Faq']
		];

		$transaction = $db->beginTransaction();
		try {
			$blockId = $this->getBlockId($moduleInstance->getId(), 'LBL_COMMENT_INFORMATION');
			if ($blockId) {
				$dataToUpdate = [
					['vtiger_blocks', ['blocklabel' => 'LBL_CUSTOM_INFORMATION'], ['tabid' => \App\Module::getModuleId('Faq'), 'blocklabel' => 'LBL_COMMENT_INFORMATION']],
					['vtiger_field', ['block' => $blockId], ['fieldname' => ['private', 'was_read', 'shownerid', 'modifiedby', 'modifiedtime', 'createdtime', 'faq_no'], 'block' => $this->getBlockId($moduleInstance->getId(), 'LBL_FAQ_INFORMATION')]],
					['vtiger_field', ['fieldlabel' => 'FL_STATUS'], ['fieldname' => 'faqstatus', 'tablename' => 'vtiger_faq']],
				];
				\App\Db\Updater::batchUpdate($dataToUpdate);
			}
			$tableName = $moduleInstance->basetable;
			foreach ($mapp as $fromField => $toField) {
				$data = [];
				if ($fieldModel = $moduleInstance->getFieldByName($fromField)) {
					if ('category' === $toField) {
						$dataCategory = [];
						$dataReader = (new \App\Db\Query())->select(['name', 'tree'])->from('vtiger_trees_templates_data')->where(['templateid' => $templateId])->createCommand()->query();
						while ($row = $dataReader->read()) {
							$dataCategory[] = [$fieldModel->getTableName(), [$fieldModel->getColumnName() => $row['tree']], [$fieldModel->getColumnName() => $row['name']]];
						}
						$dataReader->close();
						\App\Db\Updater::batchUpdate($dataCategory);
					}
					$type = $queryBuilder->getColumnType($fields[$toField]['type']);
					$db->createCommand("ALTER TABLE {$fieldModel->getTableName()} CHANGE {$fieldModel->getColumnName()} {$toField} {$type};")->execute();
					$fieldData = $fields[$toField];
					$data = ['vtiger_field', ['columnname' => $fieldData[2], 'generatedtype' => $fieldData[4], 'uitype' => $fieldData[5], 'fieldname' => $fieldData[6], 'fieldlabel' => $fieldData[7], 'readonly' => $fieldData[8], 'presence' => $fieldData[9], 'defaultvalue' => $fieldData[10], 'maximumlength' => $fieldData[11], 'displaytype' => $fieldData[14], 'typeofdata' => $fieldData[15], 'quickcreate' => $fieldData[16], 'quickcreatesequence' => $fieldData[17], 'info_type' => $fieldData[18], 'masseditable' => $fieldData[19], 'helpinfo' => $fieldData[20], 'summaryfield' => $fieldData[21], 'fieldparams' => $fieldData[22], 'header_field' => $fieldData[23], 'maxlengthtext' => $fieldData[24], 'maxwidthcolumn' => $fieldData[25]],
						['fieldid' => $fieldModel->getId()]];
					if (!empty($fieldData['blockID'])) {
						$data[1]['block'] = $fieldData['blockID'];
					}
					\App\Db\Updater::batchUpdate([$data]);
					$this->removeField($fieldModel, $toField);
				}
			}
			if (($fieldModel = $moduleInstance->getFieldByName('comments')) && !$fieldModel->isActiveField()) {
				$this->removeField($fieldModel);
			}
			$this->dropTableIfNotUse('vtiger_faqcomments', ['tablename' => 'vtiger_faqcomments'], true);
			$this->addFields($fields);
			$dbIndexes = $db->getTableKeys($tableName);
			if (!isset($dbIndexes['search']) && 'mysql' === $db->getDriverName()) {
				$db->createCommand("ALTER TABLE {$tableName} ADD FULLTEXT KEY `search` (`subject`,`content`,`introduction`);")->execute();
			}
			$this->dropIndex([$tableName => ['faq_id_idx']]);

			$moduleModel = Vtiger_Module_Model::getInstance('Faq');
			$moduleModel->setRelatedList($moduleModel, 'LBL_RELATED_FAQ', 'SELECT', 'getManyToMany');
			$this->setRelations([
				['type' => 'add', 'data' => [619, 'Accounts', 'Faq', 'getDependentsList', 37, 'Faq', 0, 'ADD', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [609, 'Faq', 'HelpDesk', 'getRelatedList', 3, 'HelpDesk', 0, 'SELECT', 0, 0, 0, 'RelatedTab,DetailBottom']],
				['type' => 'add', 'data' => [610, 'Faq', 'Project', 'getRelatedList', 4, 'Project', 0, 'SELECT', 0, 0, 0, 'RelatedTab,DetailBottom']],
				['type' => 'add', 'data' => [613, 'HelpDesk', 'Faq', 'getRelatedList', 23, 'Faq', 0, 'ADD,SELECT', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [615, 'Project', 'Faq', 'getRelatedList', 14, 'Faq', 0, 'ADD,SELECT', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [89, 'Faq', 'Documents', 'getAttachments', 1, 'Documents', 0, 'add,select', 0, 0, 0, 'RelatedTab,DetailBottom']],
				['type' => 'update', 'data' => [608, 'Faq', 'Faq', 'getManyToMany', 2, 'LBL_RELATED_FAQ', 0, 'SELECT', 0, 0, 0, 'RelatedTab,DetailBottom']]
			]);

			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			$this->log('[ERROR] ' . $ex->__toString() . " ALTER TABLE {$fieldModel->getTableName()} CHANGE {$fieldModel->getColumnName()} {$toField} {$type};");
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function getBlockId($moduleId, $blockLabel, $blockData = null)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " | {$moduleId}, {$blockLabel} " . \gettype($blockData) . ' ' . date('Y-m-d H:i:s'));

		$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => $blockLabel, 'tabid' => $moduleId])->scalar();
		if (!$blockId && $blockData) {
			$blockInstance = new \vtlib\Block();
			foreach ($blockData as $key => $value) {
				$blockInstance->{$key} = $value;
			}
			\Vtiger_Module_Model::getInstance($moduleId)->addBlock($blockInstance);
			$blockId = $blockInstance->id;
			foreach ($blockData as $key => $value) {
				if ($blockInstance->{$key} !== $value) {
					$blockInstance->{$key} = $value;
					$update = true;
				}
			}
			if (!empty($update)) {
				\App\Db::getInstance()->createCommand()->update(\vtlib\Block::$baseTable, [
					'blocklabel' => $blockInstance->label,
					'sequence' => $blockInstance->sequence,
					'show_title' => $blockInstance->showtitle,
					'visible' => $blockInstance->visible,
					'create_view' => $blockInstance->increateview,
					'edit_view' => $blockInstance->ineditview,
					'detail_view' => $blockInstance->indetailview,
					'display_status' => $blockInstance->display_status,
					'iscustom' => $blockInstance->iscustom,
				], ['blockid' => $blockId])->execute();
			}
		}

		$this->log(__METHOD__ . " | {$blockId}" . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return $blockId;
	}

	private function updatePayments()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$importerType = new \App\Db\Importers\Base();
		$queryBuilder = $db->getSchema()->getQueryBuilder();
		$moduleInstance = \Vtiger_Module_Model::getInstance('PaymentsIn');

		$transaction = $db->beginTransaction();
		try {
			$fieldModel = $moduleInstance->getFieldByName('paymentscurrency');
			if ($fieldModel) {
				$newField = 'currency_id';
				$importerType = new \App\Db\Importers\Base();
				$queryBuilder = $db->getSchema()->getQueryBuilder();
				$type = $queryBuilder->getColumnType($importerType->integer(10));
				$db->createCommand("ALTER TABLE {$fieldModel->getTableName()} CHANGE {$fieldModel->getColumnName()} {$newField} {$type};")->execute();
				$db->createCommand()->update('vtiger_field', ['columnname' => $newField, 'fieldname' => $newField, 'typeofdata' => 'V~M'], ['fieldid' => $fieldModel->getId()])->execute();
				$this->removeField($fieldModel, $newField);
			}
			$fields = [
				'payment_system' => [
					79, 2802, 'payment_system', 'vtiger_paymentsin', 1, 16, 'payment_system', 'FL_PAYMENT_SYSTEM', 1, 2, '', '64', 5, 251, 10, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->stringType(64), 'blockLabel' => 'LBL_PAYMENT_INFORMATION', 'picklistValues' => ['PLL_REDSYS', 'PLL_DOTPAY'], 'relatedModules' => [], 'moduleName' => 'PaymentsIn'
				],
				'transaction_id' => [
					79, 2803, 'transaction_id', 'vtiger_paymentsin', 1, 1, 'transaction_id', 'FL_TRANSACTION', 1, 2, '', '255', 6, 251, 10, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_PAYMENT_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'PaymentsIn'
				],
				'ssingleordersid' => [
					79, 2804, 'ssingleordersid', 'vtiger_paymentsin', 1, 10, 'ssingleordersid', 'FL_ORDER', 1, 2, '', '-2147483648,2147483647', 7, 251, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_PAYMENT_INFORMATION', 'picklistValues' => [], 'relatedModules' => ['SSingleOrders'], 'moduleName' => 'PaymentsIn'
				],
				'finvoiceid' => [
					79, 2805, 'finvoiceid', 'vtiger_paymentsin', 1, 10, 'finvoiceid', 'FL_INVOICE', 1, 2, '', '-2147483648,2147483647', 8, 251, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_PAYMENT_INFORMATION', 'picklistValues' => [], 'relatedModules' => ['FInvoice'], 'moduleName' => 'PaymentsIn'
				],
				'paymentsvalue' => [
					79, 1625, 'paymentsvalue', 'vtiger_paymentsin', 1, 72, 'paymentsvalue', 'LBL_PAYMENTSVALUE', 0, 2, '', '1.0E+22', 1, 251, 1, 'N~M', 1, 1, 'BAS', 0, '', 0, '', null, 0, 0, 0, 'type' => $importerType->decimal(25, 3), 'blockLabel' => 'LBL_PAYMENT_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'PaymentsIn'
				],
			];
			$fieldName = 'paymentsvalue';
			if ($fieldModel = $moduleInstance->getFieldByName($fieldName)) {
				$fieldData = $fields[$fieldName];
				unset($fields[$fieldName]);
				if ((int) $fieldModel->getUIType() !== $fieldData[5]) {
					$db->createCommand()->update('vtiger_field', ['uitype' => $fieldData[5], 'typeofdata' => $fieldData[15]], ['fieldid' => $fieldModel->getId()])->execute();
				}
			}
			$this->addFields($fields);
			\App\EventHandler::registerHandler('EntityAfterSave', 'PaymentsIn_PaymentsInHandler_Handler', $moduleInstance->getName());
			$this->setRelations([
				['type' => 'add', 'data' => [617, 'SSingleOrders', 'PaymentsIn', 'getDependentsList', 6, 'PaymentsIn', 0, 'ADD', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [618, 'FInvoice', 'PaymentsIn', 'getDependentsList', 4, 'PaymentsIn', 0, 'ADD', 0, 0, 0, 'RelatedTab']]
			]);
			$statusField = $moduleInstance->getFieldByName('paymentsin_status');
			$fieldValues = \App\Fields\Picklist::getValuesName($statusField->getName());
			$picklistMapp = ['Created' => 'PLL_CREATED', 'Denied' => 'PLL_DENIED', 'Paid' => 'PLL_PAID'];
			foreach ($picklistMapp as $oldValue => $newValue) {
				if (!\in_array($oldValue, $fieldValues)) {
					continue;
				}
				$tableName = "vtiger_{$statusField->getName()}";
				$db->createCommand()->update($tableName, [$statusField->getName() => $newValue], [$statusField->getName() => $oldValue])->execute();
				$db->createCommand()->update($statusField->getTableName(), [$statusField->getColumnName() => $newValue], [$statusField->getColumnName() => $oldValue])->execute();
			}
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			$this->log('[ERROR] ' . $ex->__toString());
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updatePaymentsOut()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$importerType = new \App\Db\Importers\Base();
		$queryBuilder = $db->getSchema()->getQueryBuilder();
		$moduleInstance = \Vtiger_Module_Model::getInstance('PaymentsOut');

		$transaction = $db->beginTransaction();
		try {
			$fieldModel = $moduleInstance->getFieldByName('paymentscurrency');
			if ($fieldModel) {
				$newField = 'currency_id';
				$importerType = new \App\Db\Importers\Base();
				$queryBuilder = $db->getSchema()->getQueryBuilder();
				$type = $queryBuilder->getColumnType($importerType->integer(10));
				$db->createCommand("ALTER TABLE {$fieldModel->getTableName()} CHANGE {$fieldModel->getColumnName()} {$newField} {$type};")->execute();
				$db->createCommand()->update('vtiger_field', ['columnname' => $newField, 'fieldname' => $newField, 'typeofdata' => 'V~M'], ['fieldid' => $fieldModel->getId()])->execute();
				$this->removeField($fieldModel, $newField);
			}
			$fields = [
				'paymentsvalue' => [
					80, 1641, 'paymentsvalue', 'vtiger_paymentsout', 1, 72, 'paymentsvalue', 'LBL_PAYMENTSVALUE', 0, 2, '', '1.0E+22', 1, 253, 1, 'N~M', 1, null, 'BAS', 0, '', 0, '', null, 0, 0, 0, 'type' => $importerType->decimal(25, 3), 'blockLabel' => 'LBL_PAYMENT_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'PaymentsOut'
				],
			];
			$fieldName = 'paymentsvalue';
			if ($fieldModel = $moduleInstance->getFieldByName($fieldName)) {
				$fieldData = $fields[$fieldName];
				unset($fields[$fieldName]);
				if ((int) $fieldModel->getUIType() !== $fieldData[5]) {
					$db->createCommand()->update('vtiger_field', ['uitype' => $fieldData[5], 'typeofdata' => $fieldData[15]], ['fieldid' => $fieldModel->getId()])->execute();
				}
			}
			if ($fields) {
				$this->addFields($fields);
			}
			\App\EventHandler::registerHandler('EntityAfterSave', 'PaymentsIn_PaymentsInHandler_Handler', $moduleInstance->getName());
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			$this->log('[ERROR] ' . $ex->__toString());
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateInvoice()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$importerType = new \App\Db\Importers\Base();
		$queryBuilder = $db->getSchema()->getQueryBuilder();
		$moduleInstance = \Vtiger_Module_Model::getInstance('FInvoice');

		$transaction = $db->beginTransaction();
		try {
			$mapp = ['finvoice_paymentstatus' => 'payment_status'];
			$fields = [
				'ssalesprocessesid' => [
					95, 2786, 'ssalesprocessesid', 'u_yf_finvoice', 1, 10, 'ssalesprocessesid', 'FL_OPPORTUNITY', 0, 2, '', null, 12, 310, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_BASIC_DETAILS', 'picklistValues' => [], 'relatedModules' => ['SSalesProcesses'], 'moduleName' => 'FInvoice'
				],
				'projectid' => [
					95, 2787, 'projectid', 'u_yf_finvoice', 1, 10, 'projectid', 'FL_PROJECT', 0, 2, '', null, 13, 310, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_BASIC_DETAILS', 'picklistValues' => [], 'relatedModules' => ['Project'], 'moduleName' => 'FInvoice'
				],
				'payment_status' => [
					95, 2825, 'payment_status', 'u_yf_finvoice', 1, 15, 'payment_status', 'FL_PAYMENT_STATUS', 1, 2, 'PLL_NOT_PAID', '255', 14, 310, 10, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_BASIC_DETAILS', 'picklistValues' => ['PLL_NOT_PAID', 'PLL_UNDERPAID', 'PLL_PAID', 'PLL_OVERPAID'], 'relatedModules' => [], 'moduleName' => 'FInvoice'
				],
			];

			foreach ($mapp as $fromField => $toField) {
				$data = [];
				if ($fieldModel = $moduleInstance->getFieldByName($fromField)) {
					$type = $queryBuilder->getColumnType($fields[$toField]['type']);
					$db->createCommand("ALTER TABLE {$fieldModel->getTableName()} CHANGE {$fieldModel->getColumnName()} {$toField} {$type};")->execute();
					$fieldData = $fields[$toField];
					$data[] = ['vtiger_field', ['columnname' => $fieldData[2], 'generatedtype' => $fieldData[4], 'uitype' => $fieldData[5], 'fieldname' => $fieldData[6], 'fieldlabel' => $fieldData[7], 'readonly' => $fieldData[8], 'presence' => $fieldData[9], 'defaultvalue' => $fieldData[10], 'maximumlength' => $fieldData[11], 'displaytype' => $fieldData[14], 'typeofdata' => $fieldData[15], 'quickcreate' => $fieldData[16], 'quickcreatesequence' => $fieldData[17], 'info_type' => $fieldData[18], 'masseditable' => $fieldData[19], 'helpinfo' => $fieldData[20], 'summaryfield' => $fieldData[21], 'fieldparams' => $fieldData[22], 'header_field' => $fieldData[23], 'maxlengthtext' => $fieldData[24], 'maxwidthcolumn' => $fieldData[25]], ['fieldid' => $fieldModel->getId()]];
					\App\Db\Updater::batchUpdate($data);
					$this->removeField($fieldModel, $toField);
					if ('payment_status' === $toField) {
						$dataUpdate = [];
						foreach (['PLL_AWAITING_PAYMENT' => 'PLL_NOT_PAID', 'PLL_PARTIALLY_PAID' => 'PLL_UNDERPAID', 'PLL_FULLY_PAID' => 'PLL_PAID'] as $oldValue => $newValue) {
							$dataUpdate[] = [$fieldModel->getTableName(), [$toField => $newValue], [$toField => $oldValue]];
						}
						\App\Db\Updater::batchUpdate($dataUpdate);
					}
				}
			}

			$this->addFields($fields);
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			$this->log('[ERROR] ' . $ex->__toString());
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateProducts()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$importerType = new \App\Db\Importers\Base();

		$transaction = $db->beginTransaction();
		try {
			$moduleInstance = \Vtiger_Module_Model::getInstance('Products');
			$fieldModel = $moduleInstance->getFieldByName('category_multipicklist');
			$treeData = [];
			foreach (['Hardware', 'Software', 'CRM Applications', 'Antivirus', 'Backup'] as $value) {
				$treeData[] = [0, $value, 'T1', 'T1', 0, $value, '{"loaded":"1","opened":false,"selected":false,"disabled":false}', ''];
			}
			$tree = [
				'column' => $fieldModel->getColumnName(),
				'base' => [30, 'LBL_MULTICATEGORY', $moduleInstance->getId(), 0],
				'data' => $treeData
			];
			if ($fieldModel && !$fieldModel->isActiveField()) {
				$templateId = $this->setTree($tree);
				$db->createCommand()->update('vtiger_field', ['presence' => 2, 'fieldparams' => $templateId], ['fieldid' => $fieldModel->getId()])->execute();
			}
			$fields = [
				'category_multipicklist' => [
					14, 2357, 'category_multipicklist', 'vtiger_products', 1, 309, 'category_multipicklist', 'LBL_CATEGORY_MULTIPICKLIST', 0, 2, null, '65535', 31, 31, 1, 'V~O', 1, null, 'BAS', 1, '', 0, '31', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_PRODUCT_INFORMATION', 'picklistValues' => $tree, 'relatedModules' => [], 'moduleName' => 'Products'
				],
				'purchase' => [
					14, 2817, 'purchase', 'vtiger_products', 1, 360, 'purchase', 'FL_PURCHASE', 0, 2, '', '65535', 6, 32, 1, 'V~O', 2, 3, 'BAS', 0, '', 1, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_PRICING_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Products'
				],
				'unit_price' => [
					14, 193, 'unit_price', 'vtiger_products', 1, 360, 'unit_price', 'Unit Price', 0, 0, '', '65535', 1, 32, 1, 'V~O', 2, 3, 'BAS', 0, '', 1, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_PRICING_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Products'
				],
				'weight' => [
					14, 2824, 'weight', 'vtiger_products', 1, 7, 'weight', 'FL_WEIGHT', 0, 2, '', null, 8, 33, 1, 'NN~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->decimal(11, 3), 'blockLabel' => 'LBL_STOCK_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Products'
				],
				'commissionrate' => [
					14, 194, 'commissionrate', 'vtiger_products', 1, 365, 'commissionrate', 'Commission Rate', 0, 2, '', '99999', 2, 32, 2, 'NN~O', 1, null, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->decimal(8, 3), 'blockLabel' => 'LBL_PRICING_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Products'
				],
			];
			$this->updateProductAndServiceFiels('Products', $fields);
			$this->addFields($fields);
			\App\EventHandler::registerHandler('EntityBeforeSave', 'Products_Calculations_Handler', 'Products,Services', '', 5, true, \App\Module::getModuleId('Products'));
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			$this->log('[ERROR] ' . $ex->__toString());
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateServices()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$importerType = new \App\Db\Importers\Base();
		$transaction = $db->beginTransaction();
		try {
			$fields = [
				'purchase' => [
					35, 2819, 'purchase', 'vtiger_service', 1, 360, 'purchase', 'FL_COST', 0, 2, '', '65535', 4, 92, 1, 'V~O', 2, 3, 'BAS', 0, '', 1, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_PRICING_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Services'
				],
				'unit_price' => [
					35, 575, 'unit_price', 'vtiger_service', 1, 360, 'unit_price', 'Price', 0, 0, '', '65535', 1, 92, 1, 'V~O', 2, 2, 'BAS', 0, '', 1, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_PRICING_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Services'
				],
				'commissionrate' => [
					35, 577, 'commissionrate', 'vtiger_service', 1, 365, 'commissionrate', 'Commission Rate', 0, 2, '', '99999', 2, 92, 2, 'NN~O', 1, null, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->decimal(8, 3), 'blockLabel' => 'LBL_PRICING_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Services'
				],
			];
			$this->updateProductAndServiceFiels('Services', $fields);
			$this->addFields($fields);
			$this->importer->dropTable(['vtiger_productcurrencyrel']);
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			$this->log('[ERROR] ' . $ex->__toString());
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateProductAndServiceFiels($moduleName, $fields)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$queryBuilder = $db->getSchema()->getQueryBuilder();
		$moduleInstance = \Vtiger_Module_Model::getInstance($moduleName);
		$mapp = ['unit_price' => 'multiCurrency', 'commissionrate' => 'advPercentage'];
		foreach ($mapp as $fromField => $dataType) {
			$data = [];
			if (($fieldModel = $moduleInstance->getFieldByName($fromField)) && $dataType !== $fieldModel->getFieldDataType()) {
				$type = $queryBuilder->getColumnType($fields[$fromField]['type']);
				$db->createCommand("ALTER TABLE {$fieldModel->getTableName()} CHANGE {$fieldModel->getColumnName()} {$fromField} {$type};")->execute();
				$fieldData = $fields[$fromField];
				$data[] = ['vtiger_field', ['columnname' => $fieldData[2], 'generatedtype' => $fieldData[4], 'uitype' => $fieldData[5], 'fieldname' => $fieldData[6], 'fieldlabel' => $fieldData[7], 'readonly' => $fieldData[8], 'presence' => $fieldData[9], 'defaultvalue' => $fieldData[10], 'maximumlength' => $fieldData[11], 'displaytype' => $fieldData[14], 'typeofdata' => $fieldData[15], 'quickcreate' => $fieldData[16], 'quickcreatesequence' => $fieldData[17], 'info_type' => $fieldData[18], 'masseditable' => $fieldData[19], 'helpinfo' => $fieldData[20], 'summaryfield' => $fieldData[21], 'fieldparams' => $fieldData[22], 'header_field' => $fieldData[23], 'maxlengthtext' => $fieldData[24], 'maxwidthcolumn' => $fieldData[25]], ['fieldid' => $fieldModel->getId()]];
				\App\Db\Updater::batchUpdate($data);
				$this->removeField($fieldModel, $fromField);
				if ('unit_price' === $fromField) {
					$tableName = $moduleInstance->basetable;
					$index = $moduleInstance->basetableid;
					$dataReader = (new \App\Db\Query())->select(['currency_id', $index])->from($tableName)->createCommand()->query();
					while ($row = $dataReader->read()) {
						$currencyData = [];
						$dataReaderRel = (new \App\Db\Query())->select(['currencyid', 'actual_price'])->from('vtiger_productcurrencyrel')->where(['productid' => $row[$index]])->createCommand()->query();
						while ($rowRel = $dataReaderRel->read()) {
							$currencyData['currencies'][(int) $rowRel['currencyid']]['price'] = $rowRel['actual_price'];
						}
						if ($currencyData) {
							$currencyData['currencyId'] = (int) $row['currency_id'];
						}
						$dataReaderRel->close();
						$db->createCommand()->update($fieldModel->getTableName(), [$fieldModel->getColumnName() => \App\Json::encode($currencyData)], [$index => $row['productid']])->execute();
					}
					$dataReader->close();
					$this->importer->dropColumns([[$tableName, 'currency_id']]);
				}
			}
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateNextData()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();
		$this->addPicklistValues();
		$this->statusActivate('HelpDesk', 'ticketstatus');
		\App\EventHandler::registerHandler('EntityAfterSave', 'Vtiger_RecordFlowUpdater_Handler', 'Project,ProjectMilestone,ProjectTask');
		\App\EventHandler::registerHandler('EntityAfterDelete', 'Vtiger_RecordFlowUpdater_Handler', 'Project,ProjectMilestone,ProjectTask');
		\App\EventHandler::registerHandler('EntityChangeState', 'Vtiger_RecordFlowUpdater_Handler', 'Project,ProjectMilestone,ProjectTask');
		$data = [
			['vtiger_payment_status', ['presence' => 0], ['presence' => 1, 'payment_status' => ['PLL_NOT_PAID', 'PLL_UNDERPAID', 'PLL_PAID', 'PLL_OVERPAID']]]
		];
		\App\Db\Updater::batchUpdate($data);

		$subQuery = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['uitype' => [15, 33]]);
		$dbCommand->delete('vtiger_picklist', ['not in', 'name', $subQuery])->execute();
		$subQuery = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist');
		$dbCommand->delete('vtiger_role2picklist', ['not in', 'picklistid', $subQuery])->execute();

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function statusActivate(string $moduleName, string $fieldName)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		if (!($fieldModel = $moduleModel->getFieldByName($fieldName))) {
			$this->log("[ERROR] field not exists {$moduleName}:{$fieldName}");
			return false;
		}
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$dbCommand = $db->createCommand();
		$transaction = $db->beginTransaction();
		try {
			$params = $fieldModel->getFieldParams();
			$params['isProcessStatusField'] = true;
			$fieldModel->set('fieldparams', \App\Json::encode($params));
			$fieldModel->save();
			$tableStatusHistory = $moduleModel->get('basetable') . '_state_history';
			if (!$db->getTableSchema($tableStatusHistory)) {
				$db->createTable($tableStatusHistory, [
					'id' => \yii\db\Schema::TYPE_UPK,
					'crmid' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_INTEGER, 11),
					'before' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1)->notNull()->defaultValue(0),
					'after' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1)->notNull()->defaultValue(0),
					'date' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TIMESTAMP)->null(),
				]);
				$dbCommand->createIndex($tableStatusHistory . '_crmid_idx', $tableStatusHistory, 'crmid')->execute();
				$dbCommand->addForeignKey('fk_1_' . $tableStatusHistory, $tableStatusHistory, 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT')->execute();
			}
			$tableName = 'vtiger_' . $fieldName;
			$tableSchema = $db->getTableSchema($tableName);
			if (!isset($tableSchema->columns['record_state'])) {
				$dbCommand->addColumn($tableName, 'record_state', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1)->notNull()->defaultValue(0))->execute();
			}
			if (!isset($tableSchema->columns['time_counting'])) {
				$dbCommand->addColumn($tableName, 'time_counting', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1)->notNull()->defaultValue(0))->execute();
			}
			foreach (\App\EventHandler::getAll(false) as $handler) {
				if ('Vtiger_RecordStatusHistory_Handler' === $handler['handler_class']) {
					$modules = $handler['include_modules'] ? \explode(',', $handler['include_modules']) : [];
					if (!\in_array($moduleName, $modules)) {
						$modules[] = $moduleName;
					}
					$dbCommand->update('vtiger_eventhandlers', [
						'is_active' => 1,
						'include_modules' => \implode(',', $modules)
					], ['eventhandler_id' => $handler['eventhandler_id']])->execute();
				}
			}
			if ('ticketstatus' === $fieldName) {
				$dataUpdate = [
					'Open' => ['record_state' => 0, 'time_counting' => 1],
					'In Progress' => ['record_state' => 0, 'time_counting' => 2],
					'Wait For Response' => ['record_state' => 0, 'time_counting' => 3],
					'Closed' => ['record_state' => 2, 'time_counting' => 0],
					'Answered' => ['record_state' => 0, 'time_counting' => 2],
					'Rejected' => ['record_state' => 2, 'time_counting' => 0],
					'PLL_SUBMITTED_COMMENTS' => ['record_state' => 0, 'time_counting' => 2],
					'PLL_FOR_APPROVAL' => ['record_state' => 0, 'time_counting' => 2],
					'PLL_TO_CLOSE' => ['record_state' => 0, 'time_counting' => 2]
				];
				foreach ($dataUpdate as $name => $data) {
					$dbCommand->update($tableName, $data, [$fieldName => $name])->execute();
				}
				$dbCommand->delete('u_yf_picklist_close_state', ['<>', 'fieldid', $fieldModel->getId()])->execute();
				\App\Cache::delete('getLockStatus', $moduleModel->getId());
			}
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			$this->log('[ERROR] ' . $ex->__toString());
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateData()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();

		$data = [
			['vtiger_cron_task', ['handler_file' => 'cron/CardDav.php'], ['handler_file' => 'modules/API/cron/CardDav.php']],
			['vtiger_cron_task', ['handler_file' => 'cron/CalDav.php'], ['handler_file' => 'modules/API/cron/CalDav.php']],
			['vtiger_field', ['uitype' => 99], ['fieldname' => 'password', 'tablename' => 'vtiger_osspasswords', 'uitype' => 1]],
			['vtiger_eventhandlers', ['priority' => 6], ['or',
				['handler_class' => 'ModTracker_ModTrackerHandler_Handler', 'event_name' => ['EntityChangeState', 'EntityAfterLink', 'EntityAfterSave']],
				['handler_class' => 'Vtiger_RecordLabelUpdater_Handler', 'event_name' => ['EntityAfterSave']],
				['handler_class' => 'Vtiger_Workflow_Handler', 'event_name' => ['EntityAfterDelete', 'EntityAfterSave', 'EntityChangeState']]
			]],
			['vtiger_eventhandlers', ['priority' => 5], ['handler_class' => 'Vtiger_SocialMedia_Handler']],
			['vtiger_field', ['displaytype' => 1], ['or',
				['fieldname' => 'projectstatus', 'tablename' => 'vtiger_project', 'displaytype' => 1],
				['fieldname' => 'projectmilestone_status', 'tablename' => 'vtiger_projectmilestone', 'displaytype' => 1]
			]],
			['vtiger_field', ['presence' => 2], ['fieldname' => 'crmactivity', 'tablename' => 'vtiger_entity_stats']],
			['vtiger_field', ['presence' => 2], ['fieldname' => 'private', 'tablename' => 'vtiger_crmentity']],
			['vtiger_field', ['masseditable' => 0], ['fieldname' => ['activitystatus', 'reapeat', 'recurrence'], 'tablename' => 'vtiger_activity']],
			['vtiger_field', ['maximumlength' => '65535'], ['or',
				['uitype' => [305, 300, 33, 309, 342, 69, 21, 19, 311, 315], 'maximumlength' => null],
				['uitype' => [305, 300, 33, 309, 342, 69, 21, 19, 311, 315], 'maximumlength' => ''],
				['tablename' => 'vtiger_modcomments', 'fieldname' => 'parents', 'maximumlength' => null],
				['tablename' => 'vtiger_ossmailview', 'fieldname' => ['bcc_email', 'cc_email', 'from_email', 'from_id', 'reply_to_email', 'subject', 'to_email', 'to_id'], 'maximumlength' => null],
				['tablename' => 'vtiger_paymentsin', 'fieldname' => 'paymentstitle', 'maximumlength' => null]
			]],
			['vtiger_field', ['maximumlength' => '3,64'], ['fieldname' => 'user_name', 'tablename' => 'vtiger_users']],
			['vtiger_field', ['maximumlength' => '16777215'], ['fieldname' => 'content', 'tablename' => 'vtiger_ossmailview']],
			['vtiger_field', ['maximumlength' => '16777215'], ['fieldname' => 'orginal_mail', 'tablename' => 'vtiger_ossmailview']],
			['vtiger_field', ['summaryfield' => 1], ['fieldname' => 'contract_priority', 'tablename' => 'vtiger_servicecontracts']],
			['vtiger_field', ['quickcreate' => 1, 'summaryfield' => 1, 'masseditable' => 2], ['fieldname' => 'progress', 'tablename' => 'vtiger_servicecontracts']],
			['vtiger_settings_blocks', ['sequence' => 16], ['label' => 'LBL_About_YetiForce']],
			['vtiger_relatedlists', ['name' => 'getDependentsList', 'actions' => 'ADD'], ['tabid' => \App\Module::getModuleId('Assets'), 'related_tabid' => \App\Module::getModuleId('HelpDesk')]],
			['vtiger_relatedlists', ['sequence' => 1], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('Calendar'), 'name' => 'getActivities']],
			['vtiger_relatedlists', ['sequence' => 3], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('OSSMailView'), 'name' => 'getEmails']],
			['vtiger_relatedlists', ['sequence' => 2], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('Documents'), 'name' => 'getAttachments']],
			['vtiger_relatedlists', ['sequence' => 5], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('CallHistory'), 'name' => 'getDependentsList']],
			['vtiger_relatedlists', ['sequence' => 6], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('Project'), 'name' => 'getRelatedList']],
			['vtiger_relatedlists', ['sequence' => 7], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('Campaigns'), 'name' => 'getCampaigns']],
			['vtiger_relatedlists', ['sequence' => 8], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('Assets'), 'name' => 'getDependentsList']],
			['vtiger_relatedlists', ['sequence' => 9], ['tabid' => \App\Module::getModuleId('Contacts'), 'related_tabid' => \App\Module::getModuleId('OSSSoldServices'), 'name' => 'getDependentsList']],
			['vtiger_settings_blocks', ['icon' => 'fas fa-home'], ['label' => 'LBL_MENU_SUMMARRY']],
			['vtiger_settings_field', ['sequence' => 8], ['name' => 'LBL_TIMECONTROL_PROCESSES']],
			['vtiger_settings_field', ['sequence' => 5], ['name' => 'LBL_COLORS']],
			['vtiger_settings_field', ['sequence' => 6], ['name' => 'LBL_YETIFORCE_STATUS_HEADER']],
			['vtiger_settings_field', ['sequence' => 5], ['name' => 'License']],
			['vtiger_settings_field', ['sequence' => 4, 'iconpath' => 'fas fa-shopping-cart text-danger', 'linkto' => 'index.php?module=YetiForce&parent=Settings&view=Shop'], ['name' => 'LBL_SHOP_YETIFORCE']],
			['vtiger_settings_field', ['pinned' => 0], []],
			['vtiger_settings_field', ['pinned' => 1], ['name' => ['LBL_ROLES', 'USERGROUPLIST', 'LBL_FIELDS_ACCESS', 'LBL_PICKLIST_EDITOR', 'LBL_CURRENCY_SETTINGS', 'Scheduler', 'LBL_EDIT_FIELDS', 'LBL_PDF', 'LBL_MENU_BUILDER', 'LBL_ARRANGE_RELATED_TABS', 'Mail Scanner', 'LangManagement', 'Search Setup', 'Widgets', 'LBL_MAIL_SMTP']]],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-users-2'], ['name' => 'LBL_USERS', 'iconpath' => 'adminIcon-user']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-modules-2'], ['name' => 'VTLIB_LBL_MODULE_MANAGER', 'iconpath' => 'adminIcon-modules-installation']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-workflows-2'], ['name' => 'LBL_LIST_WORKFLOWS', 'iconpath' => 'adminIcon-triggers']],
			['vtiger_settings_field', ['iconpath' => 'yfi yfi-system-warnings-2'], ['name' => 'LBL_SYSTEM_WARNINGS', 'iconpath' => 'fas fa-exclamation-triangle']],
			['vtiger_field', ['presence' => 2, 'displaytype' => 1, 'uitype' => 15], ['fieldname' => 'salutationtype', 'tablename' => 'vtiger_contactdetails']],
			['vtiger_field', ['uitype' => 1], ['fieldname' => 'firstname', 'tablename' => 'vtiger_contactdetails']],
			['vtiger_field', ['displaytype' => 2], ['displaytype' => 1, 'uitype' => 4]],
			['vtiger_contract_status', ['presence' => 0], ['presence' => 1, 'contract_status' => 'In Progress']],
		];
		\App\Db\Updater::batchUpdate($data);

		$dataReader = (new \App\Db\Query())->select(['task_id', 'workflow_id'])->from('com_vtiger_workflowtasks')->where(['like', 'task', 'VTUpdateClosedTime'])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$dbCommand->delete('com_vtiger_workflowtasks', ['task_id' => $row['task_id']])->execute();
			$dbCommand->delete('com_vtiger_workflowtask_queue', ['task_id' => $row['task_id']])->execute();
			if (!(new \App\Db\Query())->from('com_vtiger_workflowtasks')->where(['workflow_id' => $row['workflow_id']])->exists()) {
				$dbCommand->delete('com_vtiger_workflows', ['workflow_id' => $row['workflow_id']])->execute();
			}
		}
		$dataReader->close();

		$data = [
			['vtiger_settings_field', ['name' => 'LBL_DEFAULT_MODULE_VIEW']],
			['com_vtiger_workflow_tasktypes', ['tasktypename' => 'VTUpdateClosedTime']],
			['vtiger_links', ['linkurl' => 'modules/ModComments/ModCommentsCommon.js']],
			['a_#__taxes_config', ['param' => 'active']],
			['a_#__discounts_config', ['param' => 'active']],
			['vtiger_eventhandlers', ['handler_class' => 'HelpDesk_TicketRangeTime_Handler']]
		];
		$links = [
			['vtiger_links', ['linklabel' => 'Delagated Events/To Dos', 'linktype' => 'DASHBOARDWIDGET', 'linkurl' => 'index.php?module=Home&view=ShowWidget&name=AssignedUpcomingCalendarTasks']],
			['vtiger_links', ['linklabel' => 'Delegated (overdue) Events/ToDos', 'linktype' => 'DASHBOARDWIDGET', 'linkurl' => 'index.php?module=Home&view=ShowWidget&name=AssignedOverdueCalendarTasks']]
		];
		foreach ($links as $linkData) {
			if ($linkId = (new \App\Db\Query())->select(['linkid'])->from($linkData[0])->where($linkData[1])->scalar()) {
				$data[] = ['vtiger_module_dashboard', ['linkid' => $linkId]];
				$data[] = ['vtiger_module_dashboard_widgets', ['linkid' => $linkId]];
			}
		}
		\App\Db\Updater::batchDelete(array_merge($data, $links));

		$serviceContractsId = \App\Module::getModuleId('ServiceContracts');
		$documentsId = \App\Module::getModuleId('Documents');
		\App\Db\Updater::batchInsert([
			['vtiger_settings_blocks',
				[
					'label' => 'LBL_HELP',
					'sequence' => 15,
					'icon' => 'fas fa-life-ring',
					'type' => 0
				], ['label' => 'LBL_HELP']
			]
		]);
		$data = [
			['vtiger_links',
				[
					'tabid' => App\Module::getModuleId('Home'),
					'linktype' => 'DASHBOARDWIDGET',
					'linklabel' => 'LBL_CREATED_BY_ME_BUT_NOT_MINE_OVERDUE_ACTIVITIES',
					'linkurl' => 'index.php?module=Home&view=ShowWidget&name=CreatedNotMineOverdueActivities',
					'linkicon' => '',
					'sequence' => 0
				], ['tabid' => App\Module::getModuleId('Home'), 'linklabel' => 'LBL_CREATED_BY_ME_BUT_NOT_MINE_OVERDUE_ACTIVITIES']
			],
			['vtiger_settings_field',
				[
					'blockid' => (new \App\Db\Query())->select(['blockid'])->from('vtiger_settings_blocks')->where(['label' => 'LBL_HELP'])->scalar(),
					'name' => 'LBL_GITHUB',
					'iconpath' => 'fab fa-github',
					'description' => null,
					'linkto' => 'index.php?module=Help&parent=Settings&view=Index',
					'sequence' => 1,
					'active' => 0,
					'pinned' => 0,
					'admin_access' => null
				], ['name' => 'LBL_GITHUB']
			],
			['vtiger_settings_field',
				[
					'blockid' => (new \App\Db\Query())->select(['blockid'])->from('vtiger_settings_blocks')->where(['label' => 'LBL_LOGS'])->scalar(),
					'name' => 'LBL_SYSTEM_WARNINGS',
					'iconpath' => 'fas fa-exclamation-triangle',
					'description' => null,
					'linkto' => 'index.php?module=Logs&parent=Settings&view=SystemWarnings',
					'sequence' => 5,
					'active' => 0,
					'pinned' => 0,
					'admin_access' => null
				], ['name' => 'LBL_SYSTEM_WARNINGS']
			],
			['vtiger_settings_field',
				[
					'blockid' => (new \App\Db\Query())->select(['blockid'])->from('vtiger_settings_blocks')->where(['label' => 'LBL_PROCESSES'])->scalar(),
					'name' => 'LBL_SLA_POLICY',
					'iconpath' => 'fas fa-door-open',
					'description' => 'LBL_SLA_POLICY',
					'linkto' => 'index.php?module=SlaPolicy&parent=Settings&view=List',
					'sequence' => 7,
					'active' => 0,
					'pinned' => 0,
					'admin_access' => null
				], ['name' => 'LBL_SLA_POLICY']
			],
			['vtiger_settings_field',
				[
					'blockid' => (new \App\Db\Query())->select(['blockid'])->from('vtiger_settings_blocks')->where(['label' => 'LBL_CALENDAR_LABELS_COLORS'])->scalar(),
					'name' => 'LBL_BUSINESS_HOURS',
					'iconpath' => 'fas fa-business-time',
					'description' => 'LBL_BUSINESS_HOURS_DESCRIPTION',
					'linkto' => 'index.php?module=BusinessHours&parent=Settings&view=List',
					'sequence' => 4,
					'active' => 0,
					'pinned' => 0,
					'admin_access' => null
				], ['name' => 'LBL_BUSINESS_HOURS_DESCRIPTION']
			],
			['vtiger_settings_field',
				[
					'blockid' => (new \App\Db\Query())->select(['blockid'])->from('vtiger_settings_blocks')->where(['label' => 'LBL_SECURITY_MANAGEMENT'])->scalar(),
					'name' => 'LBL_VULNERABILITIES',
					'iconpath' => 'yfi yfi-security-errors-2',
					'description' => 'LBL_VULNERABILITIES_DESCRIPTION',
					'linkto' => 'index.php?module=YetiForce&parent=Settings&view=Vulnerabilities',
					'sequence' => 7,
					'active' => 0,
					'pinned' => 0,
					'admin_access' => null
				], ['name' => 'LBL_VULNERABILITIES']
			],
			['vtiger_widgets',
				[
					'tabid' => $serviceContractsId,
					'type' => 'Summary',
					'wcol' => 1,
					'sequence' => 0,
					'data' => '[]'
				], ['tabid' => $serviceContractsId, 'type' => 'Summary']
			],
			['vtiger_widgets',
				[
					'tabid' => $serviceContractsId,
					'type' => 'Updates',
					'label' => 'LBL_UPDATES',
					'wcol' => 1,
					'sequence' => 1,
					'data' => '[]'
				], ['tabid' => $serviceContractsId, 'type' => 'Updates']
			],
			['vtiger_widgets',
				[
					'tabid' => $serviceContractsId,
					'type' => 'Activities',
					'label' => 'Calendar',
					'wcol' => 2,
					'sequence' => 3,
					'data' => '{"limit":5}'
				], ['tabid' => $serviceContractsId, 'type' => 'Activities']
			],
			['vtiger_widgets',
				[
					'tabid' => $serviceContractsId,
					'type' => 'EmailList',
					'label' => 'Emails',
					'wcol' => 1,
					'sequence' => 2,
					'data' => '{"relatedmodule":"OSSMailView","limit":5}'
				], ['tabid' => $serviceContractsId, 'type' => 'EmailList']
			],
			['vtiger_widgets',
				[
					'tabid' => $serviceContractsId,
					'type' => 'RelatedModule',
					'label' => 'Documents',
					'wcol' => 2,
					'sequence' => 5,
					'data' => '{"relatedmodule":' . $documentsId . ',"relatedfields":["' . \App\Module::getModuleId('HelpDesk') . '::ticket_title","' . $documentsId . '::notes_title","' . $documentsId . '::folderid","' . $documentsId . '::filelocationtype","' . $documentsId . '::filename"],"viewtype":"List","limit":5,"action":1,"actionSelect":1,"no_result_text":0,"switchHeader":"-","filter":"-","checkbox":"-"}'
				], ['and', ['tabid' => $serviceContractsId], ['like', 'data', '"relatedmodule":' . $documentsId]]
			],
			['vtiger_widgets',
				[
					'tabid' => $serviceContractsId,
					'type' => 'Comments',
					'label' => 'ModComments',
					'wcol' => 2,
					'sequence' => 4,
					'data' => '{"relatedmodule":"ModComments","limit":5}'
				], ['tabid' => $serviceContractsId, 'type' => 'Comments']
			],
			['vtiger_ssingleorders_source',
				[
					'ssingleorders_source' => 'PLL_PORTAL',
					'sortorderid' => 1,
					'presence' => 1
				], ['ssingleorders_source' => 'PLL_PORTAL']
			],
			['vtiger_ws_fieldtype',
				[
					'uitype' => 319,
					'fieldtype' => 'multiDomain'
				], ['fieldtype' => 'multiDomain']
			],
			['vtiger_ws_fieldtype',
				[
					'uitype' => 360,
					'fieldtype' => 'multiCurrency'
				], ['fieldtype' => 'multiCurrency']
			],
			['vtiger_ws_fieldtype',
				[
					'uitype' => 365,
					'fieldtype' => 'advPercentage'
				], ['fieldtype' => 'advPercentage']
			]
		];

		\App\Db\Updater::batchInsert($data);

		\App\EventHandler::registerHandler('EntityAfterSave', 'Vtiger_RecordStatusHistory_Handler', '', '', 5, false);
		\App\EventHandler::registerHandler('EntityBeforeSave', 'Vtiger_RecordStatusHistory_Handler', '', '', 5, false);

		$fieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tablename' => 'vtiger_modcomments', 'columnname' => 'related_to'])->scalar();
		$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($fieldId);
		$fieldModel->setRelatedModules(['ServiceContracts']);
		\vtlib\Link::addLink(\App\Module::getModuleId('ServiceContracts'), 'DETAILVIEWWIDGET', 'DetailViewBlockCommentWidget', 'block://ModComments:modules/ModComments/ModComments.php');

		$this->addWorflows();
		$this->addMissingInserts();
		$this->updateCVConditions();
		$this->records();

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function updateCVConditions()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->select(['value', 'id', 'field_name', 'module_name'])->from('u_yf_cv_condition')->where(['operator' => 'bw'])->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!\App\Module::getModuleId($row['module_name'])) {
				$this->log("[ERROR] Module not exists: {$row['module_name']}");
				continue;
			}
			$field = \Vtiger_Module_Model::getInstance($row['module_name'])->getFieldByName($row['field_name']);
			if (!$field) {
				$this->log("[ERROR] Field not exists: {$row['module_name']}:{$row['field_name']}");
				continue;
			}
			if ('datetime' === $field->getFieldDataType() && strpos($row['value'], ',')) {
				$values = explode(',', $row['value']);
				foreach ($values as $key => &$data) {
					if (false === strpos($data, ' ')) {
						$data .= 0 === $key ? ' 00:00:00' : ' 23:59:59';
					}
				}
				$values = implode(',', $values);
				if ($values !== $row['value']) {
					$dbCommand->update('u_yf_cv_condition', ['value' => $values], ['id' => $row['id']])->execute();
				}
			}
		}
		$dataReader->close();

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function addMissingInserts()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$moduleWithData = (new \App\Db\Query())->select(['vtiger_tab.name'])->from('vtiger_crmentity')->innerJoin('vtiger_tab', 'vtiger_crmentity.setype = vtiger_tab.name')->where(['vtiger_tab.isentitytype' => 1])->distinct()->column();
		foreach ($moduleWithData as $moduleName) {
			$focus = \CRMEntity::getInstance($moduleName);
			$baseTable = $focus->table_name;
			$baseIndex = $focus->table_index;
			foreach ($focus->tab_name as $relatedTable) {
				if ($relatedTable === $baseTable || 'vtiger_crmentity' === $relatedTable) {
					continue;
				}
				try {
					$relatedTableIndex = $focus->tab_name_index[$relatedTable];
					$query = "INSERT INTO {$relatedTable} ({$relatedTable}.{$relatedTableIndex})
						SELECT {$baseTable}.{$baseIndex} FROM {$baseTable}
						WHERE {$baseTable}.{$baseIndex} NOT IN (SELECT {$relatedTable}.{$relatedTableIndex} FROM {$relatedTable})";
					$result = $db->createCommand($query)->execute();
					if ($result) {
						$this->log("[Info] Added {$result} missing inserts in {$relatedTable} from {$moduleName}");
					}
				} catch (\Throwable $e) {
					$message = '[ERROR] ' . __METHOD__ . ': ' . $e->__toString();
					$this->log($message);
					\App\Log::error($message);
				}
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function addWorflows()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		require_once 'modules/com_vtiger_workflow/VTWorkflowManager.php';
		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		$workflowManager = new VTWorkflowManager();
		$taskManager = new VTTaskManager();

		$workflow[] = [71, 'SSingleOrders', 'It sums up all open sales orders', '[]', 3, null, 'basic', 6, null, null, null, null, null, null];
		$workflow[] = [72, 'SSingleOrders', 'It sums up all open sales orders', '[]', 5, null, 'basic', 6, null, null, null, null, null, null];
		$workflowTask[] = [140, 71, 'It sums up all open sales orders', 'O:21:"SumFieldFromDependent":10:{s:18:"executeImmediately";b:1;s:8:"contents";N;s:10:"workflowId";i:71;s:7:"summary";s:32:"It sums up all open sales orders";s:6:"active";b:1;s:7:"trigger";N;s:11:"targetField";s:36:"accountid::Accounts::sum_open_orders";s:11:"sourceField";s:9:"sum_gross";s:10:"conditions";a:2:{s:9:"condition";s:2:"OR";s:5:"rules";a:2:{i:0;a:3:{s:9:"fieldname";s:34:"SSingleOrders:ssingleorders_status";s:8:"operator";s:1:"e";s:5:"value";s:76:"PLL_DRAFT##PLL_IN_REALIZATION##PLL_FOR_VERIFICATION##PLL_AWAITING_SIGNATURES";}i:1;a:3:{s:9:"fieldname";s:34:"SSingleOrders:ssingleorders_status";s:8:"operator";s:1:"y";s:5:"value";s:0:"";}}}s:2:"id";i:140;}'];
		$workflowTask[] = [141, 72, 'It sums up all open sales orders', 'O:21:"SumFieldFromDependent":9:{s:18:"executeImmediately";b:1;s:8:"contents";N;s:10:"workflowId";i:72;s:7:"summary";s:32:"It sums up all open sales orders";s:6:"active";b:1;s:7:"trigger";N;s:11:"targetField";s:36:"accountid::Accounts::sum_open_orders";s:11:"sourceField";s:9:"sum_gross";s:10:"conditions";a:2:{s:9:"condition";s:2:"OR";s:5:"rules";a:2:{i:0;a:3:{s:9:"fieldname";s:34:"SSingleOrders:ssingleorders_status";s:8:"operator";s:1:"e";s:5:"value";s:76:"PLL_DRAFT##PLL_IN_REALIZATION##PLL_FOR_VERIFICATION##PLL_AWAITING_SIGNATURES";}i:1;a:3:{s:9:"fieldname";s:34:"SSingleOrders:ssingleorders_status";s:8:"operator";s:1:"y";s:5:"value";s:0:"";}}}}'];

		foreach ($workflow as $record) {
			try {
				$workflowId = (new \App\Db\Query())->select(['workflow_id'])
					->from('com_vtiger_workflows')
					->where(['module_name' => $record[1], 'summary' => $record[2], 'execution_condition' => $record[4]])->scalar();
				if (!$workflowId) {
					$newWorkflow = $workflowManager->newWorkFlow($record[1]);
					$newWorkflow->description = $record[2];
					$newWorkflow->test = $record[3];
					$newWorkflow->executionCondition = $record[4];
					$newWorkflow->defaultworkflow = $record[5];
					$newWorkflow->type = $record[6];
					$newWorkflow->filtersavedinnew = $record[7];
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
				$this->log("[ERROR] {$e->getMessage()} in {$e->getTraceAsString()}");
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function addPicklistValues()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		\App\Cache::clear();
		\App\Cache::staticClear();

		$db = \App\Db::getInstance();
		$updateData = [];
		$fields = [
			['OSSTimeControl', 'PLL_UNPAID_LEAVE', ['color' => '#5E666C'], 'timecontrol_type'],
			['OSSTimeControl', 'PLL_SICK_LEAVE', ['color' => '#9900FF'], 'timecontrol_type']
		];
		foreach ($fields as $info) {
			$fieldName = $info[3];
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
				if ('H1' !== $id) {
					$rolesSelected[] = $id;
				}
			}
			$moduleModel->addPickListValues($fieldModel, $info[1], $rolesSelected);
			$tableName = $moduleModel->getPickListTableName($fieldModel->getName());
			if (isset($info[2]['color']) && \in_array('color', $db->getTableSchema($tableName)->getColumnNames())) {
				$updateData[] = [$tableName, $info[2], [$fieldModel->getName() => $info[1]]];
			}
		}
		$newColors = ['PLL_USERS' => '1baee2', 'PLL_SYSTEM' => 'FF9800'];
		$colors = (new \App\Db\Query())->select(['notification_type', 'color'])->from('vtiger_notification_type')->createCommand()->queryAllByGroup();
		foreach ($newColors as $label => $color) {
			if (!\array_key_exists($label, $colors)) {
				$this->log("[ERROR] . No required value {$label} in the notification_type field");
			} elseif (empty($colors[$label])) {
				$updateData[] = ['vtiger_notification_type', ['color' => $color], ['notification_type' => $label]];
			}
		}

		\App\Db\Updater::batchUpdate($updateData);
		\App\Colors::generate('picklist');
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateScheme()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();

		$importerType = new \App\Db\Importers\Base();
		$db->createCommand()->alterColumn('s_#__auto_record_flow_updater', 'id', $importerType->smallInteger(5)->unsigned()->autoIncrement()->notNull())->execute();

		$dropTable = [];
		$dropCustomTables = [
			'vtiger_currencies' => 'currencyid',
			'vtiger_datashare_relatedmodules' => 'datashare_relatedmodule_id',
			'vtiger_field' => 'fieldid',
			'vtiger_picklist_dependency' => 'id',
			'vtiger_settings_blocks' => 'blockid',
			'vtiger_version' => 'id',
			'vtiger_ws_entity' => 'id',
			'vtiger_ws_operation' => 'operationid'
		];
		foreach (App\Fields\Picklist::getModules() as $pickListTable) {
			$moduleModel = Vtiger_Module_Model::getInstance($pickListTable['tabname']);
			$pickListFields = [];
			foreach ($moduleModel->getFields() as $field) {
				if (\in_array($field->getFieldDataType(), ['picklist', 'multipicklist'])) {
					$pickListFields[] = $field->getName();
				}
			}
			foreach ($pickListFields as $pickListField) {
				$tableName = 'vtiger_' . $pickListField;
				$fieldName = \App\Fields\Picklist::getPickListId($pickListField);
				if (!isset($dropTable[$tableName . $fieldName]) && ($tableToRemove = $this->getTableSeqToRemove($tableName, $fieldName))) {
					$dropTable[$tableName . $fieldName] = $tableToRemove;
				}
			}
		}
		$fields = ['eventstatus', 'progress', 'projecttaskprogress'];
		foreach ($fields as $fieldName) {
			$tableName = 'vtiger_' . $fieldName;
			if ($drop = $this->dropTableIfNotUse($tableName, ['fieldname' => $fieldName, 'uitype' => [16, 15, 33]])) {
				$dropTable = array_merge($dropTable, $drop);
			}
		}
		foreach ($dropCustomTables as $tableName => $fieldName) {
			if ($tableToRemove = $this->getTableSeqToRemove($tableName, $fieldName)) {
				$dropTable[] = $tableToRemove;
			}
		}

		if (!(new \App\Db\Query())->from('vtiger_field')->where(['tablename' => 'vtiger_inventoryproductrel'])->exists()) {
			$dropTable[] = 'vtiger_inventoryproductrel';
			$dropTable[] = 'vtiger_inventoryproductrel_seq';
		}

		$this->importer->dropTable(array_unique($dropTable));

		$tableName = 'a_yf_record_converter';
		$columnScheme = $db->getTableSchema($tableName)->getColumn('id');
		if (!$columnScheme->autoIncrement) {
			$this->importer->logs .= "  > alter column: {$tableName}:{$columnScheme->name} ... ";
			$db->createCommand()->alterColumn($tableName, $columnScheme->name, $importerType->smallInteger(10)->autoIncrement()->notNull())->execute();
			$this->importer->logs .= "done\n";
		}

		$this->dropForeignKeys(['u_yf_modtracker_inv_id_fk' => 'u_yf_modtracker_inv']);
		$base = (new \App\Db\Importers\Base());
		$base->tables = [
			'vtiger_modtracker_basic' => [
				'columns' => [
					'id' => $base->integer(10)->unsigned()->autoIncrement()->notNull(),
					'crmid' => $base->integer(10)->unsigned()->notNull(),
					'module' => $base->stringType(25)->notNull(),
					'whodid' => $base->integer(10)->unsigned()->notNull(),
					'changedon' => $base->dateTime()->notNull(),
					'status' => $base->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'last_reviewed_users' => $base->stringType()->notNull()->defaultValue(''),
				],
				'columns_mysql' => [
					'status' => $base->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_detail' => [
				'columns' => [
					'id' => $base->integer(10)->unsigned()->notNull(),
					'fieldname' => $base->stringType(50)->notNull()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_relations' => [
				'columns' => [
					'id' => $base->integer(10)->unsigned()->notNull(),
					'targetmodule' => $base->stringType(25)->notNull(),
					'targetid' => $base->integer(10)->unsigned()->notNull()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__modtracker_inv' => [
				'columns' => [
					'id' => $base->integer(10)->unsigned()->notNull(),
					'changes' => $base->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			]
		];
		$base->foreignKey = [
			['u_yf_modtracker_inv_id_fk', 'u_yf_modtracker_inv', 'id', 'vtiger_modtracker_basic', 'id', 'CASCADE', null]
		];
		$this->importer->updateTables($base);
		$base->db->getSchema()->getTableSchema('u_yf_modtracker_inv', true);
		$this->importer->updateForeignKey($base);
		$this->removeRoleFromPicklist(['activitytype', 'defaultactivitytype']);
		$this->importer->logs(false);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Drop foreign keys.
	 *
	 * @param array $foreignKeys [$foreignKey=>table,...]
	 */
	public function dropForeignKeys(array $foreignKeys)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$this->importer->logs .= "> start drop foreign keys\n";
		$db = \App\Db::getInstance();
		foreach ($foreignKeys as $keyName => $tableName) {
			$this->importer->logs .= "  > drop foreign key, {$tableName}:{$keyName} ... ";
			$tableSchema = $db->getTableSchema($tableName);
			if ($tableSchema) {
				$keyName = str_replace('#__', $db->tablePrefix, $keyName);
				if (isset($tableSchema->foreignKeys[$keyName])) {
					try {
						$db->createCommand()->dropForeignKey($keyName, $tableName)->execute();
						$this->importer->logs .= "done\n";
					} catch (\Throwable $e) {
						$this->importer->logs .= " | ERROR [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
					}
				} else {
					$this->importer->logs .= " | Info - foreign key not exists\n";
				}
			} else {
				$this->importer->logs .= " | ERROR - table does not exists\n";
			}
		}
		$this->importer->logs .= "# end drop foreign keys\n";

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Drop indexes.
	 *
	 * @param array $tables [$table=>[$index,...],...]
	 */
	public function dropIndex(array $tables)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$this->importer->logs .= "> start drop indexes\n";
		$db = \App\Db::getInstance();
		foreach ($tables as $tableName => $indexes) {
			$dbIndexes = $db->getTableKeys($tableName);
			foreach ($indexes as $index) {
				$this->importer->logs .= "  > drop index, $tableName:$index ... ";
				if (isset($dbIndexes[$index])) {
					try {
						$db->createCommand()->dropIndex($index, $tableName)->execute();
						$this->importer->logs .= "done\n";
					} catch (\Throwable $e) {
						$this->importer->logs .= " | ERROR (12) [{$e->getMessage()}] in \n{$e->getTraceAsString()} !!!\n";
					}
				}
			}
		}
		$this->importer->logs .= "# end drop keys\n";
		$this->importer->logs(false);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function getTableSeqToRemove($tableName, $fieldName)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " | {$tableName} |" . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$remove = $importerField = false;
		$tableNameSeqToDrop = $tableName . '_seq';
		if ($db->isTableExists($tableName)) {
			$tableSchema = $db->getTableSchema($tableName);
			$column = $tableSchema->getColumn($fieldName);
			if ($column) {
				if (!$column->isPrimaryKey) {
					$importerField = (new \App\Db\Importers\Base())->primaryKey(10)->autoIncrement()->notNull();
				} elseif (!$column->autoIncrement) {
					$importerField = (new \App\Db\Importers\Base())->integer(10)->autoIncrement()->notNull();
				}
				if ($importerField) {
					$result = $db->createCommand()->alterColumn($tableName, $fieldName, $importerField)->execute();
					if (!$result || !is_numeric($result)) {
						$this->log("[ERROR] . Column {$fieldName} in the table {$fieldName} can not be modified. {$result}");
					} else {
						$remove = true;
					}
				} else {
					$remove = true;
				}
			} else {
				$this->log("[ERROR] Column not exists. {$tableName}:{$fieldName}");
			}
		} else {
			$this->log("[ERROR] Table not exists. {$tableName}");
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return $remove && $db->isTableExists($tableNameSeqToDrop) ? $tableNameSeqToDrop : '';
	}

	/**
	 * Add fields.
	 *
	 * @param mixed $fields
	 */
	public function addFields($fields = [])
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$importerType = new \App\Db\Importers\Base();
		if (empty($fields)) {
			$fields = [
				[29, 2784, 'default_search_module', 'vtiger_users', 1, 301, 'default_search_module', 'FL_DEFAULT_SEARCH_MODULE', 0, 2, '', null, 0, 437, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->stringType(25), 'blockLabel' => 'LBL_GLOBAL_SEARCH_SETTINGS', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'LBL_GLOBAL_SEARCH_SETTINGS', 'display_status' => 2], 'moduleName' => 'Users'],
				[29, 2785, 'default_search_override', 'vtiger_users', 1, 56, 'default_search_override', 'FL_OVERRIDE_SEARCH_MODULE', 0, 2, '', null, 0, 437, 1, 'V~O', 1, 0, 'BAS', 1, 'Edit,Detail,PreferenceDetail', 0, '', null, 0, 0, 0, 'type' => $importerType->tinyInteger(1), 'blockLabel' => 'LBL_GLOBAL_SEARCH_SETTINGS', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Users'],
				[29, 2831, 'primary_phone', 'vtiger_users', 1, 11, 'primary_phone', 'FL_PRIMARY_PHONE', 0, 2, '', '50', 13, 77, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->stringType(50), 'blockLabel' => 'LBL_USERLOGIN_ROLE', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Users'],

				[6, 2788, 'pricebook_id', 'vtiger_account', 1, 10, 'pricebook_id', 'FL_PRICEBOOK', 0, 2, '', null, 1, 439, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_FOR_THE_PORTAL', 'blockData' => ['label' => 'LBL_FOR_THE_PORTAL', 'display_status' => 2], 'picklistValues' => [], 'relatedModules' => ['PriceBooks'], 'moduleName' => 'Accounts'],
				[6, 2793, 'check_stock_levels', 'vtiger_account', 1, 56, 'check_stock_levels', 'FL_CHECK_STOCK_LEVELS', 0, 2, '', '-128,127', 0, 439, 1, 'C~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->tinyInteger(1), 'blockLabel' => 'LBL_FOR_THE_PORTAL', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Accounts'],
				[6, 2800, 'sum_open_orders', 'vtiger_account', 1, 71, 'sum_open_orders', 'FL_SUM_ORDERS', 0, 2, '', '9999999999999999999', 2, 439, 2, 'N~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => 'decimal(28,8)', 'blockLabel' => 'LBL_FOR_THE_PORTAL', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Accounts'],
				[6, 2822, 'taxes', 'vtiger_account', 1, 303, 'taxes', 'FL_TAXES', 0, 2, '', '65535', 11, 198, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_FINANSIAL_SUMMARY', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'Accounts'],
				[6, 2823, 'accounts_available_taxes', 'vtiger_account', 1, 33, 'accounts_available_taxes', 'FL_AVAILABLE_TAXES', 0, 2, '', '65535', 3, 439, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->text(), 'blockLabel' => 'LBL_FOR_THE_PORTAL', 'picklistValues' => ['LBL_REGIONAL_TAX', 'LBL_GROUP_TAX'], 'relatedModules' => [], 'moduleName' => 'Accounts'],

				[90, 2792, 'istorageaddressid', 'u_yf_ssingleorders', 1, 10, 'istorageaddressid', 'FL_STORAGE', 0, 2, '', null, 15, 284, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'picklistValues' => [], 'relatedModules' => ['IStorages'], 'moduleName' => 'SSingleOrders'],
				[90, 2818, 'ssingleorders_method_payments', 'u_yf_ssingleorders', 1, 16, 'ssingleorders_method_payments', 'FL_METHOD_PAYMENTS', 0, 2, '', '255', 16, 284, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'picklistValues' => ['PLL_REDSYS', 'PLL_DOTPAY', 'PLL_TRANSFER', 'PLL_CASH_ON_DELIVERY'], 'relatedModules' => [], 'moduleName' => 'SSingleOrders'],
				[90, 2826, 'payment_status', 'u_yf_ssingleorders', 1, 15, 'payment_status', 'FL_PAYMENT_STATUS', 1, 2, 'PLL_NOT_PAID', '255', 17, 284, 10, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->stringType(255), 'blockLabel' => 'LBL_SSINGLEORDERS_INFORMATION', 'picklistValues' => ['PLL_NOT_PAID', 'PLL_UNDERPAID', 'PLL_PAID', 'PLL_OVERPAID'], 'relatedModules' => [], 'moduleName' => 'SSingleOrders'],

				[13, 2791, 'parentid', 'vtiger_troubletickets', 1, 10, 'parentid', 'FL_HELP_DESK_PARENT', 0, 0, '', null, 13, 27, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_CUSTOM_INFORMATION', 'picklistValues' => [], 'relatedModules' => ['HelpDesk'], 'moduleName' => 'HelpDesk'],
				[13, 2806, 'response_range_time', 'vtiger_troubletickets', 1, 308, 'response_range_time', 'FL_RESPONSE_RANGE_TIME', 0, 2, '', null, 0, 444, 2, 'I~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(11), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2807, 'solution_range_time', 'vtiger_troubletickets', 1, 308, 'solution_range_time', 'FL_SOLUTION_RANGE_TIME', 0, 2, '', null, 0, 444, 2, 'I~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->integer(11), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2808, 'idle_range_time', 'vtiger_troubletickets', 1, 308, 'idle_range_time', 'FL_IDLE_RANGE_TIME', 0, 2, '', null, 0, 444, 2, 'I~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->integer(11), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2809, 'closing_range_time', 'vtiger_troubletickets', 1, 308, 'closing_range_time', 'FL_CLOSING_RANGE_TIME', 0, 2, '', null, 0, 444, 2, 'I~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->integer(11), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2810, 'response_datatime', 'vtiger_troubletickets', 1, 79, 'response_datatime', 'FL_RESPONSE_DATE_TIME', 0, 2, '', null, 0, 444, 2, 'DT~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->dateTime(), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2811, 'solution_datatime', 'vtiger_troubletickets', 1, 79, 'solution_datatime', 'FL_SOLUTION_DATE_TIME', 0, 2, '', null, 0, 444, 2, 'DT~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->dateTime(), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2812, 'idle_datatime', 'vtiger_troubletickets', 1, 79, 'idle_datatime', 'FL_IDLE_DATE_TIME', 0, 2, '', null, 0, 444, 2, 'DT~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->dateTime(), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2813, 'closing_datatime', 'vtiger_troubletickets', 1, 79, 'closing_datatime', 'FL_CLOSING_DATE_TIME', 0, 2, '', null, 0, 444, 2, 'DT~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->dateTime(), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2814, 'response_expected', 'vtiger_troubletickets', 1, 79, 'response_expected', 'FL_RESPONSE_EXPECTED', 0, 2, '', null, 0, 444, 2, 'DT~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->dateTime(), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2815, 'solution_expected', 'vtiger_troubletickets', 1, 79, 'solution_expected', 'FL_SOLUTION_EXPECTED', 0, 2, '', null, 0, 444, 2, 'DT~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->dateTime(), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2816, 'idle_expected', 'vtiger_troubletickets', 1, 79, 'idle_expected', 'FL_IDLE_DATE_EXPECTED', 0, 2, '', null, 0, 444, 2, 'DT~O', 1, 0, 'BAS', 1, 'Detail', 0, '', null, 0, 0, 0,
					'type' => $importerType->dateTime(), 'blockLabel' => 'BL_RECORD_STATUS_TIMES', 'picklistValues' => [], 'relatedModules' => [], 'blockData' => ['label' => 'BL_RECORD_STATUS_TIMES'], 'moduleName' => 'HelpDesk'],
				[13, 2829, 'sum_time_subordinate', 'vtiger_troubletickets', 1, 7, 'sum_time_subordinate', 'FL_SUM_TIME_SUBORDINATE', 1, 2, '', '99999999', 14, 27, 10, 'NN~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->decimal(10, 2), 'blockLabel' => 'LBL_CUSTOM_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'HelpDesk'],

				[34, 2827, 'description', 'vtiger_crmentity', 1, 300, 'description', 'Description', 0, 2, '', null, 0, 445, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_DESCRIPTION_INFORMATION', 'blockData' => ['label' => 'LBL_DESCRIPTION_INFORMATION'], 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'ServiceContracts'],
				[34, 2828, 'attention', 'vtiger_crmentity', 1, 300, 'attention', 'Attention', 0, 2, '', null, 0, 445, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0, 'type' => $importerType->integer(10), 'blockLabel' => 'LBL_DESCRIPTION_INFORMATION', 'picklistValues' => [], 'relatedModules' => [], 'moduleName' => 'ServiceContracts'],
			];
		}

		foreach ($fields as $field) {
			$moduleId = App\Module::getModuleId($field['moduleName']);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists) {
				$this->log("[INFO] Skip adding field. Module: {$moduleId}-{$field['moduleName']}; field name: {$field[2]}, field exists: {$isExists}");
				continue;
			}
			$blockInstance = false;
			$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => $field['blockLabel'], 'tabid' => $moduleId])->scalar();
			if ($blockId) {
				$blockInstance = \vtlib\Block::getInstance($blockId, $moduleId);
			} elseif (isset($field['blockData'])) {
				$blockInstance = new \vtlib\Block();
				foreach ($field['blockData'] as $key => $value) {
					$blockInstance->{$key} = $value;
				}
				\Vtiger_Module_Model::getInstance($field['moduleName'])->addBlock($blockInstance);
				$blockId = $blockInstance->id;
				$blockInstance = \vtlib\Block::getInstance($blockId, $moduleId);
			}
			if (!$blockInstance &&
			!($blockInstance = reset(Vtiger_Module_Model::getInstance($field['moduleName'])->getBlocks()))) {
				$this->log("[ERROR] No block found to create a field, you will need to create a field manually.
				Module: {$field['moduleName']}, field name: {$field[6]}, field label: {$field[7]}");
				\App\Log::error("No block found ({$field['blockLabel']}) to create a field, you will need to create a field manually.
				Module: {$field['moduleName']}, field name: {$field[6]}, field label: {$field[7]}");
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
			if ($field['picklistValues'] && 302 == $field[5]) {
				$field[22] = $this->setTree($field['picklistValues']);
			}
			$fieldInstance->fieldparams = $field[22];
			$blockInstance->addField($fieldInstance);
			if ($field['picklistValues'] && (15 == $field[5] || 16 == $field[5] || 33 == $field[5])) {
				$fieldInstance->setPicklistValues($field['picklistValues']);
			}
			if ($field['relatedModules'] && 10 == $field[5]) {
				$fieldInstance->setRelatedModules($field['relatedModules']);
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function setTree($tree)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$skipCheckData = false;
		$templateId = (new \App\Db\Query())->select(['templateid'])->from('vtiger_trees_templates')->where(['module' => $tree['base'][2], 'name' => $tree['base'][1]])->scalar();
		if (!$templateId) {
			$dbCommand->insert('vtiger_trees_templates', [
				'name' => $tree['base'][1],
				'module' => $tree['base'][2],
				'access' => $tree['base'][3]
			])->execute();
			$templateId = $db->getLastInsertID('vtiger_trees_templates_templateid_seq');
			$skipCheckData = true;
		}
		foreach ($tree['data'] as $data) {
			if (!$skipCheckData && (new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => $templateId, 'name' => $data[1]])->exists()) {
				continue;
			}
			$dbCommand->insert('vtiger_trees_templates_data', [
				'templateid' => $templateId,
				'name' => $data[1],
				'tree' => $data[2],
				'parentTree' => $data[3],
				'depth' => $data[4],
				'label' => $data[5],
				'state' => $data[6],
				'icon' => $data[7]
			])->execute();
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return $templateId;
	}

	/**
	 * Update fields.
	 */
	private function updateFields()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$dbCommand = \App\Db::getInstance()->createCommand();
		$moduleModel = Vtiger_Module_Model::getInstance('Users');
		foreach ($moduleModel->getFieldsByUiType(16) as $fieldModel) {
			if (!$fieldModel->isMandatory()) {
				$fieldModel->updateTypeofDataFromMandatory('M')->save();
			}
		}
		$moduleByColumn = (new \App\Db\Query())->select(['vtiger_tab.name', 'vtiger_field.fieldname'])->from('vtiger_field')->innerJoin('vtiger_tab', '`vtiger_field`.`tabid` = vtiger_tab.`tabid`')->where(['columnname' => 'closedtime', 'tablename' => 'vtiger_crmentity'])->createCommand()->queryAllByGroup();

		$modules = ['HelpDesk' => ['response_time', 'report_time'], 'SQuoteEnquiries' => ['response_time'], 'SRequirementsCards' => ['response_time'], 'SCalculations' => ['response_time'], 'SQuotes' => ['response_time'], 'SSingleOrders' => ['response_time'], 'SRecurringOrders' => ['response_time'], 'SVendorEnquiries' => ['response_time'], 'Accounts' => ['active'], 'Contacts' => ['active'], 'OSSMailView' => ['rel_mod'], 'KnowledgeBase' => ['attention']];
		$modules = array_merge_recursive($modules, $moduleByColumn);
		foreach ($modules as $moduleName => $fields) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			if (!\is_array($fields)) {
				$fields = [$fields];
			}
			foreach ($fields as $fieldName) {
				if ($fieldModel = $moduleModel->getFieldByName($fieldName)) {
					if (\in_array($fieldName, ['active', 'rel_mod']) || !$fieldModel->isActiveField() || !$this->isExistsValueForField($moduleName, $fieldName)) {
						$this->removeField($fieldModel);
					} else {
						$dbCommand->update('vtiger_field', ['presence' => 1], ['fieldid' => $fieldModel->getId()])->execute();
						$this->log('[Warning] RemoveFields' . __METHOD__ . ': field exists and is in use ' . $fieldModel->getName() . ' ' . $fieldModel->getModuleName());
					}
				}
			}
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function removeField($fieldModel, $newName = false)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " | {$fieldModel->getName()},{$fieldModel->getModuleName()},{$newName} | " . date('Y-m-d H:i:s'));
		try {
			if (false === $newName) {
				$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldModel->getId());
				$fieldInstance->delete();
				if ('vtiger_crmentity' === $fieldModel->getTableName() && !(new \App\Db\Query())->from('vtiger_field')->where(['columnname' => $fieldModel->getColumnName(), 'tablename' => $fieldModel->getTableName()])->exists()) {
					$this->importer->dropColumns([[$fieldModel->getTableName(), $fieldModel->getColumnName()]]);
					$this->importer->logs(false);
				}
			} else {
				$db = \App\Db::getInstance();
				$dbCommand = $db->createCommand();

				$fldModule = $fieldModel->getModuleName();
				$id = $fieldModel->getId();
				$fieldname = $fieldModel->getName();
				$tabId = $fieldModel->getModuleId();

				$dbCommand->delete('a_#__mapped_fields', ['or', ['source' => $id], ['target' => $id]])->execute();
				$dbCommand->update('vtiger_cvcolumnlist', ['field_name' => $newName], ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();
				$dbCommand->delete('u_#__cv_condition', ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();

				if ('picklist' === $fieldModel->getFieldDataType() || 'multipicklist' === $fieldModel->getFieldDataType()) {
					$query = (new \App\Db\Query())->from('vtiger_field')
						->where(['fieldname' => $fieldname])
						->andWhere(['in', 'uitype', [15, 16, 33]]);
					$dataReader = $query->createCommand()->query();
					if (!$dataReader->count()) {
						$dbCommand->dropTable('vtiger_' . $fieldname)->execute();
						if ($db->isTableExists('vtiger_' . $fieldname . '_seq')) {
							$dbCommand->dropTable('vtiger_' . $fieldname . '_seq')->execute();
						}
						$dbCommand->delete('vtiger_picklist', ['name' => $fieldname])->execute();
					}
					$dbCommand->delete('vtiger_picklist_dependency', ['and', ['tabid' => $tabId], ['or', ['sourcefield' => $fieldname], ['targetfield' => $fieldname]]])->execute();
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
			}
		} catch (\Throwable $e) {
			$message = '[ERROR] ' . __METHOD__ . ': ' . $e->__toString();
			$this->log($message);
			\App\Log::error($message);
		}
		\App\Cache::delete('ModuleFields', $fieldModel->getModuleId());
		\App\Cache::staticDelete('ModuleFields', $fieldModel->getModuleId());
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function removeBlock($moduleId, $blockName)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . " | {$moduleId},{$blockName} | " . date('Y-m-d H:i:s'));

		$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['vtiger_blocks.tabid' => $moduleId, 'blocklabel' => $blockName])->scalar();
		if ($blockId && (new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $moduleId, 'block' => $blockId])->exists()) {
			$this->log(__METHOD__ . '[WARNING] Could not delete block, fields exist');
		} elseif (!$blockId) {
			$this->log(__METHOD__ . '[INFO] Block not exists');
		} else {
			\Vtiger_Block_Model::getInstance($blockId)->delete(false);
		}

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function isExistsValueForField($moduleName, $fieldName)
	{
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->permission = false;
		$queryGenerator->setStateCondition('All');
		$queryGenerator->addNativeCondition(['<>', 'vtiger_crmentity.deleted', [0]]);
		$queryGenerator->addCondition($fieldName, '', 'ny');
		return $queryGenerator->createQuery()->exists();
	}

	private function setRelations($ralations = null)
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$dbCommand = \App\Db::getInstance()->createCommand();

		if (null === $ralations) {
			$moduleModel = Vtiger_Module_Model::getInstance('HelpDesk');
			if ($fieldModel = $moduleModel->getFieldByName('pssold_id')) {
				$modules = ['OutsourcedProducts', 'OSSOutsourcedServices'];
				$fieldModel->setRelatedModules($modules);
				foreach ($modules as $module) {
					Vtiger_Module_Model::getInstance($module)->setRelatedList($moduleModel, $moduleModel->getName(), 'ADD', 'getDependentsList');
				}
			}

			$ralations = [
				['type' => 'add', 'data' => [599, 'SSalesProcesses', 'FInvoice', 'getDependentsList', 24, 'FInvoice', 0, 'ADD', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [600, 'Project', 'FInvoice', 'getDependentsList', 13, 'FInvoice', 0, 'ADD', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [601, 'ServiceContracts', 'Assets', 'getRelatedList', 8, 'Assets', 0, 'SELECT', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [602, 'ServiceContracts', 'OSSSoldServices', 'getRelatedList', 9, 'OSSSoldServices', 0, 'SELECT', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [603, 'Assets', 'ServiceContracts', 'getRelatedList', 3, 'ServiceContracts', 0, 'SELECT', 0, 0, 0, 'RelatedTab']],
				['type' => 'add', 'data' => [604, 'OSSSoldServices', 'ServiceContracts', 'getRelatedList', 3, 'ServiceContracts', 0, 'SELECT', 0, 0, 0, 'RelatedTab']],
				['type' => 'update', 'data' => [605, 'OSSSoldServices', 'HelpDesk', 'getDependentsList', 1, 'HelpDesk', 0, 'ADD', 0, 0, 0, 'RelatedTab'], 'where' => ['tabid' => \App\Module::getModuleId('OSSSoldServices'), 'related_tabid' => \App\Module::getModuleId('HelpDesk'), 'name' => 'getRelatedList']],
				['type' => 'update', 'data' => [59, 'Vendors', 'Products', 'getDependentsList', 2, 'Products', 0, 'add', 0, 0, 0, 'RelatedTab'], 'where' => ['tabid' => \App\Module::getModuleId('Vendors'), 'related_tabid' => \App\Module::getModuleId('Products'), 'name' => 'getProducts']],
				['type' => 'add', 'data' => [620, 'Contacts', 'HelpDesk', 'getRelatedList', 4, 'HelpDesk', 0, '', 0, 0, 0, 'RelatedTab']]
			];
		}

		foreach ($ralations as $relation) {
			[, $moduleName, $relModuleName, $name, $sequence, $label, $presence, $actions, $favorites, $creatorDetail, $relationComment, $viewType] = $relation['data'];
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
					'view_type' => $viewType
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
					'view_type' => $viewType
				], $where)->execute();
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Set action mapping.
	 */
	private function actionMapp()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$inventoryModules = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['type' => 1])->column();
		$actions = [
			['type' => 'add', 'name' => 'RecordPdfInventory', 'tabsData' => $inventoryModules],
			['type' => 'add', 'name' => 'SetQtyProducts', 'tabsData' => (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')->where(['name' => ['Products', 'IStorages']])->column()]
		];
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
							'profileid' => $profileId, 'tabid' => $tabId, 'activityid' => $key, 'permission' => $permission
						])->execute();
					}
				}
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$files = [
			'module_record_allocation.php' => 'user_privileges',
			'moduleHierarchy.php' => 'user_privileges',
			'sharedOwner.php' => 'user_privileges',
			'owners_colors.php' => 'user_privileges',
			'cron.php' => 'user_privileges',
			'registration.php' => 'cache'
		];
		$rootDirectory = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		foreach ($files as $file => $dir) {
			$from = $rootDirectory . $dir . DIRECTORY_SEPARATOR . $file;
			if (file_exists($from)) {
				$result = rename($from, "{$rootDirectory}app_data/{$file}");
				if (!$result) {
					$this->log("[ERROR] File transfer error: {$from}");
				}
			} else {
				$this->log("[INFO] Skip move file. File not exists: {$from}");
			}
		}
		$menuRecordModel = new \Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		$this->createConfigFiles();
		\App\Cache::clearAll();
		if ($this->error || false !== strpos($this->importer->logs, ' ERROR ')) {
			$this->stopProcess();
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return true;
	}

	public function stopProcess()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		\App\Module::createModuleMetaFile();
		\App\Cache::clear();
		\App\Cache::clearOpcache();
		try {
			$dbCommand = \App\Db::getInstance()->createCommand();
			$dbCommand->insert('yetiforce_updates', [
				'user' => \Users_Record_Model::getCurrentUserModel()->get('user_name'),
				'name' => (string) $this->modulenode->label,
				'from_version' => (string) $this->modulenode->from_version,
				'to_version' => (string) $this->modulenode->to_version,
				'result' => true,
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
		file_put_contents('cache/logs/update.log', ob_get_contents(), FILE_APPEND);
		ob_end_clean();
		echo '<div class="modal in" style="display: block;top: 20%;"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">
		<h4 class="modal-title">' . \App\Language::translate('LBL__UPDATING_MODULE', 'Settings:ModuleManager') . '</h4>
		</div><div class="modal-body">Some errors appeared during the update.
		We recommend verifying logs and updating the system once again.</div><div class="modal-footer">
		<a class="btn btn-success" href="' . \App\Config::main('site_URL') . '"></span>' . \App\Language::translate('LBL_HOME') . '<a>
		</div></div></div></div>';

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		exit;
	}

	private function createConfigFiles()
	{
		\App\Cache::resetOpcache();
		\App\Config::set('module', 'OSSMail', 'root_directory', new \Nette\PhpGenerator\PhpLiteral('ROOT_DIRECTORY . DIRECTORY_SEPARATOR'));
		$skip = ['module', 'component'];
		foreach (array_diff(\App\ConfigFile::TYPES, $skip) as $type) {
			(new \App\ConfigFile($type))->create();
		}
		$dirPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Modules';
		if (!is_dir($dirPath)) {
			mkdir($dirPath);
		}
		foreach ((new \DirectoryIterator('modules/')) as $item) {
			if ($item->isDir() && !$item->isDot()) {
				$moduleName = $item->getBasename();
				$filePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
				if (file_exists($filePath)) {
					(new \App\ConfigFile('module', $moduleName))->create();
				}
			}
		}
		$path = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
		$componentsData = require_once "$path";
		foreach ($componentsData as $component => $data) {
			(new \App\ConfigFile('component', $component))->create();
		}
	}
}
