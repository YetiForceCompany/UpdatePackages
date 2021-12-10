<?php

declare(strict_types=1);
/**
 * BooleanObject class.
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class BooleanObject.
 */
class BooleanObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'Boolean';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Boolean';

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return '';
	}
}
