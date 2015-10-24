<?php
namespace SlothDemo;

use Sloth\Module\Router\Base\Router;
use Sloth\Request;
use SlothDemo\Module\ModuleLoader;

class Initialisation extends \Sloth\Base\Initialisation
{
	public function execute()
	{
		$request = $this->getRequest();
		$app = $this->getApp($this->getConfig());
		$router = $this->getRouter($app);

		echo $router->route($request);
	}

	protected function getRequest()
	{
		return Request::fromServerVars();
	}

	protected function getConfig()
	{
		return new Config();
	}

	protected function getApp(Config $config)
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
