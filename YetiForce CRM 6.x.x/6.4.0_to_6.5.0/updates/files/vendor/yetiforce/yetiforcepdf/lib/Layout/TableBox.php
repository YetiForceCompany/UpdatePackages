<?php

declare(strict_types=1);
/**
 * TableBox class.
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
 * Class TableBox.
 */
class TableBox extends BlockBox
{
	/**
	 * @var array minimal widths
	 */
	protected $minWidths = [];
	/**
	 * @var array preferred widths
	 */
	protected $preferredWidths = [];
	/**
	 * @var array maximal widths
	 */
	protected $contentWidths = [];
	/**
	 * @var string total min width
	 */
	protected $minWidth = '0';
	/**
	 * @var string total preferred width
	 */
	protected $preferredWidth = '0';
	/**
	 * @var string total max width
	 */
	protected $contentWidth = '0';
	/**
	 * @var array percentages for each percentage column
	 */
	protected $percentages = [];
	/**
	 * @var string cell spacing total width
	 */
	protected $cellSpacingWidth = '0';
	/**
	 * @var string total border width
	 */
	protected $borderWidth = '0';
	/**
	 * @var array percentage columns
	 */
	protected $percentColumns = [];
	/**
	 * @var array pixel columns
	 */
	protected $pixelColumns = [];
	/**
	 * @var array auto width columns
	 */
	protected $autoColumns = [];
	/**
	 * @var array saving state
	 */
	protected $beforeWidths = [];
	/**
	 * @var array rows
	 */
	protected $rows = [];
	/**
	 * @var TableRowGroupBox|null
	 */
	protected $anonymousRowGroup;
	/**
	 * Parent width cache.
	 *
	 * @var string
	 */
	protected $parentWidth = '0';

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
	 * Create row group inside table
	 * return TableRowGroupBox.
	 */
	public function createRowGroupBox()
	{
		$style = (new \YetiForcePDF\Style\Style())
			->setDocument($this->document)
			->setContent('')
			->parseInline();
		$box = (new TableRowGroupBox())
			->setDocument($this->document)
			->setParent($this)
			->setStyle($style)
			->init();
		$this->appendChild($box);
		$box->getStyle()->init();

		return $box;
	}

	/**
	 * Append table row group box element.
	 *
	 * @param \DOMNode                      $childDomElement
	 * @param Element                       $element
	 * @param Style                         $style
	 * @param \YetiForcePDF\Layout\BlockBox $parentBlock
	 * @param string                        $display
	 *
	 * @return $this
	 */
	public function appendTableRowGroupBox($childDomElement, $element, $style, $parentBlock, string $display)
	{
		$cleanStyle = (new \YetiForcePDF\Style\Style())
			->setDocument($this->document)
			->setContent('')
			->parseInline();
		$rowGroupClass = 'YetiForcePDF\\Layout\\TableRowGroupBox';
		switch ($display) {
			case 'table-header-group':
				$rowGroupClass = 'YetiForcePDF\\Layout\\TableHeaderGroupBox';

				break;
			case 'table-footer-group':
				$rowGroupClass = 'YetiForcePDF\\Layout\\TableFooterGroupBox';

				break;
		}
		$box = (new $rowGroupClass())
			->setDocument($this->document)
			->setParent($this)
			->setElement($element)
			->setStyle($cleanStyle)
			->init();
		$this->appendChild($box);
		$box->getStyle()->init()->setRule('display', 'block');
		$box->buildTree($box);

		return $box;
	}

	/**
	 * Append table row group box element.
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
		$box->getStyle()->init()->setRule('display', 'block');
		$box->buildTree($box);

		return $box;
	}

	/**
	 * Get all rows from all row groups.
	 *
	 * @return array of LineBoxes and TableRowBoxes
	 */
	public function getRows()
	{
		$rows = [];
		foreach ($this->getChildren() as $rowGroup) {
			if ($rowGroup instanceof TableRowGroupBox) {
				foreach ($rowGroup->getChildren() as $row) {
					$rows[] = $row;
				}
			}
		}

		return $rows;
	}

	/**
	 * Get columns - get table cells segregated by columns.
	 *
	 * @return array
	 */
	public function getColumns()
	{
		$columns = [];
		foreach ($this->getChildren() as $rowGroup) {
			foreach ($rowGroup->getChildren() as $row) {
				foreach ($row->getChildren() as $columnIndex => $column) {
					if ($column instanceof TableColumnBox) {
						$columns[$columnIndex][] = $column;
					}
				}
			}
		}

		return $columns;
	}

	/**
	 * Get cells.
	 *
	 * @return array
	 */
	public function getCells()
	{
		$cells = [];
		foreach ($this->getChildren() as $rowGroup) {
			foreach ($rowGroup->getChildren() as $row) {
				foreach ($row->getChildren() as $column) {
					$cells[] = $column->getFirstChild();
				}
			}
		}

		return $cells;
	}

	/**
	 * Get minimal and maximal column widths.
	 *
	 * @param string $availableSpace
	 *
	 * @return array
	 */
	public function setUpWidths(string $availableSpace)
	{
		foreach ($this->getChildren() as $rowGroup) {
			foreach ($rowGroup->getChildren() as $row) {
				if ($columns = $row->getChildren()) {
					foreach ($columns as $columnIndex => $column) {
						$cell = $column->getFirstChild();
						$cellStyle = $cell->getStyle();
						$columnInnerWidth = $cell->getDimensions()->getMaxWidth();
						$styleWidth = $column->getStyle()->getRules('width');
						$this->contentWidths[$columnIndex] = Math::max($this->contentWidths[$columnIndex] ?? '0', $columnInnerWidth);
						$minColumnWidth = $cell->getDimensions()->getMinWidth();
						if ($column->getColSpan() > 1) {
							$minColumnWidth = Math::div($minColumnWidth, (string) $column->getColSpan());
						}
						$this->minWidths[$columnIndex] = Math::max($this->minWidths[$columnIndex] ?? '0', $minColumnWidth);
						if ('auto' !== $styleWidth && false === strpos($styleWidth, '%')) {
							if ($column->getColSpan() > 1) {
								$styleWidth = Math::div($styleWidth, (string) $column->getColSpan());
							}
							$preferred = Math::max($styleWidth, $minColumnWidth);
							$this->minWidths[$columnIndex] = $preferred;
						} elseif (strpos($styleWidth, '%') > 0) {
							$preferred = Math::max($this->preferredWidths[$columnIndex] ?? '0', $columnInnerWidth);
							$this->percentages[$columnIndex] = Math::max($this->percentages[$columnIndex] ?? '0', trim($styleWidth, '%'));
						} else {
							$preferred = Math::max($this->preferredWidths[$columnIndex] ?? '0', $columnInnerWidth);
						}
						$this->preferredWidths[$columnIndex] = $preferred;
					}
					$this->borderWidth = Math::add($this->borderWidth, $cellStyle->getHorizontalBordersWidth());
					$this->minWidth = Math::add($this->minWidth, $this->minWidths[$columnIndex]);
					$this->contentWidth = Math::add($this->contentWidth, $this->contentWidths[$columnIndex]);
					$this->preferredWidth = Math::add($this->preferredWidth, $this->preferredWidths[$columnIndex]);
				}
			}
		}
		if ('collapse' !== $this->getParent()->getStyle()->getRules('border-collapse')) {
			$spacing = $this->getStyle()->getRules('border-spacing');
			$this->cellSpacingWidth = Math::mul((string) (\count($columns) + 1), $spacing);
		}
	}

