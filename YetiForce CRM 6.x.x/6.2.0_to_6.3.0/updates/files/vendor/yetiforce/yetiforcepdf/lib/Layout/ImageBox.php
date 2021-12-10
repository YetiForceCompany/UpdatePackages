<?php

declare(strict_types=1);
/**
 * ImageBox class.
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
 * Class ImageBox.
 */
class ImageBox extends InlineBlockBox
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
		$style = $this->getStyle();
		if ('auto' === $style->getRules('width')) {
			$img = $style->getBackgroundImageStream();
			$width = $img->getWidth();
			if (Math::comp($width, $this->getParent()->getDimensions()->computeAvailableSpace()) > 0) {
				$width = $this->getParent()->getDimensions()->computeAvailableSpace();
			}
			if ('auto' !== $style->getRules('height')) {
				Math::setAccurate(true);
				$ratio = $img->getRatio();
				$width = Math::mul($ratio, $this->getDimensions()->getStyleHeight());
				Math::setAccurate(false);
			}
			$width = Math::add($width, $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth());
			$this->getDimensions()->setWidth($width);
		} else {
			$this->applyStyleWidth();
		}
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
		$style = $this->getStyle();
		$img = $style->getBackgroundImageStream();
		Math::setAccurate(true);
		$ratio = $img->getRatio();
		$height = Math::div($this->getDimensions()->getInnerWidth(), $ratio);
		Math::setAccurate(false);
		$height = Math::add($height, $style->getVerticalBordersWidth(), $style->getVerticalPaddingsWidth());
		$this->getDimensions()->setHeight($height);
		return $this;
	}
}
