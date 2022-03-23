<?php

declare(strict_types=1);
/**
 * BorderColor class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BorderColor.
 */
class BorderColor extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			$color = \YetiForcePDF\Style\Color::toRGBA($ruleValue, true);
			return $this->normalized = [
				'border-top-color' => $color,
				'border-right-color' => $color,
				'border-bottom-color' => $color,
				'border-left-color' => $color,
			];
		}
		return $this->normalized;
	}
}
