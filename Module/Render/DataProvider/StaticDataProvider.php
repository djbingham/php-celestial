<?php
namespace Module\Render\DataProvider;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Render\Face\DataProviderInterface;

class StaticDataProvider implements DataProviderInterface
{
	private $name;
	private $options = array();

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setOptions(array $options)
	{
		$this->options = $options;
		return $this;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getData()
	{
		if (!array_key_exists('data', $this->options)) {
			throw new InvalidArgumentException(
				sprintf('Missing data in options set for static data provider with name `%s`', $this->getName())
			);
		}
		return $this->options['data'];
	}
}
