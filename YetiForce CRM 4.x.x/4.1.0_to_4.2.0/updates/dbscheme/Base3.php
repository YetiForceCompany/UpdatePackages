<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base3 extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_loginhistory' => [
				'columns' => [
					'login_id' => $this->integer(10)->autoIncrement()->notNull(),
					'user_name' => $this->stringType(32),
					'user_ip' => $this->stringType(100)->notNull(),
					'logout_time' => $this->timestamp()->null(),
					'login_time' => $this->timestamp()->null(),
					'status' => $this->stringType(25),
					'browser' => $this->stringType(25),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
