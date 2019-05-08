<?php
/**
 * YetiForceUpdate Class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * YetiForceUpdate Class.
 */
class YetiForceUpdate
{
	/**
	 * @var object
	 */
	public $modulenode;

	/**
	 * Fields to delete.
	 *
	 * @var string[]
	 */
	public $filesToDelete = [];

	/**
	 * Constructor.
	 *
	 * @param object $modulenode
	 */
	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
		$this->filesToDelete = require_once 'deleteFiles.php';
	}

	/**
	 * Preupdate.
	 */
	public function preupdate()
	{
		$minTime = 600;
		if (ini_get('max_execution_time') < $minTime || ini_get('max_input_time') < $minTime) {
			$this->package->_errorText = 'The server configuration is not compatible with the requirements of the upgrade package.' . PHP_EOL;
			$this->package->_errorText .= 'Please have a look at the list of errors:';
			if (ini_get('max_execution_time') < $minTime) {
				$this->package->_errorText .= PHP_EOL . 'max_execution_time = ' . ini_get('max_execution_time') . ' < ' . $minTime;
			}
			if (ini_get('max_input_time') < $minTime) {
				$this->package->_errorText .= PHP_EOL . 'max_input_time = ' . ini_get('max_input_time') . ' < ' . $minTime;
			}
			return false;
		}
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$this->updateData();
		$this->addPicklist();
		return true;
	}

	private function updateData()
	{
		$data = [
			['vtiger_cron_task', ['handler_file' => 'cron/CardDav.php'], ['name' => 'LBL_CARD_DAV']],
			['vtiger_cron_task', ['handler_file' => 'cron/CalDav.php'], ['name' => 'LBL_CAL_DAV']],
		];
		\App\Db\Updater::batchUpdate($data);
	}

	private function addPicklist()
	{
		$moduleModel = Settings_Picklist_Module_Model::getInstance('OSSTimeControl');
		$fieldModel = Settings_Picklist_Field_Model::getInstance('timecontrol_type', $moduleModel);
		if (!$fieldModel) {
			return;
		}
		$picklistValues = App\Fields\Picklist::getValuesName('timecontrol_type');
		if (!in_array('PLL_UNPAID_LEAVE', $picklistValues)) {
			$result = $moduleModel->addPickListValues($fieldModel, 'PLL_UNPAID_LEAVE');
			App\Colors::updatePicklistValueColor($fieldModel->getId(), $result['id'], '#5E666C');
		}
		if (!in_array('PLL_SICK_LEAVE', $picklistValues)) {
			$result = $moduleModel->addPickListValues($fieldModel, 'PLL_SICK_LEAVE');
			App\Colors::updatePicklistValueColor($fieldModel->getId(), $result['id'], '#9900FF');
		}
	}

	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		return true;
	}
}
