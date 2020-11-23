<?php

declare(strict_types=1);
/**
 * Height class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Height.
 */
class Height extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized === null && $ruleValue !== 'auto') {
			return ['height' => $this->getNumberValues($ruleValue)[0]];
		}
		if ($ruleValue === 'auto') {
			return $this->normalized = ['height' => 'auto'];
		}
		return $this->normalized;
	}
}
