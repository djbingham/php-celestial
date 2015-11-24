<?php
namespace Sloth\Module\Resource;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Resource\Face\ResourceFactoryInterface;
use Sloth\Module\Resource\Face\ResourceInterface;

class Resource implements ResourceInterface
{
	private $factory;
	private $attributes = array();

	public function __construct(ResourceFactoryInterface $factory)
	{
		$this->factory = $factory;
	}

	public function getDefinition()
	{
		return $this->factory->getResourceDefinition();
	}

	public function save()
	{
		$this->factory->update($this);
	}

	public function delete()
	{
		$this->factory->delete($this->getAttributes());
	}

	public function setAttributes(array $attributes)
	{
		foreach ($attributes as $name => $value) {
			$this->setAttribute($name, $value);
		}
		return $this;
	}

	public function getAttributes()
	{
		return $this->attributes;
	}

	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
		return $this;
	}

	public function getAttribute($name)
	{
		if (!$this->hasAttribute($name)) {
			$attributeList = json_encode(array_keys($this->getAttributes()));
			throw new InvalidArgumentException(
				sprintf('Attribute `%s` not found in resource with attributes: %s', $name, $attributeList)
			);
		}
		return $this->attributes[$name];
	}

	public function hasAttribute($name)
	{
		return array_key_exists($name, $this->attributes);
	}
}
