<?php

declare(strict_types=1);
/**
 * Document class.
 *
 * @package   YetiForcePDF
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace YetiForcePDF;

use YetiForcePDF\Layout\FooterBox;
use YetiForcePDF\Layout\HeaderBox;
use YetiForcePDF\Layout\WatermarkBox;
use YetiForcePDF\Objects\Meta;
use YetiForcePDF\Objects\PdfObject;

/**
 * Class Document.
 */
class Document
{
	/**
	 * Actual id auto incremented.
	 *
	 * @var int
	 */
	protected $actualId = 0;
	/**
	 * Main output buffer / content for pdf file.
	 *
	 * @var string
	 */
	protected $buffer = '';
	/**
	 * Main entry point - root element.
	 *
	 * @var \YetiForcePDF\Catalog
	 */
	protected $catalog;
	/**
	 * Pages dictionary.
	 *
	 * @var Pages
	 */
	protected $pagesObject;
	/**
	 * Current page object.
	 *
	 * @var Page
	 */
	protected $currentPageObject;
	/**
	 * @var string default page format
	 */
	protected $defaultFormat = 'A4';
	/**
	 * @var string default page orientation
	 */
	protected $defaultOrientation = \YetiForcePDF\Page::ORIENTATION_PORTRAIT;

	/**
	 * @var Page[] all pages in the document
	 */
	protected $pages = [];
	/**
	 * Default page margins.
	 *
	 * @var array
	 */
	protected $defaultMargins = [
		'left' => 40,
		'top' => 40,
		'right' => 40,
		'bottom' => 40,
	];
	/**
	 * All objects inside document.
	 *
	 * @var \YetiForcePDF\Objects\PdfObject[]
	 */
	protected $objects = [];
	/**
	 * @var \YetiForcePDF\Html\Parser
	 */
	protected $htmlParser;
	/**
	 * Fonts data.
	 *
	 * @var array
	 */
	protected $fontsData = [];
	/**
	 * @var array
	 */
	protected $fontInstances = [];
	/**
	 * Actual font id.
	 *
	 * @var int
	 */
	protected $actualFontId = 0;
	/**
	 * Actual graphic state id.
	 *
	 * @var int
	 */
	protected $actualGraphicStateId = 0;
	/**
	 * @var bool
	 */
	protected $debugMode = false;
	/**
	 * @var HeaderBox|null
	 */
	protected $header;
	/**
	 * @var FooterBox|null
	 */
	protected $footer;
	/**
	 * @var WatermarkBox|null
	 */
	protected $watermark;
	/**
	 * @var Meta
	 */
	protected $meta;
	/**
	 * @var bool
	 */
	protected $parsed = false;
	/**
	 * Characters int values cache for fonts.
	 *
	 * @var array
	 */
	protected $ordCache = [];
	/**
	 * Css selectors like classes ids.
	 *
	 * @var array
	 */
	protected $cssSelectors = [];

	/**
	 * Are we debugging?
	 *
	 * @return bool
	 */
	public function inDebugMode()
	{
		return $this->debugMode;
	}

	/**
	 * Is document already parsed?
	 *
	 * @return bool
	 */
	public function isParsed()
	{
		return $this->parsed;
	}

	/**
	 * Initialisation.
	 *
	 * @return $this
	 */
	public function init()
	{
		$this->catalog = (new \YetiForcePDF\Catalog())->setDocument($this)->init();
		$this->pagesObject = $this->catalog->addChild((new Pages())->setDocument($this)->init());
		$this->meta = (new Meta())->setDocument($this)->init();

		return $this;
	}

	/**
	 * Set default page format.
	 *
	 * @param string $defaultFormat
	 *
	 * @return $this
	 */
	public function setDefaultFormat(string $defaultFormat)
	{
		$this->defaultFormat = $defaultFormat;
		foreach ($this->pages as $page) {
			$page->setFormat($defaultFormat);
		}

		return $this;
	}

	/**
	 * Set default page orientation.
	 *
	 * @param string $defaultOrientation
	 *
	 * @return $this
	 */
	public function setDefaultOrientation(string $defaultOrientation)
	{
		$this->defaultOrientation = $defaultOrientation;
		foreach ($this->pages as $page) {
			$page->setOrientation($defaultOrientation);
		}

		return $this;
	}

