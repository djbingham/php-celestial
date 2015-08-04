<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;

class ResourceList extends ObjectList
{
	public function push(Resource $resource)
	{
		$this->items[] = $resource;
		return $this;
	}

	/**
	 * @param string $index
	 * @return Resource
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	/**
	 * @return Resource
	 */
	public function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * @param int $start
	 * @param int $quantity
	 * @return ResourceList
	 */
	public function slice($start, $quantity)
	{
		$resources = array_slice($this->items, $start, $quantity);
		$resourceList = new self();
		foreach ($resources as $resource) {
			$resourceList->push($resource);
		}
		return $resourceList;
	}

	/**
	 * @param string $alias
	 * @return bool
	 */
	public function containsResourceAlias($alias)
	{
		$found = false;
		foreach ($this->items as $resource) {
			/** @var Resource $resource */
			if ($resource->getAlias() === $alias) {
				$found = true;
			}
		}
		return $found;
	}
}
