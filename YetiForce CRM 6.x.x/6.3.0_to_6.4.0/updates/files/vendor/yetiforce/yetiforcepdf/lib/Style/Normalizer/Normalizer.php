<?php

declare(strict_types=1);
/**
 * Normalizer class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

use YetiForcePDF\Style\NumericValue;
use YetiForcePDF\Style\Style;

/**
 * Class Normalizer.
 */
class Normalizer extends \YetiForcePDF\Base
{
	/**
	 * @var Style
	 */
	protected $style;
	/**
	 * @var array|null normalized value
	 */
	protected $normalized;
	/**
	 * Regex used to parse numeric values.
	 *
	 * @var string
	 */
	public static $numericRegex = '/(([0-9.?]+)([a-z%]+)?\s?)/ui';

	/**
	 * Set style.
	 *
	 * @param \YetiForcePDF\Style\Style $style
	 *
	 * @return $this
	 */
	public function setStyle(Style $style)
	{
		$this->style = $style;
		return $this;
	}

	/**
	 * Get normalizer class name.
	 *
	 * @param string $ruleName
	 *
	 * @return string
	 */
	public static function getNormalizerClassName(string $ruleName)
	{
		$ucRuleName = str_replace('-', '', ucwords($ruleName, '-'));
		$normalizerClassName = "YetiForcePDF\\Style\\Normalizer\\$ucRuleName";
		if (class_exists($normalizerClassName)) {
			return $normalizerClassName;
		}
		return 'YetiForcePDF\\Style\\Normalizer\\Normalizer';
	}

	/**
	 * Get number value from style.
	 *
	 * @param NumericValue|string $ruleValue
	 * @param bool                $isFont
	 *
	 * @return string[]
	 */
	public function getNumberValues($ruleValue, bool $isFont = false)
	{
		if ($ruleValue instanceof NumericValue) {
			return $ruleValue;
		}
		preg_match_all(static::$numericRegex, $ruleValue, $matches, PREG_SET_ORDER);
		if (!$matches) {
			$matches = [['0', '0', '0']];
		}
		$originalSize = $matches[0][2];
		$originalUnit = 'em';
		if (isset($matches[0][3])) {
			$originalUnit = $matches[0][3];
		}
		$multi = [
			(new NumericValue())
				->setUnit($originalUnit)
				->setValue($originalSize)
				->setOriginal($originalSize . $originalUnit)
				->setIsFont($isFont)
				->convert($this->style),
		];
		$matchesCount = \count($matches);
		if ($matchesCount >= 2) {
			$multi[] = (new NumericValue())
				->setUnit($matches[1][3] ?? $originalUnit)
				->setValue($matches[1][2])
				->setOriginal($matches[1][2] . ($matches[1][3] ?? $originalUnit))
				->setIsFont($isFont)
				->convert($this->style);
		}
		if ($matchesCount >= 3) {
			$multi[] = (new NumericValue())
				->setUnit($matches[2][3] ?? $originalUnit)
				->setValue($matches[2][2])
				->setOriginal($matches[2][2] . ($matches[2][3] ?? $originalUnit))
				->setIsFont($isFont)
				->convert($this->style);
		}
		if (4 === $matchesCount) {
			$multi[] = (new NumericValue())
				->setUnit($matches[3][3] ?? $originalUnit)
				->setValue($matches[3][2])
				->setOriginal($matches[3][2] . ($matches[3][3] ?? $originalUnit))
				->setIsFont($isFont)
				->convert($this->style);
		}
		return $multi;
	}

	/**
	 * Get numeric unit from css value.
	 *
	 * @param string $value       value from css 12px for example
	 * @param string $defaultUnit
	 *
	 * @return string
	 */
	public static function getNumericUnit(string $value, string $defaultUnit): string
	{
		$matches = [];
		preg_match_all(static::$numericRegex, $value, $matches, PREG_SET_ORDER);
		$unit = $defaultUnit;
		if (isset($matches[0][3])) {
			$unit = $matches[0][3];
		}
		return $unit;
	}

	/**
	 * Is numeric value.
	 *
	 * @param string $value
	 *
	 * @return bool|string
	 */
	public static function getNumericValue(string $value)
	{
		$matches = [];
		preg_match_all(static::$numericRegex, $value, $matches, PREG_SET_ORDER);
		if (isset($matches[0][2])) {
			return $matches[0][2];
		}
		return false;
	}

	/**
	 * Normalize css rule.
	 *
	 * @param mixed  $ruleValue
	 * @param string $ruleName
	 *
	 * @return array
	 */
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		return [$ruleName => $ruleValue];
	}

	/**
	 * One value.
	 *
	 * @param array $ruleNames
	 * @param array $numberValues
	 *
	 * @return array
	 */
	protected function oneValue(array $ruleNames, array $numberValues)
	{
		$normalized = [];
		$normalized[$ruleNames[0]] = $numberValues[0];
		$normalized[$ruleNames[1]] = $numberValues[0];
		$normalized[$ruleNames[2]] = $numberValues[0];
		$normalized[$ruleNames[3]] = $numberValues[0];
		return $normalized;
	}

	/**
	 * Two values.
	 *
	 * @param array $ruleNames
	 * @param array $numberValues
	 *
	 * @return array
	 */
	protected function twoValues(array $ruleNames, array $numberValues)
	{
		$normalized = [];
		$normalized[$ruleNames[0]] = $numberValues[0];
		$normalized[$ruleNames[1]] = $numberValues[1];
		$normalized[$ruleNames[2]] = $numberValues[0];
		$normalized[$ruleNames[3]] = $numberValues[1];
		return $normalized;
	}

	/**
	 * Three values.
	 *
	 * @param array $ruleNames
	 * @param array $numberValues
	 *
	 * @return array
	 */
	protected function threeValues(array $ruleNames, array $numberValues)
	{
		$normalized = [];
		$normalized[$ruleNames[0]] = $numberValues[0];
		$normalized[$ruleNames[1]] = $numberValues[1];
		$normalized[$ruleNames[2]] = $numberValues[2];
		$normalized[$ruleNames[3]] = $numberValues[1];
		return $normalized;
	}

	/**
	 * Four values.
	 *
	 * @param array $ruleNames
	 * @param array $numberValues
	 *
	 * @return array
	 */
	protected function fourValues(array $ruleNames, array $numberValues)
	{
		$normalized = [];
		$normalized[$ruleNames[0]] = $numberValues[0];
		$normalized[$ruleNames[1]] = $numberValues[1];
		$normalized[$ruleNames[2]] = $numberValues[2];
		$normalized[$ruleNames[3]] = $numberValues[3];
		return $normalized;
	}

	/**
	 * Normalize multi number values.
	 *
	 * @param string[] $ruleNames ['margin-top','margin-right','margin-bottom','margin-left']
	 * @param string   $ruleValue
	 *                            return array
	 */
	public function normalizeMultiValues(array $ruleNames, $ruleValue): array
	{
		$numberValues = $this->getNumberValues($ruleValue);
		switch (\count($numberValues)) {
			case 1:
				return $this->oneValue($ruleNames, $numberValues);
			case 2:
				return $this->twoValues($ruleNames, $numberValues);
			case 3:
				return $this->threeValues($ruleNames, $numberValues);
			case 4:
				return $this->fourValues($ruleNames, $numberValues);
		}
	}
}
