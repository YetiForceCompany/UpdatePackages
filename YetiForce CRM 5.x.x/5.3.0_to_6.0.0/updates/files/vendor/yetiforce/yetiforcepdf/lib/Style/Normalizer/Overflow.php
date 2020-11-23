<?php

declare(strict_types=1);
/**
 * Overflow class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Overflow.
 */
class Overflow extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized !== null) {
			return $this->normalized;
		}
		if (in_array($ruleValue, ['visible', 'hidden'])) {
			return $this->normalized = ['overflow' => $ruleValue];
		}
		return $this->normalized = ['overflow' => 'visible'];
	}
}
