<?php
namespace Sloth\Module\Data\Resource;

use Sloth\Module\Data\Resource\Face\ResourceFactoryInterface;
use Sloth\Module\Data\Resource\Face\ResourceInterface;
use Sloth\Module\Data\Resource\Face\ResourceListInterface;

class ResourceList implements ResourceListInterface
{
	/**
	 * @var ResourceFactoryInterface
	 */
	private $factory;

	/**
	 * @var array
	 */
	private $resources = array();

	/**
	 * @var integer
	 */
	protected $position = 0;

	public function __construct(ResourceFactoryInterface $factory)
	{
		$this->factory = $factory;
	}

	public function getDefinition()
	{
		return $this->factory->getResourceDefinition();
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
		return array_key_exists($this->position, $this->resources);
	}

	public function count()
	{
		return count($this->resources);
	}

	/**
	 * @param int $index
	 * @return ResourceInterface
	 */
	public function getByIndex($index)
	{
		$item = null;
		if (array_key_exists($index, $this->resources)) {
			$item = $this->resources[$index];
		}
		return $item;
	}

	public function save()
	{
		/** @var ResourceInterface $resource */
		foreach ($this->resources as $resource) {
			$resource->save();
		}
		return $this;
	}

	public function delete()
	{
		/** @var ResourceInterface $resource */
		foreach ($this->resources as $resource) {
			$this->factory->delete($resource->getAttributes());
		}
		return $this;
	}

	public function setAttributes(array $attributes)
	{
		foreach ($this->resources as $resource) {
			$this->setResourceAttributes($resource, $attributes);
		}
		return $this;
	}

	public function getAttributes()
	{
		$attributeSets = array();
		foreach ($this->resources as $resource) {
			$attributeSets[] = $this->getResourceAttributes($resource);
		}
		return $attributeSets;
	}

	public function setAttribute($attributeName, $value)
	{
		foreach ($this->resources as $resource) {
			$this->setResourceAttribute($resource, $attributeName, $value);
		}
		return $this;
	}

	public function getAttribute($attributeName)
	{
		$values = array();
		foreach ($this->resources as $resource) {
			$values[] = $this->getResourceAttribute($resource, $attributeName);
		}
		return $values;
	}

	public function push(ResourceInterface $resource)
	{
		array_push($this->resources, $resource);
		return $this;
	}

	public function pushMany(array $resources)
	{
		foreach ($resources as $resource) {
			$this->push($resource);
		}
		return $this;
	}

	public function pop($quantity = 1)
	{
		for ($i = 0; $i < $quantity; $i++) {
			array_pop($this->resources);
		}
		return $this;
	}

	public function shift($quantity = 1)
	{
		for ($i = 0; $i < $quantity; $i++) {
			array_shift($this->resources);
		}
		return $this;
	}

	public function unshift(ResourceInterface $resource)
	{
		array_unshift($this->resources, $resource);
		return $this;
	}

	public function unshiftMany(array $resources)
	{
		foreach ($resources as $resource) {
			$this->unshift($resource);
		}
		return $this;
	}

    private function setResourceAttributes(ResourceInterface $resource, array $attributes)
    {
        $resource->setAttributes($attributes);
        return $this;
    }

    private function getResourceAttributes(ResourceInterface $resource)
    {
        return $resource->getAttributes();
    }

    private function getResourceAttribute(ResourceInterface $resource, $attributeName)
    {
        return $resource->getAttribute($attributeName);
    }

    private function setResourceAttribute(ResourceInterface $resource, $attributeName, $attributeValue)
    {
        $resource->setAttribute($attributeName, $attributeValue);
        return $this;
    }
}
