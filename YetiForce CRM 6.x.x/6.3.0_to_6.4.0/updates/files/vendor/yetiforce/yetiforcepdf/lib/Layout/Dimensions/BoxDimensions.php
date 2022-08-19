<?php

declare(strict_types=1);
/**
 * BoxDimensions class.
 *
 * @package   YetiForcePDF\Layout\Dimensions
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout\Dimensions;

use YetiForcePDF\Layout\Box;
use YetiForcePDF\Layout\LineBox;
use YetiForcePDF\Layout\TableWrapperBox;
use YetiForcePDF\Layout\TextBox;
use YetiForcePDF\Math;

/**
 * Class BoxDimensions.
 */
class BoxDimensions extends Dimensions
{
	/**
	 * @var Box
	 */
	protected $box;

	/**
	 * Set box.
	 *
	 * @param \YetiForcePDF\Layout\Box $box
	 *
	 * @return $this
	 */
	public function setBox(Box $box)
	{
		$this->box = $box;
		return $this;
	}

	/**
	 * Get box.
	 *
	 * @return \YetiForcePDF\Layout\Box
	 */
	public function getBox()
	{
		return $this->box;
	}

	/**
	 * Get raw width.
	 *
	 * @return string|null
	 */
	public function getRawWidth()
	{
		return $this->width;
	}

