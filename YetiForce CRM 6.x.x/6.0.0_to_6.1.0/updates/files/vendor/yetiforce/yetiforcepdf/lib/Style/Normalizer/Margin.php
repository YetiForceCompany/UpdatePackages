<?php

declare(strict_types=1);
/**
 * Margin class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
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
		if ($this->normalized !== null) {
			return $this->normalized;
		}
		return $this->normalized = $this->normalizeMultiValues(['margin-top', 'margin-right', 'margin-bottom', 'margin-left'], $ruleValue);
	}
}
