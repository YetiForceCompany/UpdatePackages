<?php

declare(strict_types=1);
/**
 * VerticalAlign class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class VerticalAlign.
 */
class VerticalAlign extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		if (\in_array($ruleValue, ['top', 'bottom', 'middle', 'baseline'])) {
			$normalized = ['vertical-align' => $ruleValue];
		} else {
			$normalized = ['vertical-align' => 'baseline'];
		}
		return $this->normalized = $normalized;
	}
}
