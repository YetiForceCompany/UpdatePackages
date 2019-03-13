<?php

declare(strict_types=1);
/**
 * InlineBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Html\Element;
use YetiForcePDF\Math;
use YetiForcePDF\Style\Style;

/**
 * Class InlineBox.
 */
class InlineBox extends ElementBox implements BoxInterface, BuildTreeInterface, AppendChildInterface
{
	/**
	 * @var \YetiForcePDF\Layout\TextBox
	 */
	protected $previousTextBox;
	/**
	 * Parent width cache.
	 *
	 * @var string
	 */
	protected $parentWidth ='0';
	/**
	 * Parent height cache.
	 *
	 * @var string
	 */
	protected $parentHeight ='0';

	/**
	 * Go up to Line box and clone and wrap element.
	 *
	 * @param Box $box
	 *
	 * @return Box
	 */
	public function cloneParent(Box $box)
	{
		if ($parent = $this->getParent()) {
			$clone = clone $this;
			$clone->getStyle()->setBox($clone);
			$clone->getDimensions()->setBox($clone);
			$clone->getOffset()->setBox($clone);
			$clone->getElement()->setBox($clone);
			$clone->getCoordinates()->setBox($clone);
			$clone->appendChild($box);
			if (!$parent instanceof LineBox) {
				$parent->cloneParent($clone);
			} else {
				$parent->appendChild($clone);
			}
		}
		return $box;
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendBlockBox($childDomElement, $element, $style, $parentBlock)
	{
		$box = (new BlockBox())
			->setDocument($this->document)
			->setParent($this)
			->setElement($element)
			->setStyle($style)
			->init();
		// if we add this child to parent box we loose parent inline styles if nested
		// so we need to wrap this box later and split lines at block element
		if (isset($this->getChildren()[0])) {
			$this->cloneParent($box);
		} else {
			$this->appendChild($box);
		}
		$box->getStyle()->init();
		$box->buildTree($box);
		return $box;
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendTableWrapperBox($childDomElement, $element, $style, $parentBlock)
	{
		$box = (new TableWrapperBox())
			->setDocument($this->document)
			->setParent($this)
			->setElement($element)
			->setStyle($style)
			->init();
		// if we add this child to parent box we loose parent inline styles if nested
		// so we need to wrap this box later and split lines at block element
		if (isset($this->getChildren()[0])) {
			$this->cloneParent($box);
		} else {
			$this->appendChild($box);
		}
		$box->getStyle()->init();
		// we don't want to build tree from here - we will build it from TableBox
		return $box;
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendInlineBlockBox($childDomElement, $element, $style, $parentBlock)
	{
		$box = (new InlineBlockBox())
			->setDocument($this->document)
			->setParent($this)
			->setElement($element)
			->setStyle($style)
			->init();
		// if we add this child to parent box we loose parent inline styles if nested
		// so we need to wrap this box later and split lines at block element
		if (isset($this->getChildren()[0])) {
			$this->cloneParent($box);
		} else {
			$this->appendChild($box);
		}
		$box->getStyle()->init();
		$box->buildTree($box);
		return $box;
	}

	/**
	 * {@inheritdoc}
	 */
	public function appendInlineBox($childDomElement, $element, $style, $parentBlock)
	{
		$box = (new self())
			->setDocument($this->document)
			->setParent($this)
			->setElement($element)
			->setStyle($style)
			->init();
		if (isset($this->getChildren()[0])) {
			$this->cloneParent($box);
		} else {
			$this->appendChild($box);
		}
		$box->getStyle()->init();
		$box->buildTree($parentBlock);
		return $box;
	}

	/**
	 * Create text.
	 *
	 * @param $content
	 *
	 * @return $this
	 */
	public function createText($content, bool $sameId = false)
	{
		if ($sameId && $this->previousTextBox) {
			$box = $this->previousTextBox->clone();
		} else {
			$box = (new TextBox())
				->setDocument($this->document)
				->setParent($this)
				->init();
		}
		$box->setText($content);
		$this->previousTextBox = $box;
		if (isset($this->getChildren()[0])) {
			$this->previousTextBox = $this->cloneParent($box);
		} else {
			$this->appendChild($box);
			$this->previousTextBox = $box;
		}
		return $box;
	}

	/**
	 * Get previous sibling inline-level element text.
	 *
	 * @return string|null
	 */
	protected function getPreviousText()
	{
		$closest = $this->getClosestLineBox()->getLastChild();
		$previousTop = $closest->getPrevious();
		if ($previousTop && $textBox = $previousTop->getFirstTextBox()) {
			return $textBox->getText();
		}
	}

	/**
	 * Add text.
	 *
	 * @param \DOMNode                           $childDomElement
	 * @param Element                            $element
	 * @param Style                              $style
	 * @param \YetiForcePDF\Layout\BlockBox|null $parentBlock
	 *
	 * @return $this
	 */
	public function appendText($childDomElement, $element = null, $style = null, $parentBlock = null)
	{
		$text = $childDomElement->textContent;
		$whiteSpace = $this->getStyle()->getRules('white-space');
		switch ($whiteSpace) {
			case 'normal':
			case 'nowrap':
				$text = preg_replace('/([\t ]+)?\r([\t ]+)?/u', "\r", $text);
				$text = preg_replace('/\r+/u', ' ', $text);
				$text = preg_replace('/\t+/u', ' ', $text);
				$text = preg_replace('/ +/u', ' ', $text);
				break;
		}
		if ($text !== '') {
			if ($whiteSpace === 'normal') {
				$words = preg_split('/ /u', $text, 0);
				$count = count($words);
				if ($count) {
					foreach ($words as $index => $word) {
						if ($word !== '') {
							$this->createText($word);
							$parent = $this->getParent();
							$anonymous = ($parent instanceof self && $parent->isAnonymous()) || $parent instanceof LineBox;
							if ($index + 1 !== $count || $anonymous) {
								$this->createText(' ', true);
							}
						} else {
							$this->createText(' ', true);
						}
					}
				} else {
					$this->createText(' ', true);
				}
			} elseif ($whiteSpace === 'nowrap') {
				$this->createText($text, true);
			}
		}
		return $this;
	}

	/**
	 * Measure width.
	 *
	 * @return $this
	 */
	public function measureWidth()
	{
		$style = $this->getStyle();
		if ($this->parentWidth === $this->getParent()->getDimensions()->getWidth() && $this->getDimensions()->getWidth()!==null) {
			if (!$this->isForMeasurement()) {
				$this->getDimensions()->setWidth(Math::add($style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth()));
			}
			return $this;
		}
		$this->parentWidth = $this->getParent()->getDimensions()->getWidth();
		$width = '0';
		if ($this->isForMeasurement()) {
			foreach ($this->getChildren() as $child) {
				$child->measureWidth();
				$width = Math::add($width, $child->getDimensions()->getOuterWidth());
			}
		}
		$width = Math::add($width, $style->getHorizontalBordersWidth(), $style->getHorizontalPaddingsWidth());
		$this->getDimensions()->setWidth($width);
		$this->applyStyleWidth();
		return $this;
	}

	/**
	 * Measure height.
	 *
	 * @return $this
	 */
	public function measureHeight()
	{
		if ($this->getDimensions()->getHeight()!==null) {
			return $this;
		}
		foreach ($this->getChildren() as $child) {
			$child->measureHeight();
		}
		$this->getDimensions()->setHeight(Math::add($this->getStyle()->getLineHeight(), $this->getStyle()->getVerticalPaddingsWidth()));
		$this->applyStyleHeight();
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
		$lineHeight = $this->getClosestLineBox()->getDimensions()->getHeight();
		if ($rules['vertical-align'] === 'bottom') {
			$top = Math::sub($lineHeight, $this->getDimensions()->getHeight());
		} elseif ($rules['vertical-align'] === 'top') {
			$top = '0';
		} elseif ($rules['vertical-align'] === 'middle' || $rules['vertical-align'] === 'baseline') {
			$height = $this->getDimensions()->getHeight();
			$top = Math::sub(Math::div($lineHeight, '2'), Math::div($height, '2'));
		}
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
		$parent = $this->getClosestLineBox();
		$this->getCoordinates()->setY(Math::add($parent->getCoordinates()->getY(), $this->getOffset()->getTop()));
		foreach ($this->getChildren() as $child) {
			$child->measurePosition();
		}
		return $this;
	}

	public function __clone()
	{
		$this->element = clone $this->element;
		$this->element->setBox($this);
		$this->style = clone $this->style;
		$this->style->setBox($this);
		$this->offset = clone $this->offset;
		$this->offset->setBox($this);
		$this->dimensions = clone $this->dimensions;
		$this->dimensions->setBox($this);
		$this->coordinates = clone $this->coordinates;
		$this->coordinates->setBox($this);
		$this->children = [];
	}

	public function addBackgroundColorInstructions(array $element, $pdfX, $pdfY, $width, $height)
	{
		if ($this->getStyle()->getRules('display') === 'none') {
			return $element;
		}
		$rules = $this->style->getRules();
		$graphicState = $this->style->getGraphicState();
		$graphicStateStr = '/' . $graphicState->getNumber() . ' gs';
		if ($rules['background-color'] !== 'transparent') {
			$bgColor = [
				'q',
				$graphicStateStr,
				"1 0 0 1 $pdfX $pdfY cm",
				"{$rules['background-color'][0]} {$rules['background-color'][1]} {$rules['background-color'][2]} rg",
				"0 0 $width $height re",
				'f',
				'Q'
			];
			$element = array_merge($element, $bgColor);
		}
		return $element;
	}

	/**
	 * Get element PDF instructions to use in content stream.
	 *
	 * @return string
	 */
	public function getInstructions(): string
	{
		$coordinates = $this->getCoordinates();
		$pdfX = $coordinates->getPdfX();
		$pdfY = $coordinates->getPdfY();
		$dimensions = $this->getDimensions();
		$width = $dimensions->getWidth();
		$height = $dimensions->getHeight();
		$element = [];
		$element = $this->addBackgroundColorInstructions($element, $pdfX, $pdfY, $width, $height);
		$element = $this->addBorderInstructions($element, $pdfX, $pdfY, $width, $height);
		return implode("\n", $element);
	}
}
