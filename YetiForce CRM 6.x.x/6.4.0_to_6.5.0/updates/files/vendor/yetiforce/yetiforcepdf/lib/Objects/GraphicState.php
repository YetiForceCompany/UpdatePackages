<?php

declare(strict_types=1);
/**
 * GraphicState class.
 *
 * @package   YetiForcePDF\Objects
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects;

/**
 * Class GraphicState.
 */
class GraphicState extends \YetiForcePDF\Objects\Resource
{
	/**
	 * Which type of dictionary (Page, Catalog, Font etc...).
	 *
	 * @var string
	 */
	protected $resourceType = 'ExtGState';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'ExtGState';
	/**
	 * Graphic state number.
	 *
	 * @var string
	 */
	protected $number = 'GS1';

	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		parent::init();
		$this->number = 'GS' . $this->document->getActualGraphicStateId();
		$this->document->getCurrentPage()->addResource($this->resourceType, $this->number, $this);
		$this->addValue('Type', '/ExtGState');
		$this->addValue('SA', 'true');
		return $this;
	}

	/**
	 * Get number.
	 *
	 * @return string
	 */
	public function getNumber()
	{
		return $this->number;
	}
}
