<?php
namespace Importers;

/**
 * Class that imports log database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Log extends \App\Db\Importers\Base
{

	public $dbType = 'log';

	public function scheme()
	{
		$this->tables = [
			'l_#__username_history' => [
				'columns' => [
					'user_name' => $this->stringType(32),
					'user_id' => $this->integer(10)->unsigned(),
					'date' => $this->dateTime(),
				],
				'index' => [
					['user_id', 'user_id'],
					['user_name', 'user_name'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__userpass_history' => [
				'columns' => [
					'pass' => $this->stringType(200)->notNull(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'date' => $this->dateTime()->notNull(),
				],
				'index' => [
					['user_id', ['user_id', 'pass']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
		];
	}
}
