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
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s'));

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

		$this->log(__METHOD__ . '| ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return true;
	}
}
