<?php

declare(strict_types=1);
/**
 * Padding class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Padding.
 */
class Padding extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		return $this->normalized = $this->normalizeMultiValues(['padding-top', 'padding-right', 'padding-bottom', 'padding-left'], $ruleValue);
	}
}
