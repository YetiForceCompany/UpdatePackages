<?php

/**
 * OSSMailView DetailView model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewLinks(array $linkParams): array
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$recordModel = $this->getRecord();
		$linkModelList = parent::getDetailViewLinks($linkParams);
		unset($linkModelList['DETAIL_VIEW_ADDITIONAL']);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission('OSSMail') && !$recordModel->isReadOnly();
		if ($permission && App\Config::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail')) {
			$recordId = $recordModel->getId();
			if (1 == $currentUserModel->get('internal_mailer')) {
				$config = OSSMail_Module_Model::getComposeParameters();
				$url = OSSMail_Module_Model::getComposeUrl();

				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => '',
					'linkhint' => 'LBL_REPLY',
					'linkdata' => ['url' => $url . '&mid=' . $recordId . '&type=reply', 'popup' => $config['popup']],
					'linkicon' => 'fas fa-reply',
					'linkclass' => 'btn-outline-dark btn-sm sendMailBtn',
				];
				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => '',
					'linkhint' => 'LBL_REPLYALLL',
					'linkdata' => ['url' => $url . '&mid=' . $recordId . '&type=replyAll', 'popup' => $config['popup']],
					'linkicon' => 'fas fa-reply-all',
					'linkclass' => 'btn-outline-dark btn-sm sendMailBtn',
				];
				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => '',
					'linkhint' => 'LBL_FORWARD',
					'linkdata' => ['url' => $url . '&mid=' . $recordId . '&type=forward', 'popup' => $config['popup']],
					'linkicon' => 'fas fa-share',
					'linkclass' => 'btn-outline-dark btn-sm sendMailBtn',
				];
			} else {
				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linkhref' => true,
					'linklabel' => '',
					'linkhint' => 'LBL_REPLY',
					'linkurl' => OSSMail_Module_Model::getExternalUrlForWidget($recordModel, 'reply'),
					'linkicon' => 'fas fa-reply',
					'linkclass' => 'btn-outline-dark btn-sm sendMailBtn',
				];
				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linkhref' => true,
					'linklabel' => '',
					'linkhint' => 'LBL_REPLYALLL',
					'linkurl' => OSSMail_Module_Model::getExternalUrlForWidget($recordModel, 'replyAll'),
					'linkicon' => 'fas fa-reply-all',
					'linkclass' => 'btn-outline-dark btn-sm sendMailBtn',
				];
				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linkhref' => true,
					'linklabel' => '',
					'linkhint' => 'LBL_FORWARD',
					'linkurl' => OSSMail_Module_Model::getExternalUrlForWidget($recordModel, 'forward'),
					'linkicon' => 'fas fa-share',
					'linkclass' => 'btn-outline-dark btn-sm sendMailBtn',
				];
			}

			if (\App\Privilege::isPermitted('OSSMailView', 'PrintMail')) {
				$detailViewLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => '',
					'linkhint' => 'LBL_PRINT',
					'linkurl' => 'javascript:OSSMailView_Detail_Js.printMail();',
					'linkicon' => 'fas fa-print',
					'linkclass' => 'btn-outline-dark btn-sm',
				];
			}
			foreach ($detailViewLinks as $detailViewLink) {
				$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
			}
		}
		$linkModelDetailViewList = $linkModelList['DETAIL_VIEW_BASIC'];
		$countOfList = \count($linkModelDetailViewList);
		for ($i = 0; $i < $countOfList; ++$i) {
			$linkModel = $linkModelDetailViewList[$i];
			if ('LBL_DUPLICATE' == $linkModel->get('linklabel')) {
				unset($linkModelList['DETAIL_VIEW_BASIC'][$i]);
				break;
			}
		}
		return $linkModelList;
	}
}
