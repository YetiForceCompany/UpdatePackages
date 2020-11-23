<?php

declare(strict_types=1);
/**
 * WhiteSpace class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class WhiteSpace.
 */
class WhiteSpace extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized !== null) {
			return $this->normalized;
		}
		if (in_array($ruleValue, ['normal', 'pre', 'nowrap'])) {
			$normalized = ['white-space' => $ruleValue];
		} else {
			$normalized = ['white-space' => 'normal'];
		}
		return $this->normalized = $normalized;
	}
}
