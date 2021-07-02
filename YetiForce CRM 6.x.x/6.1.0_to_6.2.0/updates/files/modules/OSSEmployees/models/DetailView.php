<?php

/**
 * OSSEmployees DetailView model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSEmployees_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewLinks(array $linkParams): array
	{
		$linkModelLists = parent::getDetailViewLinks($linkParams);
		if (!$this->getRecord()->isReadOnly()) {
			$linkURL = 'index.php?module=OSSEmployees&view=EmployeeHierarchy&record=' . $this->getRecord()->getId();
			$linkModelLists['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linkhint' => 'LBL_SHOW_EMPLOYEES_HIERARCHY',
				'linkurl' => 'javascript:OSSEmployees_Detail_Js.triggerEmployeeHierarchy("' . $linkURL . '");',
				'linkicon' => 'fas fa-user',
				'linkclass' => 'btn-outline-dark btn-sm'
			]);
		}
		return $linkModelLists;
	}
}
