<?php

declare(strict_types=1);
/**
 * TableCellBox class.
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
	 * Initial borders width - because borders are modified couple of times when rearranging.
	 *
	 * @var array
	 */
	protected $initialBordersWidths = [
		'top' => '0',
		'right' => '0',
		'bottom' => '0',
		'left' => '0',
	];

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
	 * @param bool $afterPageDividing
	 *
	 * @return $this
	 */
	public function measureWidth(bool $afterPageDividing = false)
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
	 * @param bool $afterPageDividing
	 *
	 * @return $this
	 */
	public function measureHeight(bool $afterPageDividing = false)
	{
		if ($this->wasCut() || $afterPageDividing) {
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
	 * @param string $afterPageDividing
	 *
	 * @return $this
	 */
	public function measureOffset(bool $afterPageDividing = false)
	{
		$parent = $this->getParent();
		$top = $parent->getStyle()->getOffsetTop();
		if (!$afterPageDividing) {
			// margin top inside inline and inline block doesn't affect relative to line top position
			// it only affects line margins
			$left = $this->getStyle()->getRules('margin-left');
			if ($previous = $this->getPrevious()) {
				$left = Math::add($left, $previous->getOffset()->getLeft(), $previous->getDimensions()->getWidth(), $previous->getStyle()->getRules('margin-right'));
			} else {
				$left = Math::add($left, $parent->getStyle()->getOffsetLeft());
			}
			$this->getOffset()->setLeft($left);
		}
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
		if (!$afterPageDividing) {
			$this->getCoordinates()->setX(Math::add($parent->getCoordinates()->getX(), $this->getOffset()->getLeft()));
		}
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

	/**
	 * Get initial borders width - because borders are modified couple of times when rearranging.
	 *
	 * @return array
	 */
	public function getInitialBordersWidths()
	{
		return $this->initialBordersWidths;
	}

	/**
	 * Get initial border width.
	 *
	 * @param string $which top, right, bottom, left
	 *
	 * @return string width
	 */
	public function getInitialBordersWidth(string $which)
	{
		return $this->initialBordersWidths[$which];
	}

	/**
	 * Set initial borders width - because borders are modified couple of times when rearranging.
	 *
	 * @param string $top
	 * @param string $right
	 * @param string $bottom
	 * @param string $left
	 *
	 * @return self
	 */
	public function setInitialBordersWidths(string $top, string $right, string $bottom, string $left)
	{
		$this->initialBordersWidths = [
			'top' => $top,
			'right' => $right,
			'bottom' => $bottom,
			'left' => $left,
		];

		return $this;
	}
}
