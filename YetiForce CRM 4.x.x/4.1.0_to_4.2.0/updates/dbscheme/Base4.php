<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base4 extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_reservations' => [
				'columns' => [
					'reservationsid' => $this->integer(10)->notNull()->defaultValue(0),
					'title' => $this->stringType(128),
					'reservations_no' => $this->stringType(),
					'reservations_status' => $this->stringType(128),
					'date_start' => $this->date()->notNull(),
					'time_start' => $this->stringType(50),
					'due_date' => $this->date(),
					'time_end' => $this->stringType(50),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'link' => $this->integer(10)->defaultValue(0),
					'process' => $this->integer(10)->defaultValue(0),
					'deleted' => $this->integer(1)->defaultValue(0),
					'type' => $this->stringType(128),
					'subprocess' => $this->integer(10)->defaultValue(0),
				],
				'index' => [
					['process', 'process'],
					['link', 'link'],
					['subprocess', 'subprocess'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
