<?php
namespace Sloth\Base\Config\Module;

class Module
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $factoryClass;

	/**
	 * @var Dependencies
	 */
	private $dependencies;

	/**
	 * @var array
	 */
	private $options = array();

	public function __construct(array $properties)
	{
		$this->validateProperties($properties);
		$properties = $this->padProperties($properties);
		$this->name = $properties['name'];
		$this->factoryClass = $properties['factoryClass'];
		$this->dependencies = new Dependencies($properties['dependencies']);
		$this->options = $properties['options'];
	}

	public function getName()
	{
		return $this->name;
	}

	public function getFactoryClass()
	{
		return $this->factoryClass;
	}

	public function getDependencies()
	{
		return $this->dependencies;
	}

	public function getOptions()
	{
		return $this->options;
	}

	private function validateProperties(array $properties)
	{

	}

	private function padProperties(array $properties)
	{
		if (!array_key_exists('dependencies', $properties)) {
			$properties['dependencies'] = array();
		}
		if (!array_key_exists('options', $properties)) {
			$properties['options'] = array();
		}
		return $properties;
	}
}
