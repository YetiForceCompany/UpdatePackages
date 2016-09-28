<?php
/**
 * YetiForceUpdate Class
 * @package YetiForce.UpdatePackages
 * @license https://yetiforce.com/en/implementer/license.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc';
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
require_once('include/events/include.inc');
include_once('vtlib/Vtiger/Module.php');
include_once('modules/ModTracker/ModTracker.php');

class YetiForceUpdate
{

	public $package;
	public $modulenode;
	public $return = true;
	public $filesToDelete = [
		'include/database/Postgres8.php',
		'include/Debuger.php',
		'languages/api/yetiportal.php',
		'include/Debuger.php',
		'languages/api/yetiportal.php',
		'libraries/Psr',
		'libraries/symfony',
		'vendor/php-debugbar/src/DebugBar/StandardDebugBar.php',
		'.travis.yml',
	];

	public function YetiForceUpdate($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	public function preupdate()
	{
		return true;
	}

	public function update()
	{

		$this->updateConfigurationFiles();
	}

	public function postupdate()
	{
		return true;
	}

	private function getConfigurations()
	{
		return [
			['name' => 'config/csrf_config.php', 'conditions' => [
					['type' => 'update', 'search' => '$_SERVER[\'HTTP_X_PJAX\'] == true', 'replace' => ['$_SERVER[\'HTTP_X_PJAX\'] == true', '$_SERVER[\'HTTP_X_PJAX\'] === true']],
				]
			]
		];
	}

	private function updateConfigurationFiles()
	{
		$rootDirectory = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
		foreach ($this->getConfigurations() as $config) {
			if (!$config) {
				continue;
			}
			$conditions = $config['conditions'];
			$fileName = $rootDirectory . $config['name'];
			if (file_exists($fileName)) {
				$baseContent = file_get_contents($fileName);
				$configContent = file($fileName);
				$emptyLine = false;
				$addContent = [];
				$indexes = [];
				foreach ($configContent as $key => $line) {
					if ($emptyLine && strlen($line) == 1) {
						unset($configContent[$key]);
						$emptyLine = false;
						continue;
					}
					$emptyLine = false;
					foreach ($conditions as $index => $condition) {
						if (empty($condition)) {
							continue;
						}
						if ($condition['type'] == 'add' && !in_array($index, $indexes)) {
							$addContent[$index] = $condition['value'];
							$indexes[] = $index;
						}
						if (strpos($line, $condition['search']) !== false) {
							switch ($condition['type']) {
								case 'add':
									if ($condition['checkInContents'] && strpos($baseContent, $condition['checkInContents']) === false) {
										if ($condition['trim']) {
											$configContent[$key] = $this->getTrimValue($condition['trim'], $configContent[$key]);
										}
										$configContent[$key] = $condition['addingType'] == 'before' ? $condition['value'] . $configContent[$key] : $configContent[$key] . $condition['value'];
									}
									unset($addContent[$index]);
									break;
								case 'remove':
									unset($configContent[$key]);
									$emptyLine = true;
									break;
								case 'update':
									if ($condition['replace']) {
										$configContent[$key] = str_replace($condition['replace'][0], $condition['replace'][1], $configContent[$key]);
									} else {
										$configContent[$key] = $condition['value'];
									}
									break;
								default:
									break;
							}
						}
					}
				}
				$content = implode("", $configContent);
				if ($addContent) {
					$addContentString = implode("", $addContent);
					$content .= $addContentString;
				}
				$file = fopen($fileName, "w+");
				fwrite($file, $content);
				fclose($file);
			} else {
				$dirName = 'cache/updates/' . $config['name'];
				$sourceFile = $rootDirectory . $dirName;
				if (file_exists($sourceFile)) {
					copy($sourceFile, $fileName);
				}
			}
		}
	}
}
