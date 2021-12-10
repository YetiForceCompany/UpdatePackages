<?php

declare(strict_types=1);
/**
 * NullObject class.
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class NullObject.
 */
class NullObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'Null';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Null';

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return '';
	}
}
