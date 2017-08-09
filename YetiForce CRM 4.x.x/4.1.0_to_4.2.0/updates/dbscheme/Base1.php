<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base1 extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'com_vtiger_workflowtasks' => [
				'columns' => [
					'task_id' => $this->integer(10)->autoIncrement()->notNull(),
					'workflow_id' => $this->integer(10),
					'summary' => $this->stringType(400)->notNull(),
					'task' => $this->text(),
				],
				'index' => [
					['workflow_id', 'workflow_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['com_vtiger_workflowtasks_ibfk_1', 'com_vtiger_workflowtasks', 'workflow_id', 'com_vtiger_workflows', 'workflow_id', 'CASCADE', 'RESTRICT'],
		];
	}
}
