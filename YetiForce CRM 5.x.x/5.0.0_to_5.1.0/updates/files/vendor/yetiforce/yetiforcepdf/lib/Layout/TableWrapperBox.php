<?php

declare(strict_types=1);
/**
 * TableWrapperBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
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
	public function measureWidth()
	{
		$this->applyStyleWidth();
		foreach ($this->getChildren() as $child) {
			$child->measureWidth();
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
			$child->measureHeight();
		}
		foreach ($this->getChildren() as $child) {
			$child->measureHeight();
			$maxHeight = Math::max($maxHeight, $child->getDimensions()->getHeight());
		}
		$style = $this->getStyle();
		$maxHeight = Math::add($maxHeight, $style->getVerticalBordersWidth(), $style->getVerticalPaddingsWidth());
		$this->getDimensions()->setHeight($maxHeight);
		$this->applyStyleWidth();
		return $this;
	}
}
