<?php
namespace SlothDemo;

use Sloth\App;
use Sloth\Module\Router\Base\Router;
use Sloth\Module\ModuleLoader;

class Initialisation implements \Sloth\Face\Initialisation
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

	/**
	 * @param App $app
	 * @return Router
	 */
	protected function getRouter(App $app)
	{
		return $app->module('router');
	}
}
