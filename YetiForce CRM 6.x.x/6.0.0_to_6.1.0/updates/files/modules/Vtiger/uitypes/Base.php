<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Base_UIType extends \App\Base
{
	/**
	 * Verify the value.
	 *
	 * @var mixed[]
	 */
	protected $validate = [];

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value.
	 *
	 * @param mixed                $value
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if ('' === $value && \in_array($this->getFieldModel()->getFieldType(), ['I', 'N', 'NN'])) {
			return 0;
		}
		if (null === $value) {
			return '';
		}
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * Function to get the field model for condition builder.
	 *
	 * @param string $operator
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getConditionBuilderField(string $operator): Vtiger_Field_Model
	{
		return $this->getFieldModel();
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value for condition builder.
	 *
	 * @param mixed  $value
	 * @param string $operator
	 *
	 * @return string
	 */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$this->validate($value, true);
		return $this->getDBValue($value);
	}

	/**
	 * Set value from request.
	 *
	 * @param \App\Request        $request
	 * @param Vtiger_Record_Model $recordModel
	 * @param bool|string         $requestFieldName
	 */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getByType($requestFieldName, 'Text');
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * Set default value from request.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function setDefaultValueFromRequest(App\Request $request)
	{
		$fieldModel = $this->getFieldModel();
		$recordModel = Vtiger_Record_Model::getCleanInstance($fieldModel->getModuleName());
		$this->setValueFromRequest($request, $recordModel);
		$fieldModel->set('defaultvalue', $recordModel->get($fieldModel->getName()));
	}

	/**
	 * Function to get Default Field Value.
	 *
	 * @throws \Exception
	 *
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->getFieldModel()->get('defaultvalue');
	}

	/**
	 * Verification of data.
	 *
	 * @param string $value
	 * @param bool   $isUserFormat
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if ($isUserFormat) {
			$value = \App\Purifier::decodeHtml($value);
		}
		if (!is_numeric($value) && (\is_string($value) && $value !== \App\Purifier::decodeHtml(\App\Purifier::purify($value)))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength && App\TextParser::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * Verification of value.
	 *
	 * @param mixed $value
	 */
	public function validateValue($value)
	{
		return true;
	}

	/**
	 * Convert value before writing to the database.
	 *
	 * @param mixed               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function convertToSave($value, Vtiger_Record_Model $recordModel)
	{
		return $value;
	}

	/**
	 * Function to get the display value, for the current field type with given DB Insert Value.
	 *
	 * @param mixed                    $value       Field value
	 * @param bool|int                 $record      Record Id
	 * @param bool|Vtiger_Record_Model $recordModel
	 * @param bool                     $rawText     Return text or html
	 * @param bool|int                 $length      Length of the text
	 *
	 * @return mixed
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($rawText) {
			return $value;
		}
		if (\is_int($length)) {
			$value = \App\TextParser::textTruncate($value, $length);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function to get the edit value in display view.
	 *
	 * @param mixed               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function to get the edit value.
	 *
	 * @param mixed               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getEditViewValue($value, $recordModel = false)
	{
		return $this->getEditViewDisplayValue($value, $recordModel);
	}

	/**
	 * Function to get the list value in display view.
	 *
	 * @param mixed                    $value       Field value
	 * @param int                      $record      |bool Record Id
	 * @param bool|Vtiger_Record_Model $recordModel
	 * @param bool                     $rawText     Return text or html
	 *
	 * @return mixed
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $this->getDisplayValue($value, $record, $recordModel, $rawText, $this->getFieldModel()->get('maxlengthtext'));
	}

	/**
	 * Function to get the related list value in display view.
	 *
	 * @param mixed                    $value       Field value
	 * @param int                      $record      |bool Record Id
	 * @param bool|Vtiger_Record_Model $recordModel
	 * @param bool                     $rawText     Return text or html
	 *
	 * @return mixed
	 */
	public function getRelatedListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $this->getListViewDisplayValue($value, $record, $recordModel, $rawText);
	}

	/**
	 * Function to get Display value for RelatedList.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getRelatedListDisplayValue($value)
	{
		return $this->getListViewDisplayValue($value);
	}

	/**
	 * Function to get display value for ModTracker.
	 *
	 * @param                      $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @param bool                 $rawText
	 *
	 * @return mixed
	 */
	public function getHistoryDisplayValue($value, Vtiger_Record_Model $recordModel, $rawText = false)
	{
		return $this->getDisplayValue($value, $recordModel->getId(), $recordModel, $rawText, App\Config::module('ModTracker', 'TEASER_TEXT_LENGTH'));
	}

	/**
	 * Function to get display value for TextParser.
	 *
	 * @param mixed                $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string               $params
	 *
	 * @return mixed
	 */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		return $this->getDisplayValue($value, $recordModel->getId(), $recordModel, true);
	}

	/**
	 * Function to get display value for Web Service API.
	 *
	 * @param                      $value
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel)
	{
		return \App\Purifier::decodeHtml($this->getDisplayValue($value, $recordModel->getId(), $recordModel, true, false));
	}

	/**
	 * Duplicate value from record.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getDuplicateValue(Vtiger_Record_Model $recordModel)
	{
		return $recordModel->get($this->getFieldModel()->getFieldName());
	}

	/**
	 * Static function to get the UIType object from Vtiger Field Model.
	 *
	 * @param Vtiger_Field_Model $fieldModel
	 *
	 * @return self Vtiger_Base_UIType or UIType specific object instance
	 */
	public static function getInstanceFromField($fieldModel)
	{
		$uiType = ucfirst($fieldModel->getFieldDataType());
		$moduleName = $fieldModel->getModuleName();
		$className = \Vtiger_Loader::getComponentClassName('UIType', $uiType, $moduleName, false);
		if (!$className) {
			$className = \Vtiger_Loader::getComponentClassName('UIType', 'Base', $moduleName);
		}
		$instance = new $className();
		$instance->set('field', $fieldModel);
		return $instance;
	}

	/**
	 * Function to get the Template name for the current UI Type Object.
	 *
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Base.tpl';
	}

	/**
	 * Function to get the Detailview template name for the current UI Type Object.
	 *
	 * @return string - Template Name
	 */
	public function getDetailViewTemplateName()
	{
		return 'Detail/Field/Base.tpl';
	}

	/**
	 * Function to get the Template name for the current UI Type object.
	 *
	 * @return string - Template Name
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Base.tpl';
	}

	/**
	 * Function to get the default edit view template name for the current UI Type Object.
	 *
	 * @return string - Template Name
	 */
	public function getDefaultEditTemplateName()
	{
		return 'Edit/DefaultField/Base.tpl';
	}

	/**
	 * Get field model instance.
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldModel()
	{
		return $this->get('field');
	}

	public function isActiveSearchView()
	{
		return true;
	}

	public function isAjaxEditable()
	{
		return true;
	}

	/**
	 * If the field is sortable in ListView.
	 */
	public function isListviewSortable()
	{
		return true;
	}

	/**
	 * Returns allowed types of columns in database.
	 *
	 * @return string[]|null
	 */
	public function getAllowedColumnTypes()
	{
		return ['string', 'text', 'binary'];
	}

	/**
	 * Gets header types.
	 *
	 * @return string[]
	 */
	public function getHeaderTypes()
	{
		return ['LBL_HEADER_TYPE_VALUE' => 'value'];
	}

	/**
	 * Return allowed query operators for field.
	 *
	 * @return string[]
	 */
	public function getQueryOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}

	/**
	 * Return allowed record operators for field.
	 *
	 * @return string[]
	 */
	public function getRecordOperators(): array
	{
		return array_merge($this->getQueryOperators(), ['hs']);
	}

	/**
	 * Returns template for operator.
	 *
	 * @param string $operator
	 *
	 * @return string
	 */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Base.tpl';
	}

	/**
	 * Gets value to export.
	 *
	 * @param mixed $value
	 * @param int   $recordId
	 *
	 * @return mixed
	 */
	public function getValueToExport($value, int $recordId)
	{
		return trim(App\Purifier::decodeHtml($value), '"');
	}

	/**
	 * Gets value from import.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function getValueFromImport($value)
	{
		return $value;
	}
}
