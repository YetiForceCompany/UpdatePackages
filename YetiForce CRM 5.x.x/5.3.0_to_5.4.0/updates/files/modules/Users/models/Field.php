<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * *********************************************************************************** */

/**
 * User Field Model Class.
 */
class Users_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function to check whether the current field is read-only.
	 *
	 * @return bool
	 */
	public function isReadOnly()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ((false === $currentUserModel->isAdminUser() && 98 == $this->get('uitype')) || 156 == $this->get('uitype')) {
			return true;
		}
		return parent::isReadOnly();
	}

	/**
	 * Function to check if the field is shown in detail view.
	 *
	 * @return bool
	 */
	public function isViewEnabled()
	{
		if (4 === $this->getDisplayType() || \in_array($this->get('presence'), [1, 3])) {
			return false;
		}
		if (106 === $this->get('uitype') && !App\Config::module('Users', 'USER_NAME_IS_EDITABLE')) {
			return false;
		}
		return parent::isViewEnabled();
	}

	/**
	 * Function to check if the field is export table.
	 *
	 * @return bool
	 */
	public function isExportTable()
	{
		return $this->isViewable() || 99 === $this->getUIType();
	}

	/**
	 * Function to check whether field is ajax editable'.
	 *
	 * @return bool
	 */
	public function isAjaxEditable()
	{
		if (!$this->isEditable() || 105 === $this->get('uitype') ||
			106 === $this->get('uitype') || 98 === $this->get('uitype') || 101 === $this->get('uitype') || 'date_format' === $this->getFieldName() || 'email1' === $this->getFieldName()) {
			return false;
		}
		if ('login_method' === $this->getFieldName()) {
			return \App\User::getCurrentUserModel()->isAdmin();
		}
		return parent::isAjaxEditable();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPicklistValues($skipCheckingRole = false)
	{
		if (115 == $this->get('uitype')) {
			$fieldPickListValues = [];
			$query = (new \App\Db\Query())->select([$this->getFieldName()])->from('vtiger_' . $this->getFieldName());
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$picklistValue = $row[$this->getFieldName()];
				$fieldPickListValues[$picklistValue] = \App\Language::translate($picklistValue, $this->getModuleName());
			}
			$dataReader->close();
			return $fieldPickListValues;
		}
		return parent::getPicklistValues($skipCheckingRole);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$fieldName = $this->getFieldName();
		if (('currency_decimal_separator' === $fieldName || 'currency_grouping_separator' === $fieldName) && (' ' === $value)) {
			return \App\Language::translate('LBL_SPACE', 'Users');
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}

	/**
	 * Function returns all the User Roles.
	 *
	 * @return array
	 */
	public function getAllRoles()
	{
		$roleModels = Settings_Roles_Record_Model::getAll();
		$roles = [];
		foreach ($roleModels as $roleId => $roleModel) {
			$roleName = $roleModel->getName();
			$roles[$roleName] = $roleId;
		}
		return $roles;
	}

	/**
	 * Function to check whether this field editable or not.
	 *
	 * @return bool true/false
	 */
	public function isEditable()
	{
		if ((115 === $this->get('uitype') && (!\App\User::getCurrentUserModel()->isAdmin() || \App\User::getCurrentUserId() === $this->get('rocordId')))) {
			return false;
		}
		if ('authy_secret_totp' === $this->getColumnName()) {
			return $this->get('rocordId') === \App\User::getCurrentUserId();
		}
		if (!$this->get('editable')) {
			$this->set('editable', parent::isEditable());
		}
		return $this->get('editable');
	}

	/**
	 * {@inheritdoc}
	 */
	public function isViewable()
	{
		if ('authy_secret_totp' === $this->getColumnName()) {
			return $this->get('rocordId') === \App\User::getCurrentUserId();
		}
		return parent::isViewable();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEditableReadOnly()
	{
		return ('login_method' === $this->getColumnName() && !\App\User::getCurrentUserModel()->isAdmin()) || (10 === (int) $this->get('displaytype'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function isWritable()
	{
		if ('is_admin' === $this->getFieldName() && \App\User::getCurrentUserModel()->isAdmin()) {
			return true;
		}
		return parent::isWritable();
	}
}
