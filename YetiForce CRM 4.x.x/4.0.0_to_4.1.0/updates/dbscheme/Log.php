<?php
namespace Importers;

/**
 * Class that imports log database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Log extends \DbType
{

	public $dbType = 'log';

	public function scheme()
	{
		$this->tables = [
			'l_#__profile' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'category' => $this->stringType()->notNull(),
					'info' => $this->text(),
					'log_time' => $this->stringType(20)->notNull(),
					'trace' => $this->text(),
					'level' => $this->stringType(),
					'duration' => $this->decimal('3,3')->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__settings_tracker_basic' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'type' => $this->smallInteger(1)->notNull(),
					'action' => $this->stringType(50)->notNull(),
					'record_id' => $this->integer(10),
					'module_name' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
				],
				'columns_mysql' => [
					'type' => $this->tinyInteger(1)->notNull(),
				],
				'primaryKeys' => [
					['settings_tracker_basic_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__settings_tracker_detail' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'prev_value' => $this->stringType()->notNull()->defaultValue(''),
					'post_value' => $this->stringType()->notNull()->defaultValue(''),
					'field' => $this->stringType()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__sqltime' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'type' => $this->stringType(20),
					'content' => $this->text(),
					'date' => $this->dateTime(),
					'qtime' => $this->decimal('20,3'),
					'group' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__switch_users' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'date' => $this->dateTime()->notNull(),
					'status' => $this->stringType(10)->notNull(),
					'baseid' => $this->integer(10)->notNull(),
					'destid' => $this->integer(10)->notNull(),
					'busername' => $this->stringType(50)->notNull(),
					'dusername' => $this->stringType(50)->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'agent' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['switch_users_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_for_admin' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'module' => $this->stringType(30)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
					'request' => $this->stringType(300)->notNull(),
					'referer' => $this->stringType(300),
				],
				'primaryKeys' => [
					['access_for_admin_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_for_api' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
					'request' => $this->stringType(300)->notNull(),
				],
				'primaryKeys' => [
					['access_for_api_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_for_user' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100),
					'module' => $this->stringType(30)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType(),
					'request' => $this->stringType(300)->notNull(),
					'referer' => $this->stringType(300),
				],
				'primaryKeys' => [
					['access_for_user_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_to_record' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'record' => $this->integer(10)->notNull(),
					'module' => $this->stringType(30)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
					'request' => $this->stringType(300)->notNull(),
					'referer' => $this->stringType(300),
				],
				'primaryKeys' => [
					['access_to_record_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__csrf' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'referer' => $this->stringType(300)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['csrf_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
		];
	}
}
