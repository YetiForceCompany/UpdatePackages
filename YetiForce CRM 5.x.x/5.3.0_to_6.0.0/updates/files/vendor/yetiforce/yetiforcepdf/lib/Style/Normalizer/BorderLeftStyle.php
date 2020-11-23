<?php

declare(strict_types=1);
/**
 * BorderLeftStyle class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BorderLeftStyle.
 */
class BorderLeftStyle extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized === null) {
			if (in_array($ruleValue, ['none', 'solid', 'dashed', 'dotted'])) {
				$style = $ruleValue;
			} else {
				$style = 'none';
			}
			return $this->normalized = ['border-left-style' => $style];
		}
		return $this->normalized;
	}
}
