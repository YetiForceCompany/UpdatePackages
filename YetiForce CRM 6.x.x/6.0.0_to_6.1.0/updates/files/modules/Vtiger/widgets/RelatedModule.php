<?php

/**
 * Vtiger RelatedModule widget class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_RelatedModule_Widget extends Vtiger_Basic_Widget
{
	public function getUrl()
	{
		$moduleName = is_numeric($this->Data['relatedmodule']) ? App\Module::getModuleName($this->Data['relatedmodule']) : $this->Data['relatedmodule'];
		$url = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showRelatedRecords&relatedModule=' . $moduleName . '&page=1&limit=' . $this->Data['limit'] . '&viewType=' . $this->Data['viewtype'] . '&relationId=' . $this->Data['relation_id'];
		if (isset($this->Data['no_result_text'])) {
			$url .= '&no_result_text=' . $this->Data['no_result_text'];
		}
		$fields = [];
		if (!empty($this->Data['relatedfields'])) {
			foreach ((array) $this->Data['relatedfields'] as $field) {
				[, $fieldName] = explode('::', $field);
				$fields[] = $fieldName;
			}
		}
		if ($fields) {
			$url .= '&fields=' . implode(',', $fields);
		}
		return $url;
	}

	public function getWidget()
	{
		$widget = [];
		$model = Vtiger_Module_Model::getInstance($this->Data['relatedmodule']);
		if ($model && $model->isPermitted('DetailView')) {
			$whereCondition = [];
			$this->Config['url'] = $this->getUrl();
			$this->Config['tpl'] = 'Basic.tpl';
			$isQuickCreateSupport = !$model->isQuickCreateSupported();
			$this->Config['isQuickCreateSupport'] = $isQuickCreateSupport;
			if (1 == $this->Data['action']) {
				$createPermission = $model->isPermitted('CreateView');
				$this->Config['action'] = (true === $createPermission) ? 1 : 0;
				if ($isQuickCreateSupport) {
					$url = $model->getCreateRecordUrl();
				} else {
					$url = $model->getQuickCreateUrl();
				}
				$this->Config['actionURL'] = $url . "&sourceRecord={$this->Record}&sourceModule={$this->Module}&relationOperation=true&relationId=" . $this->Data['relation_id'];
			}
			if (isset($this->Data['switchHeader']) && '-' != $this->Data['switchHeader']) {
				$switchHeaderData = Settings_Widgets_Module_Model::getHeaderSwitch([$this->Data['relatedmodule'], $this->Data['switchHeader']]);
				if ($switchHeaderData && 1 === $switchHeaderData['type']) {
					$whereConditionOff = [];
					foreach ($switchHeaderData['value'] as $name => $value) {
						$whereCondition[] = [$name, 'n', implode('##', $value)];
						$whereConditionOff[] = [$name, 'e', implode('##', $value)];
					}
					$this->getCheckboxLables($model, 'switchHeader', 'LBL_SWITCHHEADER_');
					$this->Config['switchHeader']['on'] = \App\Json::encode($whereCondition);
					$this->Config['switchHeader']['off'] = \App\Json::encode($whereConditionOff);
					$whereCondition = [$whereCondition];
				}
			}
			if (isset($this->Data['checkbox']) && '-' !== $this->Data['checkbox']) {
				if (false !== strpos($this->Data['checkbox'], '.')) {
					$separateData = explode('.', $this->Data['checkbox']);
					$columnName = $separateData[1];
				} else {
					$columnName = $this->Data['checkbox'];
				}

				$whereOnCondition[] = [$columnName, 'e', 1];
				$whereOffCondition[] = [$columnName, 'e', 0];
				$whereCondition = [$whereOnCondition];

				$this->Config['checkbox']['on'] = \App\Json::encode($whereOnCondition);
				$this->Config['checkbox']['off'] = \App\Json::encode($whereOffCondition);
				$this->getCheckboxLables($model, 'checkbox', 'LBL_SWITCH_');
			}
			if (!empty($whereCondition)) {
				$this->Config['url'] .= '&search_params=' . \App\Json::encode($whereCondition);
			}
			$widget = $this->Config;
			$widget['instance'] = $this;
		}
		return $widget;
	}

	public function getCheckboxLables($model, $type, $prefix)
	{
		$on = $prefix . 'ON_' . strtoupper($this->Data[$type]);
		$translateOn = \App\Language::translate($on, $model->getName());
		if ($on === $translateOn) {
			$translateOn = \App\Language::translate('LBL_YES', $model->getName());
		}
		$off = $prefix . 'OFF_' . strtoupper($this->Data[$type]);
		$translateOff = \App\Language::translate($off, $model->getName());

		if ($off === $translateOff) {
			$translateOff = \App\Language::translate('LBL_NO', $model->getName());
		}
		$this->Config[$type . 'Lables'] = ['on' => $translateOn, 'off' => $translateOff];
	}

	public function getConfigTplName()
	{
		return 'RelatedModuleConfig';
	}
}
