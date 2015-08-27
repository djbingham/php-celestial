<?php
namespace Sloth\Module\Resource;

use Sloth\App;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Render\View;

class Renderer implements Base\Renderer
{
	/**
	 * @var App
	 */
	protected $app;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function renderDefinition(Base\ResourceFactory $resourceFactory, $format)
	{
		$view = $resourceFactory->getDefinition()->view('definition');
		$params = array(
			'definition' => $resourceFactory->getDefinition()
		);
		return $this->render($format, $view, $params);
	}

	public function renderResource(Base\ResourceFactory $resourceFactory, Base\Resource $resource, $format)
	{
		$view = $resourceFactory->getDefinition()->view('item');
		$params = array(
			'resource' => $resource
		);
		return $this->render($format, $view, $params);
	}

	public function renderResourceList(Base\ResourceFactory $resourceFactory, Base\ResourceList $resourceList, $format)
	{
		$view = $resourceFactory->getDefinition()->view('list');
		$params = array(
			'resourceList' => $resourceList
		);
		return $this->render($format, $view, $params);
	}

	public function renderCreateForm(Base\ResourceFactory $resourceFactory, $format)
	{
		$view = $resourceFactory->getDefinition()->view('create');
		$params = array();
		return $this->render($format, $view, $params);
	}

	public function renderUpdateForm(Base\ResourceFactory $resourceFactory, Base\Resource $resource, $format)
	{
		$view = $resourceFactory->getDefinition()->view('update');
		$params = array(
			'resource' => $resource
		);
		return $this->render($format, $view, $params);
	}

	public function renderSimpleSearchForm(Base\ResourceFactory $resourceFactory, $format)
	{
		$view = $resourceFactory->getDefinition()->view('simpleSearch');
		$params = array();
		return $this->render($format, $view, $params);
	}

	public function renderSearchForm(Base\ResourceFactory $resourceFactory, $format)
	{
		$view = $resourceFactory->getDefinition()->view('search');
		$params = array();
		return $this->render($format, $view, $params);
	}

	public function renderDeletedResource(Base\ResourceFactory $resourceFactory, Base\Resource $resource, $format)
	{
		$view = $resourceFactory->getDefinition()->view('deleted');
		$params = array(
			'resource' => $resource
		);
		return $this->render($format, $view, $params);
	}

	protected function render($format, $viewName, array $params = array())
	{
		$view = new View();
		$view->name = $viewName;

		switch ($format) {
			case 'json':
				return 'No JSON available for view: ' . $viewName;
				break;
			case 'html':
			default:
				$view->engine = 'php';
				$view->path = sprintf('resource/%s.html.php', $viewName);
				break;
		}

		return $this->getRenderModule()->render($view, $params);
	}

	/**
	 * @return RendererInterface
	 */
	protected function getRenderModule()
	{
		return $this->app->module('render');
	}
}
