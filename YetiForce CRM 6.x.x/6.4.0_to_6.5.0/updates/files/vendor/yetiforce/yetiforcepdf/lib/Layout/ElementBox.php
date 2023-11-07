<?php

declare(strict_types=1);
/**
 * ElementBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Html\Element;

/**
 * Class ElementBox.
 */
class ElementBox extends Box
{
	/**
	 * @var Element
	 */
	protected $element;

	/**
	 * Get element.
	 *
	 * @return Element
	 */
	public function getElement()
	{
		return $this->element;
	}

	/**
	 * Set element.
	 *
	 * @param Element $element
	 *
	 * @return $this
	 */
	public function setElement(Element $element)
	{
		$this->element = $element;
		$element->setBox($this);

		return $this;
	}

	/**
	 * Get boxes by tag name.
	 *
	 * @param string $tagName
	 *
	 * @return array
	 */
	public function getBoxesByTagName(string $tagName)
	{
		$boxes = [];
		$allChildren = [];
		$this->getAllChildren($allChildren);
		foreach ($allChildren as $child) {
			if ($child instanceof self && $child->getElement() && $child->getElement()->getDOMElement()) {
				if (isset($child->getElement()->getDOMElement()->tagName)) {
					$elementTagName = $child->getElement()->getDOMElement()->tagName;
					if ($elementTagName && strtolower($elementTagName) === strtolower($tagName)) {
						$boxes[] = $child;
					}
				}
			}
		}

		return $boxes;
	}

