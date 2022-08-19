<?php

declare(strict_types=1);
/**
 * WordWrap class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class WordWrap.
 */
class WordWrap extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		if (null !== $this->normalized) {
			return $this->normalized;
		}
		if (\in_array($ruleValue, ['normal', 'break-word'])) {
			$normalized = ['word-wrap' => $ruleValue];
		} else {
			$normalized = ['word-wrap' => 'normal'];
		}
		return $this->normalized = $normalized;
	}
}
