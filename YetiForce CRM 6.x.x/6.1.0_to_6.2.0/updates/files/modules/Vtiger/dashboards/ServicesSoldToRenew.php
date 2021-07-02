<?php

/**
 * ServicesSoldToRenew Dashboard Class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_ServicesSoldToRenew_Dashboard extends Vtiger_ProductsSoldToRenew_Dashboard
{
	public function setData($data)
	{
		if (empty($data['orderby'])) {
			$data['orderby'] = 'dateinservice';
			$data['sortorder'] = 'asc';
		}
		return $this->data = $data;
	}

	public function getTargetModule()
	{
		return 'OSSSoldServices';
	}

	public function getTargetFields()
	{
		return ['id', 'productname', 'parent_id', 'dateinservice'];
	}

	public function getFieldNameToSecondButton()
	{
		return 'osssoldservices_renew';
	}

	/**
	 * {@inheritdoc}.
	 */
	public function getConditions(): array
	{
		return [['ssservicesstatus', 'e', 'PLL_ACCEPTED'], ['osssoldservices_renew', 'e', 'PLL_WAITING_FOR_RENEWAL']];
	}
}