	/**
	 * Set up sizing types for columns.
	 *
	 * @return $this
	 */
	protected function setUpSizingTypes()
	{
		$columnSizingTypes = [];
		// rowGroup -> row -> columns
		$columns = $this->getFirstChild()->getFirstChild()->getChildren();
		foreach ($columns as $columnIndex => $column) {
			$columnStyleWidth = $column->getStyle()->getRules('width');
			if (strpos($columnStyleWidth, '%') > 0) {
				$columnSizingTypes[$columnIndex] = 'percent';
			} elseif ('auto' !== $columnStyleWidth) {
				$columnSizingTypes[$columnIndex] = 'pixel';
			} else {
				$columnSizingTypes[$columnIndex] = 'auto';
			}
		}
		$this->percentColumns = [];
		$this->pixelColumns = [];
		$this->autoColumns = [];
		foreach ($this->getChildren() as $rowGroup) {
			foreach ($rowGroup->getChildren() as $row) {
				foreach ($row->getChildren() as $columnIndex => $column) {
					if (isset($columnSizingTypes[$columnIndex]) && 'percent' === $columnSizingTypes[$columnIndex]) {
						$this->percentColumns[$columnIndex][] = $column;
					} elseif (isset($columnSizingTypes[$columnIndex]) && 'pixel' === $columnSizingTypes[$columnIndex]) {
						$this->pixelColumns[$columnIndex][] = $column;
					} else {
						$this->autoColumns[$columnIndex][] = $column;
					}
				}
			}
		}
		unset($columnSizingTypes);

		return $this;
	}

	/**
	 * Set rows width.
	 *
	 * @param string $width
	 *
	 * @return $this
	 */
	protected function setRowsWidth()
	{
		$width = '0';
		if (empty($this->rows)) {
			return $this;
		}
		foreach ($this->rows[0]->getChildren() as $column) {
			$width = Math::add($width, $column->getDimensions()->getWidth());
		}
		foreach ($this->rows as $row) {
			$rowStyle = $row->getStyle();
			$rowWidth = $width;
			if ('separate' === $this->getStyle()->getRules('border-collapse')) {
				$rowSpacing = Math::add($rowStyle->getHorizontalPaddingsWidth(), $rowStyle->getHorizontalBordersWidth());
				$rowWidth = Math::add($rowWidth, $rowSpacing);
			}
			$row->getDimensions()->setWidth($rowWidth);
		}
		$row->getParent()->getDimensions()->setWidth($rowWidth);
		$style = $this->getStyle();
		$spacing = Math::add($style->getHorizontalPaddingsWidth(), $style->getHorizontalBordersWidth());
		$rowWidth = Math::add($rowWidth, $spacing);
		$this->getDimensions()->setWidth($rowWidth);

		return $this;
	}

	/**
	 * Minimal content guess.
	 *
	 * @param array $rows
	 *
	 * @return $this
	 */
	protected function minContentGuess(array $rows)
	{
		foreach ($rows as $row) {
			foreach ($row->getChildren() as $columnIndex => $column) {
				$this->setColumnWidth($column, $this->minWidths[$columnIndex]);
			}
		}
		$this->setRowsWidth();

		return $this;
	}

	/**
	 * Add to preferred others (add left space to preferred width of auto/pixel columns).
	 *
	 * @param string $leftSpace
	 *
	 * @return string
	 */
	protected function addToPreferredOthers(string $leftSpace)
	{
		$autoNeededTotal = '0';
		$pixelNeededTotal = '0';
		$autoNeeded = [];
		$pixelNeeded = [];
		foreach ($this->autoColumns as $columnIndex => $columns) {
			$colDmns = $columns[0]->getDimensions();
			$colWidth = $colDmns->getInnerWidth();
			if (Math::comp($this->preferredWidths[$columnIndex], $colWidth) > 0) {
				$autoNeeded[$columnIndex] = Math::sub($this->preferredWidths[$columnIndex], $colWidth);
				$autoNeededTotal = Math::add($autoNeededTotal, $autoNeeded[$columnIndex]);
			}
		}
		foreach ($this->pixelColumns as $columnIndex => $columns) {
			$colDmns = $columns[0]->getDimensions();
			$colWidth = $colDmns->getInnerWidth();
			if (Math::comp($this->preferredWidths[$columnIndex], $colWidth) > 0) {
				$pixelNeeded[$columnIndex] = Math::sub($this->preferredWidths[$columnIndex], $colWidth);
				$pixelNeededTotal = Math::add($pixelNeededTotal, $pixelNeeded[$columnIndex]);
			}
		}
		// ok, we know where we need to add extra space
		$totalNeeded = Math::add($autoNeededTotal, $pixelNeededTotal);
		$totalToAdd = Math::min($leftSpace, $totalNeeded);
		// we know how much we can distribute
		Math::setAccurate(true);
		$autoTotalRatio = Math::div($autoNeededTotal, $totalNeeded);
		$addToAutoTotal = Math::mul($autoTotalRatio, $totalToAdd);
		Math::setAccurate(false);
		$addToPixelTotal = Math::sub($totalToAdd, $addToAutoTotal);
		// we know how much space we can add to each column type (auto and pixel)
		// now we must distribute this space according to concrete column needs
		foreach ($this->autoColumns as $columnIndex => $columns) {
			if (isset($autoNeeded[$columnIndex])) {
				Math::setAccurate(true);
				$neededRatio = Math::div($autoNeeded[$columnIndex], $autoNeededTotal);
				$add = Math::mul($neededRatio, $addToAutoTotal);
				Math::setAccurate(false);
				$columnWidth = Math::add($columns[0]->getDimensions()->getWidth(), $add);
				foreach ($columns as $column) {
					$colDmns = $column->getDimensions();
					$colDmns->setWidth($columnWidth);
					$column->getFirstChild()->getDimensions()->setWidth($colDmns->getInnerWidth());
				}
			}
		}
		foreach ($this->pixelColumns as $columnIndex => $columns) {
			if (isset($pixelNeeded[$columnIndex])) {
				Math::setAccurate(true);
				$neededRatio = Math::div($pixelNeeded[$columnIndex], $pixelNeededTotal);
				$add = Math::mul($neededRatio, $addToPixelTotal);
				Math::setAccurate(false);
				$columnWidth = Math::add($columns[0]->getDimensions()->getWidth(), $add);
				foreach ($columns as $column) {
					$colDmns = $column->getDimensions();
					$colDmns->setWidth($columnWidth);
					$column->getFirstChild()->getDimensions()->setWidth($colDmns->getInnerWidth());
				}
			}
		}

		return Math::sub($leftSpace, $totalToAdd);
	}

