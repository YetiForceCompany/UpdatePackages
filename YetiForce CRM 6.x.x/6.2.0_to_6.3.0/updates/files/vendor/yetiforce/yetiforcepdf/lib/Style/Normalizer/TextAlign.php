<?php

declare(strict_types=1);
/**
 * TextAlign class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class TextAlign.
 */
class TextAlign extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized) {
			if (\in_array($ruleValue, ['left', 'right', 'center'])) {
				return $this->normalized = ['text-align' => $ruleValue];
			}
			return $this->normalized = ['text-align' => 'left'];
		}
		return $this->normalized;
	}
}
