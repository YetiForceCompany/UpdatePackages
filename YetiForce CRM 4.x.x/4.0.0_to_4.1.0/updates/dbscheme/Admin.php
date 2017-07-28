<?php
namespace Importers;

/**
 * Class that imports admin database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Admin extends \DbType
{

	public $dbType = 'admin';

	public function scheme()
	{
		$this->tables = [
			'a_#__adv_permission' => [
				'columns' => [
					'id' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
					'name' => $this->stringType()->notNull(),
					'tabid' => $this->smallInteger(5),
					'status' => $this->smallInteger(1)->unsigned()->notNull(),
					'action' => $this->smallInteger(1)->unsigned()->notNull(),
					'conditions' => $this->text(),
					'members' => $this->text()->notNull(),
					'priority' => $this->smallInteger(1)->unsigned()->notNull(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull(),
					'action' => $this->tinyInteger(1)->unsigned()->notNull(),
					'priority' => $this->tinyInteger(1)->unsigned()->notNull(),
				],
				'primaryKeys' => [
					['adv_permission_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__bruteforce_blocked' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'ip' => $this->stringType(50)->notNull(),
					'time' => $this->timestamp()->null(),
					'attempts' => $this->smallInteger(2)->defaultValue(0),
					'blocked' => $this->smallInteger(1)->defaultValue(0),
					'userid' => $this->integer(10),
				],
				'columns_mysql' => [
					'attempts' => $this->tinyInteger(2)->defaultValue(0),
					'blocked' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['bruteforce_blocked_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__bruteforce_users' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['bruteforce_users_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__discounts_global' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->decimal('5,2')->unsigned()->notNull()->defaultValue(0),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['discounts_global_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__inventory_limits' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->integer(10)->unsigned()->notNull(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['inventory_limits_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__mapped_config' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'reltabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->defaultValue(0),
					'conditions' => $this->text(),
					'permissions' => $this->stringType(),
					'params' => $this->stringType(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->defaultValue(0),
				],
				'primaryKeys' => [
					['mapped_config_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__mapped_fields' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'mappedid' => $this->integer(10),
					'type' => $this->stringType(30),
					'source' => $this->stringType(30),
					'target' => $this->stringType(30),
					'default' => $this->stringType(),
				],
				'primaryKeys' => [
					['mapped_fields_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__relatedlists_inv_fields' => [
				'columns' => [
					'relation_id' => $this->integer(10),
					'fieldname' => $this->stringType(30),
					'sequence' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__smsnotifier_servers' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'providertype' => $this->stringType(50)->notNull(),
					'isactive' => $this->smallInteger(1)->defaultValue(0),
					'api_key' => $this->stringType()->notNull(),
					'parameters' => $this->text(),
				],
				'columns_mysql' => [
					'isactive' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__taxes_global' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->decimal('5,2')->unsigned()->notNull()->defaultValue(0),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['taxes_global_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__automatic_assignment' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'field' => $this->stringType(30)->notNull(),
					'value' => $this->stringType(),
					'roles' => $this->text(),
					'smowners' => $this->text(),
					'assign' => $this->smallInteger(5),
					'active' => $this->smallInteger(1)->defaultValue(1),
					'conditions' => $this->text(),
					'user_limit' => $this->smallInteger(1),
					'roleid' => $this->stringType(200),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(1),
					'user_limit' => $this->tinyInteger(1),
				],
				'primaryKeys' => [
					['automatic_assignment_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__handler_updater' => [
				'columns' => [
					'id' => $this->integer(10)->autoIncrement()->notNull(),
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'crmid' => $this->integer(10)->unsigned()->notNull(),
					'userid' => $this->integer(10)->unsigned()->notNull(),
					'handler_name' => $this->stringType(50)->notNull(),
					'class' => $this->stringType(50)->notNull(),
					'params' => $this->text()->notNull(),
				],
				'primaryKeys' => [
					['handler_updater_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_queue' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'smtp_id' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
					'date' => $this->dateTime()->notNull(),
					'owner' => $this->integer(10)->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'from' => $this->text(),
					'subject' => $this->text(),
					'to' => $this->text(),
					'content' => $this->text(),
					'cc' => $this->text(),
					'bcc' => $this->text(),
					'attachments' => $this->text(),
					'priority' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'priority' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'primaryKeys' => [
					['mail_queue_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_relation_updater' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->unsigned()->notNull(),
					'crmid' => $this->integer(10)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_smtp' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->autoIncrement()->notNull(),
					'mailer_type' => $this->stringType(10)->defaultValue('smtp'),
					'default' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'name' => $this->stringType()->notNull(),
					'host' => $this->stringType()->notNull(),
					'port' => $this->smallInteger(5)->unsigned(),
					'username' => $this->stringType(),
					'password' => $this->stringType(),
					'authentication' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'secure' => $this->stringType(10),
					'options' => $this->text(),
					'from_email' => $this->stringType(),
					'from_name' => $this->stringType(),
					'reply_to' => $this->stringType(),
					'individual_delivery' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'params' => $this->text(),
					'save_send_mail' => $this->smallInteger(1)->defaultValue(0),
					'smtp_host' => $this->stringType(),
					'smtp_port' => $this->smallInteger(5),
					'smtp_username' => $this->stringType(),
					'smtp_password' => $this->stringType(),
					'smtp_folder' => $this->stringType(50),
					'smtp_validate_cert' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'default' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'authentication' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'individual_delivery' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'save_send_mail' => $this->tinyInteger(1)->defaultValue(0),
					'smtp_validate_cert' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['mail_smtp_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__multireference' => [
				'columns' => [
					'source_module' => $this->stringType(50)->notNull(),
					'dest_module' => $this->stringType(50)->notNull(),
					'lastid' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'type' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__pbx' => [
				'columns' => [
					'pbxid' => $this->primaryKey(5)->unsigned(),
					'default' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'name' => $this->stringType(50),
					'type' => $this->stringType(50),
					'param' => $this->text(),
				],
				'columns_mysql' => [
					'default' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__privileges_updater' => [
				'columns' => [
					'module' => $this->stringType(30)->notNull()->defaultValue(''),
					'crmid' => $this->integer(10)->notNull()->defaultValue(0),
					'priority' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'priority' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__smsnotifier_queue' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'message' => $this->stringType()->notNull(),
					'tonumbers' => $this->text()->notNull(),
					'records' => $this->text()->notNull(),
					'module' => $this->stringType(30)->notNull(),
				],
				'primaryKeys' => [
					['smsnotifier_queue_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['fk_1_vtiger_bruteforce_users', 'a_#__bruteforce_users', 'id', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['a_#__mapped_fields_ibfk_1', 'a_#__mapped_fields', 'mappedid', 'a_#__mapped_config', 'id', 'CASCADE', 'RESTRICT'],
			['s_#__mail_queue_ibfk_1', 's_#__mail_queue', 'smtp_id', 's_#__mail_smtp', 'id', 'CASCADE', 'RESTRICT'],
		];
	}
}
