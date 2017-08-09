<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (yetiforce.com)
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * YetiForceUpdate Class
 */
class YetiForceUpdate
{

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
	 * Cron list
	 * @var string[] 
	 */
	private $cronAction = [];

	/**
	 * DbImporter
	 * @var DbImporter 
	 */
	private $importer;

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
	 * Preupdate
	 */
	public function preupdate()
	{
		return true;
	}

	/**
	 * Update
	 */
	public function update()
	{
		$this->updateData();

		$db = App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		$this->importer = new \App\Db\Importer();
		$this->updateDbSchema();
		$this->importer->loadFiles(__DIR__ . '/dbscheme');
		$this->importer->updateScheme();
		$this->importer->postUpdate();
		$this->importer->logs(false);
		$this->importer->refreshSchema();
		$db->createCommand()->checkIntegrity(true)->execute();
		$this->updateConfigFile();
		require_once('include/events/include.php');
	}

	/**
	 * Postupdate
	 */
	public function postupdate()
	{
		return true;
	}

	/**
	 * Update
	 */
	public function updateDbSchema()
	{
		$this->addFields();
		$db = \App\Db::getInstance();
		$dbIndexes = $db->getTableKeys('com_vtiger_workflowtasks');
		if (isset($dbIndexes['com_vtiger_workflowtasks_idx'])) {
			$db->createCommand()->dropIndex('com_vtiger_workflowtasks_idx', 'com_vtiger_workflowtasks')->execute();
		}
		$this->importer->renameColumns([
			['vtiger_reservations', 'relatedida', 'link'],
			['vtiger_reservations', 'relatedidb', 'process'],
		]);
	}

	/**
	 * update data
	 */
	public function updateData()
	{
		$this->updateRows();
		$this->deleteRows();
		static::baseModuleTools();
	}

	/**
	 * update rows
	 */
	public function updateRows()
	{
		\App\Db\Updater::batchUpdate([
			['vtiger_cron_task', ['status' => 0], ['name' => 'LBL_HANDLER_UPDATER']],
			['vtiger_field', ['columnname' => 'link', 'fieldname' => 'link', 'fieldlabel' => 'FL_RELATION', 'typeofdata' => 'I~O', 'uitype' => 67], ['tablename' => 'vtiger_reservations', 'columnname' => 'relatedida']],
			['vtiger_field', ['columnname' => 'process', 'fieldname' => 'process', 'fieldlabel' => 'FL_PROCESS', 'typeofdata' => 'I~O', 'uitype' => 66], ['tablename' => 'vtiger_reservations', 'columnname' => 'relatedidb']],
		]);
	}

	private function addFields()
	{

//		$columnName = [0 => "tabid", 1 => "id", 2 => "column", 3 => "table", 4 => "generatedtype", 5 => "uitype", 6 => "name", 7 => "label", 8 => "readonly", 9 => "presence", 10 => "defaultvalue", 11 => "maximumlength", 12 => "sequence", 13 => "block", 14 => "displaytype", 15 => "typeofdata", 16 => "quickcreate", 17 => "quicksequence", 18 => "info_type", 19 => "masseditable", 20 => "helpinfo", 21 => "summaryfield", 22 => "fieldparams", 23 => 'header_field', 24 => "columntype", 25 => "blocklabel", 26 => "setpicklistvalues", 27 => "setrelatedmodules", 28 => 'moduleName'];
		$fields = [
			[84, 2607, 'subprocess', 'vtiger_reservations', 1, 68, 'subprocess', 'FL_SUB_PROCESS', 1, 2, '', 100, 4, 262, 1, 'I~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 'int(10)', 'LBL_BLOCK', [], [], 'Reservations'],
			[92, 2608, 'modifiedby', 'vtiger_crmentity', 1, 52, 'modifiedby', 'Last Modified By', 1, 2, '', 100, 7, 300, 3, 'V~O', 1, 0, 'BAS', 1, '', 0, '', NULL, 'smallint(5)', 'LBL_CUSTOM_INFORMATION', [], [], 'Partners']
		];
		foreach ($fields as $field) {
			$moduleId = \vtlib\Functions::getModuleId($field[28]);
			$isExists = (new \App\Db\Query())->from('vtiger_field')->where(['tablename' => $field[3], 'columnname' => $field[2], 'tabid' => $moduleId])->exists();
			if (!$moduleId || $isExists) {
				continue;
			}
			$moduleInstance = \vtlib\Module::getInstance($field[28]);
			$blockInstance = \vtlib\Block::getInstance($field[25], $moduleInstance);
			if (!$blockInstance) {
				App\Log::error("No block found to create a field, you will need to create a field manually. Module: {$field[28]}, field name: {$field[6]}, field label: {$field[7]}");
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
			if ($field[26] && ($field[5] == 15 || $field[5] == 16 || $field[5] == 33 )) {
				$fieldInstance->setPicklistValues($field[26]);
			}
			if ($field[27] && $field[5] == 10) {
				$fieldInstance->setRelatedModules($field[27]);
			}
		}
	}

	public function deleteRows()
	{
		$data = [
		];
		$link = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tablename' => 'vtiger_reservations', 'columnname' => 'link'])->scalar();
		if ($link) {
			$data[] = ['vtiger_fieldmodulerel', ['fieldid' => $link]];
		}
		$process = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['tablename' => 'vtiger_reservations', 'columnname' => 'process'])->scalar();
		if ($process) {
			$data[] = ['vtiger_fieldmodulerel', ['fieldid' => $process]];
		}
		\App\Db\Updater::batchDelete($data);
	}

	public function updateConfigFile()
	{
		$config = ROOT_DIRECTORY . '/config/performance.php';
		if (file_exists($config)) {
			if (strpos(file_get_contents($config), 'SESSION_DRIVER') === FALSE) {
				$lines = file($config);
				$last = sizeof($lines) - 1;
				unset($lines[$last]);
				file_put_contents($config, $lines);
				$configC = " //Session handler name, handler dir: vendor/yetiforce/Session/
 'SESSION_DRIVER' => 'File',
];";
				file_put_contents($config, $configC, FILE_APPEND);
			}
		}
	}

