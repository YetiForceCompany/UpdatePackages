<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Calendar_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$relatedLinks = [[
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => 'LBL_RECORD_DETAILS',
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=full',
			'linkicon' => '',
			'linkKey' => 'LBL_RECORD_DETAILS',
			'related' => 'Details',
		]];
		$parentModuleModel = $this->getModule();
		if ($parentModuleModel->isTrackingEnabled() && $parentModuleModel->isPermitted('ModTracker')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_UPDATES',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRecentActivities&page=1',
				'linkicon' => '',
				'related' => 'ModTracker',
				'countRelated' => App\Config::module('ModTracker', 'UNREVIEWED_COUNT') && $parentModuleModel->isPermitted('ReviewingUpdates'),
				'badgeClass' => 'bgDanger',
			];
		}
		return $relatedLinks;
	}

	/**
	 * Function to get the detail view links (links and widgets).
	 *
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 *
	 * @return <array> - array of link models in the format as below
	 *                 array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams)
	{
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$recordId = $recordModel->getId();
		$status = $recordModel->get('activitystatus');
		$statusActivity = Calendar_Module_Model::getComponentActivityStateLabel('current');

		if ($recordModel->isEditable() && $this->getModule()->isPermitted('DetailView') && \App\Privilege::isPermitted($moduleName, 'ActivityComplete', $recordId) && \App\Privilege::isPermitted($moduleName, 'ActivityCancel', $recordId) && \App\Privilege::isPermitted($moduleName, 'ActivityPostponed', $recordId) && \in_array($status, $statusActivity)) {
			$basicActionLink = [
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'LBL_SET_RECORD_STATUS',
				'linkurl' => '#',
				'linkdata' => ['url' => $recordModel->getActivityStateModalUrl()],
				'linkicon' => 'fas fa-check',
				'linkclass' => 'btn-outline-dark btn-sm showModal closeCalendarRekord',
			];
			$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		if (!$recordModel->isEmpty('location') && App\Privilege::isPermitted('OpenStreetMap')) {
			$basicActionLink = [
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'LBL_SHOW_LOCATION',
				'linkurl' => 'javascript:Vtiger_Index_Js.showLocation(this)',
				'linkdata' => ['location' => $recordModel->getDisplayValue('location')],
				'linkicon' => 'fas fa-map-marker-alt',
				'linkclass' => 'btn-outline-dark btn-sm'
			];
			$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		$stateColors = App\Config::search('LIST_ENTITY_STATE_COLOR');
		if ($recordModel->privilegeToMoveToTrash() && 1 === $recordModel->get('reapeat')) {
			foreach ($linkModelList['DETAIL_VIEW_EXTENDED'] as $key => $linkObject) {
				if ('LBL_MOVE_TO_TRASH' == $linkObject->linklabel) {
					unset($linkModelList['DETAIL_VIEW_EXTENDED'][$key]);
				}
			}
			$linkModelList['DETAIL_VIEW_EXTENDED'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_EXTENDED',
				'linklabel' => 'LBL_MOVE_TO_TRASH',
				'linkurl' => 'javascript:Calendar_Detail_Js.deleteRecord("index.php?module=' . $recordModel->getModuleName() . '&action=State&state=Trash&record=' . $recordModel->getId() . '")',
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn-outline-dark btn-sm entityStateBtn',
				'style' => empty($stateColors['Trash']) ? '' : "background: {$stateColors['Trash']};",
				'title' => \App\Language::translate('LBL_MOVE_TO_TRASH'),
			]);
		}
		if ($recordModel->privilegeToDelete() && 1 === $recordModel->get('reapeat')) {
			foreach ($linkModelList['DETAIL_VIEW_EXTENDED'] as $key => $linkObject) {
				if ('LBL_DELETE_RECORD_COMPLETELY' == $linkObject->linklabel) {
					unset($linkModelList['DETAIL_VIEW_EXTENDED'][$key]);
				}
			}
			$linkModelList['DETAIL_VIEW_EXTENDED'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_EXTENDED',
				'linklabel' => 'LBL_DELETE_RECORD_COMPLETELY',
				'linkurl' => 'javascript:Calendar_Detail_Js.deleteRecord("index.php?module=' . $recordModel->getModuleName() . '&action=Delete&record=' . $recordModel->getId() . '")',
				'linkicon' => 'fas fa-eraser',
				'linkclass' => 'btn-outline-dark btn-sm',
				'title' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY'),
			]);
		}
		return $linkModelList;
	}
}
