<?php

/**
 * Inventory Name Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Name_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Name';
	protected $defaultLabel = 'LBL_ITEM_NAME';
	protected $columnName = 'name';
	protected $dbType = 'int DEFAULT 0';
	protected $params = ['modules', 'limit', 'mandatory'];
	protected $colSpan = 30;
	protected $maximumLength = '-2147483648,2147483647';
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Name.tpl';
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		$label = \App\Record::getLabel($value);
		$moduleName = \App\Record::getType($value);
		if ($rawText || ($value && !\App\Privilege::isPermitted($moduleName, 'DetailView', $value))) {
			return $label;
		}
		$label = App\TextParser::textTruncate($label, \App\Config::main('href_max_length'));
		if ('Active' !== \App\Record::getState($value)) {
			$label = '<s>' . $label . '</s>';
		}
		return "<span class=\"yfm-{$moduleName} mr-1\"></span><a class=\"modCT_$moduleName showReferenceTooltip js-popover-tooltip--record\" href=\"index.php?module=$moduleName&view=Detail&record=$value\" title=\"" . App\Language::translateSingularModuleName($moduleName) . "\">$label</a>";
	}

	/** {@inheritdoc} */
	public function getEditValue($value)
	{
		return \App\Record::getLabel($value);
	}

	/**
	 * Getting value to display.
	 *
	 * @return array
	 */
	public function limitValues()
	{
		return [
			['id' => 0, 'name' => 'LBL_NO'],
			['id' => 1, 'name' => 'LBL_YES'],
		];
	}

	/**
	 * Getting value to display.
	 *
	 * @return array
	 */
	public function mandatoryValues()
	{
		return [
			['id' => 'true', 'name' => 'LBL_YES'],
			['id' => 'false', 'name' => 'LBL_NO']
		];
	}

	/** {@inheritdoc} */
	public function isMandatory()
	{
		return true;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ((empty($value) && $this->isMandatory()) || ($value && !is_numeric($value))) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if (!\App\Record::isExists($value)) {
			throw new \App\Exceptions\AppException("ERR_RECORD_NOT_FOUND||$value||$columnName", 406);
		}
		$rangeValues = explode(',', $this->maximumLength);
		if ($value && ($rangeValues[1] < $value || $rangeValues[0] > $value)) {
			throw new \App\Exceptions\AppException("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}

	/** {@inheritdoc} */
	public function isRequired()
	{
		$config = $this->getParamsConfig();
		return isset($config['mandatory']) ? 'false' !== $config['mandatory'] : true;
	}

	/**
	 * Gets URL for mass selection.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public function getUrlForMassSelection(string $moduleName): string
	{
		$view = 'RecordsList';
		if (\App\Config::module($moduleName, 'inventoryMassAddEntriesTreeView') && ($field = current(Vtiger_Module_Model::getInstance($moduleName)->getFieldsByType('tree', true))) && $field->isViewable()) {
			$view = 'TreeInventoryModal';
		}
		return "index.php?module={$moduleName}&view={$view}&src_module={$this->getModuleName()}&multi_select=true";
	}
}
