<?php

declare(strict_types=1);
/**
 * Opacity class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Opacity.
 */
class Opacity extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			return ['opacity' => (string) (float) $ruleValue];
		}
		return $this->normalized;
	}
}