	/**
	 * Get raw height.
	 *
	 * @return string|null
	 */
	public function getRawHeight()
	{
		return $this->height;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getWidth()
	{
		if (!$this->getBox()->isForMeasurement() && !$this->getBox()->getStyle()->haveSpacing()) {
			return '0';
		}
		if (!$this->getBox()->isDisplayable()) {
			return '0';
		}
		return parent::getWidth();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeight()
	{
		if (!$this->getBox()->isForMeasurement() && !$this->getBox()->getStyle()->haveSpacing()) {
			return '0';
		}
		if (!$this->getBox()->isDisplayable()) {
			return '0';
		}
		return parent::getHeight();
	}

	/**
	 * Get innerWidth.
	 *
	 * @return string
	 */
	public function getInnerWidth(): string
	{
		$box = $this->getBox();
		if (!$box->isForMeasurement() && !$this->getBox()->getStyle()->haveSpacing()) {
			return '0';
		}
		if (!$this->getBox()->isDisplayable()) {
			return '0';
		}
		$style = $box->getStyle();
		$width = $this->getWidth();
		if (null === $width) {
			return '0';
		}
		return Math::sub($width, $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth());
	}

	/**
	 * Get innerHeight.
	 *
	 * @return string
	 */
	public function getInnerHeight(): string
	{
		$box = $this->getBox();
		if (!$box->isForMeasurement() && !$this->getBox()->getStyle()->haveSpacing()) {
			return '0';
		}
		if (!$this->getBox()->isDisplayable()) {
			return '0';
		}
		$style = $box->getStyle();
		$height = $this->getHeight();
		if (null === $height) {
			$height = '0';
			$element = $box->getElement();
			if ($element && $element->getDOMElement() instanceof \DOMText) {
				$height = $style->getLineHeight();
			}
		}
		return Math::sub($height, $style->getVerticalBordersWidth(), $style->getVerticalPaddingsWidth());
	}

	/**
	 * Get width with margins.
	 *
	 * @return string
	 */
	public function getOuterWidth()
	{
		$box = $this->getBox();
		if (!$box->isForMeasurement() && !$this->getBox()->getStyle()->haveSpacing()) {
			return '0';
		}
		if (!$this->getBox()->isDisplayable()) {
			return '0';
		}
		if (!$box instanceof LineBox) {
			$style = $this->getBox()->getStyle();
			$childrenWidth = '0';
			// if some of the children overflows
			if ('inline' === $box->getStyle()->getRules('display')) {
				foreach ($box->getChildren() as $child) {
					if ($childWidth = $child->getDimensions()->getWidth()) {
						$childrenWidth = Math::add($childrenWidth, $childWidth);
					} else {
						$childrenWidth = Math::add($childrenWidth, $child->getDimensions()->getOuterWidth());
					}
				}
			} else {
				foreach ($box->getChildren() as $child) {
					if ($childWidth = $child->getDimensions()->getWidth()) {
						$childrenWidth = Math::max($childrenWidth, $childWidth);
					} else {
						$childrenWidth = Math::max($childrenWidth, $child->getDimensions()->getOuterWidth());
					}
				}
			}
			if (null !== $this->getWidth()) {
				$childrenWidth = Math::add($childrenWidth, $style->getHorizontalMarginsWidth(), $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth());
				$width = Math::add($this->getWidth(), $style->getHorizontalMarginsWidth());
				return Math::max($width, $childrenWidth);
			}
			return Math::add($childrenWidth, $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth());
		}
		return $this->getBox()->getChildrenWidth();
	}

	/**
	 * Get height with margins.
	 *
	 * @return string
	 */
	public function getOuterHeight()
	{
		$box = $this->getBox();
		if (!$box->isForMeasurement() && !$this->getBox()->getStyle()->haveSpacing()) {
			return '0';
		}
		if (!$this->getBox()->isDisplayable()) {
			return '0';
		}
		$style = $this->getBox()->getStyle();
		if (!$box instanceof LineBox) {
			$childrenHeight = '0';
			// if some of the children overflows
			if ('inline' === $box->getStyle()->getRules('display')) {
				foreach ($box->getChildren() as $child) {
					$childrenHeight = Math::add($childrenHeight, $child->getDimensions()->getOuterHeight());
				}
			} else {
				foreach ($box->getChildren() as $child) {
					$childrenHeight = Math::max($childrenHeight, $child->getDimensions()->getOuterHeight());
				}
			}
			if (null !== $this->getHeight()) {
				$height = Math::add($this->getHeight(), $style->getVerticalMarginsWidth());
				return Math::max($height, $childrenHeight);
			}
			return Math::add($childrenHeight, $style->getVerticalBordersWidth(), $style->getVerticalPaddingsWidth());
		}
		return Math::add($this->getHeight(), $style->getHorizontalMarginsWidth());
	}

	/**
	 * Reset width.
	 *
	 * @return $this
	 */
	public function resetWidth()
	{
		$this->setWidth();
		foreach ($this->getBox()->getChildren() as $child) {
			$child->getDimensions()->setWidth();
			$child->getDimensions()->resetWidth();
		}
		return $this;
	}

	/**
	 * Reset height.
	 *
	 * @return $this
	 */
	public function resetHeight()
	{
		$this->setHeight();
		foreach ($this->getBox()->getChildren() as $child) {
			$child->getDimensions()->setHeight();
			$child->getDimensions()->resetHeight();
		}
		return $this;
	}

	/**
	 * Get max width with margins.
	 *
	 * @return string
	 */
	public function getMaxWidth()
	{
		$box = $this->getBox();
		if (!$box->isForMeasurement()) {
			return '0';
		}
		if (!$box instanceof LineBox) {
			$style = $this->getBox()->getStyle();
			$childrenWidth = '0';
			// if some of the children overflows
			if ('inline' === $box->getStyle()->getRules('display')) {
				foreach ($box->getChildren() as $child) {
					$childrenWidth = Math::add($childrenWidth, $child->getDimensions()->getOuterWidth());
				}
			} elseif (\count($box->getSourceLines())) {
				foreach ($box->getSourceLines() as $line) {
					$childrenWidth = Math::max($childrenWidth, $line->getChildrenWidth());
				}
				foreach ($box->getChildren() as $child) {
					if (!$child instanceof LineBox) {
						$childrenWidth = Math::max($childrenWidth, $child->getDimensions()->getWidth() ?? '0'); // TODO: neither getOuterWidth or getWidth works here
					}
				}
			} else {
				// TODO: each block and inline-block should have source lines but for now i don't have time so this is just patch
				foreach ($box->getChildren() as $child) {
					$childrenWidth = Math::max($childrenWidth, $child->getDimensions()->getOuterWidth());
				}
			}
			if (null !== $this->getWidth()) {
				$childrenWidth = Math::add($childrenWidth, $style->getHorizontalMarginsWidth(), $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth());
				$width = Math::add($this->getWidth(), $style->getHorizontalMarginsWidth());
				return Math::max($width, $childrenWidth);
			}
			return Math::add($childrenWidth, $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth());
		}
		return $this->getBox()->getChildrenWidth();
	}

	/**
	 * Get minimum space that current box could have without overflow.
	 *
	 * @return string
	 */
	public function getMinWidth()
	{
		$box = $this->getBox();
		if (!$box->isForMeasurement()) {
			return '0';
		}
		if ($box instanceof TableWrapperBox) {
			return $box->getFirstChild()->getMinWidth();
		}
		if ($box instanceof TextBox) {
			return $this->getTextWidth($this->getBox()->getText());
		}
		$maxTextWidth = '0';
		foreach ($box->getChildren() as $childBox) {
			if ($childBox instanceof TextBox) {
				$textWidth = $childBox->getDimensions()->getTextWidth($childBox->getText());
				$maxTextWidth = Math::max($maxTextWidth, $textWidth);
			} else {
				$minWidth = $childBox->getDimensions()->getMinWidth();
				$maxTextWidth = Math::max($maxTextWidth, $minWidth);
			}
		}
		$style = $this->getBox()->getStyle();
		return Math::add($maxTextWidth, $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth(), $style->getHorizontalMarginsWidth());
	}

	/**
	 * Get text width.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function getTextWidth($text)
	{
		if (!$this->getBox()->isForMeasurement()) {
			return '0';
		}
		$font = $this->box->getStyle()->getFont();
		return $font->getTextWidth($text);
	}

	/**
	 * Get text height.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function getTextHeight($text)
	{
		if (!$this->getBox()->isForMeasurement()) {
			return '0';
		}
		$font = $this->box->getStyle()->getFont();
		return $font->getTextHeight($text);
	}

	/**
	 * Compute available space (basing on parent available space and parent border and padding).
	 *
	 * @return string
	 */
	public function computeAvailableSpace()
	{
		if (!$this->getBox()->isForMeasurement()) {
			return '0';
		}
		if ($parent = $this->getBox()->getParent()) {
			$parentStyle = $parent->getStyle();
			if (null === $parent->getDimensions()->getWidth()) {
				return Math::sub($parent->getDimensions()->computeAvailableSpace(), $parentStyle->getHorizontalBordersWidth(), $parentStyle->getHorizontalPaddingsWidth());
			}
			return $this->getBox()->getParent()->getDimensions()->getInnerWidth();
		}
		return $this->document->getCurrentPage()->getDimensions()->getWidth();
	}

	/**
	 * Calculate width from style width:10%.
	 *
	 * @return mixed|string|null
	 */
	public function getStyleWidth()
	{
		if (!$this->getBox()->isForMeasurement() && !$this->getBox()) {
			return '0';
		}
		$width = $this->getBox()->getStyle()->getRules('width');
		if ('auto' === $width) {
			return null;
		}
		$percentPos = strpos($width, '%');
		if (false !== $percentPos) {
			$widthInPercent = substr($width, 0, $percentPos);
			$closestBoxDimensions = $this->getBox()->getClosestBox()->getDimensions();
			if (null !== $closestBoxDimensions->getWidth()) {
				$parentWidth = $closestBoxDimensions->getInnerWidth();
				$style = $this->getBox()->getStyle();
				$parentWidth = Math::sub($parentWidth, $style->getHorizontalMarginsWidth());
				if ($parentWidth) {
					return Math::percent($widthInPercent, $parentWidth);
				}
			}
		} else {
			return $width;
		}
		return null;
	}

	/**
	 * Calculate height from style width:10%.
	 *
	 * @return mixed|string|null
	 */
	public function getStyleHeight()
	{
		if (!$this->getBox()->isForMeasurement()) {
			return '0';
		}
		$height = $this->getBox()->getStyle()->getRules('height');
		if ('auto' === $height) {
			return null;
		}
		$percentPos = strpos($height, '%');
		if (false !== $percentPos) {
			$widthInPercent = substr($height, 0, $percentPos);
			$closestBoxDimensions = $this->getBox()->getClosestBox()->getDimensions();
			if (null !== $closestBoxDimensions->getHeight()) {
				$parentHeight = $closestBoxDimensions->getInnerHeight();
				if ($parentHeight) {
					return Math::percent($widthInPercent, $parentHeight);
				}
			}
		} else {
			return $height;
		}
		return null;
	}
}
