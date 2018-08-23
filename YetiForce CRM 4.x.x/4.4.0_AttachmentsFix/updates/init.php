<?php
/**
 * YetiForceUpdate Class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * YetiForceUpdate Class.
 */
class YetiForceUpdate
{
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
	 * Constructor.
	 *
	 * @param object $modulenode
	 */
	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
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
		$db = \App\Db::getInstance();
		$query = (new \App\Db\Query())->select(['attachmentsid', 'path'])->from('vtiger_attachments');
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$path = $row['path'];
			if (substr(str_replace('\\', '/', $path), 0, 7) !== 'storage') {
				$path .= 'X';
				$db->createCommand()->update('vtiger_attachments', ['path' => substr($path, strpos($path, 'storage'), -1)], ['attachmentsid' => $row['attachmentsid']])->execute();
			}
		}
	}
	
	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		return true;
	}
}
