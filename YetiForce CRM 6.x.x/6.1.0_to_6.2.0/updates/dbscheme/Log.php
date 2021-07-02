<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Log extends \App\Db\Importers\Base
{
	public $dbType = 'log';

	public function scheme()
	{
		$this->tables = [
			'l_#__mail' => [
				'columns' => [
					'params' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__api_login_history' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'time' => $this->timestamp()->null(),
					'user_name' => $this->stringType(),
					'user_id' => $this->integer(10)->unsigned(),
					'status' => $this->stringType(50),
					'agent' => $this->stringType(500),
					'ip' => $this->stringType(100),
				],
				'index' => [
					['user_id', 'user_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__portal_login_history' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'time' => $this->timestamp()->null(),
					'user_name' => $this->stringType(),
					'user_id' => $this->integer(10)->unsigned(),
					'status' => $this->stringType(50),
					'agent' => $this->stringType(500),
					'ip' => $this->stringType(100),
					'device_id' => $this->stringType(100),
				],
				'index' => [
					['user_id', 'user_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['l_#__api_login_history_ibfk_1', 'l_#__api_login_history', 'user_id', 'w_#__api_user', 'id', 'CASCADE', null],
			['l_#__portal_login_history_ibfk_1', 'l_#__portal_login_history', 'user_id', 'w_#__portal_user', 'id', 'CASCADE', null],
		];
	}
}
