<?php

declare(strict_types=1);
/**
 * Font class.
 *
 * @package   YetiForcePDF\Objects
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects;

use YetiForcePDF\Math;
use YetiForcePDF\Style\NumericValue;

/**
 * Class Font.
 */
class Font extends \YetiForcePDF\Objects\Resource
{
	protected static $fontFiles = [
		'DejaVu Sans' => [
			'100' => [
				'normal' => 'DejaVuSans-ExtraLight.ttf',
				'italic' => 'DejaVuSans-ExtraLight.ttf',
			],
			'200' => [
				'normal' => 'DejaVuSans-ExtraLight.ttf',
				'italic' => 'DejaVuSans-ExtraLight.ttf',
			],
			'300' => [
				'normal' => 'DejaVuSans-ExtraLight.ttf',
				'italic' => 'DejaVuSans-ExtraLight.ttf',
			],
			'400' => [
				'normal' => 'DejaVuSans.ttf',
				'italic' => 'DejaVuSans-Oblique.ttf',
			],
			'500' => [
				'normal' => 'DejaVuSans.ttf',
				'italic' => 'DejaVuSans-Oblique.ttf',
			],
			'600' => [
				'normal' => 'DejaVuSans.ttf',
				'italic' => 'DejaVuSans-Oblique.ttf',
			],
			'700' => [
				'normal' => 'DejaVuSans-Bold.ttf',
				'italic' => 'DejaVuSans-BoldOblique.ttf',
			],
			'800' => [
				'normal' => 'DejaVuSans-Bold.ttf',
				'italic' => 'DejaVuSans-BoldOblique.ttf',
			],
			'900' => [
				'normal' => 'DejaVuSans-Bold.ttf',
				'italic' => 'DejaVuSans-BoldOblique.ttf',
			],
		],
	];
	/**
	 * @var string
	 */
	public static $defaultFontFamily = 'DejaVu Sans';
	/**
	 * @var array
	 */
	protected static $customFontFiles = [];
	/**
	 * @var string
	 */
	protected $fontDir = '';
	/**
	 * Which type of dictionary (Page, Catalog, Font etc...).
	 *
	 * @var string
	 */
	protected $resourceType = 'Font';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $resourceName = 'Font';
	/**
	 * Base font type aka font family.
	 *
	 * @var string
	 */
	protected $family = '';
	/**
	 * Font weight.
	 *
	 * @var string
	 */
	protected $weight = 'normal';
	/**
	 * Font style.
	 *
	 * @var string
	 */
	protected $style = 'normal';
	/**
	 * Font number.
	 *
	 * @var string
	 */
	protected $fontNumber = 'F1';
	/**
	 * Font size.
	 *
	 * @var NumericValue
	 */
	protected $size;
	/**
	 * Font height.
	 *
	 * @var string
	 */
	protected $height = '0';
	/**
	 * Text height with ascender and descender.
	 *
	 * @var string|null
	 */
	protected $textHeight;
	/**
	 * Font data.
	 *
	 * @var \FontLib\Font
	 */
	protected $fontData;
	/**
	 * @var
	 */
	protected $fontDescriptor;
	/**
	 * @var \YetiForcePDF\Objects\Basic\StreamObject
	 */
	protected $dataStream;
	/**
	 * Info needed to write in pdf.
	 *
	 * @var array
	 */
	protected $outputInfo = [];
	/**
	 * @var string
	 */
	protected $unitsPerEm = '1000';
	/**
	 * Character widths.
	 *
	 * @var int[]
	 */
	protected $widths = [];
	/**
	 * Cid to Gid characters map.
	 *
	 * @var \YetiForcePDF\Objects\Basic\StreamObject
	 */
	protected $cidToGid;
	/**
	 * Cid system info.
	 *
	 * @var \YetiForcePDF\Objects\Basic\DictionaryObject
	 */
	protected $cidSystemInfo;
	/**
	 * Character map (unicode).
	 *
	 * @var array
	 */
	protected $charMap = [];
	/**
	 * Unicode char map stream.
	 *
	 * @var \YetiForcePDF\Objects\Basic\StreamObject
	 */
	protected $toUnicode;
	/**
	 * Main font that is used - first font - this file is just descendant font.
	 *
	 * @var \YetiForcePDF\Objects\Basic\DictionaryObject
	 */
	protected $fontType0;
	/**
	 * From baseline to top of the font.
	 *
	 * @var string
	 */
	protected $ascender = '0';
	/**
	 * From baseline to bottom (with jyg chars that are bellow baseline).
	 *
	 * @var string
	 */
	protected $descender = '0';
	/**
	 * @var string
	 */
	protected $fontPostscriptName;
	/**
	 * Text widths cache.
	 *
	 * @var array
	 */
	protected $textWidths = [];

