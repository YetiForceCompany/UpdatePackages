<?php

declare(strict_types=1);
/**
 * Display class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Display.
 */
class Display extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized !== null) {
			return $this->normalized;
		}
		$normalized = ['display' => $ruleValue];
		if ($element = $this->style->getElement()) {
			if ($element->isTextNode()) {
				$normalized = ['display' => 'inline'];
			}
		}
		return $this->normalized = $normalized;
	}
}
