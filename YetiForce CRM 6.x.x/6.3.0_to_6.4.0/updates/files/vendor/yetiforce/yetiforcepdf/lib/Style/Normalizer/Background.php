<?php

declare(strict_types=1);
/**
 * Background class.
 *
 * @package   YetiForcePDF\Style\Normalizer
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Style\Normalizer;

/**
 * Class Background.
 */
class Background extends Normalizer
{
	public function normalize($ruleValue, string $ruleName = ''): array
	{
		$normalizerName = \YetiForcePDF\Style\Normalizer\Normalizer::getNormalizerClassName('background-color');
		return (new $normalizerName())
			->setDocument($this->document)
			->setStyle($this->style)
			->init()
			->normalize($ruleValue, $ruleName);
	}
}
