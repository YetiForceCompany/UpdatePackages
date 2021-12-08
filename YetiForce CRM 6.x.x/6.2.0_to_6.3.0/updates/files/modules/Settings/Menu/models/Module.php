<?php

/**
 * Settings menu module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Menu_Module_Model
{
	/**
	 * Fields to edit.
	 *
	 * @var strung[]
	 */
	protected $editFields = [
		'id', 'role', 'parentid', 'type', 'sequence', 'module', 'label', 'newwindow',
		'dataurl', 'showicon', 'icon', 'sizeicon', 'hotkey', 'filters', 'edit', 'source',
	];
	const TYPES = [
		0 => 'Module',
		1 => 'Shortcut',
		2 => 'Label',
		3 => 'Separator',
		5 => 'QuickCreate',
		6 => 'HomeIcon',
		7 => 'CustomFilter',
		8 => 'Profile',
		9 => 'RecycleBin',
	];

	/**
	 * Function to get instance.
	 *
	 * @param bool true/false
	 *
	 * @return <Settings_Menu_Module_Model>
	 */
	public static function getInstance()
	{
		return new self();
	}

	/**
	 * Function to get editable fields.
	 *
	 * @return string[]
	 */
	public function getEditFields()
	{
		return $this->editFields;
	}

	public function getMenuTypes($key = false)
	{
		if (false === $key) {
			return self::TYPES;
		}
		return self::TYPES[$key];
	}

	public function getMenuTypeKey($val)
	{
		return array_search($val, self::TYPES);
	}

	public function getMenuUrl($row)
	{
		switch ($row['type']) {
			case 0:
				$moduleModel = Vtiger_Module_Model::getInstance($row['module']);
				$url = $moduleModel->getDefaultUrl() . '&mid=' . $row['id'] . (empty($row['parentid']) ? '' : ('&parent=' . $row['parentid']));
				break;
			case 1:
				$url = $row['dataurl'];
				break;
			case 4:
				$url = addslashes($row['dataurl']);
				break;
			case 7:
				$url = 'index.php?module=' . $row['name'] . '&view=List&viewname=' . $row['dataurl'] . '&mid=' . $row['id'] . (empty($row['parentid']) ? '' : ('&parent=' . $row['parentid']));
				break;
			default:
				$url = $row['dataurl'];
				break;
		}
		return $url;
	}

	/**
	 * Module list.
	 *
	 * @return array
	 */
	public function getModulesList()
	{
		return (new \App\Db\Query())->select(['tabid', 'name'])->from('vtiger_tab')
			->where(['not in', 'name', ['Users', 'ModComments']])
			->andWhere(['or', ['isentitytype' => 1], ['name' => ['Home', 'OSSMail', 'Portal', 'Rss']]])
			->andWhere(['presence' => 0])
			->orderBy('tabsequence')
			->all();
	}

	public static function getLastId()
	{
		$maxSequence = (new \App\Db\Query())
			->from('yetiforce_menu')
			->max('id');

		return (int) $maxSequence;
	}

	/**
	 * Function to get all filters.
	 *
	 * @return array
	 */
	public function getCustomViewList()
	{
		$filters = (new \App\Db\Query())->select(['cvid', 'viewname', 'entitytype', 'vtiger_tab.tabid'])
			->from('vtiger_customview')
			->leftJoin('vtiger_tab', 'vtiger_tab.name = vtiger_customview.entitytype')->all();
		foreach (Vtiger_Module_Model::getAll() as $module) {
			$filterDir = 'modules' . DIRECTORY_SEPARATOR . $module->get('name') . DIRECTORY_SEPARATOR . 'filters';
			if (file_exists($filterDir)) {
				$fileFilters = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filterDir, FilesystemIterator::SKIP_DOTS));
				foreach ($fileFilters as $filter) {
					$name = str_replace('.php', '', $filter->getFilename());
					$handlerClass = Vtiger_Loader::getComponentClassName('Filter', $name, $module->get('name'));
					if (class_exists($handlerClass)) {
						$filters[] = [
							'viewname' => (new $handlerClass())->getViewName(),
							'cvid' => $name,
							'entitytype' => $module->get('name'),
							'tabid' => $module->getId(),
						];
					}
				}
			}
		}
		return $filters;
	}
}