	/**
	 * Initialization.
	 *
	 * @return $this
	 */
	public function init()
	{
		if (empty($this->family)) {
			$this->family = self::$defaultFontFamily;
		}
		$this->fontDir = realpath(__DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'Fonts') . \DIRECTORY_SEPARATOR;
		$alreadyExists = $this->document->getFontInstance($this->family, $this->weight, $this->style);
		if (null === $alreadyExists) {
			parent::init();
			$this->document->setFontInstance($this->family, $this->weight, $this->style, $this);
			$this->fontNumber = 'F' . $this->document->getActualFontId();
			$this->fontData = $this->loadFontData();
			$this->document->setFontData($this->family, $this->weight, $this->style, $this->fontData);
			$this->fontDescriptor = (new \YetiForcePDF\Objects\FontDescriptor())
				->setDocument($this->document)
				->setFont($this)
				->init();
			foreach ($this->document->getObjects('Page') as $page) {
				$page->synchronizeFonts();
			}
			return $this;
		}
		$this->setAddToDocument(false);
		// do not init parent! we don't want to create resources etc.
		return clone $alreadyExists;
	}

	/**
	 * Add custom font.
	 *
	 * @param string $family
	 * @param string $weight
	 * @param string $style
	 * @param string $fileName
	 */
	public static function addCustomFont(string $family, string $weight, string $style, string $fileName)
	{
		$strWeight = (new \YetiForcePDF\Style\Normalizer\FontWeight())->normalize($weight)['font-weight'];
		static::$customFontFiles[$family][$strWeight][$style] = $fileName;
	}

	/**
	 * Set font number.
	 *
	 * @param string $number
	 *
	 * @return \YetiForcePDF\Objects\Font
	 */
	public function setNumber(string $number): self
	{
		$this->fontNumber = $number;

		return $this;
	}

	/**
	 * Set font name.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setFamily(string $name)
	{
		$this->family = $name;

		return $this;
	}

	/**
	 * Get font name.
	 *
	 * @return string
	 */
	public function getFamily(): string
	{
		return $this->family;
	}

	/**
	 * Set font weight.
	 *
	 * @param string $weight
	 *
	 * @return $this
	 */
	public function setWeight(string $weight)
	{
		$this->weight = $weight;

		return $this;
	}

	/**
	 * Get font name.
	 *
	 * @return string
	 */
	public function getWeight(): string
	{
		return $this->weight;
	}

	/**
	 * Set font style.
	 *
	 * @param string $style
	 *
	 * @return $this
	 */
	public function setStyle(string $style)
	{
		$this->style = $style;

		return $this;
	}

	/**
	 * Get font style.
	 *
	 * @return string
	 */
	public function getStyle(): string
	{
		return $this->style;
	}

	/**
	 * Get full font name.
	 *
	 * @return string
	 */
	public function getFullName()
	{
		return $this->fontPostscriptName;
	}

	/**
	 * Get output info.
	 *
	 * @return array
	 */
	public function getOutputInfo()
	{
		return $this->outputInfo;
	}

	/**
	 * Get font data.
	 *
	 * @return \FontLib\Font
	 */
	public function getData()
	{
		return $this->fontData;
	}

	/**
	 * Set Font size.
	 *
	 * @param NumericValue $size
	 *
	 * @return $this
	 */
	public function setSize(NumericValue $size)
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * Get font size.
	 *
	 * @return string
	 */
	public function getSize(): NumericValue
	{
		return $this->size;
	}

	/**
	 * Get closest value with specified unit - not converted.
	 *
	 * @param string $unit
	 *
	 * @return Font
	 */
	public function getClosestWithUnit(string $unit)
	{
		if ($this->getSize()->getUnit() === $unit) {
			return $this;
		}
		return $this->getParent()->getClosestWithUnit($unit);
	}

