<?php

/**
 * TotalEmails class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author YetiForce.com
 */
class TotalEmails
{
	public $name = 'Total emails';
	public $sequence = 1;
	public $reference = 'OSSMailView';

	/**
	 * Function get number of emails.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int - Number of emails
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $this->reference);

		return (int) $relationListView->getRelatedEntriesCount();
	}
}
