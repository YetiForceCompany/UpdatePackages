<?php

declare(strict_types=1);
/**
 * TransformRotate class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class TransformRotate.
 */
class TransformRotate extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		$matches = [];
		preg_match('/\s?([0-9]+)([a-z]+)?\s?/i', $ruleValue, $matches);
		return [
			'rotate' => $matches[1],
		];
	}
}
