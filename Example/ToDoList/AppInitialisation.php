<?php
namespace Example\ToDoList;

use Celestial\App;
use Celestial\Base\Config\Module\Module as ModuleConfig;
use Celestial\Module\ModuleLoader;

class AppInitialisation implements \Celestial\Face\Initialisation
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
		return new AppConfig();
	}

	protected function buildApp(AppConfig $config)
	{
		$app = new App($config);
		$app->setModuleLoader($this->getModuleLoader($app, $config));

		return $app;
	}

	protected function getModuleLoader(App $app, AppConfig $config)
	{
		$moduleLoader = new ModuleLoader($app);

		foreach ($config->modules() as $moduleConfig) {
			$this->registerModuleConfig($moduleLoader, $moduleConfig);
		}

		return $moduleLoader;
	}

	protected function registerModuleConfig(ModuleLoader $moduleLoader, ModuleConfig $config)
	{
		$moduleLoader->register($config->getName(), $config);
	}
}
