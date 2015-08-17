<?php
namespace Sloth\Module\Graph\Helper;

use Sloth\Exception\InvalidArgumentException;

abstract class ObjectList implements \Iterator
{
	/**
	 * @var array
	 */
	protected $items = array();

	/**
	 * @var integer
	 */
	protected $position = 0;

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
		return $this;
	}

	public function removeByPropertyValue($propertyName, $propertyValue)
	{
		$index = $this->indexOfPropertyValue($propertyName, $propertyValue);
		if ($index !== -1) {
			$this->removeByIndex($index);
		}
		return $this;
	}
}
