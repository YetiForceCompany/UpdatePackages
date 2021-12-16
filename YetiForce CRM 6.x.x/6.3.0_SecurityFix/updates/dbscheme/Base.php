<?php
/**
 * Basic database structure file.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
			'u_#__occurrences' => [
				'columns' => [
					'participants' => $this->integer(8)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_account' => [
				'columns' => [
					'employees' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_callhistory' => [
				'columns' => [
					'duration' => $this->integer(10)->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_campaign' => [
				'columns' => [
					'expectedrevenue' => $this->decimal('28,8')->unsigned(),
					'budgetcost' => $this->decimal('28,8')->unsigned(),
					'actualcost' => $this->decimal('28,8')->unsigned(),
					'numsent' => $this->decimal('11,0')->unsigned(),
					'targetsize' => $this->integer(10)->unsigned(),
					'expectedresponsecount' => $this->integer(10)->unsigned(),
					'expectedsalescount' => $this->integer(10)->unsigned(),
					'expectedroi' => $this->decimal('28,8')->unsigned(),
					'actualresponsecount' => $this->integer(10)->unsigned(),
					'actualsalescount' => $this->integer(10)->unsigned(),
					'actualroi' => $this->decimal('28,8')->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_leaddetails' => [
				'columns' => [
					'noofemployees' => $this->integer(10)->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_outsourcedproducts' => [
				'columns' => [
					'prodcount' => $this->integer(10)->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_products' => [
				'columns' => [
					'weight' => $this->decimal('11,3')->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_project' => [
				'columns' => [
					'targetbudget' => $this->integer(10)->unsigned(),
					'estimated_work_time' => $this->decimal('15,2')->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_projectmilestone' => [
				'columns' => [
					'estimated_work_time' => $this->decimal('15,2')->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_projecttask' => [
				'columns' => [
					'estimated_work_time' => $this->decimal('8,2')->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_servicecontracts' => [
				'columns' => [
					'total_units' => $this->decimal('5,2')->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'vtiger_users' => [
				'columns' => [
					'records_limit' => $this->integer(10)->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'u_#__cfixedassets' => [
				'columns' => [
					'purchase_price' => $this->decimal('28,8')->unsigned(),
					'actual_price' => $this->decimal('28,8')->unsigned(),
					'timing_change' => $this->integer(10)->unsigned()->defaultValue(0),
					'oil_change' => $this->integer(10)->unsigned(),
					'fuel_consumption' => $this->integer(10)->unsigned(),
					'current_odometer_reading' => $this->integer(10)->unsigned(),
					'number_repair' => $this->smallInteger(5)->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'u_#__cmileagelogbook' => [
				'columns' => [
					'number_kilometers' => $this->decimal('13,2')->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'u_#__incidentregister' => [
				'columns' => [
					'peoplne_number' => $this->integer(9)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
			'u_#__locations' => [
				'columns' => [
					'capacity' => $this->integer(8)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8',
			],
		];
	}
}
