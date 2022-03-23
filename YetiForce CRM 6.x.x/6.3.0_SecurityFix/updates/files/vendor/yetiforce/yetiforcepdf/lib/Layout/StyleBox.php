<?php

declare(strict_types=1);
/**
 * StyleBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

/**
 * Class StyleBox.
 */
class StyleBox extends BlockBox
{
	/**
	 * @var bool do we need to measure this box ?
	 */
	protected $forMeasurement = false;
	/**
	 * @var bool is this element show up in view? take space?
	 */
	protected $renderable = false;
	/**
	 * @var bool
	 */
	protected $displayable = false;
}
