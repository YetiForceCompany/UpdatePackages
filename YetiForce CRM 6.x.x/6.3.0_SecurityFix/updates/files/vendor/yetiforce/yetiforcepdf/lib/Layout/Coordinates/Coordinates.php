<?php

declare(strict_types=1);
/**
 * Coordinates class.
 *
 * @package   YetiForcePDF\Layout\Coordinates
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout\Coordinates;

use YetiForcePDF\Layout\Box;
use YetiForcePDF\Math;

/**
 * Class Coordinates.
 */
class Coordinates extends \YetiForcePDF\Base
{
	/**
	 * @var Box
	 */
	protected $box;
	/**
	 * Absolute X position inside html coordinate system.
	 *
	 * @var string
	 */
	protected $htmlX = '0';
	/**
	 * Absolute Y position inside html coordinate system.
	 *
	 * @var string
	 */
	protected $htmlY = '0';

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
	 * Set absolute html coordinates x position.
	 *
	 * @param float $x
	 *
	 * @return $this
	 */
	public function setX(string $x)
	{
		$this->htmlX = $x;
		return $this;
	}

	/**
	 * Get html X.
	 *
	 * @return string
	 */
	public function getX()
	{
		return $this->htmlX;
	}

	/**
	 * Get html Y.
	 *
	 * @return string
	 */
	public function getY()
	{
		return $this->htmlY;
	}

	/**
	 * Set absolute html coordinates y position.
	 *
	 * @param string $y
	 *
	 * @return $this
	 */
	public function setY(string $y)
	{
		$this->htmlY = $y;
		return $this;
	}

	/**
	 * Get pdf X coodrinates.
	 *
	 * @return string
	 */
	public function getPdfX()
	{
		return $this->htmlX;
	}

	/**
	 * Convert html to pdf y.
	 *
	 * @return string
	 */
	public function getPdfY()
	{
		$height = $this->box->getDimensions()->getHeight();
		$page = $this->document->getCurrentPage();
		return Math::sub($page->getOuterDimensions()->getHeight(), Math::add($this->htmlY, $height));
	}

	/**
	 * Get end Y - position at the end of box.
	 *
	 * @return string
	 */
	public function getEndY()
	{
		$box = $this->getBox();
		$height = $box->getDimensions()->getHeight();
		return Math::add($height, $this->htmlY);
	}
}
