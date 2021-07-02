<?php
/**
 * Action file to save record.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\RestApi;

/**
 * Action class to save record.
 */
class Save extends \Vtiger_Save_Action
{
	/** @var int ID of application. */
	protected $appId;
	/** @var array Skipped value. */
	public $skippedData = [];

	/**
	 * Constructor removed.
	 */
	public function __construct()
	{
	}

	/**
	 * Initialization with API data.
	 *
	 * @param BaseModule\Record $record
	 *
	 * @return void
	 */
	public function init(BaseModule\Record $record): void
	{
		$this->appId = $record->controller->app['id'];
		$this->record = $record->recordModel;
	}

	/** {@inheritdoc}  */
	protected function getRecordModelFromRequest(\App\Request $request)
	{
		$fieldModelList = $this->record->getModule()->getFields();
		$requestKeys = $request->getAllRaw();
		unset($requestKeys['module'],$requestKeys['action'],$requestKeys['record']);
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if (!$fieldModel->isWritable()) {
				continue;
			}
			if ($request->has($fieldName)) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $this->record);
				unset($requestKeys[$fieldName]);
			}
		}
		if ($request->has('inventory') && $this->record->getModule()->isInventory()) {
			$this->record->initInventoryDataFromRequest($request);
			unset($requestKeys['inventory']);
		}
		$fieldInfo = \Api\Core\Module::getApiFieldPermission($request->getModule(), $this->appId);
		if ($fieldInfo) {
			$this->record->setDataForSave([$fieldInfo['tablename'] => [$fieldInfo['columnname'] => 1]]);
		}
		$this->skippedData = array_keys($requestKeys);
		return $this->record;
	}
}
