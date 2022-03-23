<?php

declare(strict_types=1);
/**
 * BorderWidth class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BorderWidth.
 */
class BorderWidth extends Normalizer
{
	/**
	 * {@inheritdoc}
	 */
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		return $this->normalized = $this->normalizeMultiValues([
			'border-top-width',
			'border-right-width',
			'border-bottom-width',
			'border-left-width', ], $ruleValue);
	}
}
