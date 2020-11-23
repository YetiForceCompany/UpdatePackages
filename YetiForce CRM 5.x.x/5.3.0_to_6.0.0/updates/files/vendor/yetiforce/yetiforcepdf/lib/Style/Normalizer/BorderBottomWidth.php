<?php

declare(strict_types=1);
/**
 * BorderBottomWidth class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BorderBottomWidth.
 */
class BorderBottomWidth extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized === null) {
			return $this->normalized = ['border-bottom-width' => $this->getNumberValues($ruleValue)[0]];
		}
		return $this->normalized;
	}
}
