<?php

declare(strict_types=1);
/**
 * InlineBlockBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Math;

/**
 * Class InlineBlockBox.
 */
class InlineBlockBox extends BlockBox
{
	/**
	 * Measure width.
	 *
	 * @param bool $afterPageDividing
	 *
	 * @return $this
	 */
	public function measureWidth(bool $afterPageDividing = false)
	{
		foreach ($this->getChildren() as $child) {
			$child->measureWidth($afterPageDividing);
		}
		$this->divideLines();
		$maxWidth = '0';
		foreach ($this->getChildren() as $child) {
			$child->measureWidth($afterPageDividing);
			$outerWidth = $child->getDimensions()->getOuterWidth();
			$maxWidth = Math::max($maxWidth, $outerWidth);
		}
		$style = $this->getStyle();
		$maxWidth = Math::add($maxWidth, $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth());
		$this->getDimensions()->setWidth($maxWidth);
		$this->applyStyleWidth();
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
		$height = '0';
		foreach ($this->getChildren() as $child) {
			$child->measureHeight();
			$height = Math::add($height, $child->getDimensions()->getOuterHeight());
		}
		$style = $this->getStyle();
		$height = Math::add($height, $style->getVerticalBordersWidth(), $style->getVerticalPaddingsWidth());
		$this->getDimensions()->setHeight($height);
		$this->applyStyleHeight();
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
		$rules = $this->getStyle()->getRules();
		$parent = $this->getParent();
		$top = $parent->getStyle()->getOffsetTop();
		// margin top inside inline and inline block doesn't affect relative to line top position
		// it only affects line margins
		$left = (string) $rules['margin-left'];
		if ($previous = $this->getPrevious()) {
			$left = Math::add($left, $previous->getOffset()->getLeft(), $previous->getDimensions()->getWidth(), $previous->getStyle()->getRules('margin-right'));
		} else {
			$left = Math::add($left, $parent->getStyle()->getOffsetLeft());
		}
		$this->getOffset()->setLeft($left);
		$this->getOffset()->setTop($top);
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
		$parent = $this->getParent();
		$this->getCoordinates()->setX(Math::add($parent->getCoordinates()->getX(), $this->getOffset()->getLeft()));
		if (!$parent instanceof InlineBox) {
			$this->getCoordinates()->setY(Math::add($parent->getCoordinates()->getY(), $this->getOffset()->getTop()));
		} else {
			$this->getCoordinates()->setY($this->getClosestLineBox()->getCoordinates()->getY());
		}
		foreach ($this->getChildren() as $child) {
			$child->measurePosition($afterPageDividing);
		}
		return $this;
	}

	public function __clone()
	{
		if ($this->element) {
			$this->element = clone $this->element;
		}
		$this->style = clone $this->style;
		$this->offset = clone $this->offset;
		$this->dimensions = clone $this->dimensions;
		$this->coordinates = clone $this->coordinates;
		$this->children = [];
	}
}
