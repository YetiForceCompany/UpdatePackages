<?php

declare(strict_types=1);
/**
 * Parser class.
 *
 * @package   YetiForcePDF\Html
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Html;

use YetiForcePDF\Layout\PageGroupBox;

/**
 * Class Parser.
 */
class Parser extends \YetiForcePDF\Base
{
	/**
	 * @var \DOMDocument
	 */
	protected $domDocument;
	/**
	 * @var string
	 */
	protected $html = '';
	/**
	 * @var array page groups with html content divided
	 */
	protected $htmlPageGroups = [];
	/**
	 * @var array
	 */
	protected $pageGroups = [];

	/**
	 * Cleanup html.
	 *
	 * @param string $html
	 * @param string $fromEncoding
	 *
	 * @return string
	 */
	protected function cleanUpHtml(string $html)
	{
		$html = preg_replace('/\r\n/', "\r", $html);
		return preg_replace('/\n/', "\r", $html);
	}

	/**
	 * Load html string.
	 *
	 * @param string $html
	 * @param string $fromEncoding
	 *
	 * @return \YetiForcePDF\Html\Parser
	 */
	public function loadHtml(string $html, string $fromEncoding = ''): self
	{
		$html = htmlspecialchars_decode($html, ENT_HTML5);
		$this->html = $this->cleanUpHtml($html);
		$this->html = mb_convert_encoding($this->html, 'HTML-ENTITIES', $fromEncoding);
		return $this;
	}

	/**
	 * Get html.
	 *
	 * @return string
	 */
	public function getHtml()
	{
		return $this->html;
	}

	/**
	 * Remove comment blocks.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public function removeComments(string $html)
	{
		return preg_replace('/<!--((?!-->))[\w\W]+-->/uUi', '', $html);
	}

	/**
	 * Divide html into page groups.
	 *
	 * @param string $html
	 *
	 * @return array
	 */
	public function getHtmlPageGroups(string $html)
	{
		$pageGroups = [];
		$matches = [];
		preg_match_all('/\<div\s+data-page-group\s?/ui', $html, $matches, PREG_OFFSET_CAPTURE);
		$matches = $matches[0];
		$groupsCount = \count($matches);
		for ($i = 0; $i < $groupsCount; ++$i) {
			$start = $matches[$i][1];
			if (isset($matches[$i + 1])) {
				$stop = $matches[$i + 1][1];
				$len = $stop - $start;
				$pageGroups[] = substr($html, $start, $len);
			} else {
				$pageGroups[] = substr($html, $start);
			}
		}
		if (empty($pageGroups)) {
			return [$html];
		}
		return $pageGroups;
	}

	/**
	 * Set page group options.
	 *
	 * @param PageGroupBox $root
	 * @param \DOMDocument $domDocument
	 *
	 * @return $this
	 */
	public function setGroupOptions(PageGroupBox $root, \DOMDocument $domDocument)
	{
		$childDomElement = $domDocument->documentElement->firstChild;
		if (!$childDomElement instanceof \DOMElement) {
			return $this;
		}
		if ($childDomElement->hasAttribute('data-format')) {
			$root->format = $childDomElement->getAttribute('data-format');
			if (!$root->format) {
				$root->format = 'A4';
			}
		}
		if ($childDomElement->hasAttribute('data-orientation')) {
			$root->orientation = $childDomElement->getAttribute('data-orientation');
			if (!$root->orientation) {
				$root->orientation = 'P';
			}
		}
		if ($childDomElement->hasAttribute('data-margin-left')) {
			$root->marginLeft = (float) $childDomElement->getAttribute('data-margin-left');
			if (!$root->marginLeft) {
				$root->marginLeft = 30;
			}
		}
		if ($childDomElement->hasAttribute('data-margin-right')) {
			$root->marginRight = (float) $childDomElement->getAttribute('data-margin-right');
			if (!$root->marginRight) {
				$root->marginRight = 30;
			}
		}
		if ($childDomElement->hasAttribute('data-margin-top')) {
			$root->marginTop = (float) $childDomElement->getAttribute('data-margin-top');
			if (!$root->marginTop) {
				$root->marginTop = 40;
			}
		}
		if ($childDomElement->hasAttribute('data-margin-bottom')) {
			$root->marginBottom = (float) $childDomElement->getAttribute('data-margin-bottom');
			if (!$root->marginBottom) {
				$root->marginBottom = 40;
			}
		}
		if ($childDomElement->hasAttribute('data-header-top')) {
			$root->headerTop = (float) $childDomElement->getAttribute('data-header-top');
			if (!$root->headerTop) {
				$root->headerTop = 10;
			}
		}
		if ($childDomElement->hasAttribute('data-footer-bottom')) {
			$root->footerBottom = (float) $childDomElement->getAttribute('data-footer-bottom');
			if (!$root->footerBottom) {
				$root->footerBottom = 10;
			}
		}
		return $this;
	}

