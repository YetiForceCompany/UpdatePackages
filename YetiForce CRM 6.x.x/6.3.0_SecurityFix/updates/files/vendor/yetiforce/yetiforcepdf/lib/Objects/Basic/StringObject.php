<?php

declare(strict_types=1);
/**
 * StringObject class.
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class StringObject.
 */
class StringObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'String';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'String';

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return '';
	}
}
