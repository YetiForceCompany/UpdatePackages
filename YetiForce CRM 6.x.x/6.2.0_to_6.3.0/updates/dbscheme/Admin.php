<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Admin extends \App\Db\Importers\Base
{
	public $dbType = 'admin';

	public function scheme()
	{
		$this->tables = [
			'a_#__encryption' => [
				'columns' => [
					'target' => $this->smallInteger(5)->notNull()->first(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(1),
				],
				'index' => [
					['a_yf_encryption_target_uidx', 'target', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'a_#__record_list_filter' => [
				'columns' => [
					'label' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			's_#__auto_assign' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'state' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'tabid' => $this->smallInteger(5)->notNull(),
					'subject' => $this->stringType(100)->notNull(),
					'workflow' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'handler' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'gui' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'default_assign' => $this->integer(10)->unsigned(),
					'conditions' => $this->text(),
					'method' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'record_limit_conditions' => $this->text(),
					'record_limit' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'state' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'workflow' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'handler' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'gui' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'method' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['s_yf_auto_assign_tabid_idx', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			's_#__auto_assign_groups' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'member' => $this->integer(10)->notNull(),
					'type' => $this->stringType(50)->notNull(),
				],
				'index' => [
					['s_yf_auto_assign_groups_id_idx', 'id'],
					['s_yf_auto_assign_groups_member_idx', 'member'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			's_#__auto_assign_roles' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'member' => $this->stringType()->notNull(),
					'type' => $this->stringType(50)->notNull(),
				],
				'index' => [
					['s_yf_auto_assign_roles_id_idx', 'id'],
					['s_yf_auto_assign_roles_member_idx', 'member'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			's_#__auto_assign_users' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'member' => $this->integer(10)->notNull(),
					'type' => $this->stringType(50)->notNull(),
				],
				'index' => [
					['s_yf_auto_assign_users_id_idx', 'id'],
					['s_yf_auto_assign_users_member_idx', 'member'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			's_#__kanban_boards' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(11),
					'tabid' => $this->smallInteger(5)->notNull(),
					'fieldid' => $this->integer(10)->notNull(),
					'detail_fields' => $this->text(),
					'sum_fields' => $this->text(),
					'sequence' => $this->integer()->unsigned()->notNull(),
				],
				'index' => [
					['s_yf_kanban_boards_fieldid_idx', 'fieldid', true],
					['s_yf_kanban_boards_tabid_idx', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			's_#__mail_rbl_list' => [
				'columns' => [
					'comment' => $this->stringType(500)->after('source'),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			's_#__sla_policy' => [
				'columns' => [
					'available_for_record_time_count' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'available_for_record_time_count' => $this->tinyInteger(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			's_#__smsnotifier_queue' => [
				'columns' => [
					'message' => $this->stringType(500)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
		];
		$this->foreignKey = [
			['s_#__auto_assign_tabid_fk', 's_#__auto_assign', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['s_#__auto_assign_groups_id_fk', 's_#__auto_assign_groups', 'id', 's_#__auto_assign', 'id', 'CASCADE', null],
			['s_#__auto_assign_groups_member_fk', 's_#__auto_assign_groups', 'member', 'vtiger_groups', 'groupid', 'CASCADE', null],
			['s_#__auto_assign_roles_id_fk', 's_#__auto_assign_roles', 'id', 's_#__auto_assign', 'id', 'CASCADE', null],
			['s_#__auto_assign_roles_role_fk', 's_#__auto_assign_roles', 'member', 'vtiger_role', 'roleid', 'CASCADE', null],
			['s_#__auto_assign_users_id_fk', 's_#__auto_assign_users', 'id', 's_#__auto_assign', 'id', 'CASCADE', null],
			['s_#__auto_assign_users_member_fk', 's_#__auto_assign_users', 'member', 'vtiger_users', 'id', 'CASCADE', null],
			['s_#__kanban_boards_fieldid_fk', 's_#__kanban_boards', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['s_#__kanban_boards_tabid_fk', 's_#__kanban_boards', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
		];
	}
}