	/**
	 * Get current others width (auto, pixel columns).
	 *
	 * @return string
	 */
	protected function getCurrentOthersWidth()
	{
		$currentOthersWidth = '0';
		foreach ($this->autoColumns as $columns) {
			$currentOthersWidth = Math::add($currentOthersWidth, $columns[0]->getDimensions()->getInnerWidth());
		}
		foreach ($this->pixelColumns as $columns) {
			$currentOthersWidth = Math::add($currentOthersWidth, $columns[0]->getDimensions()->getInnerWidth());
		}

		return $currentOthersWidth;
	}

	/**
	 * Get total percentage.
	 *
	 * @return string
	 */
	protected function getTotalPercentage()
	{
		$totalPercentageSpecified = '0';
		foreach ($this->percentColumns as $columnIndex => $columns) {
			$totalPercentageSpecified = Math::add($totalPercentageSpecified, $this->percentages[$columnIndex]);
		}

		return $totalPercentageSpecified;
	}

	/**
	 * Get total percentages width.
	 *
	 * @return string
	 */
	protected function getTotalPercentageWidth()
	{
		$totalPercentageColumnsWidth = '0';
		foreach ($this->percentColumns as $columns) {
			$totalPercentageColumnsWidth = Math::add($totalPercentageColumnsWidth, $columns[0]->getDimensions()->getInnerWidth());
		}

		return $totalPercentageColumnsWidth;
	}

	/**
	 * Expand percents to min width.
	 *
	 * @param string $availableSpace
	 *
	 * @return $this
	 */
	protected function expandPercentsToMin(string $availableSpace)
	{
		$totalPercentageSpecified = $this->getTotalPercentage();
		$maxPercentRatio = '0';
		$maxPercentRatioIndex = 0;
		$ratioPercent = '0';
		foreach ($this->percentages as $columnIndex => $percent) {
			Math::setAccurate(true);
			$ratio = Math::div($this->minWidths[$columnIndex], $percent);
			Math::setAccurate(false);
			if (Math::comp($ratio, $maxPercentRatio) > 0) {
				$maxPercentRatio = $ratio;
				$maxPercentRatioIndex = $columnIndex;
				$ratioPercent = $percent;
			}
		}
		$minWidth = $this->minWidths[$maxPercentRatioIndex];
		// lowerPercent = minWidth
		$onePercent = Math::div($minWidth, $ratioPercent);
		// we have one percent width, we must apply this to all percentages and other columns
		$currentPercentsWidth = '0';
		foreach ($this->percentColumns as $columnIndex => $columns) {
			$columnWidth = Math::mul($this->percentages[$columnIndex], $onePercent);
			foreach ($columns as $column) {
				$columnStyle = $column->getStyle();
				$column->getDimensions()->setWidth(Math::add($columnWidth, $columnStyle->getHorizontalPaddingsWidth()));
				$column->getFirstChild()->getDimensions()->setWidth($columnWidth);
			}
			$this->minWidths[$columnIndex] = $columnWidth;
			$currentPercentsWidth = Math::add($currentPercentsWidth, $column->getDimensions()->getInnerWidth());
		}
		// percentage columns are satisfied, other columns must fulfill percentages
		$otherPercent = Math::sub('100', $totalPercentageSpecified);
		$othersShouldHaveWidth = Math::mul($otherPercent, $onePercent);
		if (Math::comp(Math::add($othersShouldHaveWidth, $currentPercentsWidth), $availableSpace) > 0) {
			$othersShouldHaveWidth = Math::sub($availableSpace, $currentPercentsWidth);
		}
		$currentOthersWidth = $this->getCurrentOthersWidth();
		$leftSpace = Math::sub($othersShouldHaveWidth, $currentOthersWidth);
		$this->addToOthers($leftSpace);

		return $this;
	}

