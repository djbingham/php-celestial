<?php
namespace Sloth\Module\Render;

use Sloth\App;
use Sloth\Module\Render\Face\RenderEngineInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Render\Face\ViewInterface;

class Renderer implements RendererInterface
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var array
	 */
	protected $resourceManifest;

	protected $viewDirectory;

	public function __construct(App $app, array $engines, $viewDirectory)
	{
		$this->app = $app;
		$this->engines = $engines;
		$this->viewDirectory = $viewDirectory;
	}

	public function render(ViewInterface $view, array $params = array())
	{
		$viewPath = $this->getAbsoluteViewPath($view);
		$engine = $this->getEngine($view->getEngineName());
		if (!array_key_exists('app', $params)) {
			$params['app'] = $this->app;
		}
		return $engine->render($viewPath, $params);
	}

	/**
	 * @param $engineName
	 * @return RenderEngineInterface
	 */
	protected function getEngine($engineName)
	{
		return $this->engines[$engineName];
	}

	protected function getAbsoluteViewPath(ViewInterface $view)
	{
		$viewPath = str_replace('/', DIRECTORY_SEPARATOR, $view->getPath());
		$viewPathParts = array($this->viewDirectory, $viewPath);
		return implode(DIRECTORY_SEPARATOR, $viewPathParts);
	}
}
