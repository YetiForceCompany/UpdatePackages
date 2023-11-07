<?php

declare(strict_types=1);
/**
 * PageGroupBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

/**
 * Class PageGroupBox.
 */
class PageGroupBox extends BlockBox
{
	/**
	 * {@inheritdoc}
	 */
	protected $root = true;

	public $format = 'A4';
	public $orientation = 'P';
	public $marginLeft = 30;
	public $marginRight = 30;
	public $marginTop = 40;
	public $marginBottom = 40;
	public $headerTop = 10;
	public $footerBottom = 10;
}
