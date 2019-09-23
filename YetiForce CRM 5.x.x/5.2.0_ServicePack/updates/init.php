<?php
/**
 * YetiForceUpdate Class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * YetiForceUpdate Class.
 */
class YetiForceUpdate
{
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
	 * @var string
	 */
	public $logFile = 'cache/logs/updateLogsTrace.log';

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
		$label = (string) $this->modulenode->label;
		fwrite($fp, "{$label} {$message}" . PHP_EOL);
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
		$db = \App\Db::getInstance();
		$db->createCommand()->checkIntegrity(false)->execute();
		$this->importer = new \App\Db\Importer();
		try {
			$columns = ['purchase_price', 'actual_price', 'estimated', 'actual_sale', 'annualrevenue', 'balance', 'expectedrevenue', 'budgetcost', 'actualcost', 'expectedroi', 'actualroi', 'annualrevenue', 'cash_amount_on_delivery', 'rbh', 'paymentsvalue'];
			$dataReader = (new \App\Db\Query())
				->select(['vtiger_field.fieldid', 'vtiger_field.columnname', 'vtiger_field.tablename'])
				->from('vtiger_field')->where([
					'or',
					['uitype' => 317],
					['and',
						['uitype' => [71, 72]],
						['columnname' => $columns]
					]
				])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$fieldInfo = $db->getTableSchema($row['tablename'], true)->getColumn($row['columnname']);
				if ('decimal(28,8)' !== $fieldInfo->dbType && $db->createCommand()->alterColumn($row['tablename'], $row['columnname'], 'decimal(28,8)')->execute()) {
					$db->createCommand()->update('vtiger_field', ['maximumlength' => '1.0E+26'], ['fieldid' => $row['fieldid']])->execute();
				}
			}
			$db->createCommand()
				->insert('s_#__address_finder_config', [
					'val' => 'YetiForceGeocoder',
					'type' => 1,
					'name' => 'active'
				])
				->execute();
			$db->createCommand()
				->update('s_yf_address_finder_config', [
					'val' => 'YetiForceGeocoder',
				], ['type' => 'global', 'name' => 'default_provider'])
				->execute();
			$this->importer->logs(false);
		} catch (\Throwable $ex) {
			$this->log($ex->getMessage() . '|' . $ex->getTraceAsString());
			$this->importer->logs(false);
			throw $ex;
		}
		$this->importer->refreshSchema();

		$db->createCommand()->checkIntegrity(true)->execute();
		return true;
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		if (file_exists(ROOT_DIRECTORY . '/custom/languages/')) {
			foreach ((new \DirectoryIterator(ROOT_DIRECTORY . '/custom/languages/')) as $file) {
				if ($file->isDir() && !$file->isDot() && file_exists($file->getPathname() . '/HelpInfo.json')) {
					if (!file_exists($file->getPathname() . '/Other')) {
						mkdir($file->getPathname() . '/Other', 0755, true);
					}
					rename($file->getPathname() . '/HelpInfo.json', $file->getPathname() . '/Other/HelpInfo.json');
				}
			}
		}
		$rows = (new \App\Db\Query())->from('s_#__companies')->where(['type' => 1])->one();
		if (!$rows) {
			$rows = (new \App\Db\Query())->from('s_#__companies')->one();
		}
		if ($rows) {
			$configFile = new \App\ConfigFile('component', 'Branding');
			$configFile->set('footerName', $rows['name']);
			$configFile->set('urlLinkedIn', $rows['linkedin']);
			$configFile->set('urlTwitter', $rows['twitter']);
			$configFile->set('urlFacebook', $rows['facebook']);
			$configFile->create();
		}
		(new \App\ConfigFile('developer'))->create();
		\vtlib\Functions::recurseDelete('cache/templates_c');
		\App\Cache::clear();
		\App\Cache::resetOpcache();
		return true;
	}
}
