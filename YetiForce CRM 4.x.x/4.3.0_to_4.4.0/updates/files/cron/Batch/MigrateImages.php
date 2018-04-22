<?php
/**
 * Migration of picture fields.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
namespace Cron\Batch;

/**
 * Class for migrating picture fields.
 */
class MigrateImages
{

	/**
	 * Time limit
	 */
	CONST TIME_LIMIT = 20;

	/**
	 * Module name
	 * @var string
	 */
	private $moduleName;

	/**
	 * Uitypes
	 * @var int[]
	 */
	private $uitypes;

	/**
	 * Modules
	 * @var string[]
	 */
	public $modules;

	/**
	 * Tables
	 * @var string[]
	 */
	public $tables = [];
	public $startPrcess;
	public $break = false;

	/**
	 * Constructor
	 * @param int $module
	 * @param int[] $uitypes
	 */
	public function __construct(int $module = 0, array $uitypes = [])
	{
		if ($module) {
			$this->moduleName = \App\Module::getModuleName($module);
			$this->uitypes = array_unique($uitypes);
		}
	}

	/**
	 * Preprocess
	 * @return bool
	 */
	public function preProcess()
	{
		$db = \App\Db::getInstance();
		$uitypes = [69];
		$tables = ['u_#__attachments' => 311, 'vtiger_salesmanattachmentsrel' => 105];
		foreach ($tables as $table => $uitype) {
			if ($db->isTableExists($table)) {
				$uitypes[] = $uitype;
			} else {
				unset($tables[$table]);
			}
		}
		if ($tables) {
			$this->uitypes = $uitypes;
			$this->tables = array_keys($tables);
		}
		$this->startPrcess = time();
		return !empty($tables);
	}

	/**
	 * Process
	 */
	public function process()
	{
		$modules = array_column(\vtlib\Functions::getAllModules(), 'name');
		$fields = (new \App\Db\Query)->select(['tabid', 'uitype'])->from('vtiger_field')->where(['uitype' => $this->uitypes])->orderBy(['tabid' => SORT_ASC])->createCommand()->queryAllByGroup(2);
		foreach ($fields as $tabId => $uitypes) {
			try {
				$migrateClassModel = new self($tabId, $uitypes);
				$migrateClassModel->modules = $modules;
				$migrateClassModel->startPrcess = $this->startPrcess;
				if ($migrateClassModel->migrate()) {
					$this->break = true;
					break;
				}
				unset($migrateClassModel);
			} catch (\Throwable $ex) {
				\App\Log::error("MIGRATE FILES:" . $ex->getMessage());
			}
		}
		$this->clean();
	}

	/**
	 * Process
	 */
	public function postProcess()
	{
		return empty($this->tables) || ($this->break === false);
	}

	/**
	 * Migration of picture fields.
	 * @return bool
	 */
	public function migrate()
	{
		foreach ($this->uitypes as $uitype) {
			if ($this->break) {
				break;
			}
			switch ($uitype) {
				case 69:
				case 105:
					$this->migrateImage($uitype);
					break;
				case 311:
					$this->migrateMultiImage();
					break;
			}
		}
		return $this->break;
	}

	/**
	 * Migrate image field
	 * @param int $uitype
	 */
	private function migrateImage(int $uitype)
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$entityModel = $queryGenerator->getEntityModel();
		$fields = $queryGenerator->getModuleModel()->getFieldsByUiType($uitype);
		$field = reset($fields);
		if (empty($fields) || count($fields) !== 1 || !isset($entityModel->tab_name_index[$field->getTableName()])) {
			\App\Log::error("MIGRATE FILES ID:" . implode(',', array_keys($fields)) . "|{$this->moduleName} - Incorrect data");
			return;
		}
		$field->set('primaryColumn', $entityModel->tab_name_index[$field->getTableName()]);
		$relTable = $uitype === 105 ? 'vtiger_salesmanattachmentsrel' : 'vtiger_seattachmentsrel';
		$queryGenerator->permissions = false;
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id', $field->getName()]);
		$queryGenerator->setCustomColumn(['vtiger_attachments.*']);
		$queryGenerator->addJoin(['INNER JOIN', $relTable, $queryGenerator->getColumnName('id') . "=$relTable.crmid"]);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_attachments', '$relTable.attachmentsid = vtiger_attachments.attachmentsid']);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (time() > ($this->startPrcess + (static::TIME_LIMIT * 60))) {
				$this->break = true;
				break;
			}
			$this->updateRow($row, $field);
		}
		$dataReader->close();
	}

	/**
	 * Migrate MultiImage field
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
			$field->set('primaryColumn', $entityModel->tab_name_index[$field->getTableName()]);
			$queryGenerator->permissions = false;
			$queryGenerator->setStateCondition('All');
			$queryGenerator->setFields(['id', $field->getName()]);
			$queryGenerator->setCustomColumn(['attachmentsid' => 'u_#__attachments.attachmentid', 'path' => 'u_#__attachments.path', 'name' => 'u_#__attachments.name']);
			$queryGenerator->addJoin(['INNER JOIN', 'u_#__attachments', $queryGenerator->getColumnName('id') . '=u_#__attachments.crmid']);
			$queryGenerator->addNativeCondition(['and', ['u_#__attachments.fieldid' => $field->getId()], ['status' => 1]]);
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				if (time() > ($this->startPrcess + (static::TIME_LIMIT * 60))) {
					$this->break = true;
					break;
				}
				$this->updateRow($row, $field, true);
			}
		}
	}

	/**
	 * Update data
	 * @param array $row
	 * @param \Vtiger_Field_Model $field
	 * @param bool $isMulti
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

			$oldValue = (new \App\Db\Query)->select([$field->getColumnName()])->from($field->getTableName())->where([$field->get('primaryColumn') => $row['id']])->scalar();
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
					if ($field->getUIType() === 105) {
						$dbCommand->delete('vtiger_salesmanattachmentsrel', ['attachmentsid' => $row['attachmentsid']])->execute();
					} else {
						$dbCommand->delete('vtiger_seattachmentsrel', ['attachmentsid' => $row['attachmentsid']])->execute();
					}
				}
			}
		} else {
			\App\Log::error("MIGRATE FILES ID:{$row['id']}|{$row['attachmentsid']} - " . $file->validateError);
		}
	}

	/**
	 * Drop tables
	 * @param array $tables
	 */
	public function clean()
	{
		$db = \App\Db::getInstance();
		foreach ($this->tables as $table) {
			if (!(new \App\Db\Query)->from($table)->exists()) {
				$db->createCommand()->dropTable($table);
			} else {
				\App\Log::error("MIGRATE FILES - $table can not be deleted. There is data.");
			}
		}
	}
}