	/**
	 * Set default page margins.
	 *
	 * @param float $left
	 * @param float $top
	 * @param float $right
	 * @param float $bottom
	 *
	 * @return $this
	 */
	public function setDefaultMargins(float $left, float $top, float $right, float $bottom)
	{
		$this->defaultMargins = [
			'left' => $left,
			'top' => $top,
			'right' => $right,
			'bottom' => $bottom,
			'horizontal' => $left + $right,
			'vertical' => $top + $bottom,
		];
		foreach ($this->pages as $page) {
			$page->setMargins($left, $top, $right, $bottom);
		}

		return $this;
	}

	/**
	 * Set default left margin.
	 *
	 * @param float $left
	 */
	public function setDefaultLeftMargin(float $left)
	{
		$this->defaultMargins['left'] = $left;
		foreach ($this->pages as $page) {
			$page->setMargins($this->defaultMargins['left'], $this->defaultMargins['top'], $this->defaultMargins['right'], $this->defaultMargins['bottom']);
		}

		return $this;
	}

	/**
	 * Set default top margin.
	 *
	 * @param float $left
	 * @param float $top
	 */
	public function setDefaultTopMargin(float $top)
	{
		$this->defaultMargins['top'] = $top;
		foreach ($this->pages as $page) {
			$page->setMargins($this->defaultMargins['left'], $this->defaultMargins['top'], $this->defaultMargins['right'], $this->defaultMargins['bottom']);
		}

		return $this;
	}

	/**
	 * Set default right margin.
	 *
	 * @param float $left
	 * @param float $right
	 */
	public function setDefaultRightMargin(float $right)
	{
		$this->defaultMargins['right'] = $right;
		foreach ($this->pages as $page) {
			$page->setMargins($this->defaultMargins['left'], $this->defaultMargins['top'], $this->defaultMargins['right'], $this->defaultMargins['bottom']);
		}

		return $this;
	}

	/**
	 * Set default bottom margin.
	 *
	 * @param float $left
	 * @param float $bottom
	 */
	public function setDefaultBottomMargin(float $bottom)
	{
		$this->defaultMargins['bottom'] = $bottom;
		foreach ($this->pages as $page) {
			$page->setMargins($this->defaultMargins['left'], $this->defaultMargins['top'], $this->defaultMargins['right'], $this->defaultMargins['bottom']);
		}

		return $this;
	}

	/**
	 * Get meta.
	 *
	 * @return Meta
	 */
	public function getMeta()
	{
		return $this->meta;
	}

	/**
	 * Get actual id for newly created object.
	 *
	 * @return int
	 */
	public function getActualId()
	{
		return ++$this->actualId;
	}

	/**
	 * Get actual id for newly created font.
	 *
	 * @return int
	 */
	public function getActualFontId(): int
	{
		return ++$this->actualFontId;
	}

	/**
	 * Get actual id for newly created graphic state.
	 *
	 * @return int
	 */
	public function getActualGraphicStateId(): int
	{
		return ++$this->actualGraphicStateId;
	}

	/**
	 * Set font.
	 *
	 * @param string                     $family
	 * @param string                     $weight
	 * @param string                     $style
	 * @param \YetiForcePDF\Objects\Font $fontInstance
	 *
	 * @return $this
	 */
	public function setFontInstance(string $family, string $weight, string $style, Objects\Font $fontInstance)
	{
		$this->fontInstances[$family][$weight][$style] = $fontInstance;

		return $this;
	}

	/**
	 * Get font instance.
	 *
	 * @param string $family
	 * @param string $weight
	 * @param string $style
	 *
	 * @return \YetiForcePDF\Objects\Font|null
	 */
	public function getFontInstance(string $family, string $weight, string $style)
	{
		if (!empty($this->fontInstances[$family][$weight][$style])) {
			return $this->fontInstances[$family][$weight][$style];
		}

		return null;
	}

	/**
	 * Get all font instances.
	 *
	 * @return \YetiForcePDF\Objects\Font[]
	 */
	public function getAllFontInstances()
	{
		$instances = [];
		foreach ($this->fontInstances as $weights) {
			foreach ($weights as $styles) {
				foreach ($styles as $instance) {
					$instances[] = $instance;
				}
			}
		}

		return $instances;
	}

