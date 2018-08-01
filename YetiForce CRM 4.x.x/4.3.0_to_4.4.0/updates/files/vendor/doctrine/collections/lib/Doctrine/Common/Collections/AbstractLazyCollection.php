<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Collections;

use Closure;

/**
 * Lazy collection that is backed by a concrete collection.
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 *
 * @since  1.2
 */
abstract class AbstractLazyCollection implements Collection
{
	/**
	 * The backed collection to use.
	 *
	 * @var Collection
	 */
	protected $collection;

	/**
	 * @var bool
	 */
	protected $initialized = false;

	/**
	 * {@inheritdoc}
	 */
	public function count()
	{
		$this->initialize();
		return $this->collection->count();
	}

	/**
	 * {@inheritdoc}
	 */
	public function add($element)
	{
		$this->initialize();
		return $this->collection->add($element);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		$this->initialize();
		$this->collection->clear();
	}

	/**
	 * {@inheritdoc}
	 */
	public function contains($element)
	{
		$this->initialize();
		return $this->collection->contains($element);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEmpty()
	{
		$this->initialize();
		return $this->collection->isEmpty();
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		$this->initialize();
		return $this->collection->remove($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeElement($element)
	{
		$this->initialize();
		return $this->collection->removeElement($element);
	}

	/**
	 * {@inheritdoc}
	 */
	public function containsKey($key)
	{
		$this->initialize();
		return $this->collection->containsKey($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key)
	{
		$this->initialize();
		return $this->collection->get($key);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getKeys()
	{
		$this->initialize();
		return $this->collection->getKeys();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValues()
	{
		$this->initialize();
		return $this->collection->getValues();
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value)
	{
		$this->initialize();
		$this->collection->set($key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function toArray()
	{
		$this->initialize();
		return $this->collection->toArray();
	}

	/**
	 * {@inheritdoc}
	 */
	public function first()
	{
		$this->initialize();
		return $this->collection->first();
	}

	/**
	 * {@inheritdoc}
	 */
	public function last()
	{
		$this->initialize();
		return $this->collection->last();
	}

	/**
	 * {@inheritdoc}
	 */
	public function key()
	{
		$this->initialize();
		return $this->collection->key();
	}

	/**
	 * {@inheritdoc}
	 */
	public function current()
	{
		$this->initialize();
		return $this->collection->current();
	}

	/**
	 * {@inheritdoc}
	 */
	public function next()
	{
		$this->initialize();
		return $this->collection->next();
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists(Closure $p)
	{
		$this->initialize();
		return $this->collection->exists($p);
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter(Closure $p)
	{
		$this->initialize();
		return $this->collection->filter($p);
	}

	/**
	 * {@inheritdoc}
	 */
	public function forAll(Closure $p)
	{
		$this->initialize();
		return $this->collection->forAll($p);
	}

	/**
	 * {@inheritdoc}
	 */
	public function map(Closure $func)
	{
		$this->initialize();
		return $this->collection->map($func);
	}

	/**
	 * {@inheritdoc}
	 */
	public function partition(Closure $p)
	{
		$this->initialize();
		return $this->collection->partition($p);
	}

	/**
	 * {@inheritdoc}
	 */
	public function indexOf($element)
	{
		$this->initialize();
		return $this->collection->indexOf($element);
	}

	/**
	 * {@inheritdoc}
	 */
	public function slice($offset, $length = null)
	{
		$this->initialize();
		return $this->collection->slice($offset, $length);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		$this->initialize();
		return $this->collection->getIterator();
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		$this->initialize();
		return $this->collection->offsetExists($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		$this->initialize();
		return $this->collection->offsetGet($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		$this->initialize();
		$this->collection->offsetSet($offset, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		$this->initialize();
		$this->collection->offsetUnset($offset);
	}

	/**
	 * Is the lazy collection already initialized?
	 *
	 * @return bool
	 */
	public function isInitialized()
	{
		return $this->initialized;
	}

	/**
	 * Initialize the collection.
	 */
	protected function initialize()
	{
		if (!$this->initialized) {
			$this->doInitialize();
			$this->initialized = true;
		}
	}

	/**
	 * Do the initialization logic.
	 */
	abstract protected function doInitialize();
}
