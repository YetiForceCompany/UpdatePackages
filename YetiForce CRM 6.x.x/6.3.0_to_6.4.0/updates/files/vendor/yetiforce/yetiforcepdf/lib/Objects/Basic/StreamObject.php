<?php

declare(strict_types=1);
/**
 * StreamObject class.
 *
 * @package   YetiForcePDF\Objects\Basic
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects\Basic;

/**
 * Class StreamObject.
 */
class StreamObject extends \YetiForcePDF\Objects\PdfObject
{
	/**
	 * Basic object type (integer, string, boolean, dictionary etc..).
	 *
	 * @var string
	 */
	protected $basicType = 'Stream';
	/**
	 * Object name.
	 *
	 * @var string
	 */
	protected $name = 'Stream';
	/**
	 * Content of the stream as string instructions.
	 *
	 * @var string[]
	 */
	protected $content = [];
	/**
	 * Filter used to decode stream.
	 *
	 * @var string|null
	 */
	protected $filter;

	/**
	 * Initialisation.
	 *
	 * @return $this
	 */
	public function init()
	{
		parent::init();
		$this->id = $this->document->getActualId();
		return $this;
	}

	/**
	 * Add raw content instructions as string.
	 *
	 * @param string $content
	 * @param string $filter
	 *
	 * @return \YetiForcePDF\Objects\Basic\StreamObject
	 */
	public function addRawContent(string $content, string $filter = ''): self
	{
		$this->content[] = $content;
		if ($filter) {
			$this->filter = $filter;
		}
		return $this;
	}

	/**
	 * Set filter.
	 *
	 * @param string $filter
	 *
	 * @return $this
	 */
	public function setFilter(string $filter)
	{
		$this->filter = $filter;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): string
	{
		$stream = trim(implode("\n", $this->content), "\n");
		$sizeBefore = mb_strlen($stream, '8bit');
		if ('FlateDecode' === $this->filter) {
			$stream = gzcompress($stream);
		}
		$filter = $this->filter ? '/Filter /' . $this->filter : '';
		return implode("\n", [
			$this->getRawId() . ' obj',
			'<</Length ' . mb_strlen($stream, '8bit') . '/Lenght1 ' . $sizeBefore . $filter . '>>stream',
			$stream,
			'endstream',
			'endobj',
		]);
	}
}
