<?php

declare(strict_types=1);
/**
 * Meta class.
 *
 * @package   YetiForcePDF\Objects
 *
 * @copyright YetiForce Sp. z o.o
 * @license   MIT
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace YetiForcePDF\Objects;

/**
 * Class Meta.
 */
class Meta extends \YetiForcePDF\Objects\Basic\DictionaryObject
{
	/**
	 * @var string
	 */
	protected $author = 'YetiForce';
	/**
	 * @var string
	 */
	protected $creator = 'YetiForceCRM';
	/**
	 * @var string
	 */
	protected $producer = 'YetiForcePDF';
	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var string
	 */
	protected $subject = '';
	/**
	 * @var string[]
	 */
	protected $keywords = [];

	/**
	 * Set author.
	 *
	 * @param string $author
	 *
	 * @return $this
	 */
	public function setAuthor(string $author)
	{
		$this->author = $author;
		return $this;
	}

	/**
	 * Get author.
	 *
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * Set creator.
	 *
	 * @param string $creator
	 *
	 * @return $this
	 */
	public function setCreator(string $creator)
	{
		$this->creator = $creator;
		return $this;
	}

	/**
	 * Get creator.
	 *
	 * @return string
	 */
	public function getCreator()
	{
		return $this->creator;
	}

	/**
	 * Set producer.
	 *
	 * @param string $producer
	 *
	 * @return $this
	 */
	public function setProducer(string $producer)
	{
		$this->producer = $producer;
		return $this;
	}

	/**
	 * Get producer.
	 *
	 * @return string
	 */
	public function getProducer()
	{
		return $this->producer;
	}

	/**
	 * Set title.
	 *
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setTitle(string $title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Get title.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set subject.
	 *
	 * @param string $subject
	 *
	 * @return $this
	 */
	public function setSubject(string $subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Get subject.
	 *
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * Set keywords.
	 *
	 * @param string[] $keywords
	 *
	 * @return $this
	 */
	public function setKeywords(array $keywords)
	{
		foreach ($keywords as &$keyword) {
			$keyword = trim($keyword);
		}
		unset($keyword);
		$this->keywords = $keywords;
		return $this;
	}

	/**
	 * Get keywords.
	 *
	 * @return string[]
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}

	public function render(): string
	{
		if ($this->title) {
			$this->addValue('Title', $this->document->filterText($this->title, 'UTF-16', true, true));
		}
		if ($this->subject) {
			$this->addValue('Subject', $this->document->filterText($this->subject, 'UTF-16', true, true));
		}
		if ($this->author) {
			$this->addValue('Author', $this->document->filterText($this->author, 'UTF-16', true, true));
		}
		if ($this->creator) {
			$this->addValue('Creator', $this->document->filterText($this->creator, 'UTF-16', true, true));
		}
		if ($this->producer) {
			$this->addValue('Producer', $this->document->filterText($this->producer, 'UTF-16', true, true));
		}
		if (!empty($this->keywords) && '' !== trim(implode('', $this->keywords))) {
			$this->addValue('Keywords', $this->document->filterText(implode(', ', $this->keywords), 'UTF-16', true, true));
		}
		return parent::render();
	}
}
