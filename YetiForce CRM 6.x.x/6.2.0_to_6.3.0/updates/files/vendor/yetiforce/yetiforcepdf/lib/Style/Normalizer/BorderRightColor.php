<?php

declare(strict_types=1);
/**
 * BorderRightColor class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BorderRightColor.
 */
class BorderRightColor extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			return $this->normalized = ['border-right-color' => \YetiForcePDF\Style\Color::toRGBA($ruleValue, true)];
		}
		return $this->normalized;
	}
}
