<?php
namespace Celestial\Module\Adapter;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Adapter\Face\AdapterInterface;

class AdapterModule
{
	/**
	 * @var array
	 */
	private $adapters = array();

	/**
	 * @param string $name
	 * @return bool
	 */
	public function adapterExists($name)
	{
		return array_key_exists($name, $this->adapters);
	}

	/**
	 * @param string $name
	 * @param AdapterInterface $adapter
	 * @return $this
	 */
	public function setAdapter($name, AdapterInterface $adapter)
	{
		$this->adapters[$name] = $adapter;
		return $this;
	}

	/**
	 * @param string $name
	 * @return AdapterInterface
	 * @throws InvalidArgumentException
	 */
	public function getAdapter($name)
	{
		if (!$this->adapterExists($name)) {
			throw new InvalidArgumentException(
				sprintf('Adapter not found with name `%s`', $name)
			);
		}
		return $this->adapters[$name];
	}
}
