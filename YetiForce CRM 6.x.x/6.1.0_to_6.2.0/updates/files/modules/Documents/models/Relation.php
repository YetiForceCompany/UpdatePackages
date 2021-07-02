<?php
/**
 * Relation Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Documents_Relation_Model.
 */
class Documents_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Set exceptional data.
	 */
	public function setExceptionData()
	{
		$data = [
			'tabid' => $this->getParentModuleModel()->getId(),
			'related_tabid' => $this->getRelationModuleModel()->getId(),
			'name' => 'getRelatedRecord',
			'actions' => 'ADD, SELECT',
			'modulename' => $this->getParentModuleModel()->getName(),
		];
		$this->setData($data);
	}
}
