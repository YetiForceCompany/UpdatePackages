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

	/** @var string[] Rrror. */
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
		copy(__DIR__ . '/files/app/Db/Fixer.php', ROOT_DIRECTORY . '/app/Db/Fixer.php');
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
			$this->importer->updateScheme();
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
		$this->updateData();
		$this->createConfigFiles();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function updateData(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$batchUpdate = \App\Db\Updater::batchUpdate([]);
		$this->log('[INFO] batchUpdate: ' . \App\Utils::varExport($batchUpdate));

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Post update.
	 */
	public function createConfigFiles(): bool
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$configTemplates = 'config/ConfigTemplates.php';
		copy(__DIR__ . '/files/' . $configTemplates, ROOT_DIRECTORY . '/' . $configTemplates);

		\App\Cache::resetOpcache();
		clearstatcache();

		(new \App\ConfigFile('security'))->create();
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