	/**
	 * Convert loaded html to pdf objects.
	 */
	public function parse()
	{
		if ('' === $this->html) {
			return null;
		}
		$this->html = $this->removeComments($this->html);
		$this->htmlPageGroups = $this->getHtmlPageGroups($this->html);
		foreach ($this->htmlPageGroups as $groupIndex => $htmlPageGroup) {
			$domDocument = new \DOMDocument();
			$domDocument->encoding = 'UTF-8';
			$domDocument->strictErrorChecking = false;
			$domDocument->substituteEntities = false;
			$domDocument->recover = false;
			$domDocument->loadHTML('<div id="yetiforcepdf">' . $htmlPageGroup . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_NOWARNING | LIBXML_NOBLANKS | LIBXML_NOERROR);
			$pageGroup = (new PageGroupBox())
				->setDocument($this->document)
				->setRoot(true)
				->init();
			$pageGroup->format = $this->document->getDefaultFormat();
			$margins = $this->document->getDefaultMargins();
			$pageGroup->marginLeft = $margins['left'];
			$pageGroup->marginTop = $margins['top'];
			$pageGroup->marginRight = $margins['right'];
			$pageGroup->marginBottom = $margins['bottom'];
			$pageGroup->orientation = $this->document->getDefaultOrientation();
			$this->setGroupOptions($pageGroup, $domDocument);
			$page = $this->document->addPage($pageGroup->format, $pageGroup->orientation);
			$page->setPageNumber(1);
			$page->setGroup($groupIndex);
			$page->setMargins($pageGroup->marginLeft, $pageGroup->marginTop, $pageGroup->marginRight, $pageGroup->marginBottom);
			$rootElement = (new \YetiForcePDF\Html\Element())
				->setDocument($this->document)
				->setDOMElement($domDocument->documentElement);
			// root element must be defined before initialisation
			$rootElement->init();
			$pageGroup->setElement($rootElement);
			$pageGroup->setStyle($rootElement->parseStyle());

			$pageGroup->buildTree();
			$pageGroup->fixTables();
			$pageGroup->getStyle()->fixDomTree();
			$pageGroup->layout();
			$page->setBox($pageGroup);

			foreach ($this->document->getPages($groupIndex) as $page) {
				$page->getBox()->breakPageAfter();
			}
			foreach ($this->document->getPages($groupIndex) as $page) {
				$page->getBox()->spanAllRows();
			}
			foreach ($this->document->getPages($groupIndex) as $page) {
				$page->breakOverflow();
			}
			$page->getBox()->getStyle()->fixDomTree();
			$this->document->fixPageNumbers();
			foreach ($this->document->getPages($groupIndex) as $page) {
				$this->document->setCurrentPage($page);
				$children = [];
				$page->setUpAbsoluteBoxes();
				$page->getBox()->replacePageNumbers();
				$page->getBox()->getAllChildren($children);
				foreach ($children as $box) {
					if (!$box instanceof \YetiForcePDF\Layout\LineBox && $box->isRenderable()) {
						$page->getContentStream()->addRawContent($box->getInstructions());
					}
				}
			}
		}
	}
}