	/**
	 * Fix tables - iterate through cells and insert missing one.
	 *
	 * @return $this
	 */
	public function fixTables()
	{
		$tables = $this->getBoxesByType('TableWrapperBox');
		foreach ($tables as $tableWrapperBox) {
			$tableBox = $tableWrapperBox->getFirstChild();
			$rowGroups = $tableBox->getChildren();
			foreach ($rowGroups as $rowGroup) {
				// wrap rows with row groups
				if ($rowGroup instanceof TableRowBox) {
					if (!isset($wrapRowGroup)) {
						$wrapRowGroup = $tableBox->removeChild($tableBox->createRowGroupBox());
						$tableBox->insertBefore($wrapRowGroup, $rowGroup);
					}
					$wrapRowGroup->appendChild($tableBox->removeChild($rowGroup));
				}
			}
			unset($wrapRowGroup);
			$rowGroups = $tableBox->getChildren();
			if (empty($rowGroups)) {
				$rowGroup = $tableBox->createRowGroupBox();
				$row = $rowGroup->createRowBox();
				$column = $row->createColumnBox();
				$column->createCellBox();
			} else {
				$columnsCount = 0;
				foreach ($rowGroups as $rowGroup) {
					if (!$rowGroup->hasChildren()) {
						$row = $rowGroup->createRowBox();
						$column = $row->createColumnBox();
						$column->createCellBox();
					}
					foreach ($rowGroup->getChildren() as $rowIndex => $row) {
						$columns = $row->getChildren();
						$columnsCount = max($columnsCount, \count($columns));
						foreach ($columns as $columnIndex => $column) {
							if ($column->getRowSpan() > 1) {
								$rowSpans = $column->getRowSpan();
								for ($i = 1; $i < $rowSpans; ++$i) {
									$nextRow = $rowGroup->getChildren()[$rowIndex + $i];
									$rowChildren = $nextRow->getChildren();
									$insertColumn = $nextRow->removeChild($nextRow->createColumnBox());
									if (isset($rowChildren[$columnIndex])) {
										$before = $rowChildren[$columnIndex];
										$nextRow->insertBefore($insertColumn, $before);
									} else {
										$nextRow->appendChild($insertColumn);
									}
									$insertColumn->setStyle(clone $column->getStyle());
									$insertColumn->getStyle()->setBox($insertColumn);
									$insertCell = $insertColumn->createCellBox();
									$insertCell->setStyle(clone $column->getFirstChild()->getStyle());
									$insertCell->getStyle()->setBox($insertCell);
								}
							}
						}
					}
					foreach ($rowGroup->getChildren() as $row) {
						$columns = $row->getChildren();
						$missing = $columnsCount - \count($columns);
						for ($i = 0; $i < $missing; ++$i) {
							$column = $row->createColumnBox();
							$column->createCellBox();
						}
					}
					// fix row spans
					$rowSpans = [];
					$rowSpansUp = [];
					foreach ($rowGroup->getChildren() as $row) {
						foreach ($row->getChildren() as $columnIndex => $column) {
							if ($column->getRowSpan() > 1) {
								$rowSpans[$columnIndex] = $column->getRowSpan();
								$rowSpansUp[$columnIndex] = 0;
								$column->setRowSpanUp(0);
								$row->setRowSpanUp(max($row->getRowSpanUp(), 0));
								$row->setRowSpan(max($row->getRowSpan(), $column->getRowSpan()));
							} else {
								if (isset($rowSpans[$columnIndex]) && $rowSpans[$columnIndex] > 1) {
									if ($rowSpansUp[$columnIndex] < $rowSpans[$columnIndex]) {
										++$rowSpansUp[$columnIndex];
										$column->setRowSpanUp($rowSpansUp[$columnIndex]);
										$row->setRowSpanUp(max($row->getRowSpanUp(), $rowSpansUp[$columnIndex]));
										$row->setRowSpan(max($row->getRowSpan(), $column->getRowSpan()));
									} else {
										$rowSpansUp[$columnIndex] = 0;
										$rowSpans[$columnIndex] = 1;
									}
								}
							}
						}
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Span all rows.
	 *
	 * @return $this
	 */
	public function spanAllRows()
	{
		$tablesBoxes = $this->getBoxesByType('TableBox');
		foreach ($tablesBoxes as $tableBox) {
			$tableBox->spanRows();
		}

		return $this;
	}

	/**
	 * Build tree.
	 *
	 * @param $parentBlock
	 *
	 * @return $this
	 */
	public function buildTree($parentBlock = null)
	{
		if ($this->getElement()) {
			$domElement = $this->getElement()->getDOMElement();
		} else {
			// tablebox doesn't have element so we can get it from table wrapper (parent box)
			$domElement = $this->getParent()->getElement()->getDOMElement();
		}
		if ($domElement->hasChildNodes()) {
			foreach ($domElement->childNodes as $childDomElement) {
				if ($childDomElement instanceof \DOMComment) {
					continue;
				}

				$element = (new Element())
					->setDocument($this->document)
					->setDOMElement($childDomElement)
					->init();
				$style = (new \YetiForcePDF\Style\Style())
					->setDocument($this->document)
					->setElement($element);
				if ($childDomElement instanceof \DOMElement) {
					if ($childDomElement->hasAttribute('style')) {
						// for now only basic style is used - from current element only (with defaults)
						$style->setContent($childDomElement->getAttribute('style'));
					} elseif ('style' === $childDomElement->nodeName) {
						$style->parseCss($childDomElement->nodeValue);
					}
					$element->attachClasses();
				}
				$style = $style->parseInline();
				$display = $style->getRules('display');
				switch ($display) {
					case 'block':
						if ($childDomElement->hasAttribute('data-header')) {
							$this->appendHeaderBox($childDomElement, $element, $style, $parentBlock);
						} elseif ($childDomElement->hasAttribute('data-footer')) {
							$this->appendFooterBox($childDomElement, $element, $style, $parentBlock);
						} elseif ($childDomElement->hasAttribute('data-watermark')) {
							$this->appendWatermarkBox($childDomElement, $element, $style, $parentBlock);
						} elseif ($childDomElement->hasAttribute('data-font')) {
							$this->appendFontBox($childDomElement, $element, $style, $parentBlock);
						} elseif ($childDomElement->hasAttribute('data-barcode')) {
							$this->appendBarcodeBox($childDomElement, $element, $style, $parentBlock);
						} else {
							$this->appendBlockBox($childDomElement, $element, $style, $parentBlock);
						}

						break;
					case 'table':
						$tableWrapper = $this->appendTableWrapperBox($childDomElement, $element, $style, $parentBlock);
						$tableWrapper->appendTableBox($childDomElement, $element, $style, $parentBlock);

						break;
					case 'table-row-group':
					case 'table-header-group':
					case 'table-footer-group':
						$this->appendTableRowGroupBox($childDomElement, $element, $style, $parentBlock, $display);

						break;
					case 'table-row':
						$this->appendTableRowBox($childDomElement, $element, $style, $parentBlock);

						break;
					case 'table-cell':
						$this->appendTableCellBox($childDomElement, $element, $style, $parentBlock);

						break;
					case 'inline':
						$inline = $this->appendInlineBox($childDomElement, $element, $style, $parentBlock);
						if (isset($inline) && $childDomElement instanceof \DOMText) {
							$inline->setAnonymous(true)->appendText($childDomElement, null, null, $parentBlock);
						}

						break;
					case 'inline-block':
						$this->appendInlineBlockBox($childDomElement, $element, $style, $parentBlock);

						break;
						case 'none':
							if ('style' === $childDomElement->nodeName) {
								$this->appendStyleBox($childDomElement, $element, $style, $parentBlock);
							}

						break;
				}
			}
		}

		return $this;
	}
}
