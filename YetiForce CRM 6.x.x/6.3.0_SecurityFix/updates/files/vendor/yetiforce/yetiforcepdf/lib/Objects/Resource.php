<?php

declare(strict_types=1);
/**
 * Resource class.
 *
 * @package   YetiForcePDF\Objects
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects;

/**
 * Class Resource.
 */
class Resource extends \YetiForcePDF\Objects\Basic\DictionaryObject
{
	/**
	 * Resource type.
	 *
	 * @var string
	 */
	protected $resourceType = 'Resource';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Resource';

	/**
	 * Get resource type.
	 *
	 * @return string
	 */
	public function getResourceType(): string
	{
		return $this->resourceType;
	}
}
