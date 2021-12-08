<?php

declare(strict_types=1);
/**
 * FontFamily class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class FontFamily.
 */
class FontFamily extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		return $this->normalized = ['font-family' => str_replace(['\'', '"'], '', $ruleValue)];
	}
}
