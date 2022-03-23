<?php

declare(strict_types=1);
/**
 * TableWrapperBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Html\Element;
use YetiForcePDF\Math;
use YetiForcePDF\Style\Style;

/**
 * Class TableWrapperBox.
 */
class TableWrapperBox extends BlockBox
{
	/**
	 * We shouldn't append block box here.
	 *
	 * @param mixed $childDomElement
	 * @param mixed $element
	 * @param mixed $style
	 * @param mixed $parentBlock
	 */
	public function appendBlockBox($childDomElement, $element, $style, $parentBlock)
	{
	}

	/**
	 * We shouldn't append table wrapper here.
	 *
	 * @param mixed $childDomElement
	 * @param mixed $element
	 * @param mixed $style
	 * @param mixed $parentBlock
	 */
	public function appendTableWrapperBox($childDomElement, $element, $style, $parentBlock)
	{
	}

	/**
	 * We shouldn't append inline block box here.
	 *
	 * @param mixed $childDomElement
	 * @param mixed $element
	 * @param mixed $style
	 * @param mixed $parentBlock
	 */
	public function appendInlineBlockBox($childDomElement, $element, $style, $parentBlock)
	{
	}

	/**
	 * We shouldn't append inline box here.
	 *
	 * @param mixed $childDomElement
	 * @param mixed $element
	 * @param mixed $style
	 * @param mixed $parentBlock
	 */
	public function appendInlineBox($childDomElement, $element, $style, $parentBlock)
	{
	}

	/**
	 * Append table box element.
	 *
	 * @param \DOMNode                      $childDomElement
	 * @param Element                       $element
	 * @param Style                         $style
	 * @param \YetiForcePDF\Layout\BlockBox $parentBlock
	 *
	 * @return $this
	 */
	public function appendTableBox($childDomElement, $element, $style, $parentBlock)
	{
		$cleanStyle = (new \YetiForcePDF\Style\Style())->setDocument($this->document);
		$box = (new TableBox())
			->setDocument($this->document)
			->setParent($this)
			//->setElement($element)
			->setStyle($cleanStyle)
			->init();
		$cleanStyle->setRule('display', 'block');
		$this->appendChild($box);
		$box->getStyle()->init()->setRule('display', 'block');
		$box->buildTree($box);
		return $box;
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureWidth(bool $afterPageDividing = false)
	{
		$this->applyStyleWidth();
		foreach ($this->getChildren() as $child) {
			$child->measureWidth($afterPageDividing);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureHeight(bool $afterPageDividing = false)
	{
		if ($this->wasCut()) {
			return $this;
		}
		$maxHeight = '0';
		foreach ($this->getChildren() as $child) {
			$child->measureHeight($afterPageDividing);
		}
		foreach ($this->getChildren() as $child) {
			$child->measureHeight($afterPageDividing);
			$maxHeight = Math::max($maxHeight, $child->getDimensions()->getHeight());
		}
		$style = $this->getStyle();
		$maxHeight = Math::add($maxHeight, $style->getVerticalBordersWidth(), $style->getVerticalPaddingsWidth());
		$this->getDimensions()->setHeight($maxHeight);
		$this->applyStyleWidth();
		return $this;
	}
}
