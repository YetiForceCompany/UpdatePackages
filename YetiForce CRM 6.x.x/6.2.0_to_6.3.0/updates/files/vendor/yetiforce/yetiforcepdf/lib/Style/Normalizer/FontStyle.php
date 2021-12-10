<?php

declare(strict_types=1);
/**
 * FontStyle class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class FontStyle.
 */
class FontStyle extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		$ruleValue = strtolower($ruleValue);
		if (!\in_array($ruleValue, ['normal', 'italic'])) {
			$ruleValue = 'normal';
		}
		return $this->normalized = ['font-style' => $ruleValue];
	}
}
