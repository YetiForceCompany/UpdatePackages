<?php
/**
 * ServiceContracts PolicySaveAjax Action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_PolicySaveAjax_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$record = Vtiger_DetailView_Model::getInstance($request->getModule(), $request->getInteger('record'));
		if (!$record->getRecord()->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$result = [];
		$data = ['policy_type' => 0];
		switch ($request->getInteger('policyType')) {
			case 2: // custom
				$result = $this->saveCustomRecords($request);
				break;
			case 1: // template
				$data['policy_type'] = 1;
				$data['sla_policy_id'] = $request->getInteger('policyId');
				// no break
			case 0:
			default:
				$data['crmid'] = $request->getInteger('record');
				$data['tabid'] = \App\Module::getModuleId($request->getByType('targetModule', 'Alnum'));
				$result = ['id' => \App\Utils\ServiceContracts::saveSlaPolicy($data)];
				break;
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Save custom records.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function saveCustomRecords(App\Request $request)
	{
		$crmId = $request->getInteger('record');
		$targetModule = \App\Module::getModuleId($request->getByType('targetModule', 'Alnum'));
		$result = [];
		\App\Utils\ServiceContracts::deleteSlaPolicy($crmId, $targetModule);
		foreach ($request->getArray('rowid', 'Integer') as $rowIndex => $rowId) {
			$data = [];
			$data['policy_type'] = 2;
			if ($rowConditions = \App\Json::decode($request->getArray('conditions', 'Text')[$rowIndex])) {
				$data['conditions'] = \App\Json::encode(\App\Condition::getConditionsFromRequest($rowConditions));
			} else {
				$data['conditions'] = '';
			}
			$data['business_hours'] = implode(',', $request->getArray('business_hours', 'Integer')[$rowIndex]);
			$data['reaction_time'] = $request->getArray('reaction_time', 'TimePeriod')[$rowIndex];
			$data['idle_time'] = $request->getArray('idle_time', 'TimePeriod')[$rowIndex];
			$data['resolve_time'] = $request->getArray('resolve_time', 'TimePeriod')[$rowIndex];
			$data['crmid'] = $crmId;
			$data['tabid'] = $targetModule;
			$data['id'] = \App\Utils\ServiceContracts::saveSlaPolicy($data, false);
			$result[] = $data;
		}
		return $result;
	}
}
