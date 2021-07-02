<?php

/**
 * Report calendar.
 *
 * @package TextParser
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\TextParser;

/**
 * UserCalendar class.
 */
class UserCalendar extends Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_CALENDAR';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * @var string Default template
	 *
	 * @see \App\Condition::DATE_OPERATORS
	 */
	public $default = '$(custom : UserCalendar|__DATE_OPERATOR__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$html = '';
		$moduleName = 'Calendar';
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId'])) {
			$userId = $textParserParams['userId'];
			if (!empty($userId) && \App\User::isExists($userId) && \App\Module::isModuleActive($moduleName)) {
				$queryGenerator = (new \App\QueryGenerator($moduleName))
					->setFields(['id'])
					->addCondition('shownerid', $userId, 'e', false)
					->addCondition('assigned_user_id', $userId, 'e', false)
					->addCondition('activitystatus', 'PLL_OVERDUE', 'n', true);
				if (isset(\App\Condition::DATE_OPERATORS[$this->params[0]])) {
					$queryGenerator->addCondition('date_start', false, $this->params[0]);
				}
				$query = $queryGenerator->createQuery();
				$query->orderBy(['vtiger_crmentity.createdtime' => SORT_DESC]);
				$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
				$dataReader = $query->createCommand()->query();
				$entries = [];
				while ($row = $dataReader->read()) {
					$recordModel = \Vtiger_Record_Model::getInstanceById($row['id']);
					$entries[] = $recordModel->getDisplayValue('date_start', false, true) . ' - <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordModel->getName() . '</a>';
				}
				$html = implode('<br>', $entries);
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
