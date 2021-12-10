<?php

declare(strict_types=1);
/**
 * Box class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Html\Element;
use YetiForcePDF\Layout\Coordinates\Coordinates;
use YetiForcePDF\Layout\Coordinates\Offset;
use YetiForcePDF\Layout\Dimensions\BoxDimensions;
use YetiForcePDF\Math;
use YetiForcePDF\Style\Style;

/**
 * Class Box.
 */
class Box extends \YetiForcePDF\Base
{
	/**
	 * Id of this box (should be cloned to track inline wrapped elements).
	 *
	 * @var string
	 */
	protected $id;
	/**
	 * @var Box
	 */
	protected $parent;
	/**
	 * @var Box[]
	 */
	protected $children = [];
	/**
	 * @var Box
	 */
	protected $next;
	/**
	 * @var Box
	 */
	protected $previous;
	/** @var box dimensions */
	protected $dimensions;
	/**
	 * @var Coordinates
	 */
	protected $coordinates;
	/**
	 * @var Offset
	 */
	protected $offset;
	/**
	 * @var bool
	 */
	protected $root = false;
	/**
	 * @var Style
	 */
	protected $style;
	/**
	 * Anonymous inline element is created to wrap TextBox.
	 *
	 * @var bool
	 */
	protected $anonymous = false;
	/**
	 * @var bool do we need to measure this box ?
	 */
	protected $forMeasurement = true;
	/**
	 * @var bool is this element show up in view? take space?
	 */
	protected $renderable = true;
	/**
	 * @var array save state before it was unrenderable
	 */
	protected $renderableState = [];
	/**
	 * Is this box absolute positioned?
	 *
	 * @var bool
	 */
	protected $absolute = false;
	/**
	 * @var bool
	 */
	protected $cut = false;
	/**
	 * @var bool
	 */
	protected $displayable = true;
	/**
	 * Is this new group of pages?
	 *
	 * @var bool
	 */
	protected $pageGroup = false;
	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		parent::init();
		$this->id = uniqid();
		$this->dimensions = (new BoxDimensions())
			->setDocument($this->document)
			->setBox($this)
			->init();
		$this->coordinates = (new Coordinates())
			->setDocument($this->document)
			->setBox($this)
			->init();
		$this->offset = (new Offset())
			->setDocument($this->document)
			->setBox($this)
			->init();

