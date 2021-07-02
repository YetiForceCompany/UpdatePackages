<?php

/**
 * UIType multi email Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MultiEmail_UIType extends Vtiger_Email_UIType
{
	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		return \App\Purifier::decodeHtml($value);
	}

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getArray($requestFieldName, 'Text');
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @example validate('[{"e":"a.adach@yetiforce.com","o":0},{"e":"test@yetiforce.com","o":0}]');
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value)) {
			return;
		}
		if (\is_string($value)) {
			$value = \App\Json::decode($value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$rawValue = \App\Json::encode($value);
		if (!isset($this->validate[$rawValue])) {
			foreach ($value as $item) {
				if (!\is_array($item) || !isset($item['e'])) {
					throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
				}
				if (!filter_var($item['e'], FILTER_VALIDATE_EMAIL) || $item['e'] !== filter_var($item['e'], FILTER_SANITIZE_EMAIL)) {
					throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
				}
			}
			$this->validate[$rawValue] = true;
		}
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return $value ? \App\Json::encode($value) : '';
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$value = \App\Json::decode($value);
		if (empty($value)) {
			return '';
		}
		$emails = [];
		foreach ($value as $item) {
			if ($rawText) {
				$emails[] = parent::getDisplayValue($item['e'], $record, $recordModel, $rawText, $length);
				continue;
			}
			if ($item['o']) {
				$emails[] = parent::getDisplayValue($item['e'], $record, $recordModel, $rawText, $length) . '<span class="fas fa-check text-success ml-2" title="' . \App\Language::translate('LBL_CONSENT_TO_SEND') . '"></span>';
			} else {
				$emails[] = parent::getDisplayValue($item['e'], $record, $recordModel, true, $length) . '<span class="fas fa-ban text-danger ml-2"></span>';
			}
		}
		return implode($rawText ? ', ' : '<br>', $emails);
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		$value = \App\Json::decode($value);
		if (empty($value)) {
			return '';
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return strip_tags($this->getDisplayValue($value, $record, $recordModel, $rawText), '<br>');
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiEmail.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['c', 'k', 'y', 'ny'];
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}
}
