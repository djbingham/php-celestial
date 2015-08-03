<?php
namespace DemoGraph\Module\Graph;

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

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function setResourceManifest(array $resourceManifest)
	{
		$this->resourceManifest = $resourceManifest;
	}

	public function renderDefinition(ResourceFactoryInterface $resourceFactory, $format)
	{
		$view = $resourceFactory->getResourceDefinition()->view('definition');
		$params = array(
			'definition' => $resourceFactory->getResourceDefinition()
		);
		return $this->render($view, $format, $params);
	}

	public function renderResource(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->view('item');
		$params = array(
			'resource' => $resource
		);
		return $this->render($view, $format, $params);
	}

	public function renderResourceList(ResourceFactoryInterface $resourceFactory, ResourceListInterface $resourceList, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->view('list');
		$params = array(
			'resourceList' => $resourceList
		);
		return $this->render($view, $format, $params);
	}

	public function renderCreateForm(ResourceFactoryInterface $resourceFactory, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->view('create');
		$params = array();
		return $this->render($view, $format, $params);
	}

	public function renderUpdateForm(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->view('update');
		$params = array(
			'resource' => $resource
		);
		return $this->render($view, $format, $params);
	}

	public function renderSimpleSearchForm(ResourceFactoryInterface $resourceFactory, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->view('simpleSearch');
		$params = array();
		return $this->render($view, $format, $params);
	}

	public function renderSearchForm(ResourceFactoryInterface $resourceFactory, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->view('search');
		$params = array();
		return $this->render($view, $format, $params);
	}

	public function renderDeletedResource(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format = null)
	{
		$view = $resourceFactory->getResourceDefinition()->view('deleted');
		$params = array(
			'resource' => $resource
		);
		return $this->render($view, $format, $params);
	}

	protected function render($view, $format = 'html', array $params = array())
	{
		$renderFunction = sprintf('render%s', ucfirst($format));
		return $this->$renderFunction($view, $params);
	}

	protected function renderHtml($view, array $params = array())
	{
		$view = sprintf('resource/%s.html', $view);
		return $this->app->render()->full($view, $params);
	}

	protected function renderJson($view, $params)
	{
		switch ($view) {
			default:
				return "No JSON for view: $view";
				break;
		}
	}
}
