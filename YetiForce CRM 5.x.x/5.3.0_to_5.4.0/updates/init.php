<?php
/**
 * YetiForceUpdate Class.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
// last check: 257d52bf9d8f9f733c01e3236bd7458952242574
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

	private $error = [];

	/**
	 * Constructor.
	 *
	 * @param object $modulenode
	 * @param mixed  $moduleNode
	 */
	public function __construct($moduleNode)
	{
		$this->modulenode = $moduleNode;
		$this->filesToDelete = require_once 'deleteFiles.php';
	}

	/**
	 * Logs.
	 *
	 * @param string $message
	 * @param bool   $eol
	 */
	public function log($message, bool $eol = true)
	{
		$fp = fopen($this->logFile, 'a+');
		if (0 === strpos($message, '[')) {
			$message = $message . PHP_EOL;
		}
		if ($eol) {
			$message = PHP_EOL . $message;
		}
		fwrite($fp, $message);
		fclose($fp);
		if (false !== strpos($message, '[ERROR]')) {
			$this->error[] = $message;
		}
	}

	/**
	 * Preupdate.
	 */
	public function preupdate()
	{
		$minTime = 600;
		$error = '';
		if (version_compare(PHP_VERSION, '7.2', '<')) {
			$error = 'Wrong PHP version, recommended version >= 7.2';
		}
		if (ini_get('max_execution_time') < $minTime) {
			$error .= PHP_EOL . 'max_execution_time = ' . ini_get('max_execution_time') . ' < ' . $minTime;
		}
		if (ini_get('max_input_time') < $minTime) {
			$error .= PHP_EOL . 'max_input_time = ' . ini_get('max_input_time') . ' < ' . $minTime;
		}
		if ($error) {
			$this->package->_errorText = 'The server configuration is not compatible with the requirements of the upgrade package. Please have a look at the list of errors:' . PHP_EOL . PHP_EOL . $error;
			return false;
		}
		// copy(__DIR__ . '/TempImporter.php', ROOT_DIRECTORY . '/app/Db/Importer.php');
		// copy(__DIR__ . '/files/app/Db/Fixer.php', ROOT_DIRECTORY . '/app/Db/Fixer.php');
		// copy(__DIR__ . '/files/app/Db/Importers/Base.php', ROOT_DIRECTORY . '/app/Db/Importers/Base.php');
		// copy(__DIR__ . '/files/vtlib/Vtiger/Block.php', ROOT_DIRECTORY . '/vtlib/Vtiger/Block.php');
		// copy(__DIR__ . '/files/modules/Vtiger/models/Field.php', ROOT_DIRECTORY . '/modules/Vtiger/models/Field.php');
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));
		$this->importer = new \App\Db\Importer();
		try {
			$this->importer->loadFiles(__DIR__ . '/dbscheme');
			$this->importer->checkIntegrity(false);
			$this->importer->updateScheme();
			$this->importer->importData();
			$this->importer->postUpdate();

			// $this->addModules(['Approvals', 'ApprovalsRegister', 'MailIntegration']);

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

		$this->log(__METHOD__ . ' - ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	/**
	 * PreUpdateScheme.
	 */
	public function ddddd()
	{
		$start = microtime(true);
		$this->log(__FUNCTION__ . "\t|\t" . date('H:i:s'));
		$address = [
			'addresslevel8a' => [1, 255],
			'addresslevel8b' => [1, 255],
			'addresslevel8c' => [1, 255],
			'localnumbera' => [2, 50],
			'localnumberb' => [2, 50],
			'localnumberc' => [2, 50],
			'addresslevel5a' => [3, 255],
			'addresslevel5b' => [3, 255],
			'addresslevel5c' => [3, 255],
			'buildingnumbera' => [4, 255],
			'buildingnumberb' => [4, 255],
			'buildingnumberc' => [4, 255],
			'addresslevel7a' => [5, 255],
			'addresslevel7b' => [5, 255],
			'addresslevel7c' => [5, 255],
			'addresslevel6a' => [6, 255],
			'addresslevel6b' => [6, 255],
			'addresslevel6c' => [6, 255],
			'addresslevel2a' => [7, 255],
			'addresslevel2b' => [7, 255],
			'addresslevel2c' => [7, 255],
			'addresslevel4a' => [8, 255],
			'addresslevel4b' => [8, 255],
			'addresslevel4c' => [8, 255],
			'addresslevel1a' => [9, 255],
			'addresslevel1b' => [9, 255],
			'addresslevel1c' => [9, 255],
			'addresslevel3a' => [10, 255],
			'addresslevel3b' => [10, 255],
			'addresslevel3c' => [10, 255],
			'poboxa' => [11, 50],
			'poboxb' => [11, 50],
			'poboxc' => [11, 50],
			'first_name_a' => [12, 255],
			'first_name_b' => [12, 255],
			'first_name_c' => [12, 255],
			'last_name_a' => [13, 255],
			'last_name_b' => [13, 255],
			'last_name_c' => [13, 255],
			'company_name_a' => [14, 255],
			'company_name_b' => [14, 255],
			'company_name_c' => [14, 255],
			'vat_id_a' => [15, 50],
			'vat_id_b' => [15, 50],
			'vat_id_c' => [15, 50],
			'email_a' => [16, 100],
			'email_b' => [16, 100],
			'email_c' => [16, 100],
			'phone_a' => [17, 100],
			'phone_b' => [17, 100],
			'phone_c' => [17, 100],
		];
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($address as $fieldName => $row) {
			$dbCommand->update('vtiger_field', ['sequence' => $row[0], 'maximumlength' => $row[1]], ['fieldname' => $fieldName])->execute();
			$all = (new \App\Db\Query())->select(['tablename', 'columnname'])->from('vtiger_field')->where(['fieldname' => $fieldName])->all();
			foreach ($all as $row2) {
				$dbCommand->alterColumn($row2['tablename'], $row2['columnname'], "varchar({$row[1]})")->execute();
			}
		}
		$this->log(' -> ' . date('H:i:s') . "\t|\t" . round((microtime(true) - $start) / 60, 2) . ' min.', false);
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
	}
}
