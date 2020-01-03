<?php

declare(strict_types=1);
/**
 * MarginRight class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class MarginRight.
 */
class MarginRight extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized === null) {
			return $this->normalized = ['margin-right' => $this->getNumberValues($ruleValue)[0]];
		}
		return $this->normalized;
	}
}
