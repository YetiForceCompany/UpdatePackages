<?php

class Migration
{

	private $columnLink = [
		'servicecontractsid' => 'sc_related_to',
		'projectid' => 'linktoaccountscontacts',
		'ticketid' => 'parent_id',
	];

	public function init()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query("SHOW COLUMNS FROM `vtiger_osstimecontrol` LIKE 'ticketid';");
		if ($result->rowCount()) {
			$this->migrationTimeControl();
		}
	}

	public function migrationTimeControl()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query('SELECT * FROM vtiger_osstimecontrol');
		while ($record = $db->fetch_array($result)) {
			$subprocessId = 0;
			$processId = 0;
			$link = 0;
			if (!empty($record['projecttaskid'])) {
				$subprocessId = $record['projecttaskid'];
				$columnIdName = 'projectid';
				$result2 = $db->pquery('SELECT * FROM vtiger_projecttask WHERE projecttaskid=?', [$subprocessId]);
				while ($record2 = $db->fetch_array($result2)) {
					$processId = $record2['projectid'];
					$metaData = Vtiger_Functions::getCRMRecordMetadata($processId);
					$focus = CRMEntity::getInstance($metaData['setype']);
					$table = $focus->table_name;
					$index = $focus->table_index;
					$result4 = $db->pquery('SELECT '.$this->columnLink[$columnIdName].' FROM '.$table.' WHERE '.$index.'=?', [$processId]);
					$link = $db->getSingleValue($result4);
				}
			} else {
				if (!empty($record['projectid']) || !empty($record['servicecontractsid']) || !empty($record['ticketid'])) {
					$columnIdName = '';
					if (!empty($record['projectid'])) {
						$columnIdName = 'projectid';
						$processId = $record['projectid'];
					} elseif (!empty($record['servicecontractsid'])) {
						$columnIdName = 'servicecontractsid';
						$processId = $record['servicecontractsid'];
					} elseif (!empty($record['ticketid'])) {
						$columnIdName = 'ticketid';
						$processId = $record['ticketid'];
					}
					$metaData = Vtiger_Functions::getCRMRecordMetadata($processId);
					$focus = CRMEntity::getInstance($metaData['setype']);
					$table = $focus->table_name;
					$index = $focus->table_index;
					$result4 = $db->pquery('SELECT '.$this->columnLink[$columnIdName].' FROM '.$table.' WHERE '.$index.'=?', [$processId]);
					$link = $db->getSingleValue($result4);
				} else {
					$link = empty($record['accountid']) ? $record['leadid'] : $record['accountid'];
				}
			}
			$db->pquery('UPDATE vtiger_osstimecontrol SET link=?, process=?, subprocess=? WHERE osstimecontrolid=?', [$link, $processId, $subprocessId, $record['osstimecontrolid']]);
		}
	}
}