	/**
	 * Set font information.
	 *
	 * @param string                 $family
	 * @param string                 $weight
	 * @param string                 $style
	 * @param \FontLib\TrueType\File $font
	 *
	 * @return $this
	 */
	public function setFontData(string $family, string $weight, string $style, \FontLib\TrueType\File $font)
	{
		if (empty($this->fontsData[$family][$weight][$style])) {
			$this->fontsData[$family][$weight][$style] = $font;
		}

		return $this;
	}

	/**
	 * Get font data.
	 *
	 * @param string $family
	 * @param string $weight
	 * @param string $style
	 *
	 * @return \FontLib\Font|null
	 */
	public function getFontData(string $family, string $weight, string $style)
	{
		if (!empty($this->fontsData[$family][$weight][$style])) {
			return $this->fontsData[$family][$weight][$style];
		}

		return null;
	}

	/**
	 * Add fonts from json.
	 *
	 * @param array $fonts
	 */
	public static function addFonts(array $fonts)
	{
		return \YetiForcePDF\Objects\Font::loadFromArray($fonts);
	}

	/**
	 * Get pages object.
	 *
	 * @return \YetiForcePDF\Pages
	 */
	public function getPagesObject(): Pages
	{
		return $this->pagesObject;
	}

	/**
	 * Get default page format.
	 *
	 * @return string
	 */
	public function getDefaultFormat()
	{
		return $this->defaultFormat;
	}

	/**
	 * Get default page orientation.
	 *
	 * @return string
	 */
	public function getDefaultOrientation()
	{
		return $this->defaultOrientation;
	}

	/**
	 * Get default margins.
	 *
	 * @return array
	 */
	public function getDefaultMargins()
	{
		return $this->defaultMargins;
	}

	/**
	 * Set header.
	 *
	 * @param HeaderBox $header
	 *
	 * @return $this
	 */
	public function setHeader(HeaderBox $header)
	{
		if ($header->getParent()) {
			$header = $header->getParent()->removeChild($header);
		}
		$this->header = $header;

		return $this;
	}

	/**
	 * Get header.
	 *
	 * @return HeaderBox|null
	 */
	public function getHeader()
	{
		return $this->header;
	}

	/**
	 * Set watermark.
	 *
	 * @param WatermarkBox $watermark
	 *
	 * @return $this
	 */
	public function setWatermark(WatermarkBox $watermark)
	{
		if ($watermark->getParent()) {
			$watermark = $watermark->getParent()->removeChild($watermark);
		}
		$this->watermark = $watermark;

		return $this;
	}

	/**
	 * Get watermark.
	 *
	 * @return WatermarkBox|null
	 */
	public function getWatermark()
	{
		return $this->watermark;
	}

	/**
	 * Set footer.
	 *
	 * @param FooterBox $footer
	 *
	 * @return $this
	 */
	public function setFooter(FooterBox $footer)
	{
		if ($footer->getParent()) {
			$footer = $footer->getParent()->removeChild($footer);
		}
		$this->footer = $footer;

		return $this;
	}

	/**
	 * Get footer.
	 *
	 * @return FooterBox|null
	 */
	public function getFooter()
	{
		return $this->footer;
	}

	/**
	 * Add page to the document.
	 *
	 * @param string    $format      - optional format 'A4' for example
	 * @param string    $orientation - optional orientation 'P' or 'L'
	 * @param Page|null $page        - we can add cloned page or page from other document too
	 * @param Page|null $after       - add page after this page
	 *
	 * @return \YetiForcePDF\Page
	 */
	public function addPage(string $format = '', string $orientation = '', Page $page = null, Page $after = null): Page
	{
		if (null === $page) {
			$page = (new Page())->setDocument($this)->init();
		}
		if (!$format) {
			$format = $this->defaultFormat;
		}
		if (!$orientation) {
			$orientation = $this->defaultOrientation;
		}
		$page->setOrientation($orientation)->setFormat($format);
		$afterIndex = \count($this->pages);
		if ($after) {
			foreach ($this->pages as $afterIndex => $childPage) {
				if ($childPage === $after) {
					break;
				}
			}
			++$afterIndex;
		}
		$page->setPageNumber($afterIndex);
		if ($after) {
			$merge = array_splice($this->pages, $afterIndex);
			$this->pages[] = $page;
			$this->pages = array_merge($this->pages, $merge);
		} else {
			$this->pages[] = $page;
		}
		$this->currentPageObject = $page;

		return $page;
	}

