<?php
namespace DemoGraph\Module\Graph\QuerySet;

use DemoGraph\Module\Graph\Definition;

class DataTree
{
	/**
	 * @var array
	 */
	protected $items = array();

	/**
	 * @var array
	 */
	protected $children = array();

	public function pushItem($name, $item)
	{
		$this->items[$name] = $item;
		return $this;
	}

	public function containsItem($itemName)
	{
		return array_key_exists($itemName, $this->items);
	}

	public function getItem($key)
	{
		$foundItem = null;
		if (array_key_exists($key, $this->items)) {
			$foundItem = $this->items[$key];
		}
		return $foundItem;
	}

	public function pushChild($name, DataTree $child)
	{
		$this->children[$name] = $child;
		return $this;
	}

	public function containsChild($childName)
	{
		return array_key_exists($childName, $this->children);
	}

	/**
	 * @param string $childName
	 * @return DataTree
	 */
	public function getChild($childName)
	{
		$foundChild = null;
		if (array_key_exists($childName, $this->children)) {
			$foundChild = $this->children[$childName];
		}
		return $foundChild;
	}

	public function getItemTree()
	{
		$items = $this->items;
		foreach ($this->children as $childName => $child) {
			$items[$childName] = $child->getItemTree();
		}
		return $items;
	}
}
