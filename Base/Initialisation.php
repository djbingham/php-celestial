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
	 * @var Module\Render\Renderer $renderer
	 */
	private $renderer;

	/**
	 * @return SlothMySql\DatabaseWrapper
	 */
	abstract public function getDatabase();

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
			$this->moduleLoader = new Module\ModuleLoader();
			$this->moduleLoader->register('graph', new Module\Graph\Factory(array(
				'app' => $this->getApp(),
				'tableDirectory' => $this->getTableManifestDirectory(),
				'resourceDirectory' => $this->getResourceManifestDirectory(),
				'tableValidator' => new Module\Graph\TableManifestValidator(),
				'resourceValidator' => new Module\Graph\ResourceManifestValidator()
			)));
			$this->moduleLoader->register('render', new Module\Render\Factory(array(
				'app' => $this->getApp(),
				'engines' => array(
					'mustache' => new Module\Render\Engine\Mustache(),
					'php' => new Module\Render\Engine\Php(),
					'json' => new Module\Render\Engine\Json()
				),
				'directory' => $this->getApp()->rootDirectory() . DIRECTORY_SEPARATOR . 'view'
			)));
		}
		return $this->moduleLoader;
	}

	public function getRenderer()
	{
		if (!isset($this->renderer)) {
			$viewDirectory = $this->getApp()->rootDirectory() . DIRECTORY_SEPARATOR . 'view';
			$engines = array(
				'mustache' => new Module\Render\Engine\Mustache(),
				'php' => new Module\Render\Engine\Php(),
				'json' => new Module\Render\Engine\Json()
			);
			$this->renderer = new Module\Render\Renderer($this->getApp(), $engines, $viewDirectory);
		}
		return $this->renderer;
	}

	protected function getResourceManifestDirectory()
	{
		$directoryParts = array($this->getApp()->rootDirectory(), 'resource', 'graph', 'resourceManifest');
		return implode(DIRECTORY_SEPARATOR, $directoryParts);
	}

	protected function getTableManifestDirectory()
	{
		$directoryParts = array($this->getApp()->rootDirectory(), 'resource', 'graph', 'tableManifest');
		return implode(DIRECTORY_SEPARATOR, $directoryParts);
	}
}
