<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Base extends \App\Db\Importers\Base
{
	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'u_#__chat_messages_user' => [
				'index' => [
					['roomid', 'roomid'],
					['userid', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_user' => [
				'index' => [
					['roomid', 'roomid'],
					['userid', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_user' => [
				'index' => [
					['reluserid', 'reluserid'],
					['userid', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_quantities' => [
				'columns' => [
					'userid' => $this->integer(10)->unsigned()->notNull(),
					'num' => $this->smallInteger()->unsigned()->defaultValue(0),
					'date' => $this->dateTime(),
				],
				'primaryKeys' => [
					['mail_quantities_pk', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__servicecontracts_sla_policy' => [
				'columns' => [
					'conditions' => $this->text(),
					'business_hours' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocesses' => [
				'index' => [
					['ssalesprocesses_status', 'ssalesprocesses_status'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cron_task' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'last_update' => $this->integer(10),
					'max_exe_time' => $this->smallInteger(5),
					'description' => $this->stringType(255),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field' => [
				'columns' => [
					'icon' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_loginhistory' => [
				'columns' => [
					'user_ip' => $this->stringType(255),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__api_session' => [
				'columns' => [
					'parent_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__api_user' => [
				'index' => [
					['user_name', 'user_name'],
					['w_yf_api_user_server_id__idx', 'server_id'],
					['w_yf_api_user_user_name_status__idx', ['user_name', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__fields_server' => [
				'columns' => [
					'fieldid' => $this->integer(10)->notNull(),
					'serverid' => $this->integer(10)->unsigned()->notNull(),
					'visibility' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'is_default' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'default_value' => $this->text(),
				],
				'columns_mysql' => [
					'visibility' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'is_default' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['w_yf_fields_server_serverid_idx', 'serverid'],
				],
				'primaryKeys' => [
					['fields_server_pk', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_session' => [
				'columns' => [
					'parent_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_user' => [
				'columns' => [
					'preferences' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__auto_assign_rr' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'user' => $this->integer(10)->notNull(),
					'datetime' => $this->stringType(30)->notNull(),
				],
				'index' => [
					['s_yf_auto_assign_rr_id_date_idx', ['id', 'datetime']],
					['s_yf_auto_assign_rr_user_idx', 'user'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		// $this->dropTables = [
		// 	'roundcube_system', 'vtiger_blocks_hide'
		// ];
		$this->dropIndexes = [
			'vtiger_notes' => ['notes_notesid_idx']
		];
		$this->foreignKey = [
			['s_#__auto_assign_rr_id_fk', 'u_#__auto_assign_rr', 'id', 's_#__auto_assign', 'id', 'CASCADE', null],
			['s_#__auto_assign_rr_user_fk', 'u_#__auto_assign_rr', 'user', 'vtiger_users', 'id', 'CASCADE', null],
			['u_#__mail_quantities_ibfk_1', 'u_#__mail_quantities', 'userid', 'roundcube_users', 'user_id', 'CASCADE', null],
			['w_#__fields_server_ibfk_1', 'w_#__fields_server', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['w_#__fields_server_ibfk_2', 'w_#__fields_server', 'serverid', 'w_#__servers', 'id', 'CASCADE', null],
		];
	}
}
