<?php

declare(strict_types=1);
/**
 * BorderBottomColor class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BorderBottomColor.
 */
class BorderBottomColor extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			return $this->normalized = ['border-bottom-color' => \YetiForcePDF\Style\Color::toRGBA($ruleValue, true)];
		}
		return $this->normalized;
	}
}
