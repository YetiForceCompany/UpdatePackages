<?php

declare(strict_types=1);
/**
 * HeaderBox class.
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
 * Class HeaderBox.
 */
class HeaderBox extends BlockBox
{
	/**
	 * {@inheritdoc}
	 */
	protected $absolute = true;

	/**
	 * {@inheritdoc}
	 */
	public function measureWidth(bool $afterPageDividing = false)
	{
		if (!$this->isDisplayable()) {
			return $this;
		}
		$horizontalMargins = $this->getStyle()->getHorizontalMarginsWidth();
		$pageWidth = $this->document->getCurrentPage()->getOuterDimensions()->getWidth();
		$width = Math::sub($pageWidth, $horizontalMargins);
		$this->getDimensions()->setWidth($width);
		$this->applyStyleWidth();
		foreach ($this->getChildren() as $child) {
			$child->measureWidth($afterPageDividing);
		}
		$this->divideLines();
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureHeight(bool $afterPageDividing = false)
	{
		if (!$this->isDisplayable()) {
			return $this;
		}
		return parent::measureHeight($afterPageDividing);
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureOffset(bool $afterPageDividing = false)
	{
		if (!$this->isDisplayable()) {
			return $this;
		}
		$top = '0';
		$left = '0';
		$marginTop = $this->getStyle()->getRules('margin-top');
		$top = Math::add($top, $marginTop);
		$left = Math::add($left, $this->getStyle()->getRules('margin-left'));
		$this->getOffset()->setTop($top);
		$this->getOffset()->setLeft($left);
		foreach ($this->getChildren() as $child) {
			$child->measureOffset($afterPageDividing);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function measurePosition(bool $afterPageDividing = false)
	{
		if (!$this->isDisplayable()) {
			return $this;
		}
		$marginTop = $this->getStyle()->getRules('margin-top');
		$marginLeft = $this->getStyle()->getRules('margin-left');
		$this->getCoordinates()->setX($marginLeft)->setY($marginTop);
		foreach ($this->getChildren() as $child) {
			$child->measurePosition($afterPageDividing);
		}
		return $this;
	}
}
