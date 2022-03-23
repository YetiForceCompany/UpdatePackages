<?php

declare(strict_types=1);
/**
 * TableColumnBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

/**
 * Class TableColumnBox.
 */
class TableColumnBox extends InlineBlockBox
{
	/**
	 * @var int
	 */
	protected $colSpan = 1;
	/**
	 * @var int row span
	 */
	protected $rowSpan = 1;
	/**
	 * @var int
	 */
	protected $rowSpanUp = 0;
	/**
	 * @var int
	 */
	protected $colSpanned = 0;

	/**
	 * Set row span.
	 *
	 * @param int $rowSpan
	 *
	 * @return $this
	 */
	public function setRowSpan(int $rowSpan)
	{
		$this->rowSpan = $rowSpan;
		return $this;
	}

	/**
	 * Get row span.
	 *
	 * @return int
	 */
	public function getRowSpan()
	{
		return $this->rowSpan;
	}

	/**
	 * Set row span up.
	 *
	 * @param int $rowSpan
	 * @param int $rowSpanUp
	 *
	 * @return $this
	 */
	public function setRowSpanUp(int $rowSpanUp)
	{
		$this->rowSpanUp = $rowSpanUp;
		return $this;
	}

	/**
	 * Get row span up.
	 *
	 * @return int
	 */
	public function getRowSpanUp()
	{
		return $this->rowSpanUp;
	}

	/**
	 * Get column span.
	 *
	 * @return int
	 */
	public function getColSpan()
	{
		return $this->colSpan;
	}

	/**
	 * Set column span.
	 *
	 * @param int $colSpan
	 *
	 * @return $this
	 */
	public function setColSpan(int $colSpan)
	{
		$this->colSpan = $colSpan;
		return $this;
	}

	/**
	 * Is this column spanned with previous?
	 *
	 * @return int
	 */
	public function getColSpanned()
	{
		return $this->colSpanned;
	}

	/**
	 * Set spanned.
	 *
	 * @param int $spanned - spanned columns before
	 *
	 * @return $this
	 */
	public function setColSpanned(int $spanned)
	{
		$this->colSpanned = $spanned;
		return $this;
	}

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
	 * Create cell box.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return $this
	 */
	public function createCellBox()
	{
		$style = (new \YetiForcePDF\Style\Style())
			->setDocument($this->document)
			->setContent('')
			->parseInline();
		$box = (new TableCellBox())
			->setDocument($this->document)
			->setParent($this)
			->setStyle($style)
			->init();
		$this->appendChild($box);
		$box->getStyle()->init();
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
