<?php

declare(strict_types=1);
/**
 * MaxWidth class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class MaxWidth.
 */
class MaxWidth extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized === null && $ruleValue !== 'none') {
			return $this->normalized = ['max-width' => $this->getNumberValues($ruleValue)[0]];
		}
		if ($ruleValue === 'none') {
			return $this->normalized = ['max-width' => 'none'];
		}
		return $this->normalized;
	}
}
