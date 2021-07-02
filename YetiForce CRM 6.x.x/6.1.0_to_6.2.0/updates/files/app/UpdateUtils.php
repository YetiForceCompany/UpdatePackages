<?php

namespace App;

/**
 * Update utils class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class UpdateUtils
{
	/**
	 * Function to update configuration files.
	 * Requires optimization.
	 *
	 * @param array $params
	 */
	public static function updateConfigurationFiles($params)
	{
		$rootDirectory = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR;
		foreach ($params as $config) {
			if (!$config) {
				continue;
			}
			$fileName = $rootDirectory . $config['name'];
			if (file_exists($fileName)) {
				$baseContent = file_get_contents($fileName);
				$configContent = $configContentClone = file($fileName);
				$emptyLine = false;
				$addContent = [];
				$indexes = [];
				foreach ($configContent as $key => $line) {
					if ($emptyLine && 1 == \strlen($line)) {
						unset($configContent[$key]);
						$emptyLine = false;
						continue;
					}
					$emptyLine = false;
					foreach ($config['conditions'] as $index => $condition) {
						if (empty($condition)) {
							continue;
						}
						if ('add' === $condition['type'] && !\in_array($index, $indexes)) {
							$addContent[$index] = $condition['value'];
							$indexes[] = $index;
						}
						if (false !== strpos($line, $condition['search'])) {
							switch ($condition['type']) {
								case 'add':
									if ($condition['checkInContents'] && false === strpos($baseContent, $condition['checkInContents'])) {
										$configContent[$key] = 'before' === $condition['addingType'] ? $condition['value'] . $configContent[$key] : $configContent[$key] . $condition['value'];
									}
									unset($addContent[$index]);
									break;
								case 'remove':
									if (!empty($condition['before'])) {
										if (false !== strpos($configContentClone[$key - 1], $condition['before'])) {
											unset($configContent[$key]);
											$emptyLine = true;
										}
									} else {
										unset($configContent[$key]);
										$emptyLine = true;
									}
									break;
								case 'removeTo':
									unset($configContent[$key]);
									$while = 0;
									while (false !== $while) {
										++$while;
										unset($configContent[$key + $while]);
										if (false === strpos($configContent[$key + $while], $condition['end'])) {
											$while = false;
										}
									}
									$emptyLine = true;
									break;
								case 'update':
									if ($condition['checkInLine'] && (false !== strpos($condition['checkInLine'], $configContent[$key]))) {
										break;
									}
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
				$content = implode('', $configContent);
				if ($addContent) {
					$addContentString = implode('', $addContent);
					$content .= $addContentString;
				}
				file_put_contents($fileName, $content, LOCK_EX);
			}
		}
	}
}
