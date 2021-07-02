<?php

 /* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_Picklist_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Check description column in picklist.
	 *
	 * @param string $tableName
	 * @param string $columnName
	 *
	 * @return bool
	 */
	public static function checkColumn(string $tableName, string $columnName)
	{
		return (bool) \App\Db::getInstance()->getTableSchema($tableName, true)->getColumn($columnName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFieldsByType($type, bool $active = false): array
	{
		$fieldModels = parent::getFieldsByType($type, $active);
		$fields = [];
		foreach ($fieldModels as $fieldName => $fieldModel) {
			$field = Settings_Picklist_Field_Model::getInstanceFromFieldObject($fieldModel);
			if ($field->isEditable()) {
				$fields[$fieldName] = $field;
			}
		}
		return $fields;
	}

	/**
	 * Add value to picklist.
	 *
	 * @param Vtiger_Field_Model $fieldModel
	 * @param string             $newValue
	 * @param int[]              $rolesSelected
	 * @param string             $description
	 * @param string             $prefix
	 *
	 * @return int[]
	 */
	public function addPickListValues($fieldModel, $newValue, $rolesSelected = [], $description = '', $prefix = '')
	{
		$db = App\Db::getInstance();
		$pickListFieldName = $fieldModel->getName();
		$primaryKey = App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \App\Fields\Picklist::getPickListTableName($pickListFieldName);
		$picklistValueId = $db->getUniqueID('vtiger_picklistvalues');
		$sequence = (new \App\Db\Query())->from($tableName)->max('sortorderid');
		$row = [
			$pickListFieldName => $newValue,
			'sortorderid' => ++$sequence,
			'presence' => 1,
		];
		if ($fieldModel->isRoleBased()) {
			$row['picklist_valueid'] = $picklistValueId;
		}
		if (!empty($prefix)) {
			if (!static::checkColumn($tableName, 'prefix')) {
				$this->addPrefixColumn($tableName);
			}
			$row['prefix'] = $prefix;
		}
		if (!empty($description)) {
			if (!static::checkColumn($tableName, 'description')) {
				$this->addDescriptionColumn($tableName);
			}
			$row['description'] = $description;
		}
		if (\in_array('color', $db->getTableSchema($tableName)->getColumnNames())) {
			$row['color'] = 'E6FAD8';
		}
		$db->createCommand()->insert($tableName, $row)->execute();
		$picklistId = $db->getLastInsertID($tableName . '_' . $primaryKey . '_seq');
		if ($fieldModel->isRoleBased() && !empty($rolesSelected)) {
			$picklistid = (new \App\Db\Query())->select(['picklistid'])
				->from('vtiger_picklist')
				->where(['name' => $pickListFieldName])
				->scalar();
			//add the picklist values to the selected roles
			foreach ($rolesSelected as $roleid) {
				$sortid = (new \App\Db\Query())->from('vtiger_role2picklist')
					->leftJoin("vtiger_$pickListFieldName", "vtiger_$pickListFieldName.picklist_valueid = vtiger_role2picklist.picklistvalueid")
					->where(['roleid' => $roleid, 'picklistid' => $picklistid])
					->max('sortid') + 1;
				$db->createCommand()->insert('vtiger_role2picklist', [
					'roleid' => $roleid,
					'picklistvalueid' => $picklistValueId,
					'picklistid' => $picklistid,
					'sortid' => $sortid,
				])->execute();
			}
		}
		\App\Fields\Picklist::clearCache($pickListFieldName, $fieldModel->getModuleName());
		\App\Colors::generate('picklist');
		return ['picklistValueId' => $picklistValueId, 'id' => $picklistId];
	}

	/**
	 * Rename picklist value.
	 *
	 * @param Settings_Picklist_Field_Model $fieldModel
	 * @param string                        $oldValue
	 * @param string                        $newValue
	 * @param int                           $id
	 * @param string                        $description
	 * @param string                        $prefix
	 *
	 * @return bool
	 */
	public function renamePickListValues($fieldModel, $oldValue, $newValue, $id, $description = '', $prefix = '')
	{
		$db = App\Db::getInstance();
		$pickListFieldName = $fieldModel->getName();
		$primaryKey = App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \App\Fields\Picklist::getPickListTableName($pickListFieldName);
		$newData = [$pickListFieldName => $newValue];
		$descriptionColumnExist = static::checkColumn($tableName, 'description');
		if (!empty($description) || $descriptionColumnExist) {
			if (!$descriptionColumnExist) {
				$this->addDescriptionColumn($tableName);
			}
			$newData['description'] = $description;
		}
		$prefixColumnExist = static::checkColumn($tableName, 'prefix');
		if (!empty($prefix) || $prefixColumnExist) {
			if (!$prefixColumnExist) {
				$this->addPrefixColumn($tableName);
			}
			$newData['prefix'] = $prefix;
		}
		$result = $db->createCommand()->update($tableName, $newData, [$primaryKey => $id])->execute();
		if ($result) {
			$dataReader = (new \App\Db\Query())->select(['tablename', 'columnname', 'tabid'])
				->from('vtiger_field')
				->where(['and', ['fieldname' => $pickListFieldName], ['presence' => [0, 2]], ['uitype' => [15, 16, 33]]])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$columnName = $row['columnname'];
				$db->createCommand()->update($row['tablename'], [$columnName => $newValue], [$columnName => $oldValue])->execute();
				$db->createCommand()->update('vtiger_field', ['defaultvalue' => $newValue], ['defaultvalue' => $oldValue, 'columnname' => $columnName, 'tabid' => $row['tabid']])->execute();
				$db->createCommand()->update('vtiger_picklist_dependency', ['sourcevalue' => $newValue], ['sourcevalue' => $oldValue, 'sourcefield' => $pickListFieldName, 'tabid' => $row['tabid']])->execute();
				$moduleName = \App\Module::getModuleName($row['tabid']);
				\App\Fields\Picklist::clearCache($pickListFieldName, $moduleName);
				$eventHandler = new App\EventHandler();
				$eventHandler->setParams([
					'fieldname' => $pickListFieldName,
					'oldvalue' => $oldValue,
					'newvalue' => $newValue,
					'module' => $moduleName,
					'id' => $id,
				]);
				$eventHandler->trigger('PicklistAfterRename');
			}
			$dataReader->close();
			\App\Colors::generate('picklist');
		}
		return !empty($result);
	}

	/**
	 * Add description column to picklist.
	 *
	 * @param string $tableName
	 *
	 * @return bool
	 */
	public function addDescriptionColumn(string $tableName)
	{
		return App\Db::getInstance()->createCommand()->addColumn($tableName, 'description', 'text')->execute();
	}

	/**
	 * Add description column to picklist.
	 *
	 * @param string $tableName
	 *
	 * @return bool
	 */
	public function addPrefixColumn(string $tableName)
	{
		return App\Db::getInstance()->createCommand()->addColumn($tableName, 'prefix', 'string(30)')->execute();
	}

	public function remove($pickListFieldName, $valueToDeleteId, $replaceValueId, $moduleName)
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		if (!\is_array($valueToDeleteId)) {
			$valueToDeleteId = [$valueToDeleteId];
		}
		$primaryKey = App\Fields\Picklist::getPickListId($pickListFieldName);
		$pickListValues = array_map('App\Purifier::decodeHtml', (new \App\Db\Query())->select([$pickListFieldName])
			->from(\App\Fields\Picklist::getPickListTableName($pickListFieldName))
			->where([$primaryKey => $valueToDeleteId])
			->column());
		$replaceValue = \App\Purifier::decodeHtml((new \App\Db\Query())->select([$pickListFieldName])
			->from(\App\Fields\Picklist::getPickListTableName($pickListFieldName))
			->where([$primaryKey => $replaceValueId])
			->scalar());
		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName, $this);
		//if role based then we need to delete all the values in role based picklist
		if ($fieldModel->isRoleBased()) {
			$picklistValueId = (new \App\Db\Query())->select(['picklist_valueid'])->from(\App\Fields\Picklist::getPickListTableName($pickListFieldName))->where([$primaryKey => $valueToDeleteId])->column();
			$dbCommand->delete('vtiger_role2picklist', ['picklistvalueid' => $picklistValueId])->execute();
			$dbCommand->delete('u_#__picklist_close_state', ['valueid' => $picklistValueId])->execute();
		}
		$dbCommand->delete(\App\Fields\Picklist::getPickListTableName($pickListFieldName), [$primaryKey => $valueToDeleteId])->execute();
		$dbCommand->delete('vtiger_picklist_dependency', ['sourcevalue' => $pickListValues, 'sourcefield' => $pickListFieldName])
			->execute();
		$dataReader = (new \App\Db\Query())->select(['tablename', 'columnname'])
			->from('vtiger_field')
			->where(['fieldname' => $pickListFieldName, 'presence' => [0, 2]])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$dbCommand->update($tableName, [$columnName => $replaceValue], [$columnName => $pickListValues])
				->execute();
		}
		$dataReader->close();
		$dbCommand->update('vtiger_field', ['defaultvalue' => $replaceValue], ['defaultvalue' => $pickListValues, 'columnname' => $columnName])
			->execute();
		\App\Fields\Picklist::clearCache($pickListFieldName, $moduleName);
		$eventHandler = new App\EventHandler();
		$eventHandler->setParams([
			'fieldname' => $pickListFieldName,
			'valuetodelete' => $pickListValues,
			'replacevalue' => $replaceValue,
			'module' => $moduleName,
		]);
		$eventHandler->trigger('PicklistAfterDelete');
		\App\Colors::generate('picklist');
		return true;
	}

	public function enableOrDisableValuesForRole($picklistFieldName, $valuesToEnables, $valuesToDisable, $roleIdList)
	{
		$db = App\Db::getInstance();
		$picklistId = (new App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')
			->where(['name' => $picklistFieldName])->scalar();
		$primaryKey = App\Fields\Picklist::getPickListId($picklistFieldName);
		$pickListValueList = array_merge($valuesToEnables, $valuesToDisable);
		$dataReader = (new App\Db\Query())->select(['picklist_valueid', $picklistFieldName, $primaryKey])
			->from(\App\Fields\Picklist::getPickListTableName($picklistFieldName))
			->where([$primaryKey => $pickListValueList])
			->createCommand()->query();
		$pickListValueDetails = [];
		while ($row = $dataReader->read()) {
			$pickListValueDetails[App\Purifier::decodeHtml($row[$primaryKey])] = $row['picklist_valueid'];
		}
		$dataReader->close();
		$insertValueList = [];
		$deleteValueList = ['or'];
		foreach ($roleIdList as $roleId) {
			foreach ($valuesToEnables as $picklistValue) {
				if (empty($pickListValueDetails[$picklistValue])) {
					$pickListValueId = $pickListValueDetails[App\Purifier::encodeHtml($picklistValue)];
				} else {
					$pickListValueId = $pickListValueDetails[$picklistValue];
				}
				$insertValueList[] = [$roleId, $pickListValueId, $picklistId];
				$deleteValueList[] = ['roleid' => $roleId, 'picklistvalueid' => $pickListValueId];
			}
			foreach ($valuesToDisable as $picklistValue) {
				if (empty($pickListValueDetails[$picklistValue])) {
					$pickListValueId = $pickListValueDetails[App\Purifier::encodeHtml($picklistValue)];
				} else {
					$pickListValueId = $pickListValueDetails[$picklistValue];
				}
				$deleteValueList[] = ['roleid' => $roleId, 'picklistvalueid' => $pickListValueId];
			}
		}
		if ($deleteValueList) {
			$db->createCommand()->delete('vtiger_role2picklist', $deleteValueList)->execute();
		}
		$db->createCommand()->batchInsert('vtiger_role2picklist', ['roleid', 'picklistvalueid', 'picklistid'], $insertValueList)->execute();
	}

	/**
	 * Function to update sequence number.
	 *
	 * @param string $pickListFieldName
	 * @param array  $picklistValues
	 */
	public function updateSequence($pickListFieldName, $picklistValues)
	{
		$db = \App\Db::getInstance();
		$primaryKey = App\Fields\Picklist::getPickListId($pickListFieldName);
		$set = ' CASE ';
		foreach ($picklistValues as $values => $sequence) {
			$set .= " WHEN {$db->quoteColumnName($primaryKey)} = {$db->quoteValue($values)} THEN {$db->quoteValue($sequence)}";
		}
		$set .= ' END';
		$expression = new \yii\db\Expression($set);
		$db->createCommand()->update(\App\Fields\Picklist::getPickListTableName($pickListFieldName), ['sortorderid' => $expression])->execute();
	}

	public static function getPicklistSupportedModules()
	{
		$dataReader = (new App\Db\Query())->select(['vtiger_tab.tabid', 'vtiger_tab.tablabel', 'tabname' => 'vtiger_tab.name'])
			->from('vtiger_tab')
			->innerJoin('vtiger_field', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where([
				'and',
				['uitype' => [15, 33, 16]],
				['NOT IN', 'vtiger_field.tabid', [29, 10]],
				['<>', 'vtiger_tab.presence', 1],
				['vtiger_field.presence' => [0, 2]],
				['<>', 'vtiger_field.columnname', 'taxtype'],
			])->orderBy(['vtiger_tab.tabid' => SORT_ASC])
			->distinct()
			->createCommand()->query();
		$modulesModelsList = [];
		while ($row = $dataReader->read()) {
			$moduleLabel = $row['tablabel'];
			$moduleName = $row['tabname'];
			$instance = new self();
			$instance->name = $moduleName;
			$instance->label = $moduleLabel;
			$modulesModelsList[] = $instance;
		}
		$dataReader->close();

		return $modulesModelsList;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name.
	 *
	 * @param int|string $mixed id or name of the module
	 * @param mixed      $value
	 *
	 * @return self
	 */
	public static function getInstance($value)
	{
		$instance = false;
		$moduleObject = parent::getInstance($value);
		if ($moduleObject) {
			$instance = self::getInstanceFromModuleObject($moduleObject);
		}
		return $instance;
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given vtlib\Module object.
	 *
	 * @param vtlib\Module $moduleObj
	 *
	 * @return self instance
	 */
	public static function getInstanceFromModuleObject(vtlib\Module $moduleObj): self
	{
		$objectProperties = get_object_vars($moduleObj);
		$moduleModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$moduleModel->{$properName} = $propertyValue;
		}
		return $moduleModel;
	}

	/**
	 * list of modules in which they appear picklist fields.
	 *
	 * @param array $pickListFields
	 *
	 * @return array
	 */
	public function listModuleInterdependentPickList(array $pickListFields): array
	{
		$interdependent = [];
		$dataReader = (new App\Db\Query())->select(['tabid', 'fieldname'])
			->from('vtiger_field')
			->where(['fieldname' => $pickListFields])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$moduleName = \App\Module::getModuleName($row['tabid']);
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			if ($moduleModel->isActive() && $moduleModel->getFieldByName($row['fieldname'])->isActiveField()) {
				$interdependent[$row['fieldname']][] = \App\Language::translate($moduleName, $moduleName);
			}
		}
		return $interdependent;
	}
}
