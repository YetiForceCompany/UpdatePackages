<?php

/**
 * Inventory DiscountMode Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_DiscountMode_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'DiscountMode';
	protected $defaultLabel = 'LBL_DISCOUNT_MODE';
	protected $defaultValue = '0';
	protected $columnName = 'discountmode';
	protected $dbType = 'smallint(1) DEFAULT 0';
	protected $values = [0 => 'group', 1 => 'individual'];
	protected $blocks = [0];
	protected $maximumLength = '-32768,32767';
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return '' !== $value ? \App\Language::translate('LBL_' . strtoupper($this->values[$value]), $this->getModuleName()) : $value;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (!is_numeric($value) || !isset($this->values[$value])) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}
}
