<?php

declare(strict_types=1);
/**
 * TableRowGroupBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Html\Element;
use YetiForcePDF\Style\Style;

/**
 * Class TableRowGroupBox.
 */
class TableRowGroupBox extends BlockBox
{
	/**
	 * We shouldn't append block box here.
	 */
	public function appendBlockBox($childDomElement, $element, $style, $parentBlock)
	{
	}

	/**
	 * We shouldn't append table wrapper here.
	 */
	public function appendTableWrapperBox($childDomElement, $element, $style, $parentBlock)
	{
	}

	/**
	 * We shouldn't append inline block box here.
	 */
	public function appendInlineBlockBox($childDomElement, $element, $style, $parentBlock)
	{
	}

	/**
	 * We shouldn't append inline box here.
	 */
	public function appendInlineBox($childDomElement, $element, $style, $parentBlock)
	{
	}

	/**
	 * Create row box
	 * return TableRowBox.
	 */
	public function createRowBox()
	{
		$style = (new \YetiForcePDF\Style\Style())
			->setDocument($this->document)
			->setContent('')
			->parseInline();
		$box = (new TableRowBox())
			->setDocument($this->document)
			->setParent($this)
			->setStyle($style)
			->init();
		$this->appendChild($box);
		$box->getStyle()->init();
		return $box;
	}

	/**
	 * Append table row box element.
	 *
	 * @param \DOMNode                      $childDomElement
	 * @param Element                       $element
	 * @param Style                         $style
	 * @param \YetiForcePDF\Layout\BlockBox $parentBlock
	 *
	 * @return $this
	 */
	public function appendTableRowBox($childDomElement, $element, $style, $parentBlock)
	{
		$box = (new TableRowBox())
			->setDocument($this->document)
			->setParent($this)
			->setElement($element)
			->setStyle($style)
			->init();
		$this->appendChild($box);
		$box->getStyle()->init();
		$box->buildTree($box);
		return $box;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInstructions(): string
	{
		return ''; // not renderable
	}
}
