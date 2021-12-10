<?php

declare(strict_types=1);
/**
 * BorderSpacing class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BorderSpacing.
 */
class BorderSpacing extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			return $this->normalized = ['border-spacing' => $this->getNumberValues($ruleValue)[0]];
		}
		return $this->normalized;
	}
}
