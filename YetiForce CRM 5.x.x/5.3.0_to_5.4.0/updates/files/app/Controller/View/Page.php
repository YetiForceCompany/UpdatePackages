<?php
/**
 * Abstract page view controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\View;

/**
 * Abstract page view controller class.
 */
abstract class Page extends Base
{
	/**
	 * {@inheritdoc}
	 */
	protected function showBodyHeader()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function showFooter()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$view = $this->getViewer($request);
		if (\App\Config::performance('BROWSING_HISTORY_WORKING')) {
			\Vtiger_BrowsingHistory_Helper::saveHistory((string) $view->getVariable('PAGETITLE'));
		}
		$view->assign('BREADCRUMB_TITLE', $this->getBreadcrumbTitle($request));
		$view->assign('SHOW_BREAD_CRUMBS', $this->showBreadCrumbLine());
		if ($activeReminder = \App\Module::isModuleActive('Calendar')) {
			$userPrivilegesModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$activeReminder = $userPrivilegesModel->hasModulePermission('Calendar');
		}
		$view->assign('REMINDER_ACTIVE', $activeReminder);
		$view->assign('QUALIFIED_MODULE', $request->getModule(false));
		$view->assign('MENUS', $this->getMenu());
		$view->assign('BROWSING_HISTORY', \Vtiger_BrowsingHistory_Helper::getHistory());
		$view->assign('HOME_MODULE_MODEL', \Vtiger_Module_Model::getInstance('Home'));
		$view->assign('MENU_HEADER_LINKS', $this->getMenuHeaderLinks($request));
		$view->assign('USER_QUICK_MENU_LINKS', $this->getUserQuickMenuLinks($request));
		if (\App\Config::performance('GLOBAL_SEARCH')) {
			$view->assign('SEARCHABLE_MODULES', \Vtiger_Module_Model::getSearchableModules());
		}
		if (\App\Config::search('GLOBAL_SEARCH_SELECT_MODULE')) {
			$view->assign('SEARCHED_MODULE', $request->getModule());
		}
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
		parent::postProcess($request, false);
		$view = $this->getViewer($request);
		$view->assign('ACTIVITY_REMINDER', \Users_Record_Model::getCurrentUserModel()->getCurrentUserActivityReminderInSeconds());
		$view->assign('SHOW_FOOTER_BAR', $this->showFooter() && 8 !== \App\YetiForce\Register::getStatus());
		$view->assign('SHOW_FOOTER', true);
		if ($display) {
			$view->view('PageFooter.tpl');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			'modules.Vtiger.resources.Menu',
			'modules.Vtiger.resources.Header',
			'modules.Vtiger.resources.Edit',
			"modules.$moduleName.resources.Edit",
			'~layouts/resources/Field.js',
			'~layouts/resources/validator/BaseValidator.js',
			'~layouts/resources/validator/FieldValidator.js',
			'modules.Vtiger.resources.BasicSearch',
			'modules.Vtiger.resources.ConditionBuilder',
			'modules.Vtiger.resources.AdvanceFilter',
			"modules.$moduleName.resources.AdvanceFilter",
			'modules.Vtiger.resources.AdvanceSearch',
		];
		if (\App\Privilege::isPermitted('OSSMail')) {
			$jsFileNames[] = '~layouts/basic/modules/OSSMail/resources/checkmails.js';
		}
		if (!\App\RequestUtil::getBrowserInfo()->ie) {
			if (\App\User::getCurrentUserRealId() === \App\User::getCurrentUserId() && \App\Privilege::isPermitted('Chat')) {
				$jsFileNames[] = '~layouts/basic/modules/Chat/Chat.vue.js';
			}
			if (\App\Privilege::isPermitted('KnowledgeBase')) {
				$jsFileNames[] = '~layouts/resources/views/KnowledgeBase/KnowledgeBase.vue.js';
			}
		}
		foreach (\Vtiger_Link_Model::getAllByType(\vtlib\Link::IGNORE_MODULE, ['HEADERSCRIPT']) as $headerScripts) {
			foreach ($headerScripts as $headerScript) {
				$jsFileNames[] = $headerScript->linkurl;
			}
		}
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$prefix = '';
		if (!IS_PUBLIC_DIR) {
			$prefix = 'public_html/';
		}
		foreach (\Vtiger_Link_Model::getAllByType(\vtlib\Link::IGNORE_MODULE, ['HEADERCSS']) as $cssLinks) {
			foreach ($cssLinks as $cssLink) {
				$cssScriptModel = new \Vtiger_CssScript_Model();
				$headerCssInstances[] = $cssScriptModel->set('href', $prefix . $cssLink->linkurl);
			}
		}
		return $headerCssInstances;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadJsConfig(\App\Request $request)
	{
		parent::loadJsConfig($request);
		if (\App\Session::has('ShowAuthy2faModal')) {
			\App\Config::setJsEnv('ShowAuthy2faModal', \App\Session::get('ShowAuthy2faModal'));
			if ('TOTP_OPTIONAL' === \App\Config::security('USER_AUTHY_MODE')) {
				\App\Session::delete('ShowAuthy2faModal');
			}
		}
		if (\App\Session::has('ShowUserPasswordChange')) {
			\App\Config::setJsEnv('ShowUserPasswordChange', \App\Session::get('ShowUserPasswordChange'));
		}
		if (\App\Session::has('ShowUserPwnedPasswordChange')) {
			\App\Config::setJsEnv('ShowUserPwnedPasswordChange', \App\Session::get('ShowUserPwnedPasswordChange'));
		}
	}

