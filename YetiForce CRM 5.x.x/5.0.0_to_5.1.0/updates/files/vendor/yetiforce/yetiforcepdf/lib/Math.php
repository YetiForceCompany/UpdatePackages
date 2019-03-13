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
	public static $scale = 2;

	/**
	 * Add two numbers.
	 *
	 * @params string[] $numbers
	 *
	 * @return string
	 */
	public static function add(string ...$numbers)
	{
		if (!isset($numbers[2])) {
			return bcadd($numbers[0], $numbers[1], static::$scale);
		}
		$result = '0';
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
		if (!isset($numbers[2])) {
			return bcsub($numbers[0], $numbers[1], static::$scale);
		}
		$result = $numbers[0];
		for ($i = 1,$len = count($numbers); $i < $len; ++$i) {
			$result = bcsub($result, $numbers[$i], static::$scale);
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
		if (!isset($numbers[2])) {
			return bcmul($numbers[0], $numbers[1], static::$scale);
		}
		$result = '1';
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
		if (!isset($numbers[2])) {
			if ((float) $numbers[0] !== (float) 0 && (float) $numbers[1] !== (float) 0) {
				return bcdiv($numbers[0], $numbers[1], static::$scale);
			}

			return '0';
		}
		$result = $numbers[0];
		for ($i = 1,$len = count($numbers); $i < $len; ++$i) {
			if ((float) $numbers[$i] === (float) 0) {
				return '0';
			}
			$result = bcdiv($result, $numbers[$i], static::$scale);
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
		$result = '0';
		foreach ($numbers as $number) {
			$result = bccomp($number, $result, static::$scale) === 1 ? $number : $result;
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
		$result = $numbers[0];
		for ($i = 1,$len = count($numbers); $i < $len; ++$i) {
			$result = bccomp($result, $numbers[$i], static::$scale) === 1 ? $numbers[$i] : $result;
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
