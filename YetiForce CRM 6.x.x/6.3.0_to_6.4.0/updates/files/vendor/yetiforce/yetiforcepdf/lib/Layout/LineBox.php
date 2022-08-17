<?php

declare(strict_types=1);
/**
 * LineBox class.
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
 * Class LineBox.
 */
class LineBox extends Box implements BoxInterface
{
	/**
	 * Append block box element.
	 *
	 * @param \DOMNode                           $childDomElement
	 * @param Element                            $element
	 * @param Style                              $style
	 * @param \YetiForcePDF\Layout\BlockBox|null $parentBlock
	 *
	 * @return \YetiForcePDF\Layout\BlockBox
	 */
	public function appendBlock($childDomElement, $element, $style, $parentBlock)
	{
		return $parentBlock->appendBlock($childDomElement, $element, $style, $parentBlock);
	}

	/**
	 * Append table block box element.
	 *
	 * @param \DOMNode                           $childDomElement
	 * @param Element                            $element
	 * @param Style                              $style
	 * @param \YetiForcePDF\Layout\BlockBox|null $parentBlock
	 *
	 * @return \YetiForcePDF\Layout\BlockBox
	 */
	public function appendTableBlock($childDomElement, $element, $style, $parentBlock)
	{
		return $parentBlock->appendTableBlock($childDomElement, $element, $style, $parentBlock);
	}

	/**
	 * Append inline block box element.
	 *
	 * @param \DOMNode                           $childDomElement
	 * @param Element                            $element
	 * @param Style                              $style
	 * @param \YetiForcePDF\Layout\BlockBox|null $parentBlock
	 *
	 * @return \YetiForcePDF\Layout\InlineBlockBox
	 */
	public function appendInlineBlock($childDomElement, $element, $style, $parentBlock)
	{
		if ('img' === $childDomElement->tagName) {
			$box = (new ImageBox())
				->setDocument($this->document)
				->setElement($element)
				->setParent($this)
				->setStyle($style)
				->init();
		} else {
			$box = (new InlineBlockBox())
				->setDocument($this->document)
				->setElement($element)
				->setParent($this)
				->setStyle($style)
				->init();
		}
		$this->appendChild($box);
		$box->getStyle()->init();
		$box->buildTree($box);

		return $box;
	}

	/**
	 * Append barcode box element.
	 *
	 * @param \DOMNode                           $childDomElement
	 * @param Element                            $element
	 * @param Style                              $style
	 * @param \YetiForcePDF\Layout\BlockBox|null $parentBlock
	 *
	 * @return \YetiForcePDF\Layout\InlineBlockBox
	 */
	public function appendBarcode($childDomElement, $element, $style, $parentBlock)
	{
		$box = (new BarcodeBox())
			->setDocument($this->document)
			->setElement($element)
			->setParent($this)
			->setStyle($style, false)
			->init();
		$this->appendChild($box);
		if ($childDomElement->hasAttribute('data-barcode') && $childDomElement->getAttribute('data-barcode')) {
			$box->setType($childDomElement->getAttribute('data-barcode'));
		}
		if ($childDomElement->hasAttribute('data-size') && $childDomElement->getAttribute('data-size')) {
			$box->setSize($childDomElement->getAttribute('data-size'));
		}
		if ($childDomElement->hasAttribute('data-height') && $childDomElement->getAttribute('data-height')) {
			$box->setHeight($childDomElement->getAttribute('data-height'));
		}
		if ($childDomElement->hasAttribute('data-code') && $childDomElement->getAttribute('data-code')) {
			$box->setCode($childDomElement->getAttribute('data-code'));
		}
		$box->generateBarcodeImage();
		$box->getStyle()->init();
		$box->buildTree($box);

		return $box;
	}

	/**
	 * Add inline child (and split text to individual characters).
	 *
	 * @param \DOMNode                           $childDomElement
	 * @param Element                            $element
	 * @param Style                              $style
	 * @param \YetiForcePDF\Layout\BlockBox|null $parentBlock
	 *
	 * @return \YetiForcePDF\Layout\InlineBox
	 */
	public function appendInline($childDomElement, $element, $style, $parentBlock)
	{
		$box = (new InlineBox())
			->setDocument($this->document)
			->setElement($element)
			->setParent($this)
			->setStyle($style)
			->init();
		$this->appendChild($box);
		$box->getStyle()->init();
		$box->buildTree($parentBlock);

		return $box;
	}

