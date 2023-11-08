<?php

declare(strict_types=1);
/**
 * DictionaryObject class.
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class DictionaryObject.
 */
class DictionaryObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'Dictionary';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Dictionary';
	/**
	 * Which type of dictionary (Page, Catalog, Font etc...).
	 *
	 * @var string
	 */
	protected $dictionaryType = '';
	/**
	 * @var array
	 */
	protected $values = [];

	/**
	 * Initialisation.
	 *
	 * @return $this
	 */
	public function init()
	{
		parent::init();
		$this->setId($this->document->getActualId());
		return $this;
	}

	/**
	 * Add value.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function addValue(string $name, string $value)
	{
		$this->values[] = ['/' . $name, $value];
		return $this;
	}

	/**
	 * Clear all values.
	 *
	 * @return $this
	 */
	public function clearValues()
	{
		$this->values = [];
		return $this;
	}

	/**
	 * Get dictionary type (Page, Catalog, Font etc...).
	 *
	 * @return string
	 */
	public function getDictionaryType()
	{
		return $this->dictionaryType;
	}

	/**
	 * Set dictionary type.
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setDictionaryType(string $type)
	{
		$this->addValue('Type', '/' . $type);
		$this->dictionaryType = $type;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		$values = [
			$this->getRawId() . ' obj',
			'<<',
		];
		foreach ($this->values as $value) {
			$values[] = '  ' . implode(' ', $value);
		}
		$values[] = '>>';
		$values[] = 'endobj';
		return implode("\n", $values);
	}
}
