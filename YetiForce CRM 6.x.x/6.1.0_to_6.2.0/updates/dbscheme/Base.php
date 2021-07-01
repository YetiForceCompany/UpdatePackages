<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base extends \App\Db\Importers\Base
{
	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_ssalesprocesses_status' => [
				'columns' => [
					'record_state' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'record_state' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists' => [
				'columns' => [
					'custom_view' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_blocks' => [
				'columns' => [
					'label' => $this->stringType()->notNull(),
					'sequence' => $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'linkto' => $this->stringType()
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_field' => [
				'columns' => [
					'name' => $this->stringType()->notNull(),
					'iconpath' => $this->stringType(),
					'description' => $this->stringType(),
					'linkto' => $this->stringType()->notNull(),
					'sequence' => $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
					'active' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'pinned' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'premium' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0)
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(0),
					'active' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'pinned' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'premium' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventhandlers' => [
				'columns' => [
					'privileges' => $this->smallInteger(1)->defaultValue(1),
				],
				'columns_mysql' => [
					'privileges' => $this->tinyInteger(1)->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_label' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_search_label' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cv_privileges' => [
				'columns' => [
					'member' => $this->stringType(30)->notNull(),
					'cvid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['cv_privileges_pk', ['cvid', 'member']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_user_module_preferences' => [
				'index' => [
					['vtiger_user_module_preferences_default_cvid_fk', 'default_cvid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->dropTables = [
			'roundcube_system', 'vtiger_blocks_hide'
		];
		$this->foreignKey = [
			['u_#__finvoiceproforma_address_fk1', 'u_#__finvoiceproforma_address', 'finvoiceproformaaddressid', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'CASCADE', null],
			['u_#__recurring_info_fk1', 'u_#__recurring_info', 'srecurringordersid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', null],
			['fk_u_#__crmentity_label', 'u_#__crmentity_label', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['fk_u_#__crmentity_search_label', 'u_#__crmentity_search_label', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['u_#__cv_privileges_cvid_fk', 'u_#__cv_privileges', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', null],
			['vtiger_user_module_preferences_default_cvid_fk', 'vtiger_user_module_preferences', 'default_cvid', 'vtiger_customview', 'cvid', 'CASCADE', null],
		];
	}
}
