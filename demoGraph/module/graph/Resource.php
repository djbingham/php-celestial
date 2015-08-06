<?php
namespace DemoGraph\Module\Graph;

class Resource implements ResourceInterface
{
	private $factory;
	private $attributes = array();

	public function __construct(ResourceFactoryInterface $factory)
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
