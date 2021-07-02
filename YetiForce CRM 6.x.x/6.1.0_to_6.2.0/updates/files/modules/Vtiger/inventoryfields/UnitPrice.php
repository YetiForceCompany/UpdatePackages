<?php

/**
 * Inventory UnitPrice Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_UnitPrice_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'UnitPrice';
	protected $defaultLabel = 'LBL_UNIT_PRICE';
	protected $defaultValue = 0;
	protected $columnName = 'price';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $summationValue = false;
	protected $maximumLength = '99999999999999999999';
	protected $purifyType = \App\Purifier::NUMBER;

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return \App\Fields\Double::formatToDisplay($value);
	}

	/** {@inheritdoc} */
	public function getEditValue($value)
	{
		return \App\Fields\Double::formatToDisplay($value, false);
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		if (!isset($this->dbValue[$value])) {
			$this->dbValue[$value] = App\Fields\Double::formatToDb($value);
		}
		return $this->dbValue[$value];
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ($isUserFormat) {
			$value = $this->getDBValue($value, $columnName);
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if ($this->maximumLength < $value || -$this->maximumLength > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
