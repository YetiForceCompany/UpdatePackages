<?php

declare(strict_types=1);
/**
 * TableCellBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Math;

/**
 * Class TableCellBox.
 */
class TableCellBox extends BlockBox
{
	/**
	 * @var bool is column spanned?
	 */
	protected $spanned = false;

	/**
	 * Parent width cache.
	 *
	 * @var string
	 */
	protected $parentWidth = '0';

	/**
	 * Set column spanned.
	 *
	 * @param bool $spanned
	 *
	 * @return $this
	 */
	public function setSpanned(bool $spanned)
	{
		$this->spanned = $spanned;
		return $this;
	}

	/**
	 * Is column spanned with others?
	 *
	 * @return bool
	 */
	public function isSpanned()
	{
		return $this->spanned;
	}

	/**
	 * Measure width.
	 *
	 * @return $this
	 */
	public function measureWidth()
	{
		if ($this->parentWidth === $this->getClosestByType('TableBox')->getDimensions()->getWidth()) {
			return $this;
		}
		$this->parentWidth = $this->getClosestByType('TableBox')->getDimensions()->getWidth();
		foreach ($this->getChildren() as $child) {
			$child->measureWidth();
		}
		$this->divideLines();
		// do not set up width because it was set higher by TableBox measureWidth method
		return $this;
	}

	/**
	 * Measure height.
	 *
	 * @return $this
	 */
	public function measureHeight(bool $afterPageDividing = false)
	{
		if ($this->wasCut()) {
			return $this;
		}
		foreach ($this->getChildren() as $child) {
			$child->measureHeight();
		}
		return $this;
	}

	/**
	 * Position.
	 *
	 * @return $this
	 */
	public function measureOffset()
	{
		$rules = $this->getStyle()->getRules();
		$parent = $this->getParent();
		$top = $parent->getStyle()->getOffsetTop();
		// margin top inside inline and inline block doesn't affect relative to line top position
		// it only affects line margins
		$left = $rules['margin-left'];
		if ($previous = $this->getPrevious()) {
			$left = Math::add($left, $previous->getOffset()->getLeft(), $previous->getDimensions()->getWidth(), $previous->getStyle()->getRules('margin-right'));
		} else {
			$left = Math::add($left, $parent->getStyle()->getOffsetLeft());
		}
		$this->getOffset()->setLeft($left);
		$this->getOffset()->setTop($top);
		foreach ($this->getChildren() as $child) {
			$child->measureOffset();
		}
		return $this;
	}

	/**
	 * Position.
	 *
	 * @return $this
	 */
	public function measurePosition()
	{
		$parent = $this->getParent();
		$this->getCoordinates()->setX(Math::add($parent->getCoordinates()->getX(), $this->getOffset()->getLeft()));
		if (!$parent instanceof InlineBox) {
			$this->getCoordinates()->setY(Math::add($parent->getCoordinates()->getY(), $this->getOffset()->getTop()));
		} else {
			$this->getCoordinates()->setY($this->getClosestLineBox()->getCoordinates()->getY());
		}
		foreach ($this->getChildren() as $child) {
			$child->measurePosition();
		}
		return $this;
	}
}
