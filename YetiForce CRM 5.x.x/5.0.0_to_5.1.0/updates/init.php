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
			$this->importer->refreshSchema();
			$this->importer->postUpdate();
			$this->importer->dropTable([]);
			$this->importer->logs(false);
		} catch (\Throwable $ex) {
			$this->log($ex->getMessage() . '|' . $ex->getTraceAsString());
			$this->importer->logs(false);
			throw $ex;
		}
		$this->importer->refreshSchema();
		$db->createCommand()->checkIntegrity(true)->execute();
		$this->addFields();
		$this->updateData();
		$this->migrateSubprocess();
		$this->syncPicklist();
		$this->addAutoIncrement();
		$this->importer->dropTable([
			'com_vtiger_workflow_tasktypes_seq',
			'com_vtiger_workflowtasks_entitymethod_seq'
		]);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function addAutoIncrement()
	{
		$db = App\Db::getInstance();
		if (!$db->getTableSchema('com_vtiger_workflow_tasktypes')->getColumn('id')->autoIncrement) {
			$db->createCommand('ALTER TABLE com_vtiger_workflow_tasktypes  ADD KEY (`id`)')->execute();
			$db->createCommand('ALTER TABLE com_vtiger_workflow_tasktypes  CHANGE `id` `id` INT(10) NOT NULL AUTO_INCREMENT FIRST')->execute();
			$db->createCommand('ALTER TABLE com_vtiger_workflowtasks_entitymethod  CHANGE `workflowtasks_entitymethod_id` `workflowtasks_entitymethod_id` INT(10) NOT NULL AUTO_INCREMENT FIRST')->execute();
		}
	}

	private function synchornizationPicklist($moduleName, $fieldName, $map, $mapAutomation, $picklisitToDelete = [])
	{
		$rolesSelected = [];
		$roleRecordList = Settings_Roles_Record_Model::getAll();
		foreach ($roleRecordList as $roleRecord) {
			$rolesSelected[] = $roleRecord->getId();
		}
		$tableName = 'vtiger_' . $fieldName;
		$picklistData = (new App\Db\Query())->select([$fieldName, $fieldName . 'id'])->from($tableName)->createCommand()->queryAllByGroup();
		$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($fieldName, $moduleModel);
		foreach ($map as $newStatus => $oldStatus) {
			$id = null;
			if (empty($oldStatus)) {
				if (!isset($picklistData[$newStatus])) {
					$id = $moduleModel->addPickListValues($fieldModel, $newStatus, $rolesSelected, '', '');
				}
			} else {
				if (isset($picklistData[$oldStatus]) && $id = $picklistData[$oldStatus]) {
					if ($newStatus !== $oldStatus) {
						$moduleModel->renamePickListValues($fieldModel, $oldStatus, $newStatus, $id, '', '');
					}
				}
			}
			if ($id) {
				App\Db::getInstance()->createCommand()->update($tableName, ['presence' => 0, 'automation' => $mapAutomation[$newStatus]], [$fieldName . 'id' => $id])->execute();
			}
		}
		$picklistData = (new App\Db\Query())->select([$fieldName, $fieldName . 'id'])->from($tableName)->createCommand()->queryAllByGroup();
		foreach ($picklisitToDelete as $oldStatus => $toPicklist) {
			if (isset($picklistData[$oldStatus], $picklistData[$toPicklist])) {
				$moduleModel->remove($fieldName, $picklistData[$oldStatus], $picklistData[$toPicklist], $moduleName);
			}
		}
	}

	private function syncPicklist()
	{
		$picklistWithAutomation = ['projectmilestone_status', 'projectstatus', 'projecttaskstatus'];
		$db = App\Db::getInstance();
		$importer = new \App\Db\Importers\Base();
		foreach ($picklistWithAutomation as $fieldname) {
			$tableName = 'vtiger_' . $fieldname;
			if (!$db->getTableSchema($tableName, true)->getColumn('automation')) {
				$db->createCommand()->addColumn($tableName, 'automation', $importer->tinyInteger(1)->defaultValue(0))->execute();
			}
		}
		$this->synchornizationPicklist('ProjectMilestone', 'projectmilestone_status', [
			'PLL_PLANNED' => 'PLL_OPEN',
			'PLL_ON_HOLD' => 'PLL_DEFERRED',
			'PLL_IN_PROGRESSING' => 'PLL_IN_PROGRESS',
			'PLL_IN_APPROVAL' => '',
			'PLL_COMPLETED' => 'PLL_COMPLETED',
			'PLL_CANCELLED' => 'PLL_CANCELLED',
		], [
			'PLL_PLANNED' => '1',
			'PLL_ON_HOLD' => '1',
			'PLL_IN_PROGRESSING' => '1',
			'PLL_IN_APPROVAL' => '1',
			'PLL_COMPLETED' => '2',
			'PLL_CANCELLED' => '2',
		]);
		$this->synchornizationPicklist('Project', 'projectstatus', [
			'PLL_PLANNED' => 'prospecting',
			'PLL_ON_HOLD' => 'on hold',
			'PLL_IN_PROGRESSING' => 'in progress',
			'PLL_IN_APPROVAL' => 'waiting for feedback',
			'PLL_COMPLETED' => 'completed',
			'PLL_CANCELLED' => 'archived',
		], [
			'PLL_PLANNED' => '1',
			'PLL_ON_HOLD' => '1',
			'PLL_IN_PROGRESSING' => '1',
			'PLL_IN_APPROVAL' => '1',
			'PLL_COMPLETED' => '2',
			'PLL_CANCELLED' => '2',
		], [
			'delivered' => 'PLL_COMPLETED',
			'initiated' => 'PLL_PLANNED'
		]);
		$this->synchornizationPicklist('ProjectTask', 'projecttaskstatus', [
			'PLL_PLANNED' => 'Open',
			'PLL_ON_HOLD' => 'Deferred',
			'PLL_SUBMITTED_COMMENTS' => '',
			'PLL_IN_PROGRESSING' => 'In Progress',
			'PLL_IN_APPROVAL' => '',
			'PLL_COMPLETED' => 'Completed',
			'PLL_CANCELLED' => 'Cancelled',
		], [
			'PLL_PLANNED' => '1',
			'PLL_ON_HOLD' => '1',
			'PLL_SUBMITTED_COMMENTS' => '1',
			'PLL_IN_PROGRESSING' => '1',
			'PLL_IN_APPROVAL' => '1',
			'PLL_COMPLETED' => '2',
			'PLL_CANCELLED' => '2',
		]);
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
			[41, 2779, 'estimated_work_time', 'vtiger_projectmilestone', 1, 7, 'estimated_work_time', 'LBL_ESTIMATED_WORK_TIME', 1, 2, '', '999999', 15, 101, 10, 'NN~O', 1, 0, 'BAS', 1, '', 0, '', null, $importerType->decimal('15,2'), 'LBL_PROJECT_MILESTONE_INFORMATION', [], [], 'ProjectMilestone'],
			[43, 2780, 'estimated_work_time', 'vtiger_project', 1, 7, 'estimated_work_time', 'LBL_ESTIMATED_WORK_TIME', 1, 2, '', '999999', 16, 107, 10, 'NN~O', 1, 0, 'BAS', 1, '', 0, '', null, $importerType->decimal('15,2'), 'LBL_PROJECT_INFORMATION', [], [], 'Project'],
			[9, 2781, 'subprocess_sl', 'vtiger_activity', 1, 64, 'subprocess_sl', 'FL_SUBPROCESS_SECOND_LEVEL', 0, 0, '', '4294967295', 6, 87, 1, 'I~O', 1, 0, 'BAS', 1, '', 1, '', null, $importerType->integer(10)->unsigned(), 'LBL_RELATED_TO', [], [], 'Calendar'],
			[51, 2782, 'subprocess_sl', 'vtiger_osstimecontrol', 1, 64, 'subprocess_sl', 'FL_SUBPROCESS_SECOND_LEVEL', 0, 0, '', '4294967295', 15, 129, 1, 'I~O', 1, 0, 'BAS', 1, '', 1, '', null, $importerType->integer(10)->unsigned(), 'LBL_BLOCK', [], [], 'OSSTimeControl'],
			[29, 2783, 'sync_carddav_default_country', 'vtiger_users', 1, 35, 'sync_carddav_default_country', 'LBL_CARDDAV_DEFAULT_COUNTRY', 0, 2, '', '255', 19, 83, 1, 'V~O', 1, 0, 'BAS', 1, 'Edit,Detail,PreferenceDetail', 0, '', null, $importerType->stringType(255), 'LBL_USER_ADV_OPTIONS', [], [], 'Users']
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
			if ($field[26] && (15 == $field[5] || 16 == $field[5] || 33 == $field[5])) {
				$fieldInstance->setPicklistValues($field[26]);
			}
			if ($field[27] && 10 == $field[5]) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function updateData()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));
		$db = App\Db::getInstance();

		$data = [
		];
		\App\Db\Updater::batchDelete($data);
		$data = [
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('FInvoice'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_gross', 'tabid' => \App\Module::getModuleId(('FInvoice'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('SCalculations'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('SQuotes'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_gross', 'tabid' => \App\Module::getModuleId(('SQuotes'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('SSingleOrders'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_gross', 'tabid' => \App\Module::getModuleId(('SSingleOrders'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('FInvoiceProforma'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_gross', 'tabid' => \App\Module::getModuleId(('FInvoiceProforma'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('IGRN'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('ISTDN'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('ISTRN'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('FCorectingInvoice'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_gross', 'tabid' => \App\Module::getModuleId(('FCorectingInvoice'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('IGRNC'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('FInvoiceCost'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_gross', 'tabid' => \App\Module::getModuleId(('FInvoiceCost'))]],
			['vtiger_field', ['uitype' => '317'], ['fieldname' => 'sum_total', 'tabid' => \App\Module::getModuleId(('SVendorEnquiries'))]],
			['vtiger_field', ['defaultvalue' => 'PLL_PLANNED'], ['fieldname' => 'projectstatus', 'tabid' => \App\Module::getModuleId(('Project'))]],
			['vtiger_field', ['defaultvalue' => 'PLL_PLANNED'], ['fieldname' => 'projecttaskstatus', 'tabid' => \App\Module::getModuleId(('ProjectTask'))]],
			['vtiger_field', ['defaultvalue' => 'PLL_PLANNED'], ['fieldname' => 'projectmilestone_status', 'tabid' => \App\Module::getModuleId(('ProjectMilestone'))]],
			['vtiger_language', ['progress' => '100'], ['prefix' => 'en-US']],
			['vtiger_fieldmodulerel', ['sequence' => 2], ['module' => 'HelpDesk', 'relmodule' => 'Vendors']]
		];

		\App\Db\Updater::batchUpdate($data);
		$data = [
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('ProjectMilestone'),
				'related_tabid' => App\Module::getModuleId('Calendar'),
				'name' => 'getActivities',
				'sequence' => 2,
				'label' => 'Activities',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('ProjectMilestone'), 'related_tabid' => App\Module::getModuleId('Calendar')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('FInvoice'),
				'related_tabid' => App\Module::getModuleId('Calendar'),
				'name' => 'getActivities',
				'sequence' => 4,
				'label' => 'Activities',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('FInvoice'), 'related_tabid' => App\Module::getModuleId('Calendar')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('ProjectTask'),
				'related_tabid' => App\Module::getModuleId('Calendar'),
				'name' => 'getActivities',
				'sequence' => 4,
				'label' => 'Activities',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('ProjectTask'), 'related_tabid' => App\Module::getModuleId('Calendar')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('ProjectMilestone'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 4,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('ProjectMilestone'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('SQuoteEnquiries'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 5,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('SQuoteEnquiries'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('SRequirementsCards'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 6,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('SRequirementsCards'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('SCalculations'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 6,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('SCalculations'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('SQuotes'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 5,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('SQuotes'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('SSingleOrders'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 5,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('SSingleOrders'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('SRecurringOrders'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 3,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('SRecurringOrders'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('FInvoice'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 3,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('FInvoice'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_relatedlists', [
				'tabid' => App\Module::getModuleId('SVendorEnquiries'),
				'related_tabid' => App\Module::getModuleId('OSSTimeControl'),
				'name' => 'getDependentsList',
				'sequence' => 3,
				'label' => 'OSSTimeControl',
				'presence' => 0,
				'actions' => 'ADD',
				'favorites' => 0,
				'creator_detail' => 0,
				'relation_comment' => 0,
				'view_type' => 'RelatedTab',
			], ['tabid' => App\Module::getModuleId('SVendorEnquiries'), 'related_tabid' => App\Module::getModuleId('OSSTimeControl')]
			],
			['vtiger_links', [
				'tabid' => App\Module::getModuleId('Home'),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'LBL_UPCOMING_PROJECT_TASKS',
				'linkurl' => 'index.php?module=ProjectTask&view=ShowWidget&name=UpcomingProjectTasks',
				'linkicon' => '',
				'sequence' => 26
			], ['tabid' => App\Module::getModuleId('Home'), 'linklabel' => 'LBL_UPCOMING_PROJECT_TASKS']
			],
			['vtiger_links', [
				'tabid' => App\Module::getModuleId('Home'),
				'linktype' => 'DASHBOARDWIDGET',
				'linklabel' => 'LBL_COMPLETED_PROJECT_TASKS',
				'linkurl' => 'index.php?module=ProjectTask&view=ShowWidget&name=CompletedProjectTasks',
				'linkicon' => '',
				'sequence' => 0
			], ['tabid' => App\Module::getModuleId('Home'), 'linklabel' => 'LBL_COMPLETED_PROJECT_TASKS']
			],
		];
		\App\Db\Updater::batchInsert($data);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	public function migrateSubprocess()
	{
		$start = microtime(true);
		$db = App\Db::getInstance();
		try {
			$db->createCommand('UPDATE vtiger_activity
				INNER JOIN  vtiger_projecttask ON vtiger_activity.subprocess = vtiger_projecttask.projecttaskid
				SET subprocess_sl = vtiger_projecttask.projecttaskid, subprocess = vtiger_projecttask.projectmilestoneid')->execute();
			$db->createCommand('UPDATE vtiger_osstimecontrol
				INNER JOIN  vtiger_projecttask ON vtiger_osstimecontrol.subprocess = vtiger_projecttask.projecttaskid
				SET subprocess_sl = vtiger_projecttask.projecttaskid, subprocess = vtiger_projecttask.projectmilestoneid')->execute();
		} catch (\Throwable $e) {
			$this->log('ERROR: ' . __METHOD__ . '| ' . $e->getMessage() . '|' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		}
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

		$menuRecordModel = new \Settings_Menu_Record_Model();
		$menuRecordModel->refreshMenuFiles();
		$this->createConfigFiles();
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		$this->stopProcess();
		return true;
	}

	public function stopProcess()
	{
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
		</div><div class="modal-body">' . \App\Language::translate('LBL_IMPORTED_UPDATE', 'Settings:ModuleManager') .
			'</div><div class="modal-footer"><a class="btn btn-success" href="index.php?module=LangManagement&parent=Settings&view=Index&block=4&fieldid=53"></span>' . \App\Language::translate('LangManagement', 'Settings:LangManagement') . '<a></div></div></div></div>';
		exit;
	}

	private function createConfigFiles()
	{
		\App\Config::set('module', 'OSSMail', 'root_directory', new \Nette\PhpGenerator\PhpLiteral('ROOT_DIRECTORY . DIRECTORY_SEPARATOR'));
		\App\Config::set('module', 'Project', 'defaultGanttColors', [
			'Project' => [
				'projectstatus' => [
					'PLL_PLANNED' => '#7B1FA2',
					'PLL_IN_PROGRESSING' => '#1976D2',
					'PLL_IN_APPROVAL' => '#F57C00',
					'PLL_ON_HOLD' => '#455A64',
					'PLL_COMPLETED' => '#388E3C',
					'PLL_CANCELLED' => '#616161',
				],
			],
			'ProjectMilestone' => [
				'projectmilestone_status' => [
					'PLL_PLANNED' => '#3F51B5',
					'PLL_IN_PROGRESSING' => '#2196F3',
					'PLL_COMPLETED' => '#4CAF50',
					'PLL_ON_HOLD' => '#607D8B',
					'PLL_CANCELLED' => '#9E9E9E',
				],
			],
			'ProjectTask' => [
				'projecttaskstatus' => [
					'PLL_PLANNED' => '#7986CB',
					'PLL_IN_PROGRESSING' => '#64B5F6',
					'PLL_COMPLETED' => '#81C784',
					'PLL_ON_HOLD' => '#90A4AE',
					'PLL_CANCELLED' => '#E0E0E0',
				],
			]
		]);
		$skip = ['module', 'component'];
		foreach (array_diff(\App\ConfigFile::TYPES, $skip) as $type) {
			(new \App\ConfigFile($type))->create();
		}
		$dirPath = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Modules';
		if (!is_dir($dirPath)) {
			mkdir($dirPath);
		}
		foreach ((new \DirectoryIterator('modules/')) as $item) {
			if ($item->isDir() && !in_array($item->getBasename(), ['.', '..'])) {
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
