<?php

/**
 * YetiForce registration modal view class file.
 *
 * @package   Modules
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

/**
 * Offline registration modal view class.
 */
class Settings_YetiForce_RegistrationSerialModal_View extends \App\Controller\ModalSettings
{
	/**
	 * The name of the activation button.
	 *
	 * @var string
	 */
	public $successBtn = 'LBL_ACTIVATE';

	/**
	 * Set modal title.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'fas fa-receipt';
		$this->pageTitle = \App\Language::translate('YetiForce', $qualifiedModuleName) . ' - ' . \App\Language::translate('LBL_REGISTRATION_OFFLINE_MODAL', $qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->view('RegistrationOfflineModal.tpl', $qualifiedModuleName);
	}
}
