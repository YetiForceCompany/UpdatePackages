<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Mass Edit Record Structure Model.
 */
class Vtiger_MassEditRecordStructure_Model extends Vtiger_EditRecordStructure_Model
{
	/**
	 * Function to get the values in stuctured format.
	 *
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}
		$values = [];
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = [];
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isEditable() && $fieldModel->isMassEditable() && $fieldModel->isViewable() && $this->isFieldRestricted($fieldModel)) {
						if ($recordExists) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						}
						$fieldModel->set('typeofdata', str_replace('~M', '~O', $fieldModel->get('typeofdata')));
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}

	/**
	 * Function that return Field Restricted are not.
	 *
	 * 	@params Field Model
	 *  @returns boolean true or false
	 *
	 * @param mixed $fieldModel
	 */
	public function isFieldRestricted($fieldModel)
	{
		if ('image' == $fieldModel->getFieldDataType()) {
			return false;
		}
		return true;
	}
}