	/**
	 * Get current page.
	 *
	 * @return Page
	 */
	public function getCurrentPage(): Page
	{
		return $this->currentPageObject;
	}

	/**
	 * Set current page.
	 *
	 * @param Page $page
	 */
	public function setCurrentPage(Page $page)
	{
		$this->currentPageObject = $page;
	}

	/**
	 * Get all pages.
	 *
	 * @param int|null $groupIndex
	 *
	 * @return Page[]
	 */
	public function getPages(int $groupIndex = null)
	{
		if ($groupIndex) {
			$pages = [];
			foreach ($this->pages as $page) {
				if ($page->getGroup() === $groupIndex) {
					$pages[] = $page;
				}
			}

			return $pages;
		}

		return $this->pages;
	}

	/**
	 * Fix page numbers.
	 *
	 * pages that are expanded by overflow will have the same unique id - cloned
	 * so they are in one group of pages - if some page is added with different unique id
	 * then it means that from now on pages are from other group and we should reset page numbers / count
	 *
	 * @return $this
	 */
	public function fixPageNumbers()
	{
		$groups = [];
		foreach ($this->getPages() as $page) {
			$groups[$page->getGroup()][] = $page;
		}
		foreach ($groups as $pages) {
			$pageCount = \count($pages);
			foreach ($pages as $index => $page) {
				$page->setPageNumber($index + 1);
				$page->setPageCount($pageCount);
			}
		}

		return $this;
	}

	/**
	 * Get document header.
	 *
	 * @return string
	 */
	protected function getDocumentHeader(): string
	{
		return "%PDF-1.4\n%âăĎÓ\n";
	}

	/**
	 * Get document footer.
	 *
	 * @return string
	 */
	protected function getDocumentFooter(): string
	{
		return '%%EOF';
	}

	/**
	 * Add object to document.
	 *
	 * @param PdfObject      $object
	 * @param PdfObject|null $after  - add after this element
	 *
	 * @return \YetiForcePDF\Document
	 */
	public function addObject(PdfObject $object, $after = null): self
	{
		$afterIndex = \count($this->objects);
		if ($after) {
			foreach ($this->objects as $afterIndex => $obj) {
				if ($after === $obj) {
					break;
				}
			}
			++$afterIndex;
		}
		if (!$after) {
			$this->objects[] = $object;

			return $this;
		}
		$merge = array_splice($this->objects, $afterIndex);
		foreach ($this->objects as $obj) {
			if ($obj->getId() === $object->getId()) {
				// id already exists (maybe we are merging with other doc) - generate new one
				$object->setId($this->getActualId());

				break;
			}
		}
		$this->objects[] = $object;
		$this->objects = array_merge($this->objects, $merge);

		return $this;
	}

	/**
	 * Remove object from document.
	 *
	 * @param \YetiForcePDF\Objects\PdfObject $object
	 *
	 * @return \YetiForcePDF\Document
	 */
	public function removeObject(PdfObject $object): self
	{
		$objects = [];
		foreach ($this->objects as $currentObject) {
			if ($currentObject !== $object) {
				$objects[] = $currentObject;
			}
		}
		$this->objects = $objects;
		unset($objects);

		return $this;
	}

	/**
	 * Load html string.
	 *
	 * @param string $html
	 * @param string $inputEncoding
	 *
	 * @return $this
	 */
	public function loadHtml(string $html, string $inputEncoding = 'UTF-8')
	{
		$this->htmlParser = (new \YetiForcePDF\Html\Parser())->setDocument($this)->init();
		$this->htmlParser->loadHtml($html, $inputEncoding);

		return $this;
	}

	/**
	 * Count objects.
	 *
	 * @param string $name - object name
	 *
	 * @return int
	 */
	public function countObjects(string $name = ''): int
	{
		if ('' === $name) {
			return \count($this->objects);
		}
		$typeCount = 0;
		foreach ($this->objects as $object) {
			if ($object->getName() === $name) {
				++$typeCount;
			}
		}

		return $typeCount;
	}

