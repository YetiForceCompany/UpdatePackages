<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base2 extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'vtiger_activity' => [
				'index' => [
					['activity_activityid_subject_idx', ['activityid', 'subject']],
					['activity_activitytype_date_start_idx', ['activitytype', 'date_start']],
					['activity_date_start_due_date_idx', ['date_start', 'due_date']],
					['activity_date_start_time_start_idx', ['date_start', 'time_start']],
					['activity_status_idx', 'status'],
					['activitytype_2', ['activitytype', 'date_start', 'due_date', 'time_start', 'time_end', 'deleted', 'smownerid']],
					['link', 'link'],
					['process', 'process'],
					['followup', 'followup'],
					['subprocess', 'subprocess'],
					['activitytype_3', ['activitytype', 'status']],
					['smownerid', 'smownerid'],
					['linkextend', 'linkextend'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrol' => [
				'index' => [
					['on_update_cascade', 'deleted'],
					['osstimecontrol_status_9', ['osstimecontrol_status', 'deleted']],
					['osstimecontrol_status_6', 'osstimecontrol_status'],
					['subprocess', 'subprocess'],
					['link', 'link'],
					['process', 'process'],
					['linkextend', 'linkextend'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
