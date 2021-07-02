<?php

namespace App\Conditions\QueryFields;

/**
 * Modules Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class ModulesField extends BaseField
{
	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
	{
		return ['NOT IN', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Get value.
	 *
	 * @return array
	 */
	public function getValue()
	{
		return explode('##', $this->value);
	}
}
