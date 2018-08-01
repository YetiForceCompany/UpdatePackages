<?php
/**
 * Case-insensitive dictionary, suitable for HTTP headers.
 */

/**
 * Case-insensitive dictionary, suitable for HTTP headers.
 */
class Requests_Utility_CaseInsensitiveDictionary implements ArrayAccess, IteratorAggregate
{
	/**
	 * Actual item data.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Creates a case insensitive dictionary.
	 *
	 * @param array $data Dictionary/map to convert to case-insensitive
	 */
	public function __construct(array $data = [])
	{
		foreach ($data as $key => $value) {
			$this->offsetSet($key, $value);
		}
	}

	/**
	 * Check if the given item exists.
	 *
	 * @param string $key Item key
	 *
	 * @return bool Does the item exist?
	 */
	public function offsetExists($key)
	{
		$key = strtolower($key);
		return isset($this->data[$key]);
	}

	/**
	 * Get the value for the item.
	 *
	 * @param string $key Item key
	 *
	 * @return string Item value
	 */
	public function offsetGet($key)
	{
		$key = strtolower($key);
		if (!isset($this->data[$key])) {
			return null;
		}

		return $this->data[$key];
	}

	/**
	 * Set the given item.
	 *
	 *
	 * @param string $key   Item name
	 * @param string $value Item value
	 *
	 * @throws Requests_Exception On attempting to use dictionary as list (`invalidset`)
	 */
	public function offsetSet($key, $value)
	{
		if ($key === null) {
			throw new Requests_Exception('Object is a dictionary, not a list', 'invalidset');
		}

		$key = strtolower($key);
		$this->data[$key] = $value;
	}

	/**
	 * Unset the given header.
	 *
	 * @param string $key
	 */
	public function offsetUnset($key)
	{
		unset($this->data[strtolower($key)]);
	}

	/**
	 * Get an iterator for the data.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->data);
	}

	/**
	 * Get the headers as an array.
	 *
	 * @return array Header data
	 */
	public function getAll()
	{
		return $this->data;
	}
}
