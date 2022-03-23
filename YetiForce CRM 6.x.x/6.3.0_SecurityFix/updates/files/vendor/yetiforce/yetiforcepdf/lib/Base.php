<?php

declare(strict_types=1);
/**
 * Base class.
 *
 * @package   YetiForcePDF
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF;

/**
 * Class Base.
 */
class Base
{
	/**
	 * @var \YetiForcePDF\Document
	 */
	protected $document;

	/**
	 * Set document.
	 *
	 * @param \YetiForcePDF\Document $document
	 */
	public function setDocument(Document $document)
	{
		$this->document = $document;
		return $this;
	}

	/**
	 * Get document.
	 *
	 * @return \YetiForcePDF\Document
	 */
	public function getDocument(): Document
	{
		return $this->document;
	}

	/**
	 * Initialisation instead of constructor.
	 *
	 * @param array $args - associative array of values - might be helpful
	 */
	public function init()
	{
		return $this;
	}
}
