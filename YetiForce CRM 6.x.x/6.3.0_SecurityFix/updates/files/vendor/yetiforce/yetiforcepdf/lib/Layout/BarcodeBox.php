<?php

declare(strict_types=1);
/**
 * BarcodeBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use Milon\Barcode\DNS1D;

/**
 * Class BarcodeBox.
 */
class BarcodeBox extends InlineBlockBox
{
	/**
	 * @var string barcode type
	 */
	protected $type = 'EAN13';
	/**
	 * @var string barcode size
	 */
	protected $size = '3';
	/**
	 * @var string barcode height
	 */
	protected $height = '33';
	/**
	 * @var string code
	 */
	protected $code = '';

	/**
	 * Get barcode type.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Set barcode type.
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType(string $type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Get barcode size.
	 *
	 * @return string
	 */
	public function getSize(): string
	{
		return $this->size;
	}

	/**
	 * Set barcode size.
	 *
	 * @param string $size
	 *
	 * @return $this
	 */
	public function setSize(string $size)
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * Get barcode height.
	 *
	 * @return string
	 */
	public function getHeight(): string
	{
		return $this->height;
	}

	/**
	 * Set barcode height.
	 *
	 * @param string $height
	 *
	 * @return $this
	 */
	public function setHeight(string $height)
	{
		$this->height = $height;
		return $this;
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * Set code.
	 *
	 * @param string $code
	 *
	 * @return $this
	 */
	public function setCode(string $code)
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * Generate barcode image.
	 *
	 * @return $this
	 */
	public function generateBarcodeImage()
	{
		$barcode = new DNS1D();
		$barcode->setStorPath(__DIR__ . '/cache/');
		$image = 'data:image/png;base64,' . $barcode->getBarcodePNG($this->getCode(), $this->getType(), (int) $this->getSize(), (int) $this->getHeight());
		$this->getStyle()->setContent("background-image: url($image)");
		return $this;
	}
}
