<?php

declare(strict_types=1);
/**
 * Height class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
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
		if (null === $this->normalized && 'auto' !== $ruleValue) {
			return ['height' => $this->getNumberValues($ruleValue)[0]];
		}
		if ('auto' === $ruleValue) {
			return $this->normalized = ['height' => 'auto'];
		}
		return $this->normalized;
	}
}
