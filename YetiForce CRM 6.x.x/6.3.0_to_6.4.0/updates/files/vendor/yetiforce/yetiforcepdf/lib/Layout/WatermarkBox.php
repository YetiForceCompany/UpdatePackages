<?php

declare(strict_types=1);
/**
 * WatermarkBox class.
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
 * Class WatermarkBox.
 */
class WatermarkBox extends BlockBox
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
		if (!$this->isRenderable()) {
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
		if (!$this->isRenderable()) {
			return $this;
		}
		return parent::measureHeight();
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureOffset(bool $afterPageDividing = false)
	{
		if (!$this->isRenderable()) {
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
		if (!$this->isRenderable()) {
			return $this;
		}
		$pageHeight = $this->document->getCurrentPage()->getDimensions()->getHeight();
		$boxHeight = $this->getDimensions()->getHeight();
		$top = Math::sub(Math::div($pageHeight, '2'), Math::div($boxHeight, '2'));
		$pageWidth = $this->document->getCurrentPage()->getDimensions()->getWidth();
		$boxWidth = $this->getDimensions()->getWidth();
		$left = Math::sub(Math::div($pageWidth, '2'), Math::div($boxWidth, '2'));
		$this->getCoordinates()->setX($left)->setY($top);
		foreach ($this->getChildren() as $child) {
			$child->measurePosition($afterPageDividing);
		}
		return $this;
	}
}
