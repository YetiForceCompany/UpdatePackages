<?php

declare(strict_types=1);
/**
 * BackgroundImage class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BackgroundImage.
 */
class BackgroundImage extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null === $this->normalized && 'transparent' !== $ruleValue) {
			return $this->normalized = ['background-image' => $ruleValue];
		}
		if ('transparent' === $ruleValue) {
			return $this->normalized = ['background-image' => 'transparent'];
		}
		return $this->normalized;
	}
}
