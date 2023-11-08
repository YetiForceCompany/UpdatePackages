<?php
/**
 * YetiForceUpdate Class.
 *
 * @package   YetiForce.UpdatePackages
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	public $moduleNode;

	/**
	 * Fields to delete.
	 *
	 * @var string[]
	 */
	public $filesToDelete = [];

	/**
	 * Constructor.
	 *
	 * @param object $moduleNode
	 */
	public function __construct($moduleNode)
	{
		$this->moduleNode = $moduleNode;
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
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$this->createConfigFiles();
	}

	/**
	 * Post update.
	 */
	public function createConfigFiles(): bool
	{
		$start = microtime(true);
		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s'));

		foreach (['config/ConfigTemplates.php', 'modules/OpenStreetMap/ConfigTemplate.php'] as $configTemplates) {
			if(!file_exists(__DIR__ . '/files/' . $configTemplates)){
				continue;
			}
			$path = ROOT_DIRECTORY . '/' . $configTemplates;
			copy(__DIR__ . '/files/' . $configTemplates, $path);
			\App\Cache::resetFileCache($path);
		}

		\App\Cache::resetOpcache();
		clearstatcache();

		(new \App\ConfigFile('main'))->create();
		(new \App\ConfigFile('debug'))->create();
		(new \App\ConfigFile('security'))->create();

		$openStreetMap = new \App\ConfigFile('module', 'OpenStreetMap');
		if ('Nominatim' !== \App\Config::module('OpenStreetMap', 'coordinatesServer', null)) {
			$openStreetMap->set('coordinatesServer', 'Nominatim');
		}
		$tile = \App\Config::module('OpenStreetMap', 'tileLayerServer', null);
		if ('YetiForce' === $tile || empty($tile)) {
			$openStreetMap->set('tileLayerServer', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');

			$security = new \App\ConfigFile('security');
			$allowedImageDomains = \App\Config::security('allowedImageDomains', []);
			$value = '*.tile.openstreetmap.org';
			if (!\in_array($value, $allowedImageDomains)) {
				$allowedImageDomains[] = $value;
			}
			$security->set('allowedImageDomains', array_values($allowedImageDomains));
			$security->create();
		}

		$openStreetMap->create();

		$this->log(__METHOD__ . ' | ' . date('Y-m-d H:i:s') . ' | ' . round((microtime(true) - $start) / 60, 2) . ' mim.');
		return true;
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		\vtlib\Functions::recurseDelete('cache/templates_c');
		\App\Cache::clear();
		\App\Cache::resetOpcache();
		return true;
	}
}
