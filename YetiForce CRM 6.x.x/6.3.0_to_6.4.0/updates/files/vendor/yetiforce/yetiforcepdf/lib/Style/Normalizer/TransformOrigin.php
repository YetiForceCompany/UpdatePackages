<?php

declare(strict_types=1);
/**
 * TransformOrigin class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class TransformOrigin.
 */
class TransformOrigin extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		$values = $this->getNumberValues($ruleValue);
		return [
			'transform-origin' => [$x, $y],
		];
	}
}
