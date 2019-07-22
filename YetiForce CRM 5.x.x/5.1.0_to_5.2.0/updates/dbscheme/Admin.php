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
	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'a_#__pdf' => [
				'columns' => [
					'type' => $this->smallInteger(1)->unsigned()->defaultValue(0),
				],
				'columns_mysql' => [
					'type' => $this->tinyInteger(1)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__record_converter' => [
				'columns' => [
					'id' => $this->smallInteger(10)->autoIncrement()->notNull(),
					'name' => $this->stringType(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'source_module' => $this->smallInteger(5)->notNull(),
					'destiny_module' => $this->stringType()->notNull(),
					'field_merge' => $this->stringType(50),
					'field_mapping' => $this->text(),
					'inv_field_mapping' => $this->text(),
					'redirect_to_edit' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'check_duplicate' => $this->smallInteger(1),
					'show_in_list' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'show_in_detail' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'redirect_to_edit' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'check_duplicate' => $this->tinyInteger(1),
					'show_in_list' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'show_in_detail' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['a_yf_record_converter_fk_tab', 'source_module'],
					['status', 'status'],
					['show_in_list', 'show_in_list'],
					['show_in_detail', 'show_in_detail'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__auto_record_flow_updater' => [
				'columns' => [
					'id' => $this->smallInteger(5)->unsigned()->autoIncrement()->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'source_module' => $this->smallInteger(5)->notNull(),
					'target_module' => $this->smallInteger(5)->notNull(),
					'source_field' => $this->stringType(50)->notNull(),
					'target_field' => $this->stringType(50)->notNull(),
					'default_value' => $this->stringType()->notNull(),
					'relation_field' => $this->stringType(50)->notNull(),
					'rules' => $this->text()->notNull(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['source_module', 'source_module'],
					['target_module', 'target_module'],
					['status', 'status'],
				],
				'primaryKeys' => [
					['auto_record_flow_updater_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__batchmethod' => [
				'columns' => [
					'method' => $this->stringType(255)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__business_hours' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10)->notNull(),
					'name' => $this->stringType()->notNull(),
					'working_days' => $this->stringType(15)->notNull(),
					'working_hours_from' => $this->stringType(8)->notNull()->defaultValue('00:00:00'),
					'working_hours_to' => $this->stringType(8)->notNull()->defaultValue('00:00:00'),
					'holidays' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'reaction_time' => $this->stringType(20)->notNull()->defaultValue('0:m'),
					'idle_time' => $this->stringType(20)->notNull()->defaultValue('0:m'),
					'resolve_time' => $this->stringType(20)->notNull()->defaultValue('0:m'),
					'default' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'holidays' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'default' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['business_hours_holidays_idx', 'holidays'],
					['business_hours_default_idx', 'default'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__sla_policy' => [
				'columns' => [
					'id' => $this->primaryKey(11)->notNull(),
					'name' => $this->stringType()->notNull(),
					'operational_hours' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'tabid' => $this->smallInteger(5)->notNull(),
					'conditions' => $this->text()->notNull(),
					'reaction_time' => $this->stringType(20)->notNull()->defaultValue('0:m'),
					'idle_time' => $this->stringType(20)->notNull()->defaultValue('0:m'),
					'resolve_time' => $this->stringType(20)->notNull()->defaultValue('0:m'),
					'business_hours' => $this->text()->notNull(),
				],
				'columns_mysql' => [
					'operational_hours' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['fk_s_yf_sla_policy', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['s_#__auto_record_flow_updater_ibfk_1', 's_#__auto_record_flow_updater', 'source_module', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['s_#__auto_record_flow_updater_ibfk_2', 's_#__auto_record_flow_updater', 'target_module', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_s_#__sla_policy', 's_#__sla_policy', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
		];
	}
}
