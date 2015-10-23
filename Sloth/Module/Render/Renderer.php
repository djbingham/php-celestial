<?php
namespace Sloth\Module\Render;

use Sloth\App;
use Sloth\Module\Render\Face\DataProviderInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Render\Face\ViewFactoryInterface;
use Sloth\Module\Render\Face\ViewInterface;

class Renderer implements RendererInterface
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var ViewFactoryInterface
	 */
	protected $viewFactory;

	public function setApp(App $app)
	{
		$this->app = $app;
		return $this;
	}

	public function getApp()
	{
		return $this->app;
	}

	public function setViewFactory(ViewFactoryInterface $viewFactory)
	{
		$this->viewFactory = $viewFactory;
		return $this;
	}

	public function getViewFactory()
	{
		return $this->viewFactory;
	}

	public function viewExists($viewName)
	{
		return $this->viewFactory->viewExists($viewName);
	}

	public function getView($viewName)
	{
		return $this->viewFactory->getByName($viewName);
	}

	public function render(ViewInterface $view, array $params = array())
	{
		$engine = $view->getEngine();

		if (!array_key_exists('app', $params)) {
			$params['app'] = $this->app;
		}
		if (!array_key_exists('data', $params)) {
			$params['data'] = $this->getData($view);
		}

		return $engine->render($view->getPath(), $params);
	}

	protected function getData(ViewInterface $view)
	{
		$data = array();
		/** @var DataProviderInterface $dataProvider */
		foreach ($view->getDataProviders() as $dataProvider) {
			$data[$dataProvider->getName()] = $dataProvider->getData();
		}
		return $data;
	}
}
