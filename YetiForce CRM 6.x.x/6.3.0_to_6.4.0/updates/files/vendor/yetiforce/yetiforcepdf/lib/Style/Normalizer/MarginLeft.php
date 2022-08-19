<?php

declare(strict_types=1);
/**
 * MarginLeft class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class MarginLeft.
 */
class MarginLeft extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			$css = [];
			if ($number = $this->getNumberValues($ruleValue)) {
				$css = ['margin-left' => $number[0]];
			}
			$this->normalized = $css;
		}
		return $this->normalized;
	}
}
