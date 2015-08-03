<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;
use Sloth\Exception\InvalidArgumentException;

class ResourceList extends ObjectList
{
	public function push(Resource $connection)
	{
		$this->items[] = $connection;
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
