<?php
namespace Sloth\Module;

use Sloth\Exception\NotFoundException;
use Sloth\Module\Face\ModuleFactoryInterface;

class ModuleLoader
{
	private $moduleFactories = array();
	private $modules = array();

	public function register($name, ModuleFactoryInterface $moduleFactory)
	{
		$this->moduleFactories[$name] = $moduleFactory;
		return $this;
	}

	public function getModule($name)
	{
		if (!array_key_exists($name, $this->modules)) {
			$factory = $this->getModuleFactory($name);
			$this->modules[$name] = $factory->initialise();
		}
		return $this->modules[$name];
	}

	/**
	 * @param $name
	 * @return ModuleFactoryInterface
	 * @throws NotFoundException
	 */
	public function getModuleFactory($name)
	{
		if (!array_key_exists($name, $this->moduleFactories)) {
			throw new NotFoundException(sprintf('Failed to find a registered module named `%s`', $name));
		}
		return $this->moduleFactories[$name];
	}
}
