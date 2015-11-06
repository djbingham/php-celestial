<?php
namespace Sloth\Module\Render;

use Sloth\App;
use Sloth\Module\DataProvider\DataProvider;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Render\Face\ViewFactoryInterface;
use Sloth\Module\Render\Face\ViewInterface;

class RenderModule implements RendererInterface
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

	public function renderNamedView($viewName, array $parameters = array())
	{
		$view = $this->getView($viewName);
		return $this->render($view, $parameters);
	}

	public function render(ViewInterface $view, array $parameters = array())
	{
		$engine = $view->getEngine();

		if (!array_key_exists('app', $parameters)) {
			$parameters['app'] = $this->app;
		}
		if (!array_key_exists('data', $parameters)) {
			$parameters['data'] = $this->getViewData($view);
		}

		return $engine->render($view->getPath(), $parameters);
	}

	protected function getViewData(ViewInterface $view)
	{
		$data = array();
		/** @var DataProvider $dataProvider */
		foreach ($view->getDataProviders() as $dataProvider) {
			$data[$dataProvider->getName()] = $dataProvider->getData();
		}
		return $data;
	}
}
