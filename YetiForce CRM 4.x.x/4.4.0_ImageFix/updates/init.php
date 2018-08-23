<?php
/**
 * YetiForceUpdate Class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * YetiForceUpdate Class.
 */
class YetiForceUpdate
{
	/**
	 * @var \vtlib\PackageImport
	 */
	public $package;

	/**
	 * @var object
	 */
	public $modulenode;

	/**
	 * Fields to delete.
	 *
	 * @var string[]
	 */
	public $filesToDelete = [];
	public $modules = [];
	public $moduleName = '';
	public $uitypes = [69, 311];

	/**
	 * Constructor.
	 *
	 * @param object $modulenode
	 */
	public function __construct($modulenode)
	{
		$this->modulenode = $modulenode;
	}

	/**
	 * Preupdate.
	 */
	public function preupdate()
	{
		return true;
	}

	/**
	 * Update.
	 */
	public function update()
	{
		$db = \App\Db::getInstance();
		$modules = array_column(\vtlib\Functions::getAllModules(), 'name');
		$fields = (new \App\Db\Query())->select(['tabid', 'uitype'])->from('vtiger_field')->where(['uitype' => $this->uitypes])->orderBy(['tabid' => SORT_ASC])->createCommand()->queryAllByGroup(2);
		foreach ($fields as $tabId => $uitypes) {
			try {
				if ($tabId) {
					$this->moduleName = \App\Module::getModuleName($tabId);
					$this->modules = $modules;
					$this->migrateImage();
					$this->migrateMultiImage();
				}
			} catch (\Throwable $ex) {
				\App\Log::error('MIGRATE FILES:' . $ex->getMessage());
			}
		}
	}
	
	/**
	 * Postupdate.
	 */
	public function postupdate()
	{
		return true;
	}
	
		/**
	 * Migrate image field.
	 *
	 * @param string $field
	 */
	private function migrateImage(string $field = null)
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$entityModel = $queryGenerator->getEntityModel();
		if ($field) {
			$field = $queryGenerator->getModuleModel()->getField($field);
			$fields = $field ? [$field] : [];
		} else {
			$fields = $queryGenerator->getModuleModel()->getFieldsByUiType(69);
		}
		$field = reset($fields);
		if (empty($fields) || count($fields) !== 1 || !isset($entityModel->tab_name_index[$field->getTableName()])) {
			\App\Log::error('MIGRATE FILES ID:' . implode(',', array_keys($fields)) . "|{$this->moduleName} - Incorrect data");
			return;
		}
		$field->set('primaryColumn', $entityModel->tab_name_index[$field->getTableName()]);
		$queryGenerator->permissions = false;
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id', $field->getName()]);
		$queryGenerator->setCustomColumn(['vtiger_attachments.*']);
		if ($field->getModuleName() !== 'Users') {
			$relTable = 'vtiger_seattachmentsrel';
			$queryGenerator->addJoin(['INNER JOIN', $relTable, $queryGenerator->getColumnName('id') . "=$relTable.crmid"]);
			$queryGenerator->addJoin(['INNER JOIN', 'vtiger_attachments', "$relTable.attachmentsid = vtiger_attachments.attachmentsid"]);
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				$this->updateRow($row, $field);
			}
			$dataReader->close();
		}
	}
	
	/**
	 * Migrate MultiImage field.
	 */
	private function migrateMultiImage()
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$entityModel = $queryGenerator->getEntityModel();
		$fields = $queryGenerator->getModuleModel()->getFieldsByUiType(311);
		foreach ($fields as $field) {
			if (empty($field) || !isset($entityModel->tab_name_index[$field->getTableName()])) {
				\App\Log::error("MIGRATE FILES ID:{$field->getName()}|{$this->moduleName} - Incorrect data");
				continue;
			}
			if ($field->getModuleName() === 'Contacts' || $field->getModuleName() === 'Products') {
				$this->migrateImage($field->getName());
				continue;
			}
		}
	}
	
		/**
	 * Update data.
	 *
	 * @param array               $row
	 * @param \Vtiger_Field_Model $field
	 * @param bool                $isMulti
	 */
	private function updateRow(array $row, \Vtiger_Field_Model $field, bool $isMulti = false)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$path = strpos($row['path'], ROOT_DIRECTORY) === 0 ? $row['path'] : ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $row['path'];
		$file = \App\Fields\File::loadFromInfo([
				'path' => $path . DIRECTORY_SEPARATOR . $row['attachmentsid'],
				'name' => $row['name'],
		]);
		if (!file_exists($file->getPath())) {
			\App\Log::error("MIGRATE FILES ID:{$row['id']}|{$row['attachmentsid']} - No file");
			return;
		}
		if ($file->validate()) {
			$image = [];
			$image['key'] = $file->generateHash();
			$image['size'] = \vtlib\Functions::showBytes($file->getSize());
			$image['name'] = $file->getName();
			$image['path'] = \App\Fields\File::getLocalPath($file->getPath());

			$oldValue = (new \App\Db\Query())->select([$field->getColumnName()])->from($field->getTableName())->where([$field->get('primaryColumn') => $row['id']])->scalar();
			$value = \App\Json::decode($oldValue);
			if (!is_array($value)) {
				$value = [];
			}
			$value[] = $image;
			if ($dbCommand->update($field->getTableName(), [$field->getColumnName() => \App\Json::encode($value)], [$field->get('primaryColumn') => $row['id']])->execute()) {
				if ($isMulti) {
					$dbCommand->delete('u_#__attachments', ['and', ['attachmentid' => $id], ['fieldid' => $field->getId()]])->execute();
				} else {
					$dbCommand->delete('vtiger_crmentity', ['and', ['crmid' => $row['attachmentsid']], ['not in', 'setype', $this->modules]])->execute();
					$dbCommand->delete('vtiger_attachments', ['attachmentsid' => $row['attachmentsid']])->execute();
					if ($field->getModuleName() === 'Users') {
						$dbCommand->delete('vtiger_salesmanattachmentsrel', ['attachmentsid' => $row['attachmentsid']])->execute();
						\App\UserPrivilegesFile::createUserPrivilegesfile($row['id']);
					}
				}
			}
		} else {
			\App\Log::error("MIGRATE FILES ID:{$row['id']}|{$row['attachmentsid']} - " . $file->validateError);
		}
	}
}
