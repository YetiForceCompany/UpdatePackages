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
					'id' => $this->primaryKey(10),
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
					'id' => $this->primaryKey(10)->unsigned(),
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
			]
		];
		$this->foreignKey = [
			['s_#__record_quick_changer_ibfk_1', 's_#__record_quick_changer', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', '']
		];
	}
}
