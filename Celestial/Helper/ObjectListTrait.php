<?php
namespace Celestial\Helper;

use Celestial\Exception\InvalidArgumentException;

trait ObjectListTrait
{
	/**
	 * @var array
	 */
	protected $items = array();

	/**
	 * @var integer
	 */
	protected $position = 0;

	public function __clone()
	{
		$originalItems = $this->items;

		$this->items = array();
		$this->position = 0;

		foreach ($originalItems as $item) {
			$this->append(clone $item);
		}
	}

	public function current()
	{
		return $this->getByIndex($this->position);
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		$this->position++;
		return $this->current();
	}

	public function rewind()
	{
		$this->position = 0;
		return $this->current();
	}

	public function valid()
	{
		return array_key_exists($this->position, $this->items);
	}

	public function length()
	{
		return count($this->items);
	}

	public function pop()
	{
		$poppedItem = array_pop($this->items);
		$maxPosition = count($this->items) - 1;
		$this->position = min($this->position, $maxPosition);
		return $poppedItem;
	}

	public function shift()
	{
		return array_shift($this->items);
	}

	public function getByIndex($index)
	{
		$item = null;
		if (array_key_exists($index, $this->items)) {
			$item = $this->items[$index];
		}
		return $item;
	}

	public function getByProperty($propertyName, $propertyValue)
	{
		$index = $this->indexOfPropertyValue($propertyName, $propertyValue);
		if ($index === -1) {
			$list = json_encode($this->items);
			$message = 'Failed to find item in list with property `%s` = `%s`. Items: %s';
			$message = sprintf($message, $propertyName, $propertyValue, $list);
			throw new InvalidArgumentException($message);
		}
		return $this->getByIndex($index);
	}

	public function indexOfPropertyValue($propertyName, $propertyValue)
	{
		$index = -1;
		foreach ($this as $key => $item) {
			if ($item->$propertyName === $propertyValue) {
				$index = $key;
			}
		}
		return $index;
	}

	public function removeByIndex($index)
	{
		unset($this->items[$index]);
		array_splice($this->items, $index, 0);
		if ($this->position > $index) {
			$this->position--;
		}
		return $this;
	}

	/**
	 * Return a copy of this list, containing only items matching a specific property value
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @return $this
	 */
	public function findByPropertyValue($propertyName, $propertyValue)
	{
		$found = clone $this;
		$found->filterByPropertyValue($propertyName, $propertyValue);
		return $found;
	}

	/**
	 * Remove items not matching a specific property value
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @return $this
	 */
	public function filterByPropertyValue($propertyName, $propertyValue)
	{
		$indicesToRemove = array();
		foreach ($this as $index => $item) {
			if ($item->{$propertyName} !== $propertyValue) {
				$indicesToRemove[] = $index;
			}
		}
		while (!empty($indicesToRemove)) {
			$index = array_pop($indicesToRemove);
			$this->removeByIndex($index);
		}
		return $this;
	}

	/**
	 * Remove items matching a specific property value
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @return $this
	 */
	public function removeByPropertyValue($propertyName, $propertyValue)
	{
		$indicesToRemove = array();
		foreach ($this as $index => $item) {
			if ($item->{$propertyName} === $propertyValue) {
				$indicesToRemove[] = $index;
			}
		}
		while (!empty($indicesToRemove)) {
			$index = array_pop($indicesToRemove);
			$this->removeByIndex($index);
		}
		return $this;
	}

	protected function append($item)
	{
		$this->items[] = $item;
		return $this;
	}

	protected function prepend($item)
	{
		array_unshift($this->items, $item);
		return $this;
	}
}
