<?php

declare(strict_types=1);
/**
 * ArrayObject class.
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class ArrayObject.
 */
class ArrayObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'Array';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Array';
	/**
	 * Collection of items.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * Initialisation.
	 *
	 * @return $this
	 */
	public function init()
	{
		parent::init();
		$this->id = $this->document->getActualId();
		return $this;
	}

	public function addItem($item): self
	{
		$this->items[] = $item;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		$stringItems = [];
		foreach ($this->items as $item) {
			if ($item instanceof \YetiForcePDF\Objects\PdfObject) {
				$stringItems[] = $item->getReference();
			} else {
				$stringItems[] = (string) $item;
			}
		}
		return implode("\n", [
			$this->getRawId() . ' obj',
			'[ ' . implode(' ', $stringItems) . ' ]',
			'endobj',
		]);
	}
}
