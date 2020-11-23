<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
					'pass' => $this->stringType(32)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__smsnotifier_servers' => [
				'columns' => [
					'api_key' => $this->stringType(500)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_smtp' => [
				'columns' => [
					'password' => $this->stringType(500),
					'smtp_password' => $this->stringType(500),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__meeting_services' => [
				'columns' => [
					'id' => 'int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
					'url' => $this->stringType(),
					'key' => $this->stringType(64),
					'secret' => $this->stringType(500),
					'duration' => $this->smallInteger()->unsigned()->defaultValue(1),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__record_quick_changer' => [
				'columns' => [
					'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
					'tabid' => $this->smallInteger(5)->notNull(),
					'conditions' => $this->text()->notNull(),
					'values' => $this->text()->notNull(),
					'btn_name' => $this->stringType(),
					'class' => $this->stringType(50),
					'icon' => $this->stringType(50),
				],
				'index' => [
					['tabid', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__relatedlists_inv_fields' => [
				'columns' => [
					'relation_id' => $this->smallInteger(5)->unsigned()->notNull()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__relatedlists_widgets' => [
				'columns' => [
					'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
					'relation_id' => $this->smallInteger(5)->unsigned()->notNull(),
					'type' => $this->stringType(30),
					'label' => $this->stringType(100),
					'wcol' => $this->smallInteger(1)->defaultValue(1),
					'sequence' => $this->smallInteger(2),
					'data' => $this->text(),
				],
				'columns_mysql' => [
					'wcol' => $this->tinyInteger(1)->defaultValue(1),
					'sequence' => $this->tinyInteger(2),
				],
				'index' => [
					['relation_id', 'relation_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__settings_access' => [
				'columns' => [
					'module_id' => $this->smallInteger(5)->unsigned()->notNull(),
					'user' => $this->integer(10)->notNull(),
				],
				'index' => [
					['a_yf_settings_access_module_id_user_idx', ['module_id', 'user'], true],
					['a_yf_settings_access_user_fk', 'user'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__settings_modules' => [
				'columns' => [
					'id' => 'smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
					'name' => $this->stringType()->notNull(),
					'status' => $this->smallInteger(1)->notNull(),
					'created_time' => $this->dateTime()->notNull(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull(),
				],
				'index' => [
					['a_yf_settings_modules_name_status_idx', ['name', 'status'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'b_#__interests_conflict_conf' => [
				'columns' => [
					'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
					'date_time' => $this->dateTime()->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'user_id' => $this->smallInteger(5)->unsigned()->notNull(),
					'related_id' => $this->integer(10)->unsigned()->notNull(),
					'related_label' => $this->stringType()->notNull(),
					'source_id' => $this->integer(10)->notNull()->defaultValue(0),
					'modify_user_id' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'modify_date_time' => $this->dateTime(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['related_id', 'related_id'],
					['user_id', 'user_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__fields_dependency' => [
				'columns' => [
					'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
					'tabid' => $this->smallInteger(5)->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'name' => $this->stringType(100),
					'views' => $this->stringType(),
					'gui' => $this->smallInteger(1)->unsigned()->notNull(),
					'mandatory' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'fields' => $this->text()->notNull(),
					'conditions' => $this->text(),
					'conditionsFields' => $this->text(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'gui' => $this->tinyInteger(1)->unsigned()->notNull(),
					'mandatory' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['status', 'status'],
					['tabid', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_queue' => [
				'columns' => [
					'params' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_rbl_list' => [
				'columns' => [
					'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
					'ip' => $this->stringType(40)->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'source' => $this->stringType(20)->notNull(),
					'request' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['ip', 'ip'],
					['status', 'status'],
					['type', 'type'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_rbl_request' => [
				'columns' => [
					'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'datetime' => $this->dateTime()->notNull(),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'user' => $this->integer(10)->unsigned()->notNull(),
					'header' => $this->text()->notNull(),
					'body' => $this->mediumText(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['datetime', 'datetime'],
					['status', 'status'],
					['type', 'type'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['a_#__relatedlists_inv_fields_ibfk_1', 'a_#__relatedlists_inv_fields', 'relation_id', 'vtiger_relatedlists', 'relation_id', 'CASCADE', null],
			['a_#__relatedlists_widgets_ibfk_1', 'a_#__relatedlists_widgets', 'relation_id', 'vtiger_relatedlists', 'relation_id', 'CASCADE', null],
			['a_#__settings_access_module_id_fk', 'a_#__settings_access', 'module_id', 'a_#__settings_modules', 'id', 'CASCADE', null],
			['a_#__settings_access_user_fk', 'a_#__settings_access', 'user', 'vtiger_users', 'id', 'CASCADE', null],
			['s_#__fields_dependency_ibfk_1', 's_#__fields_dependency', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null],
			['s_#__record_quick_changer_ibfk_1', 's_#__record_quick_changer', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', null]
		];
	}
}
