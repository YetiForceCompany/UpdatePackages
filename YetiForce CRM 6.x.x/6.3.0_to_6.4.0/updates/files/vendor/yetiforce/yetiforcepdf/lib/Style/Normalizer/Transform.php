<?php

declare(strict_types=1);
/**
 * Transform class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Transform.
 */
class Transform extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		$normalized = [
			'transform' => [],
		];
		$operations = preg_split('/\s+/i', $ruleValue);
		foreach ($operations as $operation) {
			$matches = [];
			preg_match('/(rotate|scale|translate)\(([0-9]+)([a-z]+)?\)/i', $operation, $matches);
			$normalizerName = static::getNormalizerClassName('transform-' . $matches[1]);
			$normalizer = (new $normalizerName())
				->setDocument($this->document)
				->setStyle($this->style)
				->init();
			foreach ($normalizer->normalize($matches[2], $matches[1]) as $name => $value) {
				$normalized['transform'][] = [$name, $value];
			}
		}
		return $normalized;
	}
}
