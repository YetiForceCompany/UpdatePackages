<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Base extends \App\Db\Importers\Base
{
	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'roundcube_users_autologin' => [
				'columns' => [
					'active' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__pdf_inv_scheme' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'columns' => $this->text(),
				],
				'index' => [
					['crmid', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__servicecontracts_sla_policy' => [
				'columns' => [
					'id' => $this->primaryKey(11)->notNull(),
					'crmid' => $this->integer()->notNull(),
					'policy_type' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'sla_policy_id' => $this->integer(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'conditions' => $this->text()->notNull(),
					'reaction_time' => $this->stringType(20)->notNull()->defaultValue('0:H'),
					'idle_time' => $this->stringType(20)->notNull()->defaultValue('0:H'),
					'resolve_time' => $this->stringType(20)->notNull()->defaultValue('0:H'),
					'business_hours' => $this->text()->notNull(),
				],
				'columns_mysql' => [
					'policy_type' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['fk_crmid_idx', 'crmid'],
					['fk_sla_policy_idx', 'sla_policy_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_loginhistory' => [
				'columns' => [
					'user_name' => $this->stringType(64)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notification_type' => [
				'columns' => [
					'color' => $this->stringType(25),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'content' => $this->db->getSchema()->createColumnSchemaBuilder('MEDIUMTEXT'),
					'orginal_mail' => $this->db->getSchema()->createColumnSchemaBuilder('MEDIUMTEXT'),
					'attachments_exist' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->smallInteger(1)->unsigned(),
					'verify' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0)
				],
				'columns_mysql' => [
					'attachments_exist' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned(),
					'verify' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'user_name' => $this->stringType(64),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_menu' => [
				'columns' => [
					'source' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'source' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_user' => [
				'columns' => [
					'istorage' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebase' => [
				'columns' => [
					'content' => $this->db->getSchema()->createColumnSchemaBuilder('MEDIUMTEXT'),
					'featured' => $this->smallInteger(1)->defaultValue(0),
					'introduction' => $this->text(),
				],
				'columns_mysql' => [
					'featured' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__username_history' => [
				'columns' => [
					'user_name' => $this->stringType(64)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tab' => [
				'columns' => [
					'presence' => $this->smallInteger(3)->unsigned()->notNull()->defaultValue(1),
					'premium' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notification_type' => [
				'columns' => [
					'color' => $this->stringType(25)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			]
		];
		$this->foreignKey = [
			['fk_u_#__pdf_inv_scheme_crmid', 'u_#__pdf_inv_scheme', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['fk_crmid_idx', 'u_#__servicecontracts_sla_policy', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', null],
			['fk_sla_policy_idx', 'u_#__servicecontracts_sla_policy', 'sla_policy_id', 's_#__sla_policy', 'id', 'CASCADE', null]
		];
	}
}
