<?php

namespace App\Conditions\QueryFields;

/**
 * CurrencyList Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class CurrencyListField extends PicklistField
{
	/**
	 * Get order by.
	 *
	 * @param string $order
	 *
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$this->queryGenerator->addJoin(['LEFT JOIN', 'vtiger_currency_info', $this->getColumnName() . ' = vtiger_currency_info.id']);
		if ($order && 'desc' === strtolower($order)) {
			return ['vtiger_currency_info.currency_name' => SORT_DESC];
		}
		return ['vtiger_currency_info.currency_name' => SORT_ASC];
	}
}
