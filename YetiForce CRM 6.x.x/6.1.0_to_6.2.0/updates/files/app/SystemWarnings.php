<?php

namespace App;

/**
 * System warnings basic class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class SystemWarnings
{
	const FOLDERS = 'app/SystemWarnings';
	const SELECTED_FOLDERS = ['SystemRequirements', 'YetiForce', 'Security', 'Mail'];

	/**
	 * Returns a list of folders warnings.
	 *
	 * @return array
	 */
	public static function getFolders()
	{
		$folders = [];
		$i = 0;
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::FOLDERS, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isDir()) {
				$subPath = $iterator->getSubPathName();
				$fileName = $item->getFilename();
				$subPath = str_replace(\DIRECTORY_SEPARATOR, '/', $subPath);
				$parent = rtrim(rtrim($subPath, $fileName), '/');
				$folder = ['id' => $i, 'text' => Language::translate($fileName, 'Settings:SystemWarnings'), 'subPath' => $subPath, 'parent' => '#'];
				if (isset($folders[$parent])) {
					$folder['parent'] = $folders[$parent]['id'];
				}
				if (\in_array($subPath, self::SELECTED_FOLDERS)) {
					$folder['state']['selected'] = true;
				}
				$folders[$subPath] = $folder;
			}
			++$i;
		}
		return $folders;
	}

	/**
	 * Returns a list of warnings instance.
	 *
	 * @param array $folders
	 * @param mixed $active
	 *
	 * @return array
	 */
	public static function getWarnings($folders, $active = true)
	{
		if (empty($folders)) {
			return [];
		}
		if (!\is_array($folders) && 'all' === $folders) {
			$folders = array_keys(static::getFolders());
		}
		$actions = [];
		foreach ($folders as $folder) {
			$dir = self::FOLDERS . '/' . $folder;
			if (!is_dir($dir)) {
				continue;
			}
			$iterator = new \DirectoryIterator($dir);
			foreach ($iterator as $item) {
				if (!$item->isDot() && !$item->isDir()) {
					$fileName = $item->getBasename('.php');
					$folder = str_replace('/', '\\', $folder);
					$className = "\\App\\SystemWarnings\\$folder\\$fileName";
					$instace = new $className();
					if ($instace->preProcess()) {
						$isIgnored = 2 === $instace->getStatusValue();
						$show = true;
						if (!$isIgnored) {
							$instace->process();
						}
						if ($active && ($isIgnored || 1 === $instace->getStatus())) {
							$show = false;
						}
						if ($show) {
							$instace->setFolder($folder);
							$actions[$instace->getPriority() . $fileName] = $instace;
						}
					}
				}
			}
		}
		krsort($actions);

		return $actions;
	}

	/**
	 * Returns number of warnings.
	 *
	 * @return int
	 */
	public static function getWarningsCount()
	{
		$i = 0;
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::FOLDERS, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile() && 'Template' != $item->getBasename('.php')) {
				$subPath = $iterator->getSubPath();
				$fileName = $item->getBasename('.php');
				$folder = str_replace('/', '\\', $subPath);
				$className = "\\App\\SystemWarnings\\$folder\\$fileName";
				$instace = new $className();
				if ($instace->preProcess()) {
					if (2 != $instace->getStatus()) {
						$instace->process();
					}
					if (0 == $instace->getStatus()) {
						++$i;
					}
				}
			}
		}
		return $i;
	}
}
