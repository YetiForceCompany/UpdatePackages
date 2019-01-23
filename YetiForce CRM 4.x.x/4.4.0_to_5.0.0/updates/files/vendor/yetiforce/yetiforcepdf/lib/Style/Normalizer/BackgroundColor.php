<?php

declare(strict_types=1);
/**
 * BackgroundColor class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BackgroundColor.
 */
class BackgroundColor extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized === null && $ruleValue !== 'transparent') {
			return $this->normalized = ['background-color' => \YetiForcePDF\Style\Color::toRGBA($ruleValue, true)];
		}
		if ($ruleValue === 'transparent') {
			return $this->normalized = ['background-color' => 'transparent'];
		}
		return $this->normalized;
	}
}
