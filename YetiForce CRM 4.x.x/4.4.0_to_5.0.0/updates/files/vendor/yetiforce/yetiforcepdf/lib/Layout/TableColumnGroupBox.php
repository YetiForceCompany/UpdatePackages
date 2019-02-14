<?php

declare(strict_types=1);
/**
 * TableColumnGroupBox class.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License v3
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

/**
 * Class TableColumnGroupBox.
 */
class TableColumnGroupBox extends InlineBlockBox
{
	/**
	 * {@inheritdoc}
	 */
	public function getInstructions(): string
	{
		return ''; // not renderable
	}
}
