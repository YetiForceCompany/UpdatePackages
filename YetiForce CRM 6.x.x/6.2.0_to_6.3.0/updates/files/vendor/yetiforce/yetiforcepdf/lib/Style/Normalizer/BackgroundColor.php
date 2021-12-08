<?php

declare(strict_types=1);
/**
 * BackgroundColor class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
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
		if (null === $this->normalized && 'transparent' !== $ruleValue) {
			return $this->normalized = ['background-color' => \YetiForcePDF\Style\Color::toRGBA($ruleValue, true)];
		}
		if ('transparent' === $ruleValue) {
			return $this->normalized = ['background-color' => 'transparent'];
		}
		return $this->normalized;
	}
}