	/**
	 * Apply percentage dimensions.
	 *
	 * @param string $availableSpace
	 *
	 * @return $this
	 */
	protected function applyPercentage(string $availableSpace)
	{
		$currentRowsWidth = '0';
		if ('auto' === $this->getParent()->getStyle()->getRules('width')) {
			foreach ($this->getRows()[0]->getChildren() as $columnIndex => $column) {
				$currentRowsWidth = Math::add($currentRowsWidth, $column->getDimensions()->getInnerWidth());
			}
		} else {
			$currentRowsWidth = $this->getParent()->getDimensions()->getInnerWidth();
			if ('separate' === $this->getStyle()->getRules('border-collapse')) {
				$rowStyle = $this->getRows()[0]->getStyle();
				$spacing = Math::add($rowStyle->getHorizontalPaddingsWidth(), $rowStyle->getHorizontalBordersWidth());
				$currentRowsWidth = Math::sub($currentRowsWidth, $spacing);
			}
		}
		$mustExpand = false;
		foreach ($this->percentColumns as $columnIndex => $columns) {
			$columnWidth = Math::percent($this->percentages[$columnIndex], $currentRowsWidth);
			if (Math::comp($this->minWidths[$columnIndex], $columnWidth) > 0) {
				// we need to expand proportionally
				$mustExpand = true;

				break;
			}
		}
		if ($mustExpand) {
			$this->expandPercentsToMin($availableSpace);
		} else {
			// everything is ok we can resize percentages
			$percentsWidth = '0';
			foreach ($this->percentColumns as $columnIndex => $columns) {
				$columnWidth = Math::percent($this->percentages[$columnIndex], $currentRowsWidth);
				$percentsWidth = Math::add($percentsWidth, $columnWidth);
				$padding = $columns[0]->getStyle()->getHorizontalPaddingsWidth();
				$columnWidth = Math::sub($columnWidth, $padding);
				foreach ($columns as $column) {
					$this->setColumnWidth($column, $columnWidth);
				}
			}
			$totalPercentage = $this->getTotalPercentage();
			if (0 !== Math::comp($totalPercentage, '100') && 0 === Math::comp($this->getCurrentOthersWidth(), '0')) {
				// we have some space available
				$leftSpace = Math::sub($availableSpace, $percentsWidth);
				$add = Math::div($leftSpace, (string) \count($this->percentColumns));
				foreach ($this->percentColumns as $columnIndex => $columns) {
					foreach ($columns as $column) {
						$columnWidth = Math::add($column->getDimensions()->getWidth(), $add);
						$column->getDimensions()->setWidth($columnWidth);
						$column->getFirstChild()->getDimensions()->setWidth($column->getDimensions()->getInnerWidth());
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Save current columns width state.
	 *
	 * @param array $rows
	 *
	 * @return $this
	 */
	protected function saveState(array $rows)
	{
		$this->beforeWidths = [];
		foreach ($rows[0]->getChildren() as $columnIndex => $column) {
			$this->beforeWidths[$columnIndex] = $column->getDimensions()->getWidth();
		}

		return $this;
	}

	/**
	 * Minimal content percentage guess.
	 *
	 * @param array  $rows
	 * @param string $availableSpace
	 *
	 * @return $this
	 */
	protected function minContentPercentageGuess(array $rows, string $availableSpace)
	{
		$this->saveState($rows);
		$this->applyPercentage($availableSpace);
		$this->setRowsWidth();

		return $this;
	}

	/**
	 * Minimal content specified guess.
	 *
	 * @param array  $rows
	 * @param string $availableSpace
	 *
	 * @return $this
	 */
	protected function minContentSpecifiedGuess(array $rows, string $availableSpace)
	{
		$this->saveState($rows);
		$leftWidth = '0';
		foreach ($this->pixelColumns as $columnIndex => $columns) {
			foreach ($columns as $column) {
				$this->setColumnWidth($column, $this->preferredWidths[$columnIndex]);
			}
			$leftWidth = Math::add($leftWidth, $column->getDimensions()->getWidth());
		}
		foreach ($this->autoColumns as $columnIndex => $columns) {
			$leftWidth = Math::add($leftWidth, $columns[0]->getDimensions()->getWidth());
		}

		$this->applyPercentage($availableSpace);
		$this->setRowsWidth();

		return $this;
	}

	/**
	 * Set column width.
	 *
	 * @param $column
	 * @param string $width
	 */
	protected function setColumnWidth($column, string $width)
	{
		$columnStyle = $column->getStyle();
		$cell = $column->getFirstChild();
		$width = Math::add($width, $columnStyle->getHorizontalPaddingsWidth());
		$column->getDimensions()->setWidth($width);
		$cell->getDimensions()->setWidth($column->getDimensions()->getInnerWidth());
	}

	/**
	 * Maximal content guess.
	 *
	 * @param array  $rows
	 * @param string $availableSpace
	 *
	 * @return $this
	 */
	protected function maxContentGuess(array $rows, string $availableSpace)
	{
		$this->saveState($rows);
		foreach ($this->autoColumns as $columnIndex => $columns) {
			foreach ($columns as $column) {
				$this->setColumnWidth($column, $this->contentWidths[$columnIndex]);
			}
		}
		$this->applyPercentage($availableSpace);
		$this->setRowsWidth();

		return $this;
	}

	/**
	 * Span rows.
	 *
	 * @return $this
	 */
	public function spanRows()
	{
		$toRemove = [];
		foreach ($this->getChildren() as $rowGroup) {
			foreach ($rowGroup->getChildren() as $rowIndex => $row) {
				foreach ($row->getChildren() as $columnIndex => $column) {
					if ($column->getRowSpan() > 1) {
						$rowSpans = $column->getRowSpan();
						$spanHeight = '0';
						for ($i = 1; $i < $rowSpans; ++$i) {
							$spanColumn = $row->getParent()->getChildren()[$rowIndex + $i]->getChildren()[$columnIndex];
							$spanHeight = Math::add($spanHeight, $spanColumn->getDimensions()->getHeight());
							$toRemove[] = $spanColumn;
						}
						$colDmns = $column->getDimensions();
						$colDmns->setHeight(Math::add($colDmns->getHeight(), $spanHeight));
						$cell = $column->getFirstChild();
						$colInnerHeight = $colDmns->getInnerHeight();
						$cell->getDimensions()->setHeight($colInnerHeight);
						$cellHeight = '0';
						foreach ($cell->getChildren() as $cellChild) {
							$cellHeight = Math::add($cellHeight, $cellChild->getDimensions()->getOuterHeight());
						}
						$columnStyle = $column->getStyle();
						$cellStyle = $column->getFirstChild()->getStyle();
						if ('collapse' === $columnStyle->getRules('border-collapse') && $rowIndex + $i === \count($this->getChildren())) {
							// TODO: store original border widths inside cell
							$cellStyle->setRule('border-bottom-width', $cellStyle->getRules('border-top-width'));
						}
						$toDisposition = Math::sub($colInnerHeight, $cellHeight);
						switch ($cellStyle->getRules('vertical-align')) {
							case 'baseline':
							case 'middle':
								$padding = Math::div($toDisposition, '2');
								$cellStyle->setRule('padding-top', $padding);
								$cellStyle->setRule('padding-bottom', $padding);
								break;
							case 'top':
								$cellStyle->setRule('padding-bottom', $toDisposition);
								break;
							case 'bottom':
								$cellStyle->setRule('padding-top', $toDisposition);
								break;
						}
						$cell->measureWidth();
						$cell->measureHeight();
						$cell->measureOffset();
						$cell->alignText();
						$cell->measurePosition();
					}
				}
			}
		}
		foreach ($toRemove as $remove) {
			$remove->setDisplayable(false)->setRenderable(false)->setForMeasurement(false);
		}

		return $this;
	}

	/**
	 * Finish table width calculations.
	 *
	 * @return $this
	 */
	protected function finish()
	{
		foreach ($this->rows as $row) {
			$row->spanColumns();
		}
		$style = $this->getStyle();
		$width = $this->rows[0]->getDimensions()->getWidth();
		$width = Math::add($width, $style->getHorizontalPaddingsWidth(), $style->getHorizontalBordersWidth());
		$this->getDimensions()->setWidth($width);
		$parent = $this->getParent();
		$parentStyle = $parent->getStyle();
		if ('auto' === $parentStyle->getRules('width')) {
			$parentSpacing = Math::add($parentStyle->getHorizontalBordersWidth(), $parentStyle->getHorizontalPaddingsWidth());
			$width = Math::add($width, $parentSpacing);
			$parent->getDimensions()->setWidth($width);
		} else {
			$parent->applyStyleWidth();
		}
		foreach ($this->getCells() as $cell) {
			$cell->measureWidth();
		}

		return $this;
	}

	/**
	 * Check whenever table fill fit to available space.
	 *
	 * @param string $availableSpace
	 *
	 * @return bool
	 */
	protected function willFit(string $availableSpace)
	{
		$row = $this->rows[0];
		$width = $row->getDimensions()->getWidth();
		$width = Math::add($width, $this->getStyle()->getHorizontalBordersWidth(), $this->getStyle()->getHorizontalPaddingsWidth());

		return Math::comp($availableSpace, $width) >= 0;
	}

	/**
	 * Get row inner width.
	 *
	 * @return string
	 */
	protected function getRowInnerWidth()
	{
		$width = '0';
		foreach ($this->rows[0]->getChildren() as $column) {
			$width = Math::add($width, $column->getDimensions()->getWidth());
		}

		return $width;
	}

	/**
	 * Get auto columns max width.
	 *
	 * @return string
	 */
	protected function getAutoColumnsMaxWidth()
	{
		$autoColumnsMaxWidth = '0';
		foreach ($this->autoColumns as $columnIndex => $columns) {
			$autoColumnsMaxWidth = Math::add($autoColumnsMaxWidth, $this->contentWidths[$columnIndex]);
		}

		return $autoColumnsMaxWidth;
	}

	/**
	 * Get auto columns min width.
	 *
	 * @return string
	 */
	protected function getAutoColumnsMinWidth()
	{
		$autoColumnsMinWidth = '0';
		foreach ($this->autoColumns as $columnIndex => $columns) {
			$autoColumnsMinWidth = Math::add($autoColumnsMinWidth, $this->minWidths[$columnIndex]);
		}

		return $autoColumnsMinWidth;
	}

	/**
	 * Get auto columns width.
	 *
	 * @return string
	 */
	protected function getAutoColumnsWidth()
	{
		$autoColumnsWidth = '0';
		foreach ($this->autoColumns as $columns) {
			$autoColumnsWidth = Math::add($autoColumnsWidth, $columns[0]->getDimensions()->getInnerWidth());
		}

		return $autoColumnsWidth;
	}

	/**
	 * Shrink to fit.
	 *
	 * @param string $availableSpace
	 * @param int    $step
	 *
	 * @return TableBox
	 */
	protected function shrinkToFit(string $availableSpace, int $step)
	{
		$parentStyle = $this->getParent()->getStyle();
		$parentSpacing = Math::add($parentStyle->getHorizontalBordersWidth(), $parentStyle->getHorizontalPaddingsWidth());
		$availableSpace = Math::sub($availableSpace, $this->cellSpacingWidth, $parentSpacing);
		$currentWidth = Math::sub($this->getRowInnerWidth(), $this->cellSpacingWidth);
		$toRemoveTotal = Math::sub($currentWidth, $availableSpace);
		$totalPercentages = '0';
		foreach ($this->percentages as $percentage) {
			$totalPercentages = Math::add($totalPercentages, $percentage);
		}
		$percentagesFullWidth = Math::percent($totalPercentages, $availableSpace);
		$eachPercentagesWidth = [];
		foreach ($this->percentages as $columnIndex => $percent) {
			$eachPercentagesWidth[$columnIndex] = Math::percent($percent, $availableSpace);
		}
		$nonPercentageSpace = Math::sub($availableSpace, $percentagesFullWidth);
		$autoColumnsMinWidth = $this->getAutoColumnsMinWidth();
		$autoColumnsMaxWidth = $this->getAutoColumnsMaxWidth();
		$totalPixelWidth = '0';
		foreach ($this->pixelColumns as $columnIndex => $columns) {
			$totalPixelWidth = Math::add($totalPixelWidth, $this->preferredWidths[$columnIndex]);
		}
		switch ($step) {
			case 0:
				// minimal stays minimal - decreasing percents
				$rowWidth = '0';
				foreach ($this->percentColumns as $columnIndex => $columns) {
					$totalPercent = Math::div($this->percentages[$columnIndex], $totalPercentages);
					$toRemove = Math::percent($totalPercent, $toRemoveTotal);
					foreach ($columns as $column) {
						$cDimensions = $column->getDimensions();
						$cDimensions->setWidth(Math::sub($cDimensions->getWidth(), $toRemove));
						$column->getFirstChild()->getDimensions()->setWidth($cDimensions->getInnerWidth());
					}
					$rowWidth = Math::add($rowWidth, $cDimensions->getWidth());
				}
				$this->setRowsWidth();

				break;
			case 1:
				// minimal stays minimal, decreasing pixels
				$toPixelDisposition = Math::sub($nonPercentageSpace, $autoColumnsMinWidth);
				foreach ($this->pixelColumns as $columnIndex => $columns) {
					Math::setAccurate(true);
					$ratio = Math::div($this->preferredWidths[$columnIndex], $totalPixelWidth);
					$columnWidth = Math::mul($toPixelDisposition, $ratio);
					Math::setAccurate(false);
					foreach ($columns as $column) {
						$columnDimensions = $column->getDimensions();
						$columnDimensions->setWidth(Math::add($columnWidth, $column->getStyle()->getHorizontalPaddingsWidth()));
						$column->getFirstChild()->getDimensions()->setWidth($columnDimensions->getInnerWidth());
					}
				}
				foreach ($this->percentColumns as $columnIndex => $columns) {
					foreach ($columns as $column) {
						$columnDimensions = $column->getDimensions();
						$columnDimensions->setWidth(Math::add($eachPercentagesWidth[$columnIndex], $column->getStyle()->getHorizontalPaddingsWidth()));
						$column->getFirstChild()->getDimensions()->setWidth($columnDimensions->getInnerWidth());
					}
				}
				$this->setRowsWidth();

				break;
			case 2:
				// minimal stays minimal, pixels stays untouched, auto columns decreasing
				$toAutoDisposition = Math::sub($nonPercentageSpace, $totalPixelWidth);
				$nonMinWidthColumns = [];
				foreach ($this->autoColumns as $columnIndex => $columns) {
					Math::setAccurate(true);
					$ratio = Math::div($this->contentWidths[$columnIndex], $autoColumnsMaxWidth);
					$columnWidth = Math::mul($toAutoDisposition, $ratio);
					Math::setAccurate(false);
					if (Math::comp($this->minWidths[$columnIndex], $columnWidth) > 0) {
						$toAutoDisposition = Math::sub($toAutoDisposition, Math::sub($this->minWidths[$columnIndex], $columnWidth));
						$columnWidth = $this->minWidths[$columnIndex];
						foreach ($columns as $column) {
							$columnDimensions = $column->getDimensions();
							$columnDimensions->setWidth(Math::add($columnWidth, $column->getStyle()->getHorizontalPaddingsWidth()));
							$column->getFirstChild()->getDimensions()->setWidth($columnDimensions->getInnerWidth());
						}
					} else {
						$nonMinWidthColumns[$columnIndex] = $columns;
					}
				}
				foreach ($nonMinWidthColumns as $columnIndex => $columns) {
					Math::setAccurate(true);
					$ratio = Math::div($this->contentWidths[$columnIndex], $autoColumnsMaxWidth);
					$columnWidth = Math::mul($toAutoDisposition, $ratio);
					Math::setAccurate(false);
					foreach ($columns as $column) {
						$columnDimensions = $column->getDimensions();
						$columnDimensions->setWidth(Math::add($columnWidth, $column->getStyle()->getHorizontalPaddingsWidth()));
						$column->getFirstChild()->getDimensions()->setWidth($columnDimensions->getInnerWidth());
					}
				}
				foreach ($this->percentColumns as $columnIndex => $columns) {
					foreach ($columns as $column) {
						$columnDimensions = $column->getDimensions();
						$columnDimensions->setWidth(Math::add($eachPercentagesWidth[$columnIndex], $column->getStyle()->getHorizontalPaddingsWidth()));
						$column->getFirstChild()->getDimensions()->setWidth($columnDimensions->getInnerWidth());
					}
				}
				$this->setRowsWidth();

				break;
		}

		return $this->finish();
	}

	/**
	 * Add to others (left space to auto/pixel columns).
	 *
	 * @param string $leftSpace
	 * @param bool   $withPreferred
	 *
	 * @return $this
	 */
	protected function addToOthers(string $leftSpace, bool $withPreferred = false)
	{
		// first of all try to redistribute space to columns that need it most (width is under preferred)
		// left space is the space that we can add to other column types that needs extra space to preferred width
		if ($withPreferred) {
			$leftSpace = $this->addToPreferredOthers($leftSpace);
		}

		// ok, we've redistribute space to columns that needs it but if there is space left we must redistribute it
		// to fulfill percentages
		if (0 === Math::comp($leftSpace, '0')) {
			return $this;
		}
		// first redistribute it to auto columns because they are most flexible ones
		if (!empty($this->autoColumns)) {
			$autoColumnsMaxWidth = $this->getAutoColumnsMaxWidth();
			foreach ($this->autoColumns as $columnIndex => $columns) {
				Math::setAccurate(true);
				$ratio = Math::div($this->contentWidths[$columnIndex], $autoColumnsMaxWidth);
				$add = Math::mul($leftSpace, $ratio);
				Math::setAccurate(false);
				$colWidth = Math::add($columns[0]->getDimensions()->getWidth(), $add);
				foreach ($columns as $column) {
					$colDmns = $column->getDimensions();
					$colDmns->setWidth($colWidth);
					$column->getFirstChild()->getDimensions()->setWidth($colDmns->getInnerWidth());
				}
				if (!$withPreferred) {
					// if not to preferred it means that we adding to min widths
					$this->minWidths[$columnIndex] = $colWidth;
				}
			}
		} elseif ($count = \count($this->pixelColumns)) {
			// next redistribute left space to pixel columns if there where no auto columns
			$add = Math::div($leftSpace, (string) $count);
			foreach ($this->pixelColumns as $columnIndex => $columns) {
				$colWidth = Math::add($columns[0]->getDimensions()->getWidth(), $add);
				foreach ($columns as $column) {
					$colDmns = $column->getDimensions();
					$colDmns->setWidth($colWidth);
					$column->getFirstChild()->getDimensions()->setWidth($colDmns->getInnerWidth());
				}
				if (!$withPreferred) {
					// if not to preferred it means that we adding to min widths
					$this->minWidths[$columnIndex] = $colWidth;
				}
			}
		}

		return $this;
	}

	/**
	 * Try preferred width.
	 *
	 * @param string $leftSpace
	 * @param bool   $outerWidthSet
	 *
	 * @return $this|TableBox
	 */
	protected function tryPreferred(string $leftSpace, bool $outerWidthSet)
	{
		// left space is 100% width that we can use
		$totalPercentages = '0';
		$totalPercentagesWidth = '0';
		foreach ($this->percentages as $columnIndex => $percentage) {
			$totalPercentages = Math::add($totalPercentages, $percentage);
			$colWidth = $this->rows[0]->getChildren()[$columnIndex]->getDimensions()->getInnerWidth();
			$totalPercentagesWidth = Math::add($totalPercentagesWidth, $colWidth);
		}
		$forPercentages = Math::percent($totalPercentages, $leftSpace);
		$neededTotal = '0';
		$needed = [];
		foreach ($this->percentColumns as $columnIndex => $columns) {
			$colDmns = $columns[0]->getDimensions();
			$colWidth = $colDmns->getInnerWidth();
			if (Math::comp($colWidth, $this->contentWidths[$columnIndex]) < 0) {
				$needed[$columnIndex] = Math::sub($this->contentWidths[$columnIndex], $colWidth);
				$neededTotal = Math::add($neededTotal, $needed[$columnIndex]);
			}
		}
		if (0 === Math::comp($neededTotal, '0') && !$outerWidthSet) {
			return $this->setRowsWidth();
		}
		$currentPercentsWidth = '0';
		$addToPercents = Math::min($neededTotal, $forPercentages);
		foreach ($this->percentColumns as $columnIndex => $columns) {
			if (Math::comp($addToPercents, $neededTotal) < 0) {
				Math::setAccurate(true);
				$ratio = Math::div($this->percentages[$columnIndex], $totalPercentages);
				$add = Math::mul($ratio, $addToPercents);
				Math::setAccurate(false);
			} else {
				if (isset($needed[$columnIndex])) {
					$add = $needed[$columnIndex];
				} else {
					$add = '0';
				}
			}
			foreach ($columns as $column) {
				$colDmns = $column->getDimensions();
				$colDmns->setWidth(Math::add($colDmns->getWidth(), $add));
				$column->getFirstChild()->getDimensions()->setWidth($colDmns->getInnerWidth());
			}
			$currentPercentsWidth = Math::add($currentPercentsWidth, $colDmns->getInnerWidth());
		}
		// we've added space to percentage columns, now we must calculate how much space we need to add (to have 100%)
		$leftSpace2 = Math::sub($leftSpace, $addToPercents);
		if (0 === Math::comp($leftSpace2, '0')) {
			return $this->setRowsWidth();
		}
		// left space MUST be redistributed to fulfill new percentages
		$this->addToOthers($leftSpace2, true);
		// percent columns were redistributed in the first step so we don't need to do anything
		$this->setRowsWidth();

		return $this;
	}

	/**
	 * Get table min width.
	 *
	 * @return string
	 */
	public function getMinWidth()
	{
		foreach ($this->getCells() as $cell) {
			$cell->measureWidth();
		}
		$this->rows = $this->getRows();
		$this->setUpSizingTypes();
		$this->setUpWidths('0');
		$this->minContentGuess($this->rows)->finish();

		return $this->getDimensions()->getWidth();
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureWidth(bool $afterPageDividing = false)
	{
		if ($this->parentWidth === $this->getParent()->getParent()->getDimensions()->getWidth()) {
			return $this;
		}
		$this->parentWidth = $this->getParent()->getParent()->getDimensions()->getWidth();
		foreach ($this->getCells() as $cell) {
			$cell->measureWidth($afterPageDividing);
		}
		$step = 0;
		$this->setUpSizingTypes();
		$availableSpace = $this->getParent()->getDimensions()->computeAvailableSpace();
		$outerWidthSet = false;
		if ('auto' !== $this->getParent()->getStyle()->getRules('width')) {
			$this->getParent()->applyStyleWidth();
			$availableSpace = Math::min($availableSpace, $this->getParent()->getDimensions()->getInnerWidth());
			$outerWidthSet = true;
		}
		$rows = $this->rows = $this->getRows();
		$this->setUpWidths($availableSpace);
		$this->minContentGuess($rows);
		$this->minContentPercentageGuess($rows, $availableSpace);
		if (!$this->willFit($availableSpace)) {
			return $this->shrinkToFit($availableSpace, $step);
		}
		$step = 1;
		$this->minContentSpecifiedGuess($rows, $availableSpace);
		if (!$this->willFit($availableSpace)) {
			return $this->shrinkToFit($availableSpace, $step);
		}
		$step = 2;
		$this->maxContentGuess($rows, $availableSpace);
		if (!$this->willFit($availableSpace)) {
			return $this->shrinkToFit($availableSpace, $step);
		}
		$currentWidth = $this->getDimensions()->getWidth();
		$leftSpace = Math::sub($availableSpace, $currentWidth);
		if (Math::comp($leftSpace, '0') > 0) {
			$this->tryPreferred($leftSpace, $outerWidthSet);
		}

		return $this->finish();
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureHeight(bool $afterPageDividing = false)
	{
		if ($this->wasCut()) {
			return $this;
		}
		foreach ($this->getCells() as $cell) {
			$cell->measureHeight($afterPageDividing);
		}
		$style = $this->getStyle();
		$maxRowHeights = [];
		foreach ($this->getChildren() as $rowGroupIndex => $rowGroup) {
			$rows = $rowGroup->getChildren();
			$spannedRowsCount = []; // spannedRowsCount is array of number of row spans for each row
			foreach ($rows as $rowIndex => $row) {
				foreach ($row->getChildren() as $column) {
					$spannedRowsCount[$rowIndex] = max($spannedRowsCount[$rowIndex] ?? '0', $column->getRowSpan());
				}
			}
			$rowsCount = []; // rowsCount is array with number of rows they must share for each row
			foreach ($spannedRowsCount as $currentRowSpanIndex => $currentRowsCount) {
				for ($i = 0; $i < $currentRowsCount; ++$i) {
					$rowsCount[$currentRowSpanIndex + $i] = max($rowsCount[$currentRowSpanIndex + $i] ?? '0', $currentRowsCount);
				}
			}
			// get maximal height of each row
			foreach ($rows as $rowIndex => $row) {
				foreach ($row->getChildren() as $column) {
					$cell = $column->getFirstChild();
					if (!isset($maxRowHeights[$rowGroupIndex][$rowIndex])) {
						$maxRowHeights[$rowGroupIndex][$rowIndex] = '0';
					}
					$columnStyle = $column->getStyle();
					$columnVerticalSize = Math::add($columnStyle->getVerticalMarginsWidth(), $columnStyle->getVerticalPaddingsWidth(), $columnStyle->getVerticalBordersWidth());
					$columnHeight = Math::add($cell->getDimensions()->getOuterHeight(), $columnVerticalSize);
					// for now ignore height of column that have span greater than 1
					if (1 === $column->getRowSpan()) {
						$maxRowHeights[$rowGroupIndex][$rowIndex] = Math::max($maxRowHeights[$rowGroupIndex][$rowIndex], $columnHeight);
					}
				}
			}
			// column that is spanned with more than 1 row must have height that is equal to all spanned rows height
			foreach ($rows as $rowIndex => $row) {
				$currentRowMax = $maxRowHeights[$rowGroupIndex][$rowIndex] ?? '0';
				foreach ($row->getChildren() as $column) {
					$rowSpan = $column->getRowSpan();
					if ($rowSpan > 1) {
						$spannedRowsHeight = '0';
						// get sum of spanned row height starting from current row
						for ($i = 0; $i < $rowSpan; ++$i) {
							if (isset($maxRowHeights[$rowGroupIndex][$rowIndex + $i])) {
								$spannedRowsHeight = Math::add($spannedRowsHeight, $maxRowHeights[$rowGroupIndex][$rowIndex + $i]);
							}
						}
						$fromOtherRows = Math::div($spannedRowsHeight, (string) $rowSpan);
						$fromColumnHeight = Math::div($column->getDimensions()->getOuterHeight(), (string) $rowSpan);
						$currentRowMax = Math::max($currentRowMax, $fromOtherRows, $fromColumnHeight);
						// if column that have rowSpan >1 is higher than sum of all other spanned rows max height expand others
						if (Math::comp($fromColumnHeight, $fromOtherRows) > 0) {
							for ($i = 0; $i < $rowSpan; ++$i) {
								$maxRowHeights[$rowGroupIndex][$rowIndex + $i] = $currentRowMax;
							}
						}
					}
				}
				$maxRowHeights[$rowGroupIndex][$rowIndex] = $currentRowMax;
			}
		}
		$tableHeight = '0';
		$rowGroups = $this->getChildren();
		foreach ($rowGroups as $rowGroupIndex => $rowGroup) {
			$rowGroupHeight = '0';
			$rows = $rowGroup->getChildren();
			foreach ($rows as $rowIndex => $row) {
				$rowStyle = $row->getStyle();
				$row->getDimensions()->setHeight(Math::add($maxRowHeights[$rowGroupIndex][$rowIndex], $rowStyle->getVerticalBordersWidth(), $rowStyle->getVerticalPaddingsWidth()));
				$rowGroupHeight = Math::add($rowGroupHeight, $row->getDimensions()->getHeight());
				foreach ($row->getChildren() as $column) {
					if ($column->getRowSpan() > 1 && $afterPageDividing) {
						continue;
					}
					$column->getDimensions()->setHeight($row->getDimensions()->getInnerHeight());
					$cell = $column->getFirstChild();
					$cellStyle = $cell->getStyle();
					if ('auto' !== $cellStyle->getRules('height')) {
						$cellStyle->getRules()['height']->convert($cellStyle);
						$height = $cellStyle->getRules('height');
					} else {
						$height = $column->getDimensions()->getInnerHeight();
						$height = Math::div($height, (string) $column->getRowSpan());
					}
					$cellChildrenHeight = '0';
					foreach ($cell->getChildren() as $cellChild) {
						$cellChildrenHeight = Math::add($cellChildrenHeight, $cellChild->getDimensions()->getOuterHeight());
					}
					$cellVerticalSize = Math::add($cellStyle->getVerticalBordersWidth(), $cellStyle->getVerticalPaddingsWidth());
					$cellChildrenHeight = Math::add($cellChildrenHeight, $cellVerticalSize);
					$cellChildrenHeight = Math::div($cellChildrenHeight, (string) $column->getRowSpan());
					// add vertical padding if needed
					if (Math::comp($height, $cellChildrenHeight) > 0) {
						$freeSpace = Math::sub($height, $cellChildrenHeight);
						$cellStyle = $cell->getStyle();
						switch ($cellStyle->getRules('vertical-align')) {
							case 'top':
								$freeSpace = Math::add($freeSpace, $cellStyle->getRules('padding-bottom'));
								$cellStyle->setRule('padding-bottom', $freeSpace);
								break;
							case 'bottom':
								$freeSpace = Math::add($freeSpace, $cellStyle->getRules('padding-top'));
								$cellStyle->setRule('padding-top', $freeSpace);
								break;
							case 'baseline':
							case 'middle':
							default:
								$disposition = Math::div($freeSpace, '2');
								$paddingTop = Math::add($cellStyle->getRules('padding-top'), $disposition);
								$paddingBottom = Math::add($cellStyle->getRules('padding-bottom'), $disposition);
								$cellStyle->setRule('padding-top', $paddingTop);
								$cellStyle->setRule('padding-bottom', $paddingBottom);
								break;
						}
					}
					$height = Math::max($height, $cellChildrenHeight);
					$cell->getDimensions()->setHeight($height);
				}
			}
			if (isset($row) && 'separate' === $row->getStyle()->getRules('border-collapse')) {
				$rowGroupHeight = Math::add($rowGroupHeight, $row->getStyle()->getRules('border-spacing'));
			}
			$rowGroup->getDimensions()->setHeight($rowGroupHeight);
			$tableHeight = Math::add($tableHeight, $rowGroupHeight);
		}
		$this->getDimensions()->setHeight(Math::add($tableHeight, $style->getVerticalBordersWidth(), $style->getVerticalPaddingsWidth()));

		return $this;
	}

	/**
	 * Remove empty rows.
	 *
	 * @return $this
	 */
	public function removeEmptyRows()
	{
		foreach ($this->getChildren() as $rowGroup) {
			if (!$rowGroup->containContent() || !$rowGroup->hasChildren()) {
				$this->removeChild($rowGroup);
			} else {
				foreach ($rowGroup->getChildren() as $row) {
					if (!$row->containContent() || !$row->hasChildren()) {
						$rowGroup->removeChild($row);
					}
				}
			}
		}

		return $this;
	}
}
