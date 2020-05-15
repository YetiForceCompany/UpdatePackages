<?php

declare(strict_types=1);
/**
 * BoxSizing class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class BoxSizing.
 */
class BoxSizing extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if ($this->normalized !== null) {
			return $this->normalized;
		}
		$normalized = ['box-sizing' => 'border-box'];
		if (in_array($ruleValue, ['border-box', 'content-box'])) {
			$normalized = ['box-sizing' => $ruleValue];
		}
		return $this->normalized = $normalized;
	}
}
