<?php

namespace App\SystemWarnings;

/**
 * System warnings template abstract class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Template
{
	/**
	 * Status value - 2 = ignored.
	 *
	 * @var int
	 */
	protected $statusValue = 0;
	protected $title;
	protected $description;
	protected $priority = 0;
	protected $color;
	protected $status = 0;
	protected $folder;
	protected $link;
	protected $tpl = false;

	/**
	 * Checking whether there is a warning.
	 */
	abstract public function process();

	/**
	 * Whether a warning is active.
	 *
	 * @return bool
	 */
	public function preProcess()
	{
		return true;
	}

	/**
	 * Returns the warning priority.
	 *
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * Returns the warning title.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Returns the warning color.
	 *
	 * @return string
	 */
	public function getColor()
	{
		return $this->color;
	}

	/**
	 * Get status value.
	 *
	 * @return int
	 */
	public function getStatusValue(): int
	{
		return $this->statusValue;
	}

	/**
	 * Returns the warning status.
	 *
	 * @param mixed $returnText
	 *
	 * @return int|string
	 */
	public function getStatus($returnText = false)
	{
		if (!$returnText) {
			return $this->status;
		}
		$error = '';
		switch ($this->status) {
			case 1:
				$error = 'OK';
				break;
			case 2:
				$error = 'Error';
				break;
			default:
				break;
		}
		return $error;
	}

	/**
	 * Returns the warning description.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Returns the warning link.
	 *
	 * @return string
	 */
	public function getLink()
	{
		return $this->link;
	}

	public function getTpl()
	{
		if (!$this->tpl || \is_string($this->tpl)) {
			return $this->tpl;
		}
		$refClass = new \ReflectionClass($this);
		$className = $refClass->getShortName();
		$path = \App\Layout::getTemplatePath("{$this->getFolder(false)}/{$className}.tpl", 'Settings:SystemWarnings');
		$this->tpl = $path;

		return $path;
	}

	/**
	 * Returns the warning folder.
	 *
	 * @param mixed $toArray
	 *
	 * @return string
	 */
	public function getFolder($toArray = true)
	{
		if ($toArray && false !== strpos($this->folder, '\\')) {
			$this->folder = explode('\\', $this->folder);
		}
		return $this->folder;
	}

	/**
	 * Updates the warning folder.
	 *
	 * @param mixed $folder
	 *
	 * @return string
	 */
	public function setFolder($folder)
	{
		return $this->folder = $folder;
	}

	/**
	 * Update ignoring status.
	 *
	 * @param int $params
	 *
	 * @return bool
	 */
	public function update($params)
	{
		$statusValue = '2' === $params ? 0 : 2;
		$refClass = new \ReflectionClass($this);
		$filePath = $refClass->getFileName();
		$fileContent = file_get_contents($filePath);
		if (false !== strpos($fileContent, 'protected $statusValue ')) {
			$pattern = '/\$statusValue = ([^;]+)/';
			$replacement = '$statusValue = ' . $statusValue;
			$fileContent = preg_replace($pattern, $replacement, $fileContent);
		} else {
			$replacement = '{' . PHP_EOL . '	protected $statusValue = ' . $statusValue . ';';
			$fileContent = preg_replace('/{/', $replacement, $fileContent, 1);
		}
		file_put_contents($filePath, $fileContent);
		return ['result' => true, 'message' => \App\Language::translate('LBL_DATA_SAVE_OK', 'Settings::SystemWarnings')];
	}
}
