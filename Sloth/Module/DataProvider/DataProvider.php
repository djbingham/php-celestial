<?php
namespace Sloth\Module\DataProvider;

use Sloth\Module\DataProvider\Face\DataProviderInterface;

class DataProvider
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var DataProviderInterface
	 */
	private $engine;

	/**
	 * @var array
	 */
	private $options = array();

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	public function setEngine($engine)
	{
		$this->engine = $engine;
		return $this;
	}

	public function getEngine()
	{
		return $this->engine;
	}

	public function setOptions($options)
	{
		$this->options = $options;
		return $this;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getData(array $options = array())
	{
		$options = array_merge($this->getOptions(), $options);
		return $this->engine->getData($options);
	}
}
