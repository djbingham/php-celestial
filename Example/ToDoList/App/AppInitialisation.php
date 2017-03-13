<?php
namespace ToDoList;

use Celestial\App;
use Celestial\Base\Config\Module\Module as ModuleConfig;
use Celestial\Module\ModuleLoader;
use ToDoList\Config\AppConfig;

class AppInitialisation implements \Celestial\Face\Initialisation
{
	/**
	 * @var App
	 */
	private $app;

	public function getApp()
	{
		if (!isset($this->app)) {
			$this->app = $this->buildApp();
		}

		return $this->app;
	}

	protected function buildApp()
	{
		$config = new AppConfig();
		$app = new App($config);
		$moduleLoader = new ModuleLoader($app);

		foreach ($config->modules() as $moduleConfig) {
			$this->registerModuleConfig($moduleLoader, $moduleConfig);
		}

		$app->setModuleLoader($moduleLoader);

		return $app;
	}

	protected function registerModuleConfig(ModuleLoader $moduleLoader, ModuleConfig $config)
	{
		$moduleLoader->register($config->getName(), $config);
	}
}
