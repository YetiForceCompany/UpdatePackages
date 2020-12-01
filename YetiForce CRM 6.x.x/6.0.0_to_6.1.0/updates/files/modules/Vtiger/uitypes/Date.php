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

class Vtiger_Date_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return empty($value) ? '' : DateTimeField::convertToDBFormat($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConditionBuilderField(string $operator): Vtiger_Field_Model
	{
		$fieldModel = $this->getFieldModel();
		if ('moreThanDaysAgo' === $operator) {
			$fieldModel = Vtiger_Field_Model::init($fieldModel->getModuleName(), [
				'uitype' => 7,
				'name' => $fieldModel->getName(),
				'label' => 'LBL_INTEGER',
				'displaytype' => 1,
				'typeofdata' => 'I~M'
			]);
		}
		return $fieldModel;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		switch ($operator) {
			case 'bw':
				$values = explode(',', $value);
				foreach ($values as &$val) {
					$this->validate($val, true);
					$val = $this->getDBValue($val);
				}
				$dbValue = implode(',', $values);
				break;
			case 'moreThanDaysAgo':
				$uiTypeModel = $this->getConditionBuilderField($operator)->getUITypeModel();
				$uiTypeModel->validate($value, true);
				$dbValue = $uiTypeModel->getDBValue($value);
				break;
			default:
				$this->validate($value, true);
				$dbValue = $this->getDBValue($value);
		}
		return $dbValue;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if ($isUserFormat) {
			[$y, $m, $d] = App\Fields\Date::explode($value, App\User::getCurrentUserModel()->getDetail('date_format'));
		} else {
			[$y, $m, $d] = explode('-', $value);
		}
		if (!is_numeric($m) || !is_numeric($d) || !is_numeric($y) || !checkdate($m, $d, $y)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$dateValue = App\Fields\Date::formatToDisplay($value);

		if ('--' === $dateValue) {
			return '';
		}
		return $dateValue;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value) || ' ' === $value) {
			$value = trim($value);
			$fieldName = $this->getFieldModel()->getFieldName();
			$moduleName = $this->getFieldModel()->getModule()->getName();
			//Restricted Fields for to show Default Value
			if (('birthday' === $fieldName && 'Contacts' === $moduleName) || 'Products' === $moduleName) {
				return \App\Purifier::encodeHtml($value);
			}

			//Special Condition for field 'support_end_date' in Contacts Module
			if ('support_end_date' === $fieldName && 'Contacts' === $moduleName) {
				$value = DateTimeField::convertToUserFormat(date('Y-m-d', strtotime('+1 year')));
			} elseif ('support_start_date' === $fieldName && 'Contacts' === $moduleName) {
				$value = DateTimeField::convertToUserFormat(date('Y-m-d'));
			}
		} else {
			$value = DateTimeField::convertToUserFormat($value);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Date.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Date.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultEditTemplateName()
	{
		return 'Edit/DefaultField/Date.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultValueFromRequest(App\Request $request)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		$value = $request->getByType($fieldName, 'Text');
		if (!\App\TextParser::isVaribleToParse($value)) {
			$this->validate($value, true);
			$value = $this->getDBValue($value);
		}
		$this->getFieldModel()->set('defaultvalue', $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValue()
	{
		$defaultValue = $this->getFieldModel()->get('defaultvalue');
		if ($defaultValue && \App\TextParser::isVaribleToParse($defaultValue)) {
			$textParser = \App\TextParser::getInstance($this->getFieldModel()->getModuleName());
			$textParser->setContent($defaultValue)->parse();
			$defaultValue = $textParser->getContent();
		}
		return $defaultValue;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQueryOperators()
	{
		return array_merge(['e', 'n', 'bw', 'b', 'a', 'y', 'ny'], array_keys(App\Condition::DATE_OPERATORS));
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
		switch ($operator) {
			case 'bw':
				$template = 'ConditionBuilder/DateRange.tpl';
				break;
			case 'moreThanDaysAgo':
				$template = 'ConditionBuilder/Base.tpl';
				break;
			default:
				$template = 'ConditionBuilder/Date.tpl';
		}
		return $template;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		if (!$params) {
			return $this->getDisplayValue($value, $recordModel->getId(), $recordModel, true);
		}
		$p = [];
		foreach (explode('|', $params) as $row) {
			[$key,$val] = explode('=', $row);
			$p[$key] = $val;
		}
		if (isset($p['format'])) {
			$return = (new \DateTime($value))->format($p['format']);
		} else {
			$return = $this->getDisplayValue($value, $recordModel->getId(), $recordModel, true);
		}
		return $return;
	}
}