	/**
	 * Base module tools
	 * @var string[] 
	 */
	public static $baseModuleTools = ['Import', 'Export', 'DuplicatesHandling', 'CreateCustomFilter',
		'DuplicateRecord', 'MassEdit', 'MassDelete', 'MassAddComment', 'MassTransferOwnership',
		'ReadRecord', 'WorkflowTrigger', 'Dashboard', 'CreateDashboardFilter',
		'QuickExportToExcel', 'ExportPdf',
		'RecordMapping', 'RecordMappingList', 'FavoriteRecords', 'WatchingRecords',
		'WatchingModule', 'RemoveRelation', 'ReviewingUpdates'];

	/**
	 * Base module tools exceptions
	 * @var array 
	 */
	public static $baseModuleToolsExceptions = [
		'Documents' => ['notAllowed' => ['Import', 'DuplicatesHandling']],
		'Calendar' => ['notAllowed' => ['DuplicatesHandling']],
		'Faq' => ['notAllowed' => ['Import', 'Export', 'DuplicatesHandling']],
		'Events' => ['notAllowed' => 'all'],
		'PBXManager' => ['notAllowed' => 'all'],
		'OSSMailView' => ['notAllowed' => 'all'],
		'CallHistory' => ['allowed' => ['QuickExportToExcel']],
	];

	/**
	 * Add missing entries in vtiger_profile2utility
	 */
	public static function baseModuleTools()
	{
		$missing = $curentProfile2utility = [];
		foreach ((new \App\Db\Query())->from('vtiger_profile2utility')->all() as $row) {
			$curentProfile2utility[$row['profileid']][$row['tabid']][$row['activityid']] = $row['permission'];
		}
		$profileIds = \vtlib\Profile::getAllIds();
		$moduleIds = array_keys(\vtlib\Functions::getAllModules());
		$baseActionIds = array_map('YetiForceUpdate::getActionId', static::$baseModuleTools);
		$exceptions = static::getBaseModuleToolsExceptions();
		foreach ($profileIds as $profileId) {
			foreach ($moduleIds as $moduleId) {
				foreach ($baseActionIds as $actionId) {
					if (!isset($curentProfile2utility[$profileId][$moduleId][$actionId])) {
						$missing[] = ['profileid' => $profileId, 'tabid' => $moduleId, 'activityid' => $actionId];
					}
				}
			}
		}
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($missing as $row) {
			if (isset($exceptions[$row['tabid']]['allowed'])) {
				if (!isset($exceptions[$row['tabid']]['allowed'][$row['activityid']])) {
					continue;
				}
			} elseif (isset($exceptions[$row['tabid']]['notAllowed']) && ($exceptions[$row['tabid']]['notAllowed'] === false || isset($exceptions[$row['tabid']]['notAllowed'][$row['activityid']]))) {
				continue;
			}
			$dbCommand->insert('vtiger_profile2utility', ['profileid' => $row['profileid'], 'tabid' => $row['tabid'], 'activityid' => $row['activityid'], 'permission' => 1,])->execute();
		}
		RecalculateSharingRules();
	}

	/**
	 * Get module base tools exceptions parse to ids
	 * @return array
	 */
	public static function getBaseModuleToolsExceptions()
	{
		$exceptions = [];
		$actionIds = (new \App\Db\Query())->select(['actionname', 'actionid'])->from('vtiger_actionmapping')->createCommand()->queryAllByGroup();
		foreach (static::$baseModuleToolsExceptions as $moduleName => $moduleException) {
			foreach ($moduleException as $type => $exception) {
				if (is_array($exception)) {
					$moduleExceptions = [];
					foreach ($exception as $actionName) {
						$moduleExceptions[$actionIds[$actionName]] = $actionName;
					}
					$exceptions[App\Module::getModuleId($moduleName)][$type] = $moduleExceptions;
				} else {
					$exceptions[App\Module::getModuleId($moduleName)][$type] = false;
				}
			}
		}
		return $exceptions;
	}

	public static function getActionId($action)
	{
		if (empty($action)) {
			return null;
		}
		if (\App\Cache::has('getActionId', $action)) {
			return \App\Cache::get('getActionId', $action);
		}
		$actionIds = \App\Module::getTabData('actionId');
		if (isset($actionIds[$action])) {
			$actionId = $actionIds[$action];
		}
		if (empty($actionId)) {
			$actionId = (new Db\Query())->select(['actionid'])->from('vtiger_actionmapping')->where(['actionname' => $action])->scalar();
		}
		\App\Cache::save('getActionId', $action, $actionId, \App\Cache::LONG);
		return $actionId;
	}
}
