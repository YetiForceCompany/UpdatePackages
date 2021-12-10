<?php

declare(strict_types=1);
/**
 * LineHeight class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class LineHeight.
 */
class LineHeight extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			return $this->normalized = ['line-height' => $this->getNumberValues($ruleValue)[0]];
		}
		return $this->normalized;
	}
}
