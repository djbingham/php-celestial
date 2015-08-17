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

	public function setResourceManifest(array $resourceManifest)
	{
		$this->resourceManifest = $resourceManifest;
	}

	public function renderDefinition(ResourceFactoryInterface $resourceFactory, $format)
	{
		$view = $resourceFactory->getResourceDefinition()->views->getByProperty('name', 'definition');
		$params = array(
			'definition' => $resourceFactory->getResourceDefinition()
		);
		return $this->render($view, $params);
	}

	public function renderResource(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->views->getByProperty('name', 'item');
		$params = array(
			'resource' => $resource
		);
		return $this->render($view, $params);
	}

	public function renderResourceList(Definition\Resource $resourceDefinition, ResourceListInterface $resourceList, $format = null)
	{
		$view = $resourceDefinition->views->getByProperty('name', 'list.html');
		$params = array(
			'resourceList' => $resourceList
		);
		return $this->render($view, $params);
	}

	public function renderCreateForm(ResourceFactoryInterface $resourceFactory, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->views->getByProperty('name', 'create');
		$params = array();
		return $this->render($view, $params);
	}

	public function renderUpdateForm(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->views->getByProperty('name', 'update');
		$params = array(
			'resource' => $resource
		);
		return $this->render($view, $params);
	}

	public function renderSimpleSearchForm(ResourceFactoryInterface $resourceFactory, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->views->getByProperty('name', 'simpleSearch');
		$params = array();
		return $this->render($view, $params);
	}

	public function renderSearchForm(ResourceFactoryInterface $resourceFactory, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->views->getByProperty('name', 'search');
		$params = array();
		return $this->render($view, $params);
	}

	public function renderDeletedResource(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->views->getByProperty('name', 'deleted');
		$params = array(
			'resource' => $resource
		);
		return $this->render($view, $params);
	}

	public function render(View $view, array $params = array())
	{
		$viewPath = $this->app->rootDirectory() . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR .
			'resource' . DIRECTORY_SEPARATOR . $view->path;
		$engine = $this->engines[$view->engine];
		if (!array_key_exists('app', $params)) {
			$params['app'] = $this->app;
		}
		return $engine->render($viewPath, $params);
	}
}
