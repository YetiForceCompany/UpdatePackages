<?php

/**
 * Send mail modal class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_SendMailModal_View extends Vtiger_BasicModal_View
{
	public $fields = [];

	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if (!$request->isEmpty('sourceRecord') && !\App\Privilege::isPermitted($request->getByType('sourceModule', 2), 'DetailView', $request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function getSize(App\Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$templateModule = $moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule && isset(\App\TextParser::$sourceModules[$sourceModule]) && \in_array($moduleName, \App\TextParser::$sourceModules[$sourceModule])) {
			$templateModule = $sourceModule;
		}
		[$recordsNumber, $duplicates, $emailsByField, $emails] = $this->getStatistics($request);
		$viewer->assign('TEMPLATE_MODULE', $templateModule);
		$viewer->assign('RECORDS_NUMBER', $recordsNumber);
		$viewer->assign('DUPLICATES', $duplicates);
		$viewer->assign('EMAILS_BY_FIELD', $emailsByField);
		$viewer->assign('EMAIL_LIST', $emails);
		$viewer->assign('FIELDS', $this->fields);
		$viewer->view('SendMailModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	/**
	 * Get statistics from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function getStatistics(App\Request $request)
	{
		$dataReader = $this->getQuery($request)->createCommand()->query();
		$records = $duplicates = 0;
		$emails = $emailsByField = [];
		$fieldsName = array_keys($this->fields);
		foreach ($fieldsName as $fieldName) {
			$emailsByField[$fieldName] = 0;
		}
		while ($row = $dataReader->read()) {
			++$records;
			foreach ($fieldsName as $fieldName) {
				if (!empty($row[$fieldName])) {
					if (isset($emails[$row[$fieldName]])) {
						++$duplicates;
					} else {
						$emails[$row[$fieldName]] = 0;
					}
					++$emails[$row[$fieldName]];
					++$emailsByField[$fieldName];
				}
			}
		}
		if (!\App\Config::component('Mail', 'showEmailsInMassMail')) {
			$emails = [];
		}
		return [$records, $duplicates, $emailsByField, $emails];
	}

	/**
	 * Get query instance.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\Db\Query
	 */
	public function getQuery(App\Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $sourceModule);
			$listView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, $request->getInteger('relationId'));
		} else {
			$listView = Vtiger_ListView_Model::getInstance($moduleName, $request->getByType('viewname', 2));
		}
		if (!$request->isEmpty('searchResult', true)) {
			$listView->set('searchResult', $request->getArray('searchResult', 'Integer'));
		}
		$searchKey = $request->getByType('search_key', 'Alnum');
		$operator = $request->getByType('operator');
		$searchValue = App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $listView->getQueryGenerator()->getModule(), $searchKey, $operator);
		if (!empty($searchKey) && !empty($searchValue)) {
			$listView->set('operator', $operator);
			$listView->set('search_key', $searchKey);
			$listView->set('search_value', $searchValue);
		}
		$searchParams = App\Condition::validSearchParams($listView->getQueryGenerator()->getModule(), $request->getArray('search_params'));
		if (!empty($searchParams) && \is_array($searchParams)) {
			$transformedSearchParams = $listView->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
			$listView->set('search_params', $transformedSearchParams);
		}
		if ($sourceModule) {
			$queryGenerator = $listView->getRelationQuery(true);
		} else {
			$listView->loadListViewCondition();
			$queryGenerator = $listView->getQueryGenerator();
		}
		$moduleModel = $queryGenerator->getModuleModel();
		$baseTableName = $moduleModel->get('basetable');
		$baseTableId = $moduleModel->get('basetableid');
		foreach ($moduleModel->getFieldsByType('email') as $fieldName => $fieldModel) {
			if ($fieldModel->isActiveField()) {
				$this->fields[$fieldName] = $fieldModel;
			}
		}
		$queryGenerator->setFields(array_merge(['id'], array_keys($this->fields)));
		$selected = $request->getArray('selected_ids', 2);
		if ($selected && 'all' !== $selected[0]) {
			$queryGenerator->addNativeCondition(["$baseTableName.$baseTableId" => $selected]);
		}
		$excluded = $request->getArray('excluded_ids', 2);
		if ($excluded) {
			$queryGenerator->addNativeCondition(['not in', "$baseTableName.$baseTableId" => $excluded]);
		}
		return $queryGenerator->createQuery();
	}
}
