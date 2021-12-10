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

class SMSNotifier_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to check whether the module is an entity type module or not.
	 *
	 * @return bool true/false
	 */
	public function isQuickCreateSupported()
	{
		//SMSNotifier module is not enabled for quick create
		return false;
	}

	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/**
	 * Function to get the module is permitted to specific action.
	 *
	 * @param string $actionName
	 *
	 * @return <boolean>
	 */
	public function isPermitted($actionName)
	{
		if ('EditView' === $actionName) {
			return false;
		}
		return \App\Privilege::isPermitted($this->getName(), $actionName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSettingLinks(): array
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$settingsLinks = [];
		if (VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_WORKFLOWS',
				'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
				'linkicon' => 'yfi yfi-workflows-2',
			];
		}
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => \App\Language::translate('LBL_SERVER_CONFIG', $this->getName()),
			'linkurl' => 'index.php?module=SMSNotifier&parent=Settings&view=List',
			'linkicon' => 'yfm-SMSNotifier',
		];
		return $settingsLinks;
	}

	/**
	 * Function to get instance of provider model.
	 *
	 * @param string $providerName
	 * @param string $jsonParams
	 *
	 * @return bool|\SMSNotifier_Basic_Provider
	 */
	public static function getProviderInstance($providerName, $jsonParams = '')
	{
		$provider = false;
		if (!empty($providerName)) {
			$providerName = trim($providerName);
			$className = Vtiger_Loader::getComponentClassName('Provider', $providerName, 'SMSNotifier');
			if ($className && class_exists($className)) {
				$provider = new $className();
				if (!empty($jsonParams)) {
					$params = \App\Json::decode(App\Purifier::decodeHtml($jsonParams));
					foreach ($params as $key => $value) {
						$provider->set($key, $value);
					}
				}
			}
		}
		return $provider;
	}

	/**
	 * Function to get All providers.
	 *
	 * @return \SMSNotifier_Basic_Provider[]
	 */
	public static function getProviders()
	{
		$iterator = new \DirectoryIterator(__DIR__ . '/../providers');
		foreach ($iterator as $item) {
			if ($item->isFile() && 'Basic.php' !== $item->getFilename() && 'php' === $item->getExtension()) {
				$providers[] = self::getProviderInstance($item->getBasename('.php'), '');
			}
		}
		return $providers;
	}

	/**
	 * Function to get active provider.
	 *
	 * @return \SMSNotifier_Basic_Provider[]
	 */
	public static function getActiveProviderInstance()
	{
		if (\App\Cache::has('SMSNotifierConfig', 'activeProviderInstance')) {
			$provider = \App\Cache::get('SMSNotifierConfig', 'activeProviderInstance');

			return $provider ? clone $provider : $provider;
		}
		$provider = false;
		$data = (new App\Db\Query())->from('a_#__smsnotifier_servers')->where(['isactive' => 1])->one();
		if ($data) {
			$provider = self::getProviderInstance($data['providertype'], $data['parameters']);
			$provider->set('api_key', \App\Encryption::getInstance()->decrypt($data['api_key']));
		}
		\App\Cache::save('SMSNotifierConfig', 'activeProviderInstance', $provider, \App\Cache::LONG);

		return $provider;
	}

	/**
	 * Check server.
	 *
	 * @return bool
	 */
	public static function checkServer()
	{
		$provider = self::getActiveProviderInstance();

		return false !== $provider;
	}

	/**
	 * Adds sms notifications to cron.
	 *
	 * @param string   $message
	 * @param string[] $toNumbers
	 * @param int[]    $recordIds
	 * @param string   $ralModuleName
	 *
	 * @return int
	 */
	public static function addSmsToCron($message, $toNumbers, $recordIds, $ralModuleName)
	{
		return \App\Db::getInstance('admin')->createCommand()->insert('s_#__smsnotifier_queue', [
			'message' => $message,
			'tonumbers' => \is_array($toNumbers) ? implode(',', $toNumbers) : $toNumbers,
			'records' => \is_array($recordIds) ? implode(',', $recordIds) : $recordIds,
			'module' => $ralModuleName, ])->execute();
	}

	/** {@inheritdoc} */
	public function isListViewNameFieldNavigationEnabled()
	{
		return false;
	}
}
