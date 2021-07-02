<?php
/**
 * Advanced percentage field.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_AdvPercentage_UIType class.
 */
class Vtiger_AdvPercentage_UIType extends Vtiger_Double_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return App\Fields\Double::formatToDisplay($value) . '%';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/AdvPercentage.tpl';
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/AdvPercentage.tpl';
	}
}
