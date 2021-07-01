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

class Vtiger_Reference_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			$value = 0;
		}
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		return \App\Purifier::decodeHtml($value);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength) {
			$rangeValues = explode(',', $maximumLength);
			if (($rangeValues[1] ?? $rangeValues[0]) < $value || (isset($rangeValues[1]) ? $rangeValues[0] : 0) > $value) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
		}
		$this->validate[$value] = true;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param int $value
	 *
	 * @return Vtiger_Module_Model|null
	 */
	public function getReferenceModule($value): ?Vtiger_Module_Model
	{
		$fieldModel = $this->getFieldModel();
		$referenceModuleList = $fieldModel->getReferenceList();
		$referenceEntityType = \App\Record::getType($value);
		if (!empty($referenceModuleList) && \in_array($referenceEntityType, $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance($referenceEntityType);
		}
		if (!empty($referenceModuleList) && \in_array('Users', $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance('Users');
		}
		return null;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value) || !($referenceModule = $this->getReferenceModule($value))) {
			return '';
		}
		$referenceModuleName = $referenceModule->getName();

		if ('Users' === $referenceModuleName || 'Groups' === $referenceModuleName) {
			return \App\Fields\Owner::getLabel($value);
		}
		if (!\App\Record::isExists($value)) {
			return '';
		}
		$label = \App\Record::getLabel($value, $rawText);
		if ($rawText || ($value && !\App\Privilege::isPermitted($referenceModuleName, 'DetailView', $value))) {
			return $label;
		}
		if (\is_int($length)) {
			$label = \App\TextParser::textTruncate($label, $length);
		} elseif (true !== $length) {
			$label = App\TextParser::textTruncate($label, \App\Config::main('href_max_length'));
		}
		if ('Active' !== \App\Record::getState($value)) {
			$label = '<s>' . $label . '</s>';
		}
		$url = "index.php?module={$referenceModuleName}&view={$referenceModule->getDetailViewName()}&record={$value}";
		if (!empty($this->fullUrl)) {
			$url = Config\Main::$site_URL . $url;
		}
		return "<a class='modCT_$referenceModuleName showReferenceTooltip js-popover-tooltip--record' href='$url'>$label</a>";
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		if (($referenceModule = $this->getReferenceModule($value)) && ('Users' === $referenceModule->getName() || 'Groups' === $referenceModule->getName())) {
			return \App\Fields\Owner::getLabel($value);
		}
		return \App\Record::getLabel($value);
	}

	/** {@inheritdoc} */
	public function getEditViewValue($value, $recordModel = false)
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel)
	{
		if (empty($value) || !($referenceModule = $this->getReferenceModule($value))) {
			return '';
		}
		$referenceModuleName = $referenceModule->getName();
		if ('Users' === $referenceModuleName || 'Groups' === $referenceModuleName) {
			return \App\Fields\Owner::getLabel($value);
		}
		if (!\App\Record::isExists($value)) {
			return '';
		}
		return [
			'value' => \App\Record::getLabel($value, true),
			'record' => $value,
			'referenceModule' => $referenceModuleName,
			'state' => \App\Record::getState($value),
			'isPermitted' => \App\Privilege::isPermitted($referenceModuleName, 'DetailView', $value),
		];
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		$fieldModel = $this->getFieldModel();
		$fieldName = $fieldModel->getName();
		if ('modifiedby' === $fieldName) {
			return 'List/Field/Owner.tpl';
		}
		if (App\Config::performance('SEARCH_REFERENCE_BY_AJAX')) {
			return 'List/Field/Reference.tpl';
		}
		return parent::getListSearchTemplateName();
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Reference.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['bigint', 'integer', 'smallint'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}
}
