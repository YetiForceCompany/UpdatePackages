<?php

declare(strict_types=1);
/**
 * TableRowBox class.
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
 * Class TableRowBox.
 */
class TableRowBox extends BlockBox
{
	/**
	 * @var int
	 */
	protected $rowSpan = 1;
	/**
	 * @var int
	 */
	protected $rowSpanUp = 0;

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
	 * Create column box.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return $this
	 */
	public function createColumnBox()
	{
		$style = (new \YetiForcePDF\Style\Style())
			->setDocument($this->document)
			->parseInline();
		$box = (new TableColumnBox())
			->setDocument($this->document)
			->setParent($this)
			->setStyle($style)
			->init();
		$this->appendChild($box);
		$box->getStyle()->init();

		return $box;
	}

	/**
	 * Span columns.
	 *
	 * @return $this
	 */
	public function spanColumns()
	{
		$colSpans = [];
		foreach ($this->getChildren() as $column) {
			if ($column->getColSpan() > 1) {
				$spanCount = $column->getColSpan() - 1;
				$spans = [$column];
				$currentColumn = $column;
				for ($i = 0; $i < $spanCount; ++$i) {
					$currentColumn = $currentColumn->getNext();
					$spans[] = $currentColumn;
				}
				$colSpans[] = $spans;
			}
		}
		$colSpansCount = \count($colSpans);
		foreach ($colSpans as $index => $columns) {
			$source = array_shift($columns);
			$spannedWidth = '0';
			foreach ($columns as $column) {
				if (isset($column) && null !== $column) {
					$spannedWidth = Math::add($spannedWidth, $column->getDimensions()->getWidth());
				}
			}
			$tableAuto = 'auto' === $this->getParent()->getParent()->getParent()->getStyle()->getRules('width');
			if (null !== $column) {
				$separate = 'separate' === $column->getStyle()->getRules('border-collapse');
				if ($separate && $tableAuto) {
					$spannedWidth = Math::add($spannedWidth, Math::mul((string) (\count($columns)), $column->getStyle()->getRules('border-spacing')));
				}
				if ($separate && null === $column->getNext() && !$tableAuto) {
					$spannedWidth = Math::sub($spannedWidth, $column->getStyle()->getRules('border-spacing'));
				}
				foreach ($columns as $column) {
					$column->setColSpanned($colSpansCount - $index)->setRenderable(false);
				}
				if (null === $source->getNext()) {
					$cell = $source->getFirstChild();
					$cellStyle = $cell->getStyle();
					$cellStyle->setRule('border-right-width', $cellStyle->getRules('border-left-width'));
				}
				$sourceDmns = $source->getDimensions();
				$sourceDmns->setWidth(Math::add($sourceDmns->getWidth(), $spannedWidth));
				$cell = $source->getFirstChild();
				$cell->getDimensions()->setWidth($sourceDmns->getInnerWidth());
			}
		}

		return $this;
	}

	/**
	 * Append table cell box element.
	 *
	 * @param \DOMElement                   $childDomElement
	 * @param Element                       $element
	 * @param Style                         $style
	 * @param \YetiForcePDF\Layout\BlockBox $parentBlock
	 *
	 * @return $this
	 */
	public function appendTableCellBox($childDomElement, $element, $style, $parentBlock)
	{
		$colSpan = 1;
		$style->setRule('display', 'block');
		$attributeColSpan = $childDomElement->getAttribute('colspan');
		if ($attributeColSpan) {
			$colSpan = (int) $attributeColSpan;
		}
		$rowSpan = 1;
		$attributeRowSpan = $childDomElement->getAttribute('rowspan');
		if ($attributeRowSpan) {
			$rowSpan = (int) $attributeRowSpan;
		}
		$clearStyle = (new \YetiForcePDF\Style\Style())
			->setDocument($this->document)
			->parseInline();
		$column = (new TableColumnBox())
			->setDocument($this->document)
			->setParent($this)
			->setStyle($clearStyle)
			->init();
		$column->setColSpan($colSpan)->setRowSpan($rowSpan);
		$this->appendChild($column);
		$column->getStyle()->init()->setRule('display', 'block');
		$box = (new TableCellBox())
			->setDocument($this->document)
			->setParent($column)
			->setElement($element)
			->setStyle($style)
			->init();
		$column->appendChild($box);
		$box->getStyle()->init();
		--$colSpan;
		for ($i = 0; $i < $colSpan; ++$i) {
			$clearStyle = (new \YetiForcePDF\Style\Style())
				->setDocument($this->document)
				->parseInline();
			$column = (new TableColumnBox())
				->setDocument($this->document)
				->setParent($this)
				->setStyle($clearStyle)
				->init();
			$column->setColSpan(-1);
			$this->appendChild($column);
			$column->getStyle()->init()->setRule('display', 'block');
			$spanBox = (new TableCellBox())
				->setDocument($this->document)
				->setParent($column)
				->setStyle(clone $style)
				->setElement(clone $element)
				->setSpanned(true)
				->init();
			$column->appendChild($spanBox);
		}
		$box->buildTree($box);

		return $box;
	}
}
