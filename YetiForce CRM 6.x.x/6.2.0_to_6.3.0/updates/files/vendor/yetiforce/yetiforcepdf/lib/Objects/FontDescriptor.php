<?php

declare(strict_types=1);
/**
 * FontDescriptor class.
 *
 * @package   YetiForcePDF\Objects
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects;

/**
 * Class FontDescriptor.
 */
class FontDescriptor extends \YetiForcePDF\Objects\Resource
{
	/**
	 * @var \YetiForcePDF\Objects\Font
	 */
	protected $font;

	/**
	 * Set font instance.
	 *
	 * @param \YetiForcePDF\Objects\Font $font
	 *
	 * @return $this
	 */
	public function setFont(Font $font)
	{
		$this->font = $font;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		$descriptor = $this->font->getOutputInfo()['descriptor'];
		return implode("\n", [
			$this->getRawId() . ' obj',
			'<<',
			'  /Type /FontDescriptor',
			'  /FontName /' . $this->font->getFullName(),
			'  /FontBBox ' . $descriptor['FontBBox'],
			'  /Flags ' . $descriptor['Flags'],
			'  /Ascent ' . $descriptor['Ascent'],
			'  /Descent ' . $descriptor['Descent'],
			'  /CapHeight ' . $descriptor['Ascent'],
			'  /ItalicAngle ' . $descriptor['ItalicAngle'],
			'  /StemV ' . $descriptor['StemV'],
			'  /MissingWidth ' . $descriptor['MissingWidth'],
			'  /FontFile2 ' . $this->font->getDataStream()->getReference(),
			'>>',
			'endobj',
		]);
	}
}
