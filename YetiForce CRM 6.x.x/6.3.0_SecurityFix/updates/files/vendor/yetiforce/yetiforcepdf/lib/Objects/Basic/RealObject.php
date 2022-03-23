<?php

declare(strict_types=1);
/**
 * RealObject class (real number).
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class RealObject.
 */
class RealObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'Real';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Real';

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		return '';
	}
}
