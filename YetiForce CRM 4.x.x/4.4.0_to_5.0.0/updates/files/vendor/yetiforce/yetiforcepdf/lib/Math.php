<?php

declare(strict_types=1);
/**
 * Math class.
 *
 * @package   YetiForcePDF
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF;

/**
 * Class Math.
 */
class Math
{
	public static $scale = 8;

	/**
	 * Add two numbers.
	 *
	 * @params string[] $numbers
	 *
	 * @return string
	 */
	public static function add(string ...$numbers)
	{
		$numbers = array_reverse($numbers);
		$result = array_pop($numbers);
		$numbers = array_reverse($numbers);
		foreach ($numbers as $number) {
			$result = bcadd($result, $number, static::$scale);
		}
		return $result;
	}

	/**
	 * Subtract two numbers.
	 *
	 * @params string[] $numbers
	 *
	 * @return string
	 */
	public static function sub(string ...$numbers)
	{
		$numbers = array_reverse($numbers);
		$result = array_pop($numbers);
		$numbers = array_reverse($numbers);
		foreach ($numbers as $number) {
			$result = bcsub($result, $number, static::$scale);
		}
		return $result;
	}

	/**
	 * Multiply numbers.
	 *
	 * @param string[] $numbers
	 *
	 * @return mixed|string
	 */
	public static function mul(string ...$numbers)
	{
		$numbers = array_reverse($numbers);
		$result = array_pop($numbers);
		$numbers = array_reverse($numbers);
		foreach ($numbers as $number) {
			$result = bcmul($result, $number, static::$scale);
		}
		return $result;
	}

	/**
	 * Divide two numbers.
	 *
	 * @params string[] $numbers
	 *
	 * @return string
	 */
	public static function div(string ...$numbers)
	{
		foreach ($numbers as $number) {
			if (static::comp($number, '0') === 0) {
				return '0';
			}
		}
		$numbers = array_reverse($numbers);
		$result = array_pop($numbers);
		$numbers = array_reverse($numbers);
		foreach ($numbers as $number) {
			$result = bcdiv($result, $number, static::$scale);
		}
		return $result;
	}

	/**
	 * Compare two numbers.
	 *
	 * @param string $left
	 * @param string $right
	 *
	 * @return string
	 */
	public static function comp(string $left, string $right)
	{
		return bccomp($left, $right, static::$scale);
	}

	/**
	 * Get max number.
	 *
	 * @param string ...$numbers
	 *
	 * @return string
	 */
	public static function max(string ...$numbers)
	{
		$numbers = array_reverse($numbers);
		$result = array_pop($numbers);
		$numbers = array_reverse($numbers);
		foreach ($numbers as $number) {
			$result = static::comp($number, $result) >= 0 ? $number : $result;
		}
		return $result;
	}

	/**
	 * Get min number.
	 *
	 * @param string ...$numbers
	 *
	 * @return string
	 */
	public static function min(string ...$numbers)
	{
		$numbers = array_reverse($numbers);
		$result = array_pop($numbers);
		$numbers = array_reverse($numbers);
		foreach ($numbers as $number) {
			$result = static::comp($result, $number) > 0 ? $number : $result;
		}
		return $result;
	}

	/**
	 * Get percent from value.
	 *
	 * @param string $percent
	 * @param string $from
	 *
	 * @return mixed|string
	 */
	public static function percent(string $percent, string $from)
	{
		$percent = trim($percent, '%');
		return static::mul(static::div($from, '100'), $percent);
	}
}
