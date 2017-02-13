<?php
namespace CelestialDemo;

use Celestial\App;
use Celestial\Module\Router\Base\Router;
use Celestial\Module\ModuleLoader;

class Initialisation implements \Celestial\Face\Initialisation
{
	/**
	 * @var App
	 */
	private $app;

	public function getApp()
	{
		if (!isset($this->app)) {
			$this->app = $this->buildApp($this->getConfig());
		}
		return $this->app;
	}

	protected function getConfig()
	{
		return new Config();
	}

	protected function buildApp(Config $config)
	{
		$app = new App($config);
		$app->setModuleLoader($this->getModuleLoader($app, $config));
		return $app;
	}

	protected function getModuleLoader(App $app, Config $config)
	{
		$moduleLoader = new ModuleLoader($app);
		foreach ($config->modules() as $moduleConfig) {
			$moduleLoader->register($moduleConfig->getName(), $moduleConfig);
		}
		return $moduleLoader;
	}
}
