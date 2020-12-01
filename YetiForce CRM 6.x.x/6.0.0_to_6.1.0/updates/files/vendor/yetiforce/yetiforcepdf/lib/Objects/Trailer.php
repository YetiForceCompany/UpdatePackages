<?php

declare(strict_types=1);
/**
 * Trailer class.
 *
 * @package   YetiForcePDF\Objects
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects;

/**
 * Class Font.
 */
class Trailer extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * @var string
	 */
	protected $basicType = 'Trailer';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Trailer';
	/**
	 * Root element.
	 *
	 * @var \YetiForcePDF\Objects\PdfObject
	 */
	protected $root;
	/**
	 * Number of objects in the document.
	 *
	 * @var int
	 */
	protected $size = 0;

	/**
	 * Set root object.
	 *
	 * @param \YetiForcePDF\Objects\PdfObject $root
	 */
	public function setRootObject(\YetiForcePDF\Objects\PdfObject $root): \YetiForcePDF\Objects\Trailer
	{
		$this->root = $root;
		return $this;
	}

	/**
	 * Set document size - number of objects.
	 *
	 * @param int $size
	 *
	 * @return \YetiForcePDF\Objects\Trailer
	 */
	public function setSize(int $size): \YetiForcePDF\Objects\Trailer
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return implode("\n", [
			'trailer',
			'<<',
			'  /Root ' . $this->root->getReference(),
			'  /Size ' . $this->size,
			'  /Info ' . $this->document->getMeta()->getReference(),
			'>>'
		]);
	}
}