	/**
	 * Is this line empty?  - filled with whitespaces / non measurable elements.
	 */
	public function isEmpty()
	{
		foreach ($this->getChildren() as $child) {
			if ($child->isForMeasurement() || $child->getStyle()->haveSpacing()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Will this box fit in line? (or need to create new one).
	 *
	 * @param \YetiForcePDF\Layout\Box $box
	 *
	 * @return bool
	 */
	public function willFit(Box $box)
	{
		$childrenWidth = $this->getChildrenWidth();
		$availableSpace = $this->getDimensions()->computeAvailableSpace();
		$boxWidth = $box->getDimensions()->getWidth();
		if (!$boxWidth) {
			$boxWidth = $box->getDimensions()->getOuterWidth();
		}

		return Math::comp(Math::sub($availableSpace, $childrenWidth), $boxWidth) >= 0;
	}

	/**
	 * Remove white spaces.
	 *
	 * @return $this
	 */
	public function removeWhiteSpaces()
	{
		$this->iterateChildren(function ($child) {
			if ($child->containContent()) {
				$child->setForMeasurement(true);

				return false;
			}
			$child->setForMeasurement(false);
		}, true, false);

		return $this;
	}

	/**
	 * Divide this line into more lines when objects doesn't fit.
	 *
	 * @return LineBox[]
	 */
	public function divide()
	{
		$lines = [];
		$line = (new self())
			->setDocument($this->document)
			->setParent($this->getParent())
			->setStyle(clone $this->style)
			->init();
		$children = $this->getChildren();
		foreach ($children as $index => $childBox) {
			if ($line->willFit($childBox)) {
				// if this is beginning of the line
				if (!$line->containContent()) {
					if (!$childBox->containContent()) {
						$childBox->setForMeasurement(false);
					} else {
						$childBox->setForMeasurement(true);
					}
					$line->appendChild($childBox);
				} else {
					if (!$childBox->containContent()) {
						// if we doesn't have content and previous element too do not measure me
						if ($previous = $children[$index - 1]) {
							if (!$previous->containContent()) {
								$childBox->setForMeasurement(false);
							} else {
								$childBox->setForMeasurement(true);
							}
						} else {
							$childBox->setForMeasurement(true);
						}
					}
					$line->appendChild($childBox);
				}
			} else {
				$lines[] = $line;
				$line = (new self())
					->setDocument($this->document)
					->setParent($this->getParent())
					->setStyle(clone $this->style)
					->init();
				if (!$childBox->containContent()) {
					$childBox->setForMeasurement(false);
				} else {
					$childBox->setForMeasurement(true);
				}
				$line->appendChild($childBox);
			}
		}
		// append last line
		$lines[] = $line;
		foreach ($lines as $line) {
			$isForMeasurement = false;
			foreach ($line->getChildren() as $child) {
				if ($child->isForMeasurement()) {
					$isForMeasurement = true;

					break;
				}
			}
			$line->forMeasurement = $isForMeasurement;
		}
		unset($children);

		return $lines;
	}

	/**
	 * Measure width.
	 *
	 * @param bool $afterPageDividing
	 *
	 * @return $this
	 */
	public function measureWidth(bool $afterPageDividing = false)
	{
		$this->clearStyles();
		$width = '0';
		foreach ($this->getChildren() as $child) {
			$child->measureWidth($afterPageDividing);
			$width = Math::add($width, $child->getDimensions()->getOuterWidth());
		}
		$this->getDimensions()->setWidth($width);
		return $this;
	}

	/**
	 * Measure height.
	 *
	 * @param bool $afterPageDividing
	 *
	 * @return $this
	 */
	public function measureHeight(bool $afterPageDividing = false)
	{
		if (!$this->isForMeasurement() || $this->isEmpty()) {
			$this->getDimensions()->setHeight('0');
			return $this;
		}
		foreach ($this->getChildren() as $child) {
			$child->measureHeight($afterPageDividing);
		}
		$lineHeight = $this->getStyle()->getMaxLineHeight();
		$this->getDimensions()->setHeight($lineHeight);
		$this->measureMargins();

		return $this;
	}

	/**
	 * Measure margins.
	 *
	 * @return $this
	 */
	public function measureMargins()
	{
		$allChildren = [];
		$this->getAllChildren($allChildren, false);
		$marginTop = '0';
		$marginBottom = '0';
		foreach ($allChildren as $child) {
			if ($child instanceof InlineBlockBox) {
				$marginTop = Math::max($marginTop, $child->getStyle()->getRules('margin-top'));
				$marginBottom = Math::max($marginBottom, $child->getStyle()->getRules('margin-bottom'));
			}
		}
		$style = $this->getStyle();
		$style->setRule('margin-top', $marginTop);
		$style->setRule('margin-bottom', $marginBottom);

		return $this;
	}

	/**
	 * Position.
	 *
	 * @param bool $afterPageDividing
	 *
	 * @return $this
	 */
	public function measureOffset(bool $afterPageDividing = false)
	{
		$parent = $this->getParent();
		$parentStyle = $parent->getStyle();
		$top = $parentStyle->getOffsetTop();
		$left = $parentStyle->getOffsetLeft();
		$previous = $this->getPrevious();
		if ($previous && !$previous->isAbsolute()) {
			$top = Math::add($previous->getOffset()->getTop(), $previous->getDimensions()->getHeight(), $previous->getStyle()->getRules('margin-bottom'));
		}
		$top = Math::add($top, $this->getStyle()->getRules('margin-top'));
		$this->getOffset()->setTop($top);
		$this->getOffset()->setLeft($left);
		foreach ($this->getChildren() as $child) {
			$child->measureOffset($afterPageDividing);
		}

		return $this;
	}

	/**
	 * Position.
	 *
	 * @param bool $afterPageDividing
	 *
	 * @return $this
	 */
	public function measurePosition(bool $afterPageDividing = false)
	{
		if (!$this->isRenderable()) {
			return $this;
		}
		$parent = $this->getParent();
		$this->getCoordinates()->setX(Math::add($parent->getCoordinates()->getX(), $this->getOffset()->getLeft()));
		$this->getCoordinates()->setY(Math::add($parent->getCoordinates()->getY(), $this->getOffset()->getTop()));
		foreach ($this->getChildren() as $child) {
			$child->measurePosition($afterPageDividing);
		}

		return $this;
	}

	/**
	 * Clear styles
	 * return $this;.
	 */
	public function clearStyles()
	{
		$allNestedChildren = [];
		$maxLevel = '0';
		foreach ($this->getChildren() as $child) {
			$allChildren = [];
			$child->getAllChildren($allChildren);
			$maxLevel = Math::max($maxLevel, (string) \count($allChildren));
			$allNestedChildren[] = $allChildren;
		}
		$clones = [];
		for ($row = 0; $row < $maxLevel; ++$row) {
			foreach ($allNestedChildren as $childArray) {
				if (isset($childArray[$row])) {
					$current = $childArray[$row];
					$clones[$current->getId()][] = $current;
				}
			}
		}
		foreach ($clones as $row => $cloneArray) {
			$count = \count($cloneArray);
			if ($count > 1) {
				foreach ($cloneArray as $index => $clone) {
					if (0 === $index) {
						$clone->getStyle()->clearFirstInline();
					} elseif ($index === $count - 1) {
						$clone->getStyle()->clearLastInline();
					} elseif ($index > 0 && $index < ($count - 1)) {
						$clone->getStyle()->clearMiddleInline();
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Get children width.
	 *
	 * @return string
	 */
	public function getChildrenWidth()
	{
		$width = '0';
		foreach ($this->getChildren() as $childBox) {
			if ($childBox->isForMeasurement()) {
				if ($childWidth = $childBox->getDimensions()->getWidth()) {
					$width = Math::add($width, $childWidth);
				} else {
					$width = Math::add($width, $childBox->getDimensions()->getOuterWidth());
				}
			}
		}

		return $width;
	}

	/**
	 * Get element PDF instructions to use in content stream.
	 *
	 * @return string
	 */
	public function getInstructions(): string
	{
		$coordinates = $this->getCoordinates();
		$pdfX = $coordinates->getPdfX();
		$pdfY = $coordinates->getPdfY();
		$dimensions = $this->getDimensions();
		$width = $dimensions->getWidth();
		$height = $dimensions->getHeight();
		$element = [];
		$element = $this->addBorderInstructions($element, $pdfX, $pdfY, $width, $height);

		return implode("\n", $element);
	}
}