		return $this;
	}

	/**
	 * Get box id (id might be cloned and then we can track cloned elements).
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get current box dom tree structure.
	 *
	 * @return \DOMElement
	 */
	public function getDOMTree()
	{
		return $this->domTree;
	}

	/**
	 * Set parent.
	 *
	 * @param \YetiForcePDF\Layout\Box|null $parent
	 *
	 * @return $this
	 */
	public function setParent(self $parent = null)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Get parent.
	 *
	 * @return \YetiForcePDF\Layout\Box
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Set next.
	 *
	 * @param \YetiForcePDF\Layout\Box|null $next
	 *
	 * @return $this
	 */
	public function setNext(self $next = null)
	{
		$this->next = $next;

		return $this;
	}

	/**
	 * Get next.
	 *
	 * @return \YetiForcePDF\Layout\Box
	 */
	public function getNext()
	{
		return $this->next;
	}

	/**
	 * Set previous.
	 *
	 * @param \YetiForcePDF\Layout\Box|null $previous
	 *
	 * @return $this
	 */
	public function setPrevious(self $previous = null)
	{
		$this->previous = $previous;

		return $this;
	}

	/**
	 * Get previous.
	 *
	 * @return \YetiForcePDF\Layout\Box
	 */
	public function getPrevious()
	{
		return $this->previous;
	}

	/**
	 * Set root - is this root element?
	 *
	 * @param bool $isRoot
	 *
	 * @return $this
	 */
	public function setRoot(bool $isRoot)
	{
		$this->root = $isRoot;

		return $this;
	}

	/**
	 * Is this root element?
	 *
	 * @return bool
	 */
	public function isRoot(): bool
	{
		return $this->root;
	}

	/**
	 * Is this box absolute positioned?
	 *
	 * @return bool
	 */
	public function isAbsolute()
	{
		return $this->absolute;
	}

	/**
	 * Set absolute - this box will be absolute positioned.
	 *
	 * @param bool $absolute
	 *
	 * @return $this
	 */
	public function setAbsolute(bool $absolute)
	{
		$this->absolute = $absolute;

		return $this;
	}

	/**
	 * Box was cut to next page.
	 *
	 * @return bool
	 */
	public function wasCut()
	{
		return $this->cut;
	}

	/**
	 * Set cut.
	 *
	 * @param int $cut
	 *
	 * @return $this
	 */
	public function setCut(int $cut)
	{
		$this->cut = $cut;

		return $this;
	}

	/**
	 * Is this element displayable.
	 *
	 * @return bool
	 */
	public function isDisplayable()
	{
		return $this->displayable;
	}

	/**
	 * Set displayable.
	 *
	 * @param bool $displayable
	 *
	 * @return $this
	 */
	public function setDisplayable(bool $displayable)
	{
		$allChildren = [];
		$this->getAllChildren($allChildren);
		foreach ($allChildren as $child) {
			$child->displayable = $displayable;
		}

		return $this;
	}

	/**
	 * Set style.
	 *
	 * @param \YetiForcePDF\Style\Style $style
	 * @param bool                      $init
	 *
	 * @return $this
	 */
	public function setStyle(Style $style, bool $init = true)
	{
		$this->style = $style;
		if ($element = $style->getElement()) {
			$element->setBox($this);
		}
		$style->setBox($this);
		if ($init) {
			$style->init();
		}

		return $this;
	}

	/**
	 * Get style.
	 *
	 * @return Style
	 */
	public function getStyle()
	{
		return $this->style;
	}

	/**
	 * Is this box anonymous.
	 *
	 * @return bool
	 */
	public function isAnonymous()
	{
		return $this->anonymous;
	}

	/**
	 * Set anonymous field.
	 *
	 * @param bool $anonymous
	 *
	 * @return $this
	 */
	public function setAnonymous(bool $anonymous)
	{
		$this->anonymous = $anonymous;

		return $this;
	}

	/**
	 * Set for measurement - enable or disable this box measurement.
	 *
	 * @param bool $forMeasure
	 * @param bool $forMeasurement
	 *
	 * @return $this
	 */
	public function setForMeasurement(bool $forMeasurement)
	{
		$allChildren = [];
		$this->getAllChildren($allChildren);
		foreach ($allChildren as $child) {
			$child->forMeasurement = $forMeasurement;
		}

		return $this;
	}

	/**
	 * Is this box available for measurement? or it should now have any width?
	 *
	 * @return bool
	 */
	public function isForMeasurement()
	{
		return $this->forMeasurement;
	}

	/**
	 * Save renderable state.
	 *
	 * @return $this
	 */
	protected function saveRenderableState()
	{
		$this->renderableState['styleRules'] = $this->getStyle()->getRules();
		$this->renderableState['width'] = $this->getDimensions()->getRawWidth();
		$this->renderableState['height'] = $this->getDimensions()->getRawHeight();

		return $this;
	}

	/**
	 * Restore renderable state.
	 *
	 * @return $this
	 */
	protected function restoreRenderableState()
	{
		$this->getStyle()->setRules($this->renderableState['styleRules']);
		if (isset($this->renderableState['width'])) {
			$this->getDimensions()->setWidth($this->renderableState['width']);
		}
		if (isset($this->renderableState['height'])) {
			$this->getDimensions()->setHeight($this->renderableState['height']);
		}

		return $this;
	}

	/**
	 * Hide this element - set width / height to 0 and remove all styles that will change width / height.
	 *
	 * @return $this
	 */
	protected function hide()
	{
		$this->getStyle()->setRules([
			'padding-top' => '0',
			'padding-right' => '0',
			'padding-bottom' => '0',
			'padding-left' => '0',
			'margin-top' => '0',
			'margin-right' => '0',
			'margin-bottom' => '0',
			'margin-left' => '0',
			'border-top-width' => '0',
			'border-right-width' => '0',
			'border-bottom-width' => '0',
			'border-left-width' => '0',
		]);
		$this->getDimensions()->setWidth('0');
		$this->getDimensions()->setHeight('0');

		return $this;
	}

	/**
	 * Set renderable.
	 *
	 * @param bool $renderable
	 * @param bool $forceUpdate
	 *
	 * @return $this
	 */
	public function setRenderable(bool $renderable = true, bool $forceUpdate = false)
	{
		$changed = $this->renderable !== $renderable;
		if (!$this->renderable && $renderable) {
			$this->restoreRenderableState();
		}
		if ($this->renderable && !$renderable) {
			$this->saveRenderableState();
			$this->hide();
		}
		if ($changed || $forceUpdate) {
			$this->renderable = $renderable;
			foreach ($this->getChildren() as $child) {
				$child->setRenderable($renderable, $forceUpdate);
			}
		}

		return $this;
	}

	/**
	 * Contain content - do we have some elements that are not whitespace characters?
	 *
	 * @return bool
	 */
	public function containContent()
	{
		if ($this instanceof TextBox && '' === trim($this->getTextContent())) {
			return false;
		}
		// we are not text node - traverse further
		$children = $this->getChildren();
		if (0 === \count($children) && !$this instanceof LineBox) {
			return true; // we are the content
		}
		if (0 === \count($children) && $this instanceof LineBox) {
			return false; // we are the content
		}
		$childrenContent = false;
		foreach ($children as $child) {
			$childrenContent = $childrenContent || $child->containContent();
		}

		return $childrenContent;
	}

	/**
	 * Is this element renderable?
	 *
	 * @return bool
	 */
	public function isRenderable()
	{
		return $this->renderable;
	}

	/**
	 * Append child box - line box can have only inline/block boxes - not line boxes!
	 *
	 * @param Box $box
	 *
	 * @return $this
	 */
	public function appendChild(self $box)
	{
		$box->setParent($this);
		$childrenCount = \count($this->children);
		if ($childrenCount > 0) {
			$previous = $this->children[$childrenCount - 1];
			$box->setPrevious($previous);
			$previous->setNext($box);
		} else {
			$box->setPrevious()->setNext();
		}
		$this->children[] = $box;

		return $this;
	}

	/**
	 * Remove child.
	 *
	 * @param $child
	 *
	 * @return Box
	 */
	public function removeChild(self $child)
	{
		$oldChildren = $this->children; // copy children
		$this->children = [];
		foreach ($oldChildren as $currentChild) {
			if ($currentChild !== $child) {
				$this->appendChild($currentChild);
			}
		}
		if ($child->getPrevious()) {
			if ($child->getNext()) {
				$child->getPrevious()->setNext($child->getNext());
			} else {
				$child->getPrevious()->setNext();
			}
		}
		if ($child->getNext()) {
			if ($child->getPrevious()) {
				$child->getNext()->setPrevious($child->getPrevious());
			} else {
				$child->getNext()->setPrevious();
			}
		}
		$child->setParent()->setPrevious()->setNext();

		return $child;
	}

	/**
	 * Just clear children without changing associations for them.
	 *
	 * @return $this
	 */
	public function clearChildren()
	{
		$this->children = [];

		return $this;
	}

	/**
	 * Remove all children.
	 *
	 * @return $this
	 */
	public function removeChildren()
	{
		foreach ($this->getChildren() as $child) {
			$child->removeChildren();
			$this->removeChild($child);
		}

		return $this;
	}

	/**
	 * Insert box before other box.
	 *
	 * @param \YetiForcePDF\Layout\Box $child
	 * @param \YetiForcePDF\Layout\Box $before
	 *
	 * @return $this
	 */
	public function insertBefore(self $child, self $before)
	{
		$currentChildren = $this->children; // copy children
		$this->children = [];
		foreach ($currentChildren as $currentChild) {
			if ($currentChild === $before) {
				$this->appendChild($child);
				$this->appendChild($currentChild);
			} else {
				$this->appendChild($currentChild);
			}
		}

		return $this;
	}

	/**
	 * Get children.
	 *
	 * @param bool $onlyRenderable
	 * @param bool $onlyForMeasurment
	 *
	 * @return Box[]
	 */
	public function getChildren(bool $onlyRenderable = false, bool $onlyForMeasurment = false): array
	{
		if (!$onlyRenderable && !$onlyForMeasurment) {
			return $this->children;
		}
		$children = [];
		foreach ($this->children as $box) {
			if ($onlyRenderable && $onlyForMeasurment && !($box->isRenderable() && $box->isForMeasurement())) {
				continue;
			}
			if ($onlyRenderable && !$box->isRenderable()) {
				continue;
			}
			if (!$box->isForMeasurement()) {
				continue;
			}
			$children[] = $box;
		}

		return $children;
	}

	/**
	 * Get all children.
	 *
	 * @param Box[] $allChildren
	 * @param bool  $withCurrent
	 *
	 * @return Box[]
	 */
	public function getAllChildren(&$allChildren = [], bool $withCurrent = true)
	{
		if ($withCurrent) {
			$allChildren[] = $this;
		}
		foreach ($this->getChildren() as $child) {
			$child->getAllChildren($allChildren);
		}

		return $allChildren;
	}

	/**
	 * Iterate all children.
	 *
	 * @param callable $fn
	 * @param bool     $reverse
	 * @param bool     $deep
	 *
	 * @return $this
	 */
	public function iterateChildren(callable $fn, bool $reverse = false, bool $deep = true)
	{
		$allChildren = [];
		if ($deep) {
			$this->getAllChildren($allChildren);
		} else {
			$allChildren = $this->getChildren();
		}
		if ($reverse) {
			$allChildren = array_reverse($allChildren);
		}
		foreach ($allChildren as $child) {
			if (false === $fn($child)) {
				break;
			}
		}

		return $this;
	}

	/**
	 * Get boxes by type.
	 *
	 * @param string      $shortClassName
	 * @param string|null $until
	 *
	 * @return array
	 */
	public function getBoxesByType(string $shortClassName, string $until = '')
	{
		$boxes = [];
		$untilWas = 0;
		$allChildren = [];
		$this->getAllChildren($allChildren);
		foreach ($allChildren as $child) {
			$reflectShortClassName = (new \ReflectionClass($child))->getShortName();
			if ($reflectShortClassName === $shortClassName) {
				$boxes[] = $child;
			}
			if ($reflectShortClassName === $until) {
				++$untilWas;
			}
			if ($reflectShortClassName === $until && 2 === $untilWas) {
				break;
			}
		}

		return $boxes;
	}

	/**
	 * Get closest parent box by type.
	 *
	 * @param string $className
	 *
	 * @return Box
	 */
	public function getClosestByType(string $className)
	{
		if ('YetiForce' !== substr($className, 0, 9)) {
			$className = 'YetiForcePDF\\Layout\\' . $className;
		}
		if ($this instanceof $className) {
			return $this;
		}
		if ($this->getParent() instanceof $className) {
			return $this->getParent();
		}
		if ($this->getParent()->isRoot()) {
			return null;
		}

		return $this->getParent()->getClosestByType($className);
	}

	/**
	 * Do we have children?
	 *
	 * @return bool
	 */
	public function hasChildren()
	{
		return isset($this->children[0]); // faster than count
	}

	/**
	 * Get first child.
	 *
	 * @return \YetiForcePDF\Layout\Box|null
	 */
	public function getFirstChild()
	{
		if (isset($this->children[0])) {
			return $this->children[0];
		}
	}

	/**
	 * Get last child.
	 *
	 * @return \YetiForcePDF\Layout\Box|null
	 */
	public function getLastChild()
	{
		if ($count = \count($this->children)) {
			return $this->children[$count - 1];
		}
	}

	/**
	 * Get closest line box.
	 *
	 * @return \YetiForcePDF\Layout\LineBox
	 */
	public function getClosestLineBox()
	{
		$parent = $this->getParent();
		if ($parent instanceof LineBox) {
			return $parent;
		}

		return $parent->getClosestLineBox();
	}

	/**
	 * Get closet box that is not a LineBox.
	 *
	 * @return \YetiForcePDF\Layout\Box
	 */
	public function getClosestBox()
	{
		$parent = $this->getParent();
		if (!$parent instanceof LineBox) {
			return $parent;
		}

		return $parent->getClosestBox();
	}

	/**
	 * Get dimensions.
	 *
	 * @return BoxDimensions
	 */
	public function getDimensions()
	{
		return $this->dimensions;
	}

	/**
	 * Get coordinates.
	 *
	 * @return Coordinates
	 */
	public function getCoordinates()
	{
		return $this->coordinates;
	}

	/**
	 * Shorthand for offset.
	 *
	 * @return Offset
	 */
	public function getOffset(): Offset
	{
		return $this->offset;
	}

	/**
	 * Get first root child - closest parent that are child of root node.
	 *
	 * @return Box
	 */
	public function getFirstRootChild()
	{
		if ($this->isRoot()) {
			return $this;
		}
		if (null !== $this->getParent()) {
			$box = $this->getParent();
			if ($box->isRoot()) {
				return $this;
			}

			return $box->getFirstRootChild();
		}
	}

	/**
	 * Get text content from current and all nested boxes.
	 *
	 * @return string
	 */
	public function getTextContent()
	{
		$textContent = '';
		$allChildren = [];
		$this->getAllChildren($allChildren);
		foreach ($allChildren as $box) {
			if ($box instanceof TextBox) {
				$textContent .= $box->getText();
			}
		}

		return $textContent;
	}

	/**
	 * Get first child text box.
	 *
	 * @return \YetiForcePDF\Layout\TextBox|null
	 */
	public function getFirstTextBox()
	{
		if ($this instanceof TextBox) {
			return $this;
		}
		foreach ($this->getChildren() as $child) {
			if ($child instanceof TextBox) {
				return $child;
			}

			return $child->getFirstTextBox();
		}
	}

	/**
	 * Should we break page after this element?
	 *
	 * @return bool
	 */
	public function shouldBreakPage()
	{
		return false; // only block boxes should break the page
	}

	/**
	 * Set marker that this is a new group of pages and should have new numbering.
	 *
	 * @param bool $pageGroup
	 *
	 * @return $this
	 */
	public function setPageGroup(bool $pageGroup)
	{
		$this->pageGroup = true;

		return $this;
	}

	/**
	 * Get force page number.
	 *
	 * @return int
	 */
	public function getPageGroup()
	{
		return $this->pageGroup;
	}

	/**
	 * Set option (page group for example).
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setOption(string $name, string $value)
	{
		$this->options[$name] = $value;

		return $this;
	}

	/**
	 * Get option.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getOption(string $name)
	{
		if (isset($this->options[$name])) {
			return $this->options[$name];
		}

		return '';
	}

	/**
	 * Get height from style.
	 *
	 * @return $this
	 */
	public function applyStyleWidth()
	{
		$styleWidth = $this->getDimensions()->getStyleWidth();
		if (null !== $styleWidth) {
			$this->getDimensions()->setWidth($styleWidth);
		}

		return $this;
	}

	/**
	 * Get height from style.
	 *
	 * @return $this
	 */
	public function applyStyleHeight()
	{
		$height = $this->getStyle()->getRules('height');
		if ('auto' === $height) {
			return $this;
		}
		$percentPos = strpos($height, '%');
		if (false !== $percentPos) {
			$heightInPercent = substr($height, 0, $percentPos);
			$parentDimensions = $this->getParent()->getDimensions();
			if (null !== $parentDimensions->getHeight()) {
				$parentHeight = $this->getParent()->getDimensions()->getInnerHeight();
				if ($parentHeight) {
					$calculatedHeight = Math::mul(Math::div($parentHeight, '100'), $heightInPercent);
					$this->getDimensions()->setHeight($calculatedHeight);

					return $this;
				}
			}

			return $this;
		}
		$this->getDimensions()->setHeight($height);

		return $this;
	}

	/**
	 * Fix offsets inside lines where text-align !== 'left'.
	 *
	 * @return $this
	 */
	public function alignText()
	{
		if ($this instanceof LineBox) {
			$textAlign = $this->getParent()->getStyle()->getRules('text-align');
			if ('right' === $textAlign) {
				$offset = Math::sub($this->getDimensions()->computeAvailableSpace(), $this->getChildrenWidth());
				foreach ($this->getChildren() as $childBox) {
					$childBox->getOffset()->setLeft(Math::add($childBox->getOffset()->getLeft(), $offset));
				}
			} elseif ('center' === $textAlign) {
				$offset = Math::sub(Math::div($this->getDimensions()->computeAvailableSpace(), '2'), Math::div($this->getChildrenWidth(), '2'));
				foreach ($this->getChildren() as $childBox) {
					$childBox->getOffset()->setLeft(Math::add($childBox->getOffset()->getLeft(), $offset));
				}
			}
		} else {
			foreach ($this->getChildren() as $child) {
				$child->alignText();
			}
		}

		return $this;
	}

	/**
	 * Add border instructions.
	 *
	 * @param array  $element
	 * @param string $pdfX
	 * @param string $pdfY
	 * @param string $width
	 * @param string $height
	 *
	 * @return array
	 */
	protected function addBorderInstructions(array $element, string $pdfX, string $pdfY, string $width, string $height)
	{
		if ('none' === $this->getStyle()->getRules('display')) {
			return $element;
		}
		$style = $this->style;
		$graphicState = $this->style->getGraphicState();
		$graphicStateStr = '/' . $graphicState->getNumber() . ' gs';
		$x1 = '0';
		$x2 = $width;
		$y1 = $height;
		$y2 = '0';
		$element[] = '% start border';
		if ($style->getRules('border-top-width') && 'none' !== $style->getRules('border-top-style') && 'transparent' !== $style->getRules('border-top-color')) {
			$path = implode(" l\n", [
				implode(' ', [$x2, $y1]),
				implode(' ', [Math::sub($x2, $style->getRules('border-right-width')), Math::sub($y1, $style->getRules('border-top-width'))]),
				implode(' ', [Math::add($x1, $style->getRules('border-left-width')), Math::sub($y1, $style->getRules('border-top-width'))]),
				implode(' ', [$x1, $y1]),
			]);
			$borderTop = [
				'q',
				$graphicStateStr,
				"{$style->getRules('border-top-color')[0]} {$style->getRules('border-top-color')[1]} {$style->getRules('border-top-color')[2]} rg",
				"1 0 0 1 $pdfX $pdfY cm",
				"$x1 $y1 m", // move to start point
				$path . ' l h',
				'f',
				'Q',
			];
			$element = array_merge($element, $borderTop);
		}
		if ($style->getRules('border-right-width') && 'none' !== $style->getRules('border-right-style') && 'transparent' !== $style->getRules('border-right-color')) {
			$path = implode(" l\n", [
				implode(' ', [$x2, $y2]),
				implode(' ', [Math::sub($x2, $style->getRules('border-right-width')), Math::add($y2, $style->getRules('border-bottom-width'))]),
				implode(' ', [Math::sub($x2, $style->getRules('border-right-width')), Math::sub($y1, $style->getRules('border-top-width'))]),
				implode(' ', [$x2, $y1]),
			]);
			$borderTop = [
				'q',
				$graphicStateStr,
				"1 0 0 1 $pdfX $pdfY cm",
				"{$style->getRules('border-right-color')[0]} {$style->getRules('border-right-color')[1]} {$style->getRules('border-right-color')[2]} rg",
				"$x2 $y1 m",
				$path . ' l h',
				'f',
				'Q',
			];
			$element = array_merge($element, $borderTop);
		}
		if ($style->getRules('border-bottom-width') && 'none' !== $style->getRules('border-bottom-style') && 'transparent' !== $style->getRules('border-bottom-color')) {
			$path = implode(" l\n", [
				implode(' ', [$x2, $y2]),
				implode(' ', [Math::sub($x2, $style->getRules('border-right-width')), Math::add($y2, $style->getRules('border-bottom-width'))]),
				implode(' ', [Math::add($x1, $style->getRules('border-left-width')), Math::add($y2, $style->getRules('border-bottom-width'))]),
				implode(' ', [$x1, $y2]),
			]);
			$borderTop = [
				'q',
				$graphicStateStr,
				"1 0 0 1 $pdfX $pdfY cm",
				"{$style->getRules('border-bottom-color')[0]} {$style->getRules('border-bottom-color')[1]} {$style->getRules('border-bottom-color')[2]} rg",
				"$x1 $y2 m",
				$path . ' l h',
				'f',
				'Q',
			];
			$element = array_merge($element, $borderTop);
		}
		if ($style->getRules('border-left-width') && 'none' !== $style->getRules('border-left-style') && 'transparent' !== $style->getRules('border-left-color')) {
			$path = implode(" l\n", [
				implode(' ', [Math::add($x1, $style->getRules('border-left-width')), Math::sub($y1, $style->getRules('border-top-width'))]),
				implode(' ', [Math::add($x1, $style->getRules('border-left-width')), Math::add($y2, $style->getRules('border-bottom-width'))]),
				implode(' ', [$x1, $y2]),
				implode(' ', [$x1, $y1]),
			]);
			$borderTop = [
				'q',
				$graphicStateStr,
				"1 0 0 1 $pdfX $pdfY cm",
				"{$style->getRules('border-left-color')[0]} {$style->getRules('border-left-color')[1]} {$style->getRules('border-left-color')[2]} rg",
				"$x1 $y1 m",
				$path . ' l h',
				'f',
				'Q',
			];
			$element = array_merge($element, $borderTop);
		}
		$element[] = '% end border';

		return $element;
	}

	/**
	 * Clone this element along with the children elements.
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return Box
	 */
	public function cloneWithChildren()
	{
		$newBox = $this->clone();
		$newBox->getCoordinates()->setBox($newBox);
		$newBox->getDimensions()->setBox($newBox);
		$newBox->getOffset()->setBox($newBox);
		$newBox->getStyle()->setBox($newBox);
		$newBox->clearChildren();
		foreach ($this->getChildren() as $child) {
			$clonedChild = $child->cloneWithChildren();
			$newBox->appendChild($clonedChild->getParent()->removeChild($clonedChild));
		}

		return $newBox;
	}

	public function clone()
	{
		$newBox = clone $this;
		$newBox->dimensions->setBox($newBox);
		$newBox->coordinates->setBox($newBox);
		$newBox->offset->setBox($newBox);
		$newBox->style->setBox($newBox);

		return $newBox;
	}

	public function __clone()
	{
		if (isset($this->element)) {
			$this->element = clone $this->element;
			$this->element->setBox($this);
		}
		$this->dimensions = clone $this->dimensions;
		$this->dimensions->setBox($this);
		$this->coordinates = clone $this->coordinates;
		$this->coordinates->setBox($this);
		$this->offset = clone $this->offset;
		$this->offset->setBox($this);
		$this->style = $this->style->clone($this);
	}
}