	/**
	 * Get objects.
	 *
	 * @param string $name - object name
	 *
	 * @return \YetiForcePDF\Objects\PdfObject[]
	 */
	public function getObjects(string $name = ''): array
	{
		if ('' === $name) {
			return $this->objects;
		}
		$objects = [];
		foreach ($this->objects as $object) {
			if ($object->getName() === $name) {
				$objects[] = $object;
			}
		}

		return $objects;
	}

	/**
	 * Filter text
	 * Filter the text, this is applied to all text just before being inserted into the pdf document
	 * it escapes the various things that need to be escaped, and so on.
	 *
	 * @param string $text
	 * @param string $encoding
	 * @param bool   $withParenthesis
	 * @param bool   $prependBom
	 *
	 * @return string
	 */
	public function filterText(string $text, string $encoding = 'UTF-16', bool $withParenthesis = true, bool $prependBom = false)
	{
		$text = preg_replace('/[\n\r\t\s]+/u', ' ', mb_convert_encoding($text, 'UTF-8'));
		$text = preg_replace('/^\s+|\s+$/u', '', $text);
		$text = preg_replace('/\s+/u', ' ', $text);
		$text = mb_convert_encoding($text, $encoding, mb_detect_encoding($text));
		$text = strtr($text, [')' => '\\)', '(' => '\\(', '\\' => '\\\\', \chr(13) => '\r']);
		if ($prependBom) {
			$text = \chr(254) . \chr(255) . $text;
		}
		if ($withParenthesis) {
			return '(' . $text . ')';
		}

		return $text;
	}

	/**
	 * Parse html.
	 *
	 * @return $this
	 */
	public function parse()
	{
		if (!$this->isParsed()) {
			$this->htmlParser->parse();
			$this->parsed = true;
		}

		return $this;
	}

	/**
	 * Layout document content to pdf string.
	 *
	 * @return string
	 */
	public function render(): string
	{
		$xref = $this->buffer = '';
		$this->buffer .= $this->getDocumentHeader();
		$this->parse();
		$objectSize = 0;
		foreach ($this->objects as $object) {
			if (\in_array($object->getBasicType(), ['Dictionary', 'Stream', 'Array'])) {
				$xref .= sprintf("%010d 00000 n \n", \strlen($this->buffer));
				$this->buffer .= $object->render() . "\n";
				++$objectSize;
			}
		}
		$offset = \strlen($this->buffer);
		$this->buffer .= implode("\n", [
			'xref',
			'0 ' . ($objectSize + 1),
			'0000000000 65535 f ',
			$xref,
		]);
		$trailer = (new \YetiForcePDF\Objects\Trailer())
			->setDocument($this)->setRootObject($this->catalog)->setSize($objectSize);
		$this->buffer .= $trailer->render() . "\n";
		// $this->buffer .= implode("\n", [
		// 	'startxref',
		// 	$offset,
		// 	'',
		// ]);
		$this->buffer .= $this->getDocumentFooter();
		$this->removeObject($trailer);
		return $this->buffer;
	}

	/**
	 * Get css selector rules.
	 *
	 * @param string $selector
	 *
	 * @return array
	 */
	public function getCssSelectorRules(string $selector)
	{
		$rules = [];
		foreach (explode(' ', $selector) as $className) {
			if ($className && isset($this->cssSelectors[$className])) {
				$rules = array_merge($rules, $this->cssSelectors[$className]);
			}
		}

		return $rules;
	}

	/**
	 * Get css selectors.
	 *
	 * @return array
	 */
	public function getCssSelectors()
	{
		return $this->cssSelectors;
	}

	/**
	 * Add css selector rules.
	 *
	 * @param string $selector .className or #id
	 * @param array  $rules
	 *
	 * @return $this
	 */
	public function addCssSelectorRules(string $selector, array $rules): self
	{
		if (isset($this->cssSelectors[$selector])) {
			$this->cssSelectors[$selector] = array_merge($this->cssSelectors[$selector], $rules);
		} else {
			$this->cssSelectors[$selector] = $rules;
		}
		return $this;
	}
}
