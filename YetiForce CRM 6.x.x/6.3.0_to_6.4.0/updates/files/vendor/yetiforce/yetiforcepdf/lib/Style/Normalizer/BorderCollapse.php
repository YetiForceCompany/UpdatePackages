<?php

declare(strict_types=1);
/**
 * BorderCollapse class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BorderCollapse.
 */
class BorderCollapse extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			if (!\in_array($ruleValue, ['collapse', 'separate', 'inherit'])) {
				$ruleValue = 'separate';
			}
			return $this->normalized = ['border-collapse' => $ruleValue];
		}
		return $this->normalized;
	}
}
