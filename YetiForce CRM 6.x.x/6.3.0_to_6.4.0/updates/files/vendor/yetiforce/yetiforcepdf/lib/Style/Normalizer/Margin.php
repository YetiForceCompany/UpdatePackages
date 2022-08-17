<?php

declare(strict_types=1);
/**
 * Margin class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Margin.
 */
class Margin extends Normalizer
{
	/**
	 * {@inheritdoc}
	 */
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		return $this->normalized = $this->normalizeMultiValues(['margin-top', 'margin-right', 'margin-bottom', 'margin-left'], $ruleValue);
	}
}
