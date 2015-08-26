<?php
namespace Sloth\Module\Resource;

class Resource implements Base\Resource
{
	private $factory;
	private $attributes;

	public function __construct(Base\ResourceFactory $factory)
	{
		$this->factory = $factory;
	}

	public function save()
	{
		$this->factory->update($this);
	}

	public function delete()
	{
		$this->factory->delete($this);
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
		return $this->attributes[$name];
	}
}
