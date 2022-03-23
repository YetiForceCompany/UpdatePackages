<?php

declare(strict_types=1);
/**
 * WhiteSpace class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
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
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		if (\in_array($ruleValue, ['normal', 'pre', 'nowrap'])) {
			$normalized = ['white-space' => $ruleValue];
		} else {
			$normalized = ['white-space' => 'normal'];
		}
		return $this->normalized = $normalized;
	}
}
