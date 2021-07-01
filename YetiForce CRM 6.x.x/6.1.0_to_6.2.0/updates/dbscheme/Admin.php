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
			'a_#__record_converter' => [
				'columns' => [
					'id' => $this->smallInteger(10)->unsigned()->autoIncrement()->notNull(),
					'conditions' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__record_converter_mapping' => [
				'columns' => [
					'id' => $this->smallInteger(10)->unsigned()->notNull(),
					'dest_module' => $this->smallInteger(5)->notNull(),
					'source_field' => $this->integer(10)->notNull(),
					'dest_field' => $this->integer(10)->notNull(),
					'state' => $this->smallInteger(1)->unsigned()->defaultValue(1),
				],
				'columns_mysql' => [
					'state' => $this->tinyInteger(1)->unsigned()->defaultValue(1),
				],
				'index' => [
					['a_yf_record_converter_mapping_dest_field', 'dest_field'],
					['a_yf_record_converter_mapping_id', 'id'],
					['a_yf_record_converter_mapping_source_field', 'source_field'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__record_list_filter' => [
				'columns' => [
					'id' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
					'relationid' => $this->smallInteger(5)->unsigned()->notNull(),
					'rel_relationid' => $this->smallInteger(5)->unsigned()->notNull(),
					'dest_relationid' => $this->smallInteger(5)->unsigned()->notNull(),
				],
				'index' => [
					['a_yf_record_list_filter_relationid_idx', 'relationid'],
					['a_yf_record_list_filter_rel_relationid_idx', 'rel_relationid'],
					['a_yf_record_list_filter_dest_relationid_idx', 'dest_relationid'],
				],
				'primaryKeys' => [
					['record_list_filter_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__fields_anonymization' => [
				'columns' => [
					'field_id' => $this->integer(10)->notNull(),
					'anonymization_target' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['fields_anonymization_pk', 'field_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__batchmethod' => [
				'columns' => [
					'created_time' => $this->dateTime()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__pauser' => [
				'columns' => [
					'key' => $this->stringType(50)->notNull(),
					'value' => $this->stringType(100)->notNull(),
				],
				'index' => [
					['key', 'key'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__api_session' => [
				'columns' => [
					'id' => $this->char(64)->notNull(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'language' => $this->stringType(10),
					'created' => $this->dateTime()->notNull(),
					'changed' => $this->dateTime(),
					'params' => $this->text(),
					'ip' => $this->stringType(100)->notNull(),
					'last_method' => $this->stringType(100),
					'agent' => $this->stringType(100)->notNull(),
				],
				'index' => [
					['user_id', 'user_id'],
				],
				'primaryKeys' => [
					['api_session_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__api_user' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'server_id' => $this->integer(10)->unsigned()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'user_name' => $this->stringType(100)->notNull(),
					'password' => $this->stringType(500),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'login_time' => $this->dateTime(),
					'crmid' => $this->integer(10),
					'user_id' => $this->integer(10),
					'login_method' => $this->stringType(30)->notNull()->defaultValue('PLL_PASSWORD'),
					'auth' => $this->stringType(500),
					'custom_params' => $this->text(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['server_id', 'server_id'],
					['user_name', 'user_name'],
					['user_name_status', ['user_name', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_session' => [
				'columns' => [
					'id' => $this->char(64)->notNull(),
					'created' => $this->dateTime()->notNull(),
					'changed' => $this->dateTime(),
					'params' => $this->text(),
					'ip' => $this->stringType(100)->notNull(),
					'last_method' => $this->stringType(100),
					'agent' => $this->stringType(100)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_user' => [
				'columns' => [
					'user_name' => $this->stringType(100)->notNull(),
					'login_time' => $this->dateTime(),
					'login_method' => $this->stringType(30)->notNull()->defaultValue('PLL_PASSWORD'),
					'auth' => $this->stringType(500),
					'custom_params' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__tokens' => [
				'columns' => [
					'uid' => $this->char(64)->notNull(),
					'method' => $this->stringType()->notNull(),
					'params' => $this->text()->notNull(),
					'created_by_user' => $this->integer(10)->notNull(),
					'created_date' => $this->dateTime()->notNull(),
					'expiration_date' => $this->dateTime(),
				],
				'primaryKeys' => [
					['tokens_pk', 'uid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__servers' => [
				'columns' => [
					'type' => $this->stringType(40)->notNull(),
					'name' => $this->stringType(100)->notNull(),
					'pass' => $this->stringType(500)->notNull(),
					'api_key' => $this->stringType(500)->notNull(),
					'url' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__manage_consents_user' => [
				'columns' => [
					'custom_params' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['a_#__record_converter_mapping_fk1', 'a_#__record_converter_mapping', 'id', 'a_#__record_converter', 'id', 'CASCADE', null],
			['a_#__record_converter_mapping_fk2', 'a_#__record_converter_mapping', 'source_field', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['a_#__record_converter_mapping_fk3', 'a_#__record_converter_mapping', 'dest_field', 'vtiger_field', 'fieldid', 'CASCADE', null],
			['a_#__record_list_filter_dest_relationid_idx', 'a_#__record_list_filter', 'dest_relationid', 'vtiger_relatedlists', 'relation_id', 'CASCADE', null],
			['a_#__record_list_filter_rel_relationid_idx', 'a_#__record_list_filter', 'rel_relationid', 'vtiger_relatedlists', 'relation_id', 'CASCADE', null],
			['a_#__record_list_filter_relationid_idx', 'a_#__record_list_filter', 'relationid', 'vtiger_relatedlists', 'relation_id', 'CASCADE', null],
			['s_#__fields_anonymization_fieldid_fk', 's_#__fields_anonymization', 'field_id', 'vtiger_field', 'fieldid', 'CASCADE', null],
		];
	}
}
