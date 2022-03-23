<?php

declare(strict_types=1);
/**
 * NameObject class.
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class NameObject.
 */
class NameObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'Name';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Name';

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return '';
	}
}
