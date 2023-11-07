<?php
/**
 * YetiForce system update package file.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
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

	/** @var string[] Errors. */
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

		if ((is_numeric($maxExecutionTime) && 0 != $maxExecutionTime && $maxExecutionTime < $minTime) || (is_numeric($maxInputTime) && $maxInputTime > 0 && $maxInputTime < $minTime)) {
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

		if(!\function_exists('openssl_encrypt') || !extension_loaded('openssl')){
			$this->package->_errorText = 'The server configuration is not compatible with the requirements: openssl extension is required.' . PHP_EOL;
			return false;
		}
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
			$this->importer->dropColumns([
				['s_yf_companies', 'status'],
				['s_yf_companies', 'type'],
				['s_yf_companies', 'city'],
				['s_yf_companies', 'address'],
				['s_yf_companies', 'post_code'],
				['s_yf_companies', 'companysize'],
				['s_yf_companies', 'logo'],
				['s_yf_companies', 'firstname'],
				['s_yf_companies', 'lastname'],
				['s_yf_companies', 'facebook'],
				['s_yf_companies', 'twitter'],
				['s_yf_companies', 'linkedin'],
			]);
			$this->importer->dropIndexes([
				'u_yf_modentity_sequences' => ['u_yf_modentity_sequences_tabid_fk'],
			]);
			$this->preSchemaUpdateData();
			$this->importer->updateScheme();
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
		$this->importer->logs(false);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
	}

	private function preSchemaUpdateData(){
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$db = \App\Db::getInstance();
		$db->createCommand("DELETE FROM `dav_users` WHERE userid NOT IN (SELECT id FROM vtiger_users)")->execute();
		$db->createCommand("DELETE FROM `dav_users` WHERE userid IN (SELECT id FROM vtiger_users WHERE `status`= 'Inactive')")->execute();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	private function updateData(): void
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		$batchDelete = \App\Db\Updater::batchDelete([
			['a_yf_settings_modules', ['name' => 'Magento']],
			['a_yf_settings_modules', ['name' => 'Wapro']],
			['s_yf_address_finder_config', ['val' => 'YetiForceGeocoder']],
			['s_yf_address_finder_config', ['type' => 'YetiForceGeocoder']],
			['vtiger_cron_task', ['name' => 'LBL_MAGENTO']],
		]);
		$this->log('  [INFO] batchDelete: ' . \App\Utils::varExport($batchDelete));
		unset($batchDelete);

		$updates = [
			['vtiger_field', ['maximumlength' => '400'], ['fieldname' => 'filename', 'tablename' => 'vtiger_notes']],
			['vtiger_notification_status', ['presence' => 0], ['notification_status' => ['PLL_UNREAD','PLL_READ']]],
			['vtiger_settings_field', ['linkto' => 'index.php?parent=Settings&module=Companies&view=Edit'], ['linkto' => 'index.php?parent=Settings&module=Companies&view=List']],
			['vtiger_settings_field', ['active' => 1], ['name' => ['LBL_VULNERABILITIES','LBL_MAGENTO','LBL_WAPRO_ERP']]],
		];
		$batchUpdate = \App\Db\Updater::batchUpdate($updates);
		$this->log('  [INFO] batchUpdate: ' . \App\Utils::varExport($batchUpdate));
		unset($batchUpdate);

		$db = \App\Db::getInstance();
		$db->createCommand("UPDATE vtiger_ossmailview SET uid = TRIM(LEADING '<' FROM TRIM(TRAILING '>' FROM uid)) WHERE uid LIKE '<%'")->execute();
		$db->createCommand("update `vtiger_field` set icon = null where icon is not null and icon != '' and icon not like '{%';")->execute();
		$db->createCommand("update `vtiger_field` set icon = CONCAT(trim(TRAILING '}' from icon),',\"type\":\"icon\"}') where icon is not null and icon != '' and icon like '{%' and icon not like '%\"type\"%'")->execute();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
	}

	/**
	 * Post update.
	 */
	public function createConfigFiles(): bool
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		foreach (['config/ConfigTemplates.php', 'config/Components/ConfigTemplates.php', 'modules/OSSMail/ConfigTemplate.php', 'modules/OpenStreetMap/ConfigTemplate.php', 'modules/ModComments/ConfigTemplate.php'] as $configTemplates) {
			if(!file_exists(__DIR__ . '/files/' . $configTemplates)){
				continue;
			}
			$path = ROOT_DIRECTORY . '/' . $configTemplates;
			copy(__DIR__ . '/files/' . $configTemplates, $path);
			\App\Cache::resetFileCache($path);
		}

		\App\Cache::resetOpcache();
		clearstatcache();

		$openStreetMap = new \App\ConfigFile('module', 'OpenStreetMap');
		if ('Nominatim' !== \App\Config::module('OpenStreetMap', 'coordinatesServer', null)) {
			$openStreetMap->set('coordinatesServer', 'Nominatim');
		}
		\App\Config::set('module', 'OpenStreetMap', 'coordinatesServers', [
			'Nominatim' => [
				'driverName' => 'Nominatim',
				'apiUrl' => 'https://nominatim.openstreetmap.org',
				'docUrl' => 'https://wiki.openstreetmap.org/wiki/Nominatim',
			],
		]);
		if ('YetiForce' === \App\Config::module('OpenStreetMap', 'routingServer', null) || empty(\App\Config::module('OpenStreetMap', 'routingServer', null))) {
			$openStreetMap->set('routingServer', 'Osrm');
		}
		\App\Config::set('module', 'OpenStreetMap', 'routingServers', [
			'Yours' => [
				'driverName' => 'Yours',
				'apiUrl' => 'http://www.yournavigation.org/api/1.0/gosmore.php',
				'params' => ['preference' => 'fastest', 'profile' => 'driving-car', 'units' => 'km'],
			],
			'Osrm' => [
				'driverName' => 'Osrm',
				'apiUrl' => 'https://routing.openstreetmap.de/routed-car',
			],
			'GraphHopper' => [
				'driverName' => 'GraphHopper',
				'apiUrl' => 'https://graphhopper.com/api/1',
				'params' => ['key' => 'b16b1d60-3c8c-4cd6-bae6-07493f23e589'],
			],
		]);
		if ('YetiForce' === \App\Config::module('OpenStreetMap', 'tileLayerServer', null)) {
			$openStreetMap->set('tileLayerServer', '');
		}
		\App\Config::set('module', 'OpenStreetMap', 'tileLayerServers', [
			'OpenStreetMap Default' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
			'OpenStreetMap HOT' => 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
			'Esri WorldTopoMap' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
			'Esri WorldImagery' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
		]);
		$openStreetMap->create();

		\App\Config::set('performance', 'recursiveTranslate', true);

		$skip = ['module', 'component'];
		foreach (array_diff(\App\ConfigFile::TYPES, $skip) as $type) {
			(new \App\ConfigFile($type))->create();
		}

		$dataReader = (new \App\Db\Query())->select(['name'])->from('vtiger_tab')->createCommand()->query();
		while ($moduleName = $dataReader->readColumn(0)) {
			$filePath = 'modules' . \DIRECTORY_SEPARATOR . $moduleName . \DIRECTORY_SEPARATOR . 'ConfigTemplate.php';
			if (!\in_array($moduleName, ['OpenStreetMap']) && file_exists($filePath)) {
				(new \App\ConfigFile('module', $moduleName))->create();
			}
		}
		$path = \ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'Components' . \DIRECTORY_SEPARATOR . 'ConfigTemplates.php';
		$componentsData = require_once "$path";
		foreach ($componentsData as $component => $data) {
			(new \App\ConfigFile('component', $component))->create();
		}

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
		$this->createConfigFiles();
		(new \Settings_Menu_Record_Model())->refreshMenuFiles();

		(new \App\BatchMethod(['method' => '\App\Db\Fixer::baseModuleTools', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\Db\Fixer::baseModuleActions', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\Db\Fixer::profileField', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => '\App\UserPrivilegesFile::recalculateAll', 'params' => []]))->save();
		(new \App\BatchMethod(['method' => 'Settings_SharingAccess_Module_Model::recalculateSharingRules', 'params' => []]))->save();

		\App\Cache::clear();
		\App\Cache::resetOpcache();
		if ($this->error || false !== strpos($this->importer->logs, 'Error')) {
			$this->stopProcess();
			$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
			exit;
		}else{
			$db= \App\Db::getInstance();
			$db->createCommand()->insert('yetiforce_updates', [
				'user' => \Users_Record_Model::getCurrentUserModel()->get('user_name'),
				'name' => (string) $this->moduleNode->label,
				'from_version' => (string) $this->moduleNode->from_version,
				'to_version' => (string) $this->moduleNode->to_version,
				'result' => true,
				'time' => date('Y-m-d H:i:s'),
			])->execute();

			$db->createCommand()->update('vtiger_version', ['current_version' => (string) $this->moduleNode->to_version])->execute();

			\vtlib\Functions::recurseDelete('cache/updates/updates');
			register_shutdown_function(function () {
				try {
					\vtlib\Functions::recurseDelete('cache/templates_c');
				} catch (\Exception $e) {
					\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
				}
			});
			\App\Module::createModuleMetaFile();
			\App\Cache::clear();
			\App\Cache::clearOpcache();
			\vtlib\Functions::recurseDelete('app_data/LanguagesUpdater.json');
			\vtlib\Functions::recurseDelete('app_data/SystemUpdater.json');
			\vtlib\Functions::recurseDelete('app_data/cron.php');
			\vtlib\Functions::recurseDelete('app_data/ConfReport_AllErrors.php');
			\vtlib\Functions::recurseDelete('app_data/shop.php');
			\vtlib\Functions::recurseDelete('app_data/shopCache.php');
			file_put_contents('cache/logs/update.log', PHP_EOL . date('Y-m-d H:i:s') . ' | ' . ob_get_clean(), FILE_APPEND);
			$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' min');
			$this->logout();
		}
		exit;
	}

	private function logout(){
		if(PHP_SAPI !== 'cli'){
			$moduleModel = Users_Module_Model::getInstance('Users');
			$moduleModel->saveLogoutHistory();

			$eventHandler = new App\EventHandler();
			$eventHandler->trigger('UserLogoutBefore');
			if (\Config\Security::$loginSessionRegenerate ?? false) {
				App\Session::regenerateId(true);
			}
			OSSMail_Logout_Model::logoutCurrentUser();
			App\Session::destroy();
			header('location: index.php');
			exit;
		}

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
