<?php

namespace App\Conditions\QueryFields;

/**
 * UserRole Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class UserRoleField extends StringField
{
	/**
	 * Equal operator.
	 *
	 * @return array
	 */
	public function operatorE()
	{
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id']);

		return ['vtiger_user2role.roleid' => explode(',', $this->getValue())];
	}

	/**
	 * Contains operator.
	 *
	 * @return array
	 */
	public function operatorC()
	{
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id']);
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_role', 'vtiger_role.roleid = ' . $this->getColumnName()]);

		return ['like', 'vtiger_role.rolename', $this->getValue()];
	}

	/**
	 * Get order by.
	 *
	 * @param mixed $order
	 *
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id']);
		$this->queryGenerator->addJoin(['INNER JOIN', 'vtiger_role', 'vtiger_role.roleid = ' . $this->getColumnName()]);
		if ($order && 'DESC' === strtoupper($order)) {
			return ['vtiger_role.rolename' => SORT_DESC];
		}
		return ['vtiger_role.rolename' => SORT_ASC];
	}
}
