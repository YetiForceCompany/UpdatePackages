<?php

declare(strict_types=1);
/**
 * Catalog class.
 *
 * @package   YetiForcePDF\Document
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF;

/**
 * Class Catalog.
 */
class Catalog extends \YetiForcePDF\Objects\Basic\DictionaryObject
{
	/**
	 * {@inheritdoc}
	 */
	protected $dictionaryType = 'Catalog';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Catalog';
	/**
	 * Children elements.
	 *
	 * @var \YetiForcePDF\Objects\PdfObject[]
	 */
	protected $children = [];

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return implode("\n", [
			$this->getRawId() . ' obj',
			'<</Type /Catalog/Pages ' . $this->document->getPagesObject()->getReference() . '>>',
			'endobj',
		]);
	}
}
