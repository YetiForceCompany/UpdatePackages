<?php

declare(strict_types=1);
/**
 * TextBox class.
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
 * Class TextBox.
 */
class TextBox extends ElementBox implements BoxInterface
{
	/**
	 * @var string
	 */
	protected $text;

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		parent::init();
		$this->style = (new \YetiForcePDF\Style\Style())
			->setDocument($this->document)
			->setBox($this)
			->init();
		return $this;
	}

	/**
	 * Set text.
	 *
	 * @param string $text
	 *
	 * @return $this
	 */
	public function setText(string $text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * Get text.
	 *
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * Measure width.
	 *
	 * @return $this
	 */
	public function measureWidth()
	{
		$this->getDimensions()->setWidth($this->getStyle()->getFont()->getTextWidth($this->getText()));
		return $this;
	}

	/**
	 * Measure height.
	 *
	 * @return $this
	 */
	public function measureHeight()
	{
		$this->getDimensions()->setHeight($this->getStyle()->getFont()->getTextHeight($this->getText()));
		return $this;
	}

	/**
	 * Position.
	 *
	 * @return $this
	 */
	public function measureOffset()
	{
		$this->getOffset()->setLeft('0');
		$this->getOffset()->setTop('0');
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
		$this->getCoordinates()->setY(Math::add($parent->getCoordinates()->getY(), $this->getOffset()->getTop()));
		return $this;
	}

	public function __clone()
	{
		$this->style = clone $this->style;
		$this->offset = clone $this->offset;
		$this->dimensions = clone $this->dimensions;
		$this->coordinates = clone $this->coordinates;
		$this->children = [];
	}

	/**
	 * Get element PDF instructions to use in content stream.
	 *
	 * @return string
	 */
	public function getInstructions(): string
	{
		$style = $this->getStyle();
		$rules = $style->getRules();
		$graphicState = $this->style->getGraphicState();
		$graphicStateStr = '/' . $graphicState->getNumber() . ' gs';
		$font = $style->getFont();
		$fontStr = '/' . $font->getNumber() . ' ' . $font->getSize() . ' Tf';
		$coordinates = $this->getCoordinates();
		$pdfX = $coordinates->getPdfX();
		$pdfY = $coordinates->getPdfY();
		$baseLine = $style->getFont()->getDescender();
		$baseLineY = Math::sub($pdfY, $baseLine);
		$textWidth = $style->getFont()->getTextWidth($this->getText());
		$textHeight = $style->getFont()->getTextHeight();
		$textContent = $this->document->filterText($this->getText());
		$transform = $style->getTransformations($pdfX, $baseLineY);
		$element = [
			'q',
			$graphicStateStr,
			$transform,
			"{$rules['color'][0]} {$rules['color'][1]} {$rules['color'][2]} rg",
			'BT',
			$fontStr,
			"$textContent Tj",
			'ET',
			'Q',
		];
		$this->drawTextOutline = false;
		if ($this->drawTextOutline) {
			$element = array_merge($element, [
				'q',
				'1 w',
				'1 0 0 RG',
				"1 0 0 1 $pdfX $pdfY cm",
				"0 0 $textWidth $textHeight re",
				'S',
				'Q',
			]);
		}
		return implode("\n", $element);
	}
}
