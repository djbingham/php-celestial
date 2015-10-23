<?php
namespace Sloth\Base;

use SlothMySql;
use Sloth\App;
use Sloth\Utility;
use Sloth\Module;

abstract class Initialisation
{
	/**
	 * @var App
	 */
	private $app;

	/**
	 * @var ModuleLoader
	 */
	private $moduleLoader;

	/**
	 * @return Router
	 */
	abstract public function getRouter();

	/**
	 * @var Config
	 */
	protected $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function getApp()
	{
		if (!isset($this->app)) {
			$this->app = new App($this->config);
		}
		return $this->app;
	}

	public function getModuleLoader()
	{
		if (!isset($this->moduleLoader)) {
			$this->moduleLoader = new Module\ModuleLoader($this->getApp());
			$modules = $this->config->modules();
			foreach ($modules as $moduleConfig) {
				$this->moduleLoader->register($moduleConfig->getName(), $moduleConfig);
			}
		}
		return $this->moduleLoader;
	}
}
