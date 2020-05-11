<?php
/**
 * Process wizard base model file.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Process wizard base model class.
 */
class Vtiger_ProcessWizard_Model extends \App\Base
{
	/**
	 * The current process wizard map.
	 *
	 * @var array
	 */
	protected $wizardMap;
	/**
	 * Vtiger_Record_Model.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $recordModel;
	/**
	 * Current process step.
	 *
	 * @var array
	 */
	protected $step;

	/**
	 * Get instance model.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return self
	 */
	public static function getInstance(Vtiger_Record_Model $recordModel): self
	{
		$className = Vtiger_Loader::getComponentClassName('Model', 'ProcessWizard', $recordModel->getModuleName());
		$instance = new $className();
		$instance->recordModel = $recordModel;
		if (method_exists($instance, 'load')) {
			$instance->load();
		}
		$instance->loadGroup();
		$instance->loadConditions();
		return $instance;
	}

	/**
	 * Load current map group.
	 *
	 * @return void
	 */
	public function loadGroup(): void
	{
		if (isset($this->wizardMap[0]['groupConditions'])) {
			foreach ($this->wizardMap as $groupMap) {
				if (isset($groupMap['groupConditions']) && \App\Condition::checkConditions($groupMap['groupConditions'], $this->recordModel)) {
					$this->wizardMap = $groupMap['group'];
					return;
				}
			}
			$this->wizardMap = [];
		}
	}

	/**
	 * Load and check the process wizard conditions.
	 *
	 * @return void
	 */
	public function loadConditions(): void
	{
		foreach ($this->wizardMap as $id => &$map) {
			$map['id'] = $id;
			if (isset($map['conditionsStatus'])) {
				continue;
			}
			if (isset($map['conditions'])) {
				$map['conditionsStatus'] = \App\Condition::checkConditions($map['conditions'], $this->recordModel);
				if ($map['conditionsStatus']) {
					break;
				}
			}
		}
	}

	/**
	 * Get process wizard steps.
	 *
	 * @return array
	 */
	public function getSteps(): array
	{
		return $this->wizardMap;
	}

	/**
	 * Get active process wizard step.
	 *
	 * @return array|null
	 */
	public function getStep(): ?array
	{
		if (isset($this->step)) {
			return $this->step;
		}
		foreach ($this->wizardMap as $id => $map) {
			if ($map['conditionsStatus']) {
				return $this->step = $map;
			}
		}
		return $this->step = null;
	}

	/**
	 * Set the active step of the process wizard.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function setStep(int $id): void
	{
		if (empty($this->getStep()) || $id < $this->getStep()['id']) {
			$this->step = $this->wizardMap[$id];
		}
	}

	/**
	 * Get the blocks of the current step.
	 *
	 * @return array
	 */
	public function getStepBlocks(): array
	{
		$blocks = [];
		if ($step = $this->getStep()) {
			foreach ($step['blocks'] as $block) {
				switch ($block['type']) {
					case 'fields':
						$blocks[] = $this->getFieldsStructure($block);
						break;
					case 'relatedLists':
						$blocks[] = $this->getRelatedListStructure($block);
						break;
					default:
						// code...
						break;
				}
			}
		}
		return $blocks;
	}

	/**
	 * Get fields structure for fields block type.
	 *
	 * @param array $block
	 *
	 * @return array
	 */
	public function getFieldsStructure(array $block): array
	{
		$fields = [];
		foreach ($block['fields'] as $field) {
			if (\is_array($field) && 'relatedField' === $field['type']) {
				if (App\Record::isExists($this->recordModel->get($field['field']))) {
					$recordModel = \Vtiger_Record_Model::getInstanceById($this->recordModel->get($field['field']));
					$fieldModel = $recordModel->getField($field['relatedField']);
					if ($fieldModel && $fieldModel->isViewable()) {
						$fieldModel->set('fieldvalue', $recordModel->get($field['relatedField']));
						$fields[$field['relatedField']] = $fieldModel;
					}
				}
			} else {
				$fieldModel = $this->recordModel->getField($field);
				if ($fieldModel && $fieldModel->isViewable()) {
					$fieldModel->set('fieldvalue', $this->recordModel->get($field));
					$fields[$field] = $fieldModel;
				}
			}
		}
		$block['fieldsStructure'] = $fields;
		return $block;
	}

	/**
	 * Get fields structure for related lists block type.
	 *
	 * @param array $block
	 *
	 * @return array
	 */
	public function getRelatedListStructure(array $block): array
	{
		$relation = Vtiger_Relation_Model::getInstanceById($block['relationId']);
		$block['relationStructure'] = Vtiger_Link_Model::getInstanceFromValues([
			'linklabel' => $block['label'] ?? $relation->get('label'),
			'linkurl' => $relation->getListUrl($this->recordModel) . ($block['relationConditions'] ?? ''),
			'linkicon' => '',
			'relatedModuleName' => $relation->get('relatedModuleName'),
			'relationId' => $relation->getId(),
		]);
		return $block;
	}

	/**
	 * Get the actions of the current step..
	 *
	 * @return array
	 */
	public function getActions(): array
	{
		$actions = [];
		if (($step = $this->getStep()) && !empty($step['actions']) && $step['conditionsStatus']) {
			foreach ($step['actions'] as $action) {
				$actions[] = Vtiger_Link_Model::getInstanceFromValues($action);
			}
		}
		return $actions;
	}

	/**
	 * Check permissions to step.
	 *
	 * @return bool
	 */
	public function checkPermissionsToStep(): bool
	{
		$step = $this->getStep();
		if (empty($step['permissionsToStep'])) {
			return true;
		}
		if (\is_callable($step['permissionsToStep'])) {
			return \call_user_func($step['permissionsToStep']);
		}
		return false;
	}
}
