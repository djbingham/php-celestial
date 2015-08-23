<?php
namespace Sloth\Module\Graph;

use Sloth\Module\Graph\Definition\View;
use Sloth\App;

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

	public function __construct(App $app, array $engines)
	{
		$this->app = $app;
		$this->engines = $engines;
	}

	public function render(View $view, array $params = array())
	{
		$viewPath = str_replace('/', DIRECTORY_SEPARATOR, $view->path);
		$viewPathParts = array($this->app->rootDirectory(), 'view', 'resource', $viewPath);
		$viewPath = implode(DIRECTORY_SEPARATOR, $viewPathParts);
		$engine = $this->engines[$view->engine];
		if (!array_key_exists('app', $params)) {
			$params['app'] = $this->app;
		}
		return $engine->render($viewPath, $params);
	}
}
