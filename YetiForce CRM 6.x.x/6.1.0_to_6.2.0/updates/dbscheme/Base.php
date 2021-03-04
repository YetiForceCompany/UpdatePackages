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
		];
		$this->dropTables = [
			'roundcube_system', 'vtiger_blocks_hide'
		];
		$this->foreignKey = [
			['u_#__finvoiceproforma_address_fk1', 'u_#__finvoiceproforma_address', 'finvoiceproformaaddressid', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'CASCADE', null],
			['u_#__recurring_info_fk1', 'u_#__recurring_info', 'srecurringordersid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', null],
		];
	}
}
