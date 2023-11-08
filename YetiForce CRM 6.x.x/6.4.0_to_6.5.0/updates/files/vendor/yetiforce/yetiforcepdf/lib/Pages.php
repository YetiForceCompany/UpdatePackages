<?php

declare(strict_types=1);
/**
 * Page class.
 *
 * @package   YetiForcePDF\Document
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF;

/**
 * Class Pages.
 */
class Pages extends \YetiForcePDF\Objects\Basic\DictionaryObject
{
	/**
	 * {@inheritdoc}
	 */
	protected $dictionaryType = 'Pages';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Pages';

	/**
	 * Proc Set.
	 *
	 * @var \YetiForcePDF\Objects\Basic\ArrayObject
	 */
	protected $procSet;

	/**
	 * Add proc set.
	 *
	 * @param \YetiForcePDF\Objects\Basic\ArrayObject $procSet
	 *
	 * @return $this
	 */
	public function addProcSet(Objects\Basic\ArrayObject $procSet)
	{
		$this->procSet = $procSet;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		$kids = [];
		foreach ($this->children as $child) {
			$kids[] = $child->getReference();
		}
		$this->clearValues()
			->addValue('Type', '/Pages')
			->addValue('Count', (string) \count($kids))
			->addValue('Kids', '[' . implode("\n    ", $kids) . ']');
		if ($this->procSet) {
			$this->addValue('ProcSet', $this->procSet->getReference());
		}
		return parent::render();
	}
}
