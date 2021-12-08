<?php

declare(strict_types=1);
/**
 * FontBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Objects\Font;

/**
 * Class FontBox.
 */
class FontBox extends BlockBox
{
	/**
	 * @var string
	 */
	protected $fontFamily = '';
	/**
	 * @var string
	 */
	protected $fontWeight = '';
	/**
	 * @var string
	 */
	protected $fontStyle = '';
	/**
	 * @var string
	 */
	protected $fontFile = '';
	/**
	 * {@inheritdoc}
	 */
	protected $absolute = true;
	/**
	 * @var bool
	 */
	protected $renderable = false;
	/**
	 * @var bool
	 */
	protected $forMeasurement = false;

	/**
	 * {@inheritdoc}
	 */
	public function measureWidth(bool $afterPageDividing = false)
	{
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureHeight(bool $afterPageDividing = false)
	{
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function measureOffset(bool $afterPageDividing = false)
	{
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function measurePosition(bool $afterPageDividing = false)
	{
		return $this;
	}

	/**
	 * Set font family.
	 *
	 * @param string $family
	 *
	 * @return $this
	 */
	public function setFontFamily(string $family)
	{
		$this->fontFamily = $family;
		return $this;
	}

	/**
	 * Get font family.
	 *
	 * @return string
	 */
	public function getFontFamily()
	{
		return $this->fontFamily;
	}

	/**
	 * Get font weight.
	 *
	 * @return string
	 */
	public function getFontWeight(): string
	{
		return $this->fontWeight;
	}

	/**
	 * Set font weight.
	 *
	 * @param string $fontWeight
	 *
	 * @return $this
	 */
	public function setFontWeight(string $fontWeight)
	{
		$this->fontWeight = $fontWeight;
		return $this;
	}

	/**
	 * Get font style.
	 *
	 * @return string
	 */
	public function getFontStyle(): string
	{
		return $this->fontStyle;
	}

	/**
	 * Set font style.
	 *
	 * @param string $fontStyle
	 *
	 * @return $this
	 */
	public function setFontStyle(string $fontStyle)
	{
		$this->fontStyle = $fontStyle;
		return $this;
	}

	/**
	 * Get font file.
	 *
	 * @return string
	 */
	public function getFontFile(): string
	{
		return $this->fontFile;
	}

	/**
	 * Set font file.
	 *
	 * @param string $fontFile
	 *
	 * @return $this
	 */
	public function setFontFile(string $fontFile)
	{
		$this->fontFile = $fontFile;
		return $this;
	}

	/**
	 * Load font.
	 *
	 * @return $this
	 */
	public function loadFont()
	{
		Font::addCustomFont($this->fontFamily, $this->fontWeight, $this->fontStyle, $this->fontFile);
		return $this;
	}
}
