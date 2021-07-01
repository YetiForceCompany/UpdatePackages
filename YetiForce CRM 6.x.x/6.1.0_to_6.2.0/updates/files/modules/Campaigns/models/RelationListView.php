<?php

 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o
 * *********************************************************************************** */

class Campaigns_RelationListView_Model extends Vtiger_RelationListView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getLinks(): array
	{
		$relatedLinks = parent::getLinks();
		if ($this->getParentRecordModel()->isReadOnly()) {
			return $relatedLinks;
		}
		$relatedModuleModel = $this->getRelationModel()->getRelationModuleModel();
		$relatedModuleName = $relatedModuleModel->getName();
		$id = $this->getParentRecordModel()->getId();
		if (\in_array($relatedModuleName, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition']) && $relatedModuleModel->isPermitted('MassComposeEmail') && App\Config::main('isActiveSendingMails') && App\Mail::getDefaultSmtp()) {
			$emailLink = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => \App\Language::translate('LBL_SEND_EMAIL', $relatedModuleName),
				'linkurl' => 'javascript:Campaigns_RelatedList_Js.triggerSendEmail();',
				'linkicon' => 'fas fa-envelope',
			]);
			$emailLink->set('_sendEmail', true);
			$relatedLinks['LISTVIEWBASIC'][] = $emailLink;
		}
		if ($this->getRelationModel()->privilegeToDelete() && $relatedModuleModel->isPermitted('Delete')) {
			$relatedLinks['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'RELATEDLIST_MASSACTIONS',
				'linklabel' => 'LBL_MASS_DELETE',
				'linkurl' => "javascript:Vtiger_RelatedList_Js.triggerMassAction('index.php?module=Campaigns&action=RelationAjax&mode=massDeleteRelation&src_record={$id}&relatedModule={$relatedModuleName}')",
				'linkclass' => '',
				'linkicon' => 'fas fa-eraser',
			]);
		}
		return $relatedLinks;
	}
}
