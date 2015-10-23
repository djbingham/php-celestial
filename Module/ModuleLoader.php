<?php
namespace Sloth\Module;

use Sloth\App;
use Sloth\Base\Config\Module\Module;
use Sloth\Exception\NotFoundException;
use Sloth\Module\Face\ModuleFactoryInterface;

class ModuleLoader
{
	/**
	 * @var App
	 */
	private $app;
	private $moduleConfigs = array();
	private $moduleFactories = array();
	private $modules = array();

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function register($moduleName, Module $moduleConfig)
	{
		$this->moduleConfigs[$moduleName] = $moduleConfig;
		return $this;
	}

	public function getModule($name)
	{
		if (!array_key_exists($name, $this->modules)) {
			$config = $this->getModuleConfig($name);
			$this->modules[$name] = $this->initialiseModule($config);
		}
		return $this->modules[$name];
	}

	/**
	 * @param string $name
	 * @return Module
	 * @throws NotFoundException
	 */
	private function getModuleConfig($name)
	{
		if (!array_key_exists($name, $this->moduleConfigs)) {
			throw new NotFoundException(sprintf('Failed to find a registered module named `%s`', $name));
		}
		return $this->moduleConfigs[$name];
	}

	private function initialiseModule(Module $config)
	{
		$factory = $this->getModuleFactory($config);
		return $factory->initialise($config->getOptions());
	}

	/**
	 * @param Module $config
	 * @return ModuleFactoryInterface
	 * @throws NotFoundException
	 */
	private function getModuleFactory(Module $config)
	{
		$moduleName = $config->getName();
		if (!array_key_exists($moduleName, $this->moduleFactories)) {
			$this->moduleFactories[$moduleName] = $this->initialiseModuleFactory($config);
		}
		return $this->moduleFactories[$moduleName];
	}

	private function initialiseModuleFactory(Module $config)
	{
		$factoryClass = $config->getFactoryClass();
		return new $factoryClass($this->app, $config->getOptions());
	}
}
