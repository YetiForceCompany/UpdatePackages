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
 
//  SHA-1: 57b3f1a9b29bfe0030829840ec85cd51c5be89e6

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
		$createCommand = \App\Db::getInstance()->createCommand();
		$menu = (new \App\Db\Query())->select(['id', 'dataurl'])->from('yetiforce_menu')
			->where(['like', 'dataurl', '%view=CalendarExtended&%', false])
			->all();
		foreach ($menu as $value) {
			$url = $value['dataurl'];
			if (0 === strpos($url, Config\Main::$site_URL) || 0 === strpos($url, 'index.php?')) {
				$url = str_replace('view=CalendarExtended', 'view=Calendar&', $url);
				$createCommand->update('yetiforce_menu', ['dataurl' => $url], ['id' => $value['id']])->execute();
			}
		}
		(new \Settings_Menu_Record_Model())->refreshMenuFiles();
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
