<?php
/**
 * Basic database structure file.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Importers;

/**
 * Basic database structure class.
 */
class Base extends \App\Db\Importers\Base
{
	/** {@inheritdoc} */
	public $dbType = 'base';

	/**
	 * Scheme.
	 *
	 * @return void
	 */
	public function scheme(): void
	{
		$this->tables = [
			's_#__reg_data' => [
				'columns' => [
					'method' => $this->stringType(50)->notNull(),
					'vector' => $this->stringType(100),
					'pass' => $this->stringType(10),
					'key' => $this->stringType(100),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__tokens' => [
				'columns' => [
					'one_time_use' => $this->smallInteger(1)->unsigned()->notNull(),
				],
				'columns_mysql' => [
					'one_time_use' => $this->tinyInteger(1)->unsigned()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtask_queue' => [
				'index' => [
					['workflowtask_queue_task_id_entity_id', ['task_id', 'entity_id']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'dav_users' => [
				'columns' => [
					'userid' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'u_#__modentity_sequences' => [
				'index' => [
					['u_yf_modentity_sequences_tabid_idx', ['tabid', 'value'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notes' => [
				'columns' => [
					'filename' => $this->stringType(400),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'records_limit' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['fk_dav_users', 'dav_users', 'userid', 'vtiger_users', 'id', 'CASCADE', NULL],
			['u_yf_modentity_sequences_tabid_fk', 'u_yf_modentity_sequences', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', NULL],
		];
	}
}
