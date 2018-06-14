<?php
/**
 * Update fields.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
namespace Cron\Batch;

/**
 * Class for Update fields.
 */
class UpdateFields
{

	/**
	 * Preprocess
	 * @return bool
	 */
	public function preProcess()
	{
		return true;
	}

	/**
	 * Process
	 */
	public function process()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->from('vtiger_field')->createCommand()->query();
		while ($row = $dataReader->read()) {
			$fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($row['fieldid']);
			try {
				$maxValues = $fieldModel->getRangeValues($row['fieldid']);
				$dbCommand->update('vtiger_field', ['maximumlength' => $maxValues], ['fieldid' => $row['fieldid']])->execute();
			} catch (\App\Exceptions\AppException $ex) {
				echo 'ERROR update maximumlength: ' . $row['fieldid'] . ' - ' . $fieldModel->getFieldName() . ' - ' . $fieldModel->getTableName() . ' - ' . $fieldModel->getFieldDataType() . ' Message:' . $ex->getMessage() . PHP_EOL;
			}
		}
	}

	/**
	 * Process
	 */
	public function postProcess()
	{
		return true;
	}
}
