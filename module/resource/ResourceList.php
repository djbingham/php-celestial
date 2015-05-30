<?php
namespace Sloth\Module\Resource;

class ResourceList implements Base\ResourceList
{
	private $factory;
	private $resources = array();

	public function __construct(Base\ResourceFactory $factory)
	{
		$this->factory = $factory;
	}

	public function save()
	{
		foreach ($this->resources as $resource) {
			$this->factory->update($resource);
		}
		return $this;
	}

	public function delete()
	{
		foreach ($this->resources as $resource) {
			$this->factory->delete($resource);
		}
		return $this;
	}

	public function setAttributes(array $attributes)
	{
		foreach ($this->resources as $resource) {
			$resource->setAttributes($attributes);
		}
		return $this;
	}

	public function getAttributes()
	{
		$attributeSets = array();
		foreach ($this->resources as $resource) {
			$attributeSets[] = $resource->getAttributes();
		}
		return $attributeSets;
	}

	public function setAttribute($name, $value)
	{
		foreach ($this->resources as $resource) {
			$resource->setAttribute($name, $value);
		}
		return $this;
	}

	public function getAttribute($name)
	{
		$values = array();
		foreach ($this->resources as $resource) {
			$values[] = $resource->getAttribute($name);
		}
		return $values;
	}

	public function push(Base\Resource $resource)
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

	public function unshift(Base\Resource $resource)
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

    /**
     * @param int $index
     * @return Base\Resource
     */
	public function get($index)
	{
		return $this->resources[$index];
	}

    public function count()
    {
        return count($this->resources);
    }
}
