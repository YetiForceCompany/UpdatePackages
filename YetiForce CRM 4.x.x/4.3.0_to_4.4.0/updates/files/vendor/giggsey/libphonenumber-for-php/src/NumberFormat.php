<?php

namespace libphonenumber;

/**
 * Number Format.
 */
class NumberFormat
{
	protected $pattern;
	protected $format;
	protected $leadingDigitsPattern = [];
	protected $nationalPrefixFormattingRule;
	/**
	 * @var bool
	 */
	protected $nationalPrefixOptionalWhenFormatting = false;
	protected $domesticCarrierCodeFormattingRule;

	public function __construct()
	{
		$this->clear();
	}

	/**
	 * @return NumberFormat
	 */
	public function clear()
	{
		$this->pattern = '';
		$this->format = '';
		$this->leadingDigitsPattern = [];
		$this->nationalPrefixFormattingRule = '';
		$this->nationalPrefixOptionalWhenFormatting = false;
		$this->domesticCarrierCodeFormattingRule = '';

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasPattern()
	{
		return isset($this->pattern);
	}

	/**
	 * @return string
	 */
	public function getPattern()
	{
		return $this->pattern;
	}

	/**
	 * @param string $value
	 *
	 * @return NumberFormat
	 */
	public function setPattern($value)
	{
		$this->pattern = $value;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasNationalPrefixOptionalWhenFormatting()
	{
		return isset($this->nationalPrefixOptionalWhenFormatting);
	}

	/**
	 * @return bool
	 */
	public function getNationalPrefixOptionalWhenFormatting()
	{
		return $this->nationalPrefixOptionalWhenFormatting;
	}

	/**
	 * @param bool $nationalPrefixOptionalWhenFormatting
	 */
	public function setNationalPrefixOptionalWhenFormatting($nationalPrefixOptionalWhenFormatting)
	{
		$this->nationalPrefixOptionalWhenFormatting = $nationalPrefixOptionalWhenFormatting;
	}

	/**
	 * @return bool
	 */
	public function hasFormat()
	{
		return $this->format;
	}

	/**
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * @param string $value
	 *
	 * @return NumberFormat
	 */
	public function setFormat($value)
	{
		$this->format = $value;

		return $this;
	}

	/**
	 * @return string
	 */
	public function leadingDigitPatterns()
	{
		return $this->leadingDigitsPattern;
	}

	/**
	 * @return int
	 */
	public function leadingDigitsPatternSize()
	{
		return count($this->leadingDigitsPattern);
	}

	/**
	 * @param int $index
	 *
	 * @return string
	 */
	public function getLeadingDigitsPattern($index)
	{
		return $this->leadingDigitsPattern[$index];
	}

	/**
	 * @param string $value
	 *
	 * @return NumberFormat
	 */
	public function addLeadingDigitsPattern($value)
	{
		$this->leadingDigitsPattern[] = $value;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasNationalPrefixFormattingRule()
	{
		return isset($this->nationalPrefixFormattingRule);
	}

	/**
	 * @return string
	 */
	public function getNationalPrefixFormattingRule()
	{
		return $this->nationalPrefixFormattingRule;
	}

	/**
	 * @param string $value
	 *
	 * @return NumberFormat
	 */
	public function setNationalPrefixFormattingRule($value)
	{
		$this->nationalPrefixFormattingRule = $value;

		return $this;
	}

	/**
	 * @return NumberFormat
	 */
	public function clearNationalPrefixFormattingRule()
	{
		$this->nationalPrefixFormattingRule = null;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasDomesticCarrierCodeFormattingRule()
	{
		return isset($this->domesticCarrierCodeFormattingRule);
	}

	/**
	 * @return string
	 */
	public function getDomesticCarrierCodeFormattingRule()
	{
		return $this->domesticCarrierCodeFormattingRule;
	}

	/**
	 * @param string $value
	 *
	 * @return NumberFormat
	 */
	public function setDomesticCarrierCodeFormattingRule($value)
	{
		$this->domesticCarrierCodeFormattingRule = $value;

		return $this;
	}

	/**
	 * @param NumberFormat $other
	 *
	 * @return NumberFormat
	 */
	public function mergeFrom(self $other)
	{
		if ($other->hasPattern()) {
			$this->setPattern($other->getPattern());
		}
		if ($other->hasFormat()) {
			$this->setFormat($other->getFormat());
		}
		$leadingDigitsPatternSize = $other->leadingDigitsPatternSize();
		for ($i = 0; $i < $leadingDigitsPatternSize; $i++) {
			$this->addLeadingDigitsPattern($other->getLeadingDigitsPattern($i));
		}
		if ($other->hasNationalPrefixFormattingRule()) {
			$this->setNationalPrefixFormattingRule($other->getNationalPrefixFormattingRule());
		}
		if ($other->hasDomesticCarrierCodeFormattingRule()) {
			$this->setDomesticCarrierCodeFormattingRule($other->getDomesticCarrierCodeFormattingRule());
		}
		if ($other->hasNationalPrefixOptionalWhenFormatting()) {
			$this->setNationalPrefixOptionalWhenFormatting($other->getNationalPrefixOptionalWhenFormatting());
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$output = [];
		$output['pattern'] = $this->getPattern();
		$output['format'] = $this->getFormat();

		$output['leadingDigitsPatterns'] = $this->leadingDigitPatterns();

		if ($this->hasNationalPrefixFormattingRule()) {
			$output['nationalPrefixFormattingRule'] = $this->getNationalPrefixFormattingRule();
		}

		if ($this->hasDomesticCarrierCodeFormattingRule()) {
			$output['domesticCarrierCodeFormattingRule'] = $this->getDomesticCarrierCodeFormattingRule();
		}

		if ($this->hasNationalPrefixOptionalWhenFormatting()) {
			$output['nationalPrefixOptionalWhenFormatting'] = $this->getNationalPrefixOptionalWhenFormatting();
		}

		return $output;
	}

	/**
	 * @param array $input
	 */
	public function fromArray(array $input)
	{
		$this->setPattern($input['pattern']);
		$this->setFormat($input['format']);
		foreach ($input['leadingDigitsPatterns'] as $leadingDigitsPattern) {
			$this->addLeadingDigitsPattern($leadingDigitsPattern);
		}

		if (isset($input['nationalPrefixFormattingRule'])) {
			$this->setNationalPrefixFormattingRule($input['nationalPrefixFormattingRule']);
		}
		if (isset($input['domesticCarrierCodeFormattingRule'])) {
			$this->setDomesticCarrierCodeFormattingRule($input['domesticCarrierCodeFormattingRule']);
		}

		if (isset($input['nationalPrefixOptionalWhenFormatting'])) {
			$this->setNationalPrefixOptionalWhenFormatting($input['nationalPrefixOptionalWhenFormatting']);
		}
	}
}
