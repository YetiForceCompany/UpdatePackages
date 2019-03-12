<?php

declare(strict_types=1);
/**
 * Normalizer class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

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
	 * @param $ruleValue
	 *
	 * @return string[]
	 */
	public function getNumberValues($ruleValue)
	{
		$matches = [];
		preg_match_all('/(([0-9.?]+)([a-z%]+)?\s?)/ui', $ruleValue, $matches, PREG_SET_ORDER);
		$originalSize = $matches[0][2];
		if (isset($matches[0][3])) {
			$originalUnit = $matches[0][3];
		} else {
			$originalUnit = 'em';
		}
		$multi = [
			$this->style->convertUnits($originalUnit, $originalSize),
		];
		$matchesCount = count($matches);
		if ($matchesCount >= 2) {
			$multi[] = $this->style->convertUnits($matches[1][3], $matches[1][2]);
		}
		if ($matchesCount >= 3) {
			$multi[] = $this->style->convertUnits($matches[2][3], $matches[2][2]);
		}
		if ($matchesCount === 4) {
			$multi[] = $this->style->convertUnits($matches[3][3], $matches[3][2]);
		}
		return $multi;
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
		switch (count($numberValues)) {
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