	/**
	 * Convert character to int.
	 *
	 * @param $string
	 *
	 * @return int
	 */
	public function mbOrd($string)
	{
		if (isset($this->document->ordCache[$string])) {
			return $this->document->ordCache[$string];
		}
		if (true === \extension_loaded('mbstring')) {
			mb_language('Neutral');
			mb_internal_encoding('UTF-8');
			mb_detect_order(['UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'ASCII']);
			$result = unpack('N', mb_convert_encoding($string, 'UCS-4BE', 'UTF-8'));
			if (true === \is_array($result)) {
				return $result[1];
			}
		}

		return $this->document->ordCache[$string] = \ord($string);
	}

	/**
	 * Get text width.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function getTextWidth(string $text): string
	{
		if (isset($this->textWidths[$text])) {
			return $this->textWidths[$text];
		}
		$width = '0';
		for ($i = 0, $len = mb_strlen($text); $i < $len; ++$i) {
			$char = mb_substr($text, $i, 1);
			if (isset($this->widths[$this->mbOrd($char)])) {
				$width = Math::add($width, (string) $this->widths[$this->mbOrd($char)]);
			}
		}

		return $this->textWidths[$text] = Math::div(Math::mul($this->size->getConverted(), $width), '1000');
	}

	/**
	 * Get text height.
	 *
	 * @param string|null $text
	 *
	 * @return string
	 */
	public function getTextHeight(string $text = null): string
	{
		if (null === $this->textHeight) {
			$this->textHeight = Math::add($this->getAscender(), Math::mul($this->getDescender(), '-1'));
		}

		return $this->textHeight;
	}

	/**
	 * Get ascender (from baseline to top of the bounding box).
	 *
	 * @return string
	 */
	public function getAscender(): string
	{
		return Math::div(Math::mul($this->size->getConverted(), $this->ascender), $this->unitsPerEm);
	}

	/**
	 * Get descender (from baseline to bottom of the bounding box).
	 *
	 * @return string
	 */
	public function getDescender(): string
	{
		return Math::div(Math::mul($this->size->getConverted(), $this->descender), $this->unitsPerEm);
	}

	/**
	 * Get font number.
	 *
	 * @return string
	 */
	public function getNumber(): string
	{
		return $this->fontNumber;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getReference(): string
	{
		return $this->getRawId() . ' R';
	}

	/**
	 * Get data stream.
	 *
	 * @return \YetiForcePDF\Objects\Basic\StreamObject
	 */
	public function getDataStream()
	{
		return $this->dataStream;
	}

	public function normalizeUnit(string $value, string $base = '1000')
	{
		return bcmul($value, Math::div($base, $this->unitsPerEm), 0);
	}

	protected function setUpUnicode($charMapUnicode)
	{
		$stream = implode("\n", [
			'/CIDInit /ProcSet findresource begin',
			'12 dict begin',
			'begincmap',
			'/CIDSystemInfo',
			'<</Registry (Adobe)',
			'/Ordering (UCS)',
			'/Supplement 0',
			'>> def',
			'/CMapName /Adobe-Identity-UCS def',
			'/CMapType 2 def',
			'1 begincodespacerange',
			'<0000> <FFFF>',
			'endcodespacerange',
			'1 beginbfrange',
			'<0000> <FFFF> <0000>',
			'endbfrange',
			'endcmap',
			'CMapName currentdict /CMap defineresource pop',
			'end',
			'end',
		]);
		$this->toUnicode->addRawContent($stream);
	}

	/**
	 * Get Type0 font - main one.
	 *
	 * @return Font
	 */
	public function getType0Font()
	{
		return $this->fontType0;
	}

	/**
	 * Match font to weights and styles - try other weighs/styles if not present.
	 *
	 * @param bool $custom
	 *
	 * @return string
	 */
	protected function matchFont(bool $custom = false)
	{
		if (!$custom) {
			return static::$fontFiles[$this->family][$this->weight][$this->style];
		}
		if (isset(static::$customFontFiles[$this->family][$this->weight][$this->style]) && file_exists(static::$customFontFiles[$this->family][$this->weight][$this->style])) {
			return static::$customFontFiles[$this->family][$this->weight][$this->style];
		}
		$weight = '';
		for ($currentWeight = (int) $this->weight; $currentWeight >= 0; $currentWeight -= 100) {
			if (isset(static::$customFontFiles[$this->family][(string) $currentWeight])) {
				$weight = (string) $currentWeight;

				break;
			}
		}
		if (!$weight) {
			for ($currentWeight = (int) $this->weight; $currentWeight <= 900; $currentWeight += 100) {
				if (isset(static::$customFontFiles[$this->family][(string) $currentWeight])) {
					$weight = (string) $currentWeight;

					break;
				}
			}
		}
		if (!$weight) {
			// font file not found return default one
			return $this->fontDir . static::$fontFiles[static::$defaultFontFamily][$this->weight][$this->style];
		}
		if (isset(static::$customFontFiles[$this->family][$weight][$this->style]) && file_exists(static::$customFontFiles[$this->family][$weight][$this->style])) {
			return static::$customFontFiles[$this->family][$weight][$this->style];
		}
		// inverse style
		$style = 'normal' === $this->style ? 'italic' : 'normal';
		if (isset(static::$customFontFiles[$this->family][$weight][$style]) && file_exists(static::$customFontFiles[$this->family][$weight][$style])) {
			return static::$customFontFiles[$this->family][$weight][$style];
		}
		// font file not found - get default one
		return $this->fontDir . static::$fontFiles[static::$defaultFontFamily][$this->weight][$this->style];
	}

	/**
	 * Load fonts from array.
	 *
	 * @param array $decoded
	 *
	 * @throws \ErrorException
	 */
	public static function loadFromArray(array $decoded)
	{
		if (!\is_array($decoded)) {
			throw new \ErrorException('Invalid fonts json structure.');
		}
		foreach ($decoded as $font) {
			if (!\is_array($font)) {
				throw new \ErrorException('Invalid fonts json structure.');
			}
			if (empty($font['family']) || empty($font['weight']) || empty($font['style']) || empty($font['file'])) {
				throw new \ErrorException('Invalid fonts json structure.');
			}
			static::addCustomFont($font['family'], $font['weight'], $font['style'], $font['file']);
		}
	}

	/**
	 * Get font file name without extension.
	 *
	 * @throws \ErrorException
	 *
	 * @return string
	 */
	public function getFontFileName()
	{
		if (isset(static::$fontFiles[$this->family])) {
			$match = $this->matchFont();
			if (file_exists($this->fontDir . $match)) {
				return $this->fontDir . $match;
			}
			if (\defined('ROOT_DIRECTORY')) {
				$path = ROOT_DIRECTORY;
				$path .= \DIRECTORY_SEPARATOR . 'public_html';
				$path .= \DIRECTORY_SEPARATOR . 'vendor';
				$path .= \DIRECTORY_SEPARATOR . 'yetiforce';
				$path .= \DIRECTORY_SEPARATOR . 'yetiforcepdf';
				$path .= \DIRECTORY_SEPARATOR . 'lib';
				$path .= \DIRECTORY_SEPARATOR . 'Fonts';
				$path .= \DIRECTORY_SEPARATOR . $match;
				if (file_exists($path)) {
					return $path;
				}
			}
		}

		return $this->matchFont(true);
	}

	/**
	 * Load font.
	 *
	 * @throws \FontLib\Exception\FontNotFoundException
	 *
	 * @return \FontLib\TrueType\File|null
	 */
	protected function loadFontData()
	{
		$fileName = $this->getFontFileName();
		$fileContent = file_get_contents($fileName);
		$font = \FontLib\Font::load($fileName);
		$font->parse();
		$head = $font->getData('head');
		$hhea = $font->getData('hhea');
		$hmtx = $font->getData('hmtx');
		$post = $font->getData('post');
		$os2 = $font->getData('OS/2');
		if (isset($head['unitsPerEm'])) {
			$this->unitsPerEm = (string) $head['unitsPerEm'];
		}
		$this->outputInfo['descriptor'] = [];
		$this->outputInfo['descriptor']['FontBBox'] = '[' . implode(' ', [
			$this->normalizeUnit((string) $head['xMin']),
			$this->normalizeUnit((string) $head['yMin']),
			$this->normalizeUnit((string) $head['xMax']),
			$this->normalizeUnit((string) $head['yMax']),
		]) . ']';
		$this->outputInfo['descriptor']['Ascent'] = (string) $hhea['ascent'];
		$this->outputInfo['descriptor']['Descent'] = (string) $hhea['descent'];
		$this->ascender = (string) $this->outputInfo['descriptor']['Ascent'];
		$this->descender = (string) $this->outputInfo['descriptor']['Descent'];
		$this->outputInfo['descriptor']['MissingWidth'] = '500';
		$this->outputInfo['descriptor']['StemV'] = '80';
		if (isset($post['usWeightClass']) && $post['usWeightClass'] > 400) {
			$this->outputInfo['descriptor']['StemV'] = '120';
		}
		$this->outputInfo['descriptor']['ItalicAngle'] = (string) $post['italicAngle'];
		$flags = 0;
		if ('0' !== $this->outputInfo['descriptor']['ItalicAngle']) {
			$flags += 2 ** 6;
		}
		if (true === $post['isFixedPitch']) {
			++$flags;
		}
		$flags += 2 ** 5;
		$this->outputInfo['descriptor']['Flags'] = (string) $flags;
		$this->outputInfo['font'] = [];
		$widths = [];
		$this->widths = [];
		$this->charMap = $font->getUnicodeCharMap();
		$charMapUnicode = [];
		$cidToGid = str_pad('', 256 * 256 * 2, "\x00");
		foreach ($this->charMap as $c => $glyph) {
			// Set values in CID to GID map
			if ($c >= 0 && $c < 0xFFFF && $glyph) {
				$cidToGid[$c * 2] = \chr($glyph >> 8);
				$cidToGid[$c * 2 + 1] = \chr($glyph & 0xFF);
			}
			$width = $this->normalizeUnit(isset($hmtx[$glyph]) ? (string) $hmtx[$glyph][0] : (string) $hmtx[0][0]);
			$widths[] = $c . ' [' . $width . ']';
			$this->widths[$c] = $width;
		}
		$this->cidToGid = (new \YetiForcePDF\Objects\Basic\StreamObject())
			->setDocument($this->document)
			->init();
		$this->cidToGid->addRawContent($cidToGid)->setFilter('FlateDecode');
		$this->outputInfo['font']['Widths'] = $widths;
		$this->outputInfo['font']['FirstChar'] = 0;
		$this->outputInfo['font']['LastChar'] = \count($widths) - 1;
		$this->height = Math::sub((string) $hhea['ascent'], (string) $hhea['descent']);
		if (isset($os2['typoLineGap'])) {
			$this->height = Math::add($this->height, (string) $os2['typoLineGap']);
		}
		$this->fontType0 = (new \YetiForcePDF\Objects\Basic\DictionaryObject())
			->setDocument($this->document)
			->init();
		$this->cidSystemInfo = (new \YetiForcePDF\Objects\Basic\DictionaryObject())
			->setDocument($this->document)
			->init();
		$this->cidSystemInfo->addValue('Registry', '(Adobe)')
			->addValue('Ordering', '(UCS)')
			->addValue('Supplement', '0');
		$this->dataStream = (new \YetiForcePDF\Objects\Basic\StreamObject())
			->setDocument($this->document)
			->setFilter('FlateDecode')
			->init()
			->addRawContent($fileContent);
		$this->toUnicode = (new \YetiForcePDF\Objects\Basic\StreamObject())
			->setDocument($this->document)
			->init();
		$this->setUpUnicode($charMapUnicode);
		$this->fontPostscriptName = $font->getFontPostscriptName();
		$this->fontType0->setDictionaryType('Font')
			->addValue('Subtype', '/Type0')
			->addValue('BaseFont', '/' . $this->fontPostscriptName)
			->addValue('Encoding', '/Identity-H')
			->addValue('DescendantFonts', '[' . $this->getReference() . ']')
			->addValue('ToUnicode', $this->toUnicode->getReference());
		$font->close();

		return $font;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return implode("\n", [$this->getRawId() . ' obj',
			'<</Type /Font/Subtype /CIDFontType2',
			'  /BaseFont /' . $this->getFullName(),
			'  /FontDescriptor ' . $this->fontDescriptor->getReference(),
			'  /DW 500',
			'  /W [' . implode(' ', $this->outputInfo['font']['Widths']) . ' ]',
			'  /CIDSystemInfo ' . $this->cidSystemInfo->getReference(),
			'  /CIDToGIDMap ' . $this->cidToGid->getReference(),
			'>>',
			'endobj', ]);
	}
}
