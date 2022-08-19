<?php

declare(strict_types=1);
/**
 * Box interface.
 *
 * @package   YetiForcePDF\Layout
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Layout;

use YetiForcePDF\Layout\Coordinates\Offset;

/**
 * Interface BoxInterface.
 */
interface BoxInterface
{
	/**
	 * Measure width.
	 *
	 * @return $this
	 */
	public function measureWidth();

	/**
	 * Measure height.
	 *
	 * @return $this
	 */
	public function measureHeight();

	/**
	 * Measure offset relative to parent Box.
	 *
	 * @return $this
	 */
	public function measureOffset();

	/**
	 * Measure absolute position.
	 *
	 * @return $this
	 */
	public function measurePosition();
}
