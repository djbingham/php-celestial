<?php
namespace Sloth\Module\Resource;

use Sloth\App;

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

    protected function render($format, $view, array $params = array())
    {
        if (is_null($format)) {
            $format = 'html';
        }
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
