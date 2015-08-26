<?php
namespace Sloth\Base;

use Sloth\Module\Render;
use SlothMySql;
use Sloth\App;
use Sloth\Utility;

abstract class Initialisation
{
	/**
	 * @var App
	 */
	private $app;

	/**
	 * @var Render\Renderer $renderer
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

	public function getRenderer()
	{
		if (!isset($this->renderer)) {
			$viewDirectory = $this->getApp()->rootDirectory() . DIRECTORY_SEPARATOR . 'view';
			$engines = array(
				'mustache' => new Render\Engine\Mustache(),
				'php' => new Render\Engine\Php(),
				'json' => new Render\Engine\Json()
			);
			$this->renderer = new Render\Renderer($this->getApp(), $engines, $viewDirectory);
		}
		return $this->renderer;
	}
}
