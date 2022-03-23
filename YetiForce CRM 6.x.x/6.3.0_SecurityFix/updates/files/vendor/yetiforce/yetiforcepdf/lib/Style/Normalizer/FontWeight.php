<?php

declare(strict_types=1);
/**
 * FontWeight class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class FontWeight.
 */
class FontWeight extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		$ruleValue = strtolower($ruleValue);
		if (!\in_array($ruleValue, ['100', '200', '300', '400', '500', '600', '700', '800', '900'])) {
			switch ($ruleValue) {
				case 'normal':
					$ruleValue = '400';
					break;
				case 'bold':
					$ruleValue = '700';
					break;
				default:
					$ruleValue = '400';
			}
		}
		return $this->normalized = ['font-weight' => $ruleValue];
	}
}
