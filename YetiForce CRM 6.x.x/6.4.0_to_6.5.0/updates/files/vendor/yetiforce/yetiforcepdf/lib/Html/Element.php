<?php

declare(strict_types=1);
/**
 * BoxDimensions class.
 *
 * @package   YetiForcePDF\Html
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Html;

/**
 * Class BoxDimensions.
 */
class Element extends \YetiForcePDF\Base
{
	/**
	 * @var Box
	 */
	protected $box;
	/**
	 * @var Box
	 */
	protected $parentBox;
	/**
	 * DOMElement tagName.
	 *
	 * @var string
	 */
	protected $name;
	/**
	 * @var \YetiForcePDF\Document
	 */
	protected $document;
	/**
	 * @var \DOMElement
	 */
	protected $domElement;
	/**
	 * Class names for element.
	 *
	 * @var string
	 */
	protected $classNames = '';

	/**
	 * Initialisation.
	 *
	 * @return $this
	 */
	public function init()
	{
		parent::init();
		if (isset($this->domElement->tagName)) {
			$this->name = $this->domElement->tagName;
		} else {
			$this->name = $this->domElement->nodeName;
		}
		return $this;
	}

	/**
	 * Set box for this element (element is always inside box).
	 *
	 * @param \YetiForcePDF\Html\Box $box
	 *
	 * @return $this
	 */
	public function setBox($box)
	{
		$this->box = $box;
		return $this;
	}

	/**
	 * Get box.
	 *
	 * @return \YetiForcePDF\Html\Box
	 */
	public function getBox()
	{
		return $this->box;
	}

	/**
	 * Get parent element (from parent box).
	 *
	 * @return mixed
	 */
	public function getParent()
	{
		if ($this->box) {
			if ($parentBox = $this->box->getParent()) {
				return $parentBox->getElement();
			}
		}
	}

	/**
	 * Set dom element (only for parsing dom tree - domElement should not be used anywhere else).
	 *
	 * @param $element
	 *
	 * @return \YetiForcePDF\Html\Element
	 */
	public function setDOMElement($element): self
	{
		$this->domElement = $element;
		return $this;
	}

	/**
	 * Get dom element.
	 *
	 * @return \DOMElement
	 */
	public function getDOMElement()
	{
		return $this->domElement;
	}

	/**
	 * Get element internal unique id.
	 *
	 * @return string
	 */
	public function getElementId(): string
	{
		return $this->elementId;
	}

	/**
	 * Attach classes.
	 *
	 * @return self
	 */
	public function attachClasses()
	{
		if ($this->domElement instanceof \DOMElement && $this->domElement->hasAttribute('class')) {
			$classNames = [];
			foreach (explode(' ', $this->domElement->getAttribute('class')) as $className) {
				if (trim($className)) {
					$classNames[] = '.' . $className;
				}
			}
			$this->setClassNames(implode(' ', $classNames));
		}
		return $this;
	}

	/**
	 * Parse element style.
	 *
	 * @return \YetiForcePDF\Style\Style
	 */
	public function parseStyle(): \YetiForcePDF\Style\Style
	{
		$styleStr = null;
		if ($this->domElement instanceof \DOMElement && $this->domElement->hasAttribute('style')) {
			$styleStr = $this->domElement->getAttribute('style');
		}
		$this->attachClasses();
		$style = (new \YetiForcePDF\Style\Style())
			->setDocument($this->document)
			->setElement($this)
			->setContent($styleStr);
		if ($this->box) {
			$style->setBox($this->box);
		}
		return $style->init();
	}

	/**
	 * Get element style.
	 *
	 * @return \YetiForcePDF\Style\Style
	 */
	public function getStyle(): \YetiForcePDF\Style\Style
	{
		return $this->style;
	}

	/**
	 * Is this text node?
	 *
	 * @return bool
	 */
	public function isTextNode()
	{
		if ($this->domElement instanceof \DOMText) {
			return true;
		}
		return false;
	}

	/**
	 * Set class names for element.
	 *
	 * @param string $classNames Class names for element
	 *
	 * @return self
	 */
	public function setClassNames(string $classNames)
	{
		$this->classNames = $classNames;

		return $this;
	}

	/**
	 * Get class names for element.
	 *
	 * @return string[]
	 */
	public function getClassNames()
	{
		return $this->classNames;
	}
}
