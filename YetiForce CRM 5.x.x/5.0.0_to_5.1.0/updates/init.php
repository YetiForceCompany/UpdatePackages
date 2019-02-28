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
		$this->updateData();
		$this->addFields();
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
			[41, 2779, 'estimated_work_time', 'vtiger_projectmilestone', 1, 7, 'estimated_work_time', 'LBL_ESTIMATED_WORK_TIME', 1, 2, '', null, 15, 101, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0],
			[43, 2780, 'estimated_work_time', 'vtiger_project', 1, 7, 'estimated_work_time', 'LBL_ESTIMATED_WORK_TIME', 1, 2, '', null, 16, 107, 1, 'V~O', 1, 0, 'BAS', 1, '', 0, '', null, 0, 0, 0]
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
		];

		\App\Db\Updater::batchUpdate($data);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
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
}
