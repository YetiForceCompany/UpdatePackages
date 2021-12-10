<?php

declare(strict_types=1);
/**
 * Color class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Color.
 */
class Color extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized && 'transparent' !== $ruleValue) {
			return $this->normalized = ['color' => \YetiForcePDF\Style\Color::toRGBA($ruleValue, true)];
		}
		if ('trasparent' === $ruleValue) {
			return $this->normalized = ['color' => 'transparent'];
		}
		return $this->normalized;
	}
}
