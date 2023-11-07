<?php

declare(strict_types=1);
/**
 * Dimensions class.
 *
 * @package   YetiForcePDF\Layout\Dimensions
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout\Dimensions;

/**
 * Class Dimensions.
 */
class Dimensions extends \YetiForcePDF\Base
{
	/**
	 * @var string
	 */
	protected $width;
	/**
	 * Height initially must be null to figure out it was calculated already or not.
	 *
	 * @var string|null
	 */
	protected $height;

	/**
	 * Get width.
	 *
	 * @return string
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * Get height.
	 *
	 * @return string|null
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * Set width.
	 *
	 * @param string|null $width
	 *
	 * @return $this
	 */
	public function setWidth(string $width = null)
	{
		$this->width = $width;
		return $this;
	}

	/**
	 * Set height.
	 *
	 * @param string|null $height
	 *
	 * @return $this
	 */
	public function setHeight(string $height = null)
	{
		$this->height = $height;
		return $this;
	}
}
