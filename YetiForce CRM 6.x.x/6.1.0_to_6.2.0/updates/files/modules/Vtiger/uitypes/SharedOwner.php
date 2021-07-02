<?php

/**
 * UIType sharedOwner Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_SharedOwner_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (\is_array($value)) {
			$value = implode(',', $value);
		}
		return \App\Purifier::decodeHtml($value);
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!\is_array($value)) {
			$value = $value ? explode('##', $value) : [];
		}
		foreach ($value as $val) {
			$values[] = parent::getDbConditionBuilderValue($val, $operator);
		}
		return implode('##', $values);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$hashValue = \is_array($value) ? implode('|', $value) : $value;
		if (isset($this->validate[$hashValue]) || empty($value)) {
			return;
		}
		if (!\is_array($value)) {
			$value = explode(',', $value);
		}
		$rangeValues = null;
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength) {
			$rangeValues = explode(',', $maximumLength);
		}
		foreach ($value as $shownerid) {
			if (!is_numeric($shownerid)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $shownerid, 406);
			}
			if ($rangeValues && (($rangeValues[1] ?? $rangeValues[0]) < $shownerid || (isset($rangeValues[1]) ? $rangeValues[0] : 0) > $shownerid)) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $shownerid, 406);
			}
		}
		$this->validate[$hashValue] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$isAdmin = \App\User::getCurrentUserModel()->isAdmin();
		if (empty($value)) {
			return '';
		}
		if (!\is_array($value)) {
			$values = explode(',', $value);
		}
		$displayValue = [];
		foreach ($values as $shownerid) {
			$ownerName = rtrim(\App\Fields\Owner::getLabel($shownerid));
			if (!$isAdmin || $rawText) {
				$displayValue[] = $ownerName;
				continue;
			}
			$detailViewUrl = '';
			switch (\App\Fields\Owner::getType($shownerid)) {
				case 'Users':
					$userModel = Users_Privileges_Model::getInstanceById($shownerid);
					$userModel->setModule('Users');
					if ('Inactive' === $userModel->get('status')) {
						$ownerName = '<span class="redColor"><s>' . $ownerName . '</s></span>';
					} elseif ($isAdmin && 'Active' === $userModel->get('status')) {
						$detailViewUrl = 'index.php?module=Users&view=Detail&record=' . $shownerid;
						$popoverRecordClass = 'class="js-popover-tooltip--record"';
					}
					break;
				case 'Groups':
					if ($isAdmin) {
						$recordModel = new Settings_Groups_Record_Model();
						$recordModel->set('groupid', $shownerid);
						$detailViewUrl = $recordModel->getDetailViewUrl();
						$popoverRecordClass = '';
					}
					break;
				default:
					$ownerName = '<span class="redColor">---</span>';
					break;
			}
			if (!empty($detailViewUrl)) {
				if (!empty($this->fullUrl)) {
					$detailViewUrl = Config\Main::$site_URL . $detailViewUrl;
				}
				$displayValue[] = "<a $popoverRecordClass href=\"$detailViewUrl\"> $ownerName </a>";
			} else {
				$displayValue[] = $ownerName;
			}
		}
		return implode(', ', $displayValue);
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$values = \App\Fields\SharedOwner::getById($record);
		if (empty($values)) {
			return '';
		}
		$display = $shownerData = [];
		$maxLengthText = $this->getFieldModel()->get('maxlengthtext');
		$isAdmin = \App\User::getCurrentUserModel()->isAdmin();
		foreach ($values as $key => $shownerid) {
			$name = \App\Fields\Owner::getLabel($shownerid);
			switch (\App\Fields\Owner::getType($shownerid)) {
				case 'Users':
					$userModel = Users_Privileges_Model::getInstanceById($shownerid);
					$userModel->setModule('Users');
					$display[$key] = $name;
					if ('Inactive' === $userModel->get('status')) {
						$shownerData[$key]['inactive'] = true;
					} elseif ($isAdmin && !$rawText) {
						$shownerData[$key]['link'] = 'index.php?module=Users&view=Detail&record=' . $shownerid;
						$shownerData[$key]['class'] = 'class="js-popover-tooltip--record"';
					}
					break;
				case 'Groups':
					if (empty($name)) {
						continue 2;
					}
					$display[$key] = $name;
					$recordModel = new Settings_Groups_Record_Model();
					$recordModel->set('groupid', $shownerid);
					$detailViewUrl = $recordModel->getDetailViewUrl();
					if ($isAdmin && !$rawText) {
						$shownerData[$key]['link'] = $detailViewUrl;
						$shownerData[$key]['class'] = '';
					}
					break;
				default:
					break;
			}
		}
		$display = implode(', ', $display);
		$display = explode(', ', \App\TextParser::textTruncate($display, $maxLengthText));
		foreach ($display as $key => &$shownerName) {
			if (isset($shownerData[$key]['inactive'])) {
				$shownerName = '<span class="redColor"><s>' . $shownerName . '</s></span>';
			} elseif (isset($shownerData[$key]['link'])) {
				$shownerName = '<a ' . $shownerData[$key]['class'] . 'href="' . $shownerData[$key]['link'] . '">' . $shownerName . '</a>';
			}
		}
		return implode(', ', $display);
	}

	/**
	 * Get users and group for module list.
	 *
	 * @param string $moduleName
	 * @param int    $cvId
	 * @param string $fieldName
	 *
	 * @return array
	 */
	public static function getSearchViewList($moduleName, $cvId, $fieldName = 'id'): array
	{
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->initForCustomViewById($cvId);

		if (false !== strpos($fieldName, ':')) {
			$queryField = $queryGenerator->getQueryRelatedField($fieldName);
			$queryGenerator->addRelatedJoin($queryField->getRelated());
			$fieldName = $queryField->getRelated()['sourceField'];
		} else {
			$fieldName = 'id';
		}
		$queryGenerator->clearFields()->setFields([])->setCustomColumn('u_#__crmentity_showners.userid');
		$queryGenerator->addJoin(['INNER JOIN', 'u_#__crmentity_showners', "{$queryGenerator->getColumnName($fieldName)} = u_#__crmentity_showners.crmid"]);
		$dataReader = $queryGenerator->createQuery()->distinct()->createCommand()->query();
		$users = $group = [];
		while ($id = $dataReader->readColumn(0)) {
			$name = \App\Fields\Owner::getUserLabel($id);
			if (!empty($name)) {
				$users[$id] = $name;
				continue;
			}
			$name = \App\Fields\Owner::getGroupName($id);
			if (false !== $name) {
				$group[$id] = $name;
			}
		}
		asort($users);
		asort($group);

		return ['users' => $users, 'group' => $group];
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/SharedOwner.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/SharedOwner.tpl';
	}

	/** {@inheritdoc} */
	public function isListviewSortable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getRangeValues()
	{
		return '65535';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny', 'om', 'ogr'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/SharedOwner.tpl';
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		$values = [];
		foreach (\App\Fields\SharedOwner::getById($recordId) as $owner) {
			$values[] = \App\Fields\Owner::getLabel($owner);
		}
		return implode(',', $values);
	}
}
