<?php

declare(strict_types=1);
/**
 * IntegerObject class.
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class IntegerObject.
 */
class IntegerObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'Integer';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Integer';

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return '';
	}
}