	/**
	 * Function to get the list of Header Links.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Link_Model[] - List of Vtiger_Link_Model instances
	 */
	protected function getMenuHeaderLinks(\App\Request $request)
	{
		$userModel = \Users_Record_Model::getCurrentUserModel();
		$headerLinks = [];
		if (\App\MeetingService::getInstance()->isActive() && \App\Privilege::isPermitted('Users', 'MeetingUrl', false, $userModel->getRealId())) {
			$headerLinks[] = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_VIDEO_CONFERENCE',
				'linkdata' => ['url' => 'index.php?module=Users&view=MeetingModal&record=' . $userModel->getRealId()],
				'icon' => 'AdditionalIcon-VideoConference',
				'linkclass' => 'js-show-modal'
			];
		}
		if ($userModel->isAdminUser()) {
			if ('Settings' !== $request->getByType('parent', 2)) {
				$headerLinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => 'LBL_SYSTEM_SETTINGS',
					'linkurl' => 'index.php?module=YetiForce&parent=Settings&view=Shop',
					'icon' => 'fas fa-cog fa-fw',
				];
			} else {
				$headerLinks[] = [
					'linktype' => 'HEADERLINK',
					'linklabel' => 'LBL_USER_PANEL',
					'linkurl' => 'index.php',
					'icon' => 'fas fa-house-user fa-fw',
				];
			}
		}
		$headerLinks[] = [
			'linktype' => 'HEADERLINK',
			'linklabel' => 'LBL_SIGN_OUT',
			'linkurl' => 'index.php?module=Users&parent=Settings&action=Logout',
			'icon' => 'fas fa-power-off fa-fw',
			'linkclass' => 'btn-danger d-md-none',
		];
		$headerLinkInstances = [];
		foreach ($headerLinks as $headerLink) {
			$headerLinkInstance = \Vtiger_Link_Model::getInstanceFromValues($headerLink);
			if (isset($headerLink['childlinks'])) {
				foreach ($headerLink['childlinks'] as $childLink) {
					$headerLinkInstance->addChildLink(\Vtiger_Link_Model::getInstanceFromValues($childLink));
				}
			}
			$headerLinkInstances[] = $headerLinkInstance;
		}
		$headerLinks = \Vtiger_Link_Model::getAllByType(\vtlib\Link::IGNORE_MODULE, ['HEADERLINK']);
		foreach ($headerLinks as $headerLinks) {
			foreach ($headerLinks as $headerLink) {
				$headerLinkInstances[] = \Vtiger_Link_Model::getInstanceFromLinkObject($headerLink);
			}
		}
		return $headerLinkInstances;
	}

	/**
	 * Function to get the list of User Quick Menu Links.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Link_Model[] - List of Vtiger_Link_Model instances
	 */
	protected function getUserQuickMenuLinks(\App\Request $request)
	{
		$userModel = \Users_Record_Model::getCurrentUserModel();
		$headerLinks[] = [
			'linktype' => 'GROUPNAME',
			'linklabel' => 'LBL_ACCOUNT_SETTINGS',
		];
		if (\App\Config::security('SHOW_MY_PREFERENCES')) {
			$headerLinks[] = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_MY_PREFERENCES',
				'linkurl' => $userModel->getPreferenceDetailViewUrl(),
				'linkclass' => 'd-block',
				'icon' => 'fas fa-user-cog fa-fw',
			];
		}
		$headerLinks[] = [
			'linktype' => 'HEADERLINK',
			'linklabel' => 'LBL_CHANGE_PASSWORD',
			'linkdata' => ['url' => 'index.php?module=Users&view=PasswordModal&mode=change&record=' . $userModel->get('id')],
			'linkclass' => 'showModal d-block',
			'icon' => 'fas fa-key fa-fw',
		];
		if (\Users_Module_Model::getSwitchUsers()) {
			$headerLinks[] = [
				'linktype' => 'HEADERLINK',
				'linklabel' => 'SwitchUsers',
				'linkurl' => '',
				'icon' => 'fas fa-exchange-alt fa-fw',
				'linkdata' => ['url' => $userModel->getSwitchUsersUrl()],
				'linkclass' => 'showModal d-block',
			];
		}
		$headerLinks[] = [
			'linktype' => 'HEADERLINK',
			'linklabel' => 'LBL_LOGIN_HISTORY',
			'linkdata' => ['url' => 'index.php?module=Users&view=LoginHistoryModal&mode=change&record=' . $userModel->get('id')],
			'linkclass' => 'showModal d-block',
			'icon' => 'mdi mdi-lock-reset',
		];
		$headerLinks[] = [
			'linktype' => 'SEPARATOR',
			'linkclass' => 'd-none d-sm-none d-md-block',
		];
		$headerLinks[] = [
			'linktype' => 'HEADERLINK',
			'linklabel' => 'LBL_SIGN_OUT',
			'linkurl' => 'index.php?module=Users&parent=Settings&action=Logout',
			'icon' => 'fas fa-power-off fa-fw',
			'linkclass' => 'd-none d-sm-none d-md-block',
		];
		$headerLinkInstances = [];
		foreach ($headerLinks as $headerLink) {
			$headerLinkInstances[] = \Vtiger_Link_Model::getInstanceFromValues($headerLink);
		}
		return $headerLinkInstances;
	}

	/**
	 * Function to get the list of menu.
	 *
	 * @return array
	 */
	protected function getMenu()
	{
		return \Vtiger_Menu_Model::getAll(true);
	}
}
