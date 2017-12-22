<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base3 extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_osspasswords' => [
				'index' => [
					['linkto', 'linkto'],
					['linkextend', 'linkextend'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_import_locks' => [
				'columns' => [
					'vtiger_import_lock_id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'importid' => $this->integer(10)->notNull(),
					'locked_since' => $this->dateTime(),
				],
				'primaryKeys' => [
					['import_locks_pk', 'vtiger_import_lock_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
