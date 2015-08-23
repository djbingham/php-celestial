<?php
namespace Sloth\Module\Graph\RequestParser;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Graph;
use Sloth\Request;

class RestfulParsedRequest extends Request implements ParsedRequestInterface
{
	/**
	 * @var Request
	 */
	protected $originalRequest;

	/**
	 * @var string
	 */
	protected $manifest;

	/**
	 * @var string
	 */
	protected $factoryClass;

	/**
	 * @var string
	 */
	protected $resourceRoute;

	/**
	 * @var string
	 */
	protected $viewName;

	/**
	 * @var string
	 */
	protected $resourceId;

	/**
	 * @var string
	 */
	protected $unresolvedRoute;

	/**
	 * @var Graph\Definition\Resource
	 */
	protected $resourceDefinition;

	/**
	 * @var Graph\ResourceFactory
	 */
	protected $resourceFactory;

	/**
	 * @var Graph\Definition\View
	 */
	protected $view;

	public function __construct(array $properties)
	{
		foreach ($properties as $key => $value) {
			if (!property_exists($this, $key)) {
				throw new InvalidArgumentException(
					sprintf('Unrecognised property given to RestfulParsedRequest: %s', $key)
				);
			}
			$this->$key = $value;
		}
	}

	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	public function setUri($uri)
	{
		$this->uri = $uri;
		return $this;
	}

	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	public function setOriginalRequest(Request $request)
	{
		$this->originalRequest = $request;
		return $this;
	}

	public function getOriginalRequest()
	{
		return $this->originalRequest;
	}

	public function setManifest(array $manifest)
	{
		$this->manifest = $manifest;
		return $this;
	}

	public function getManifest()
	{
		return $this->manifest;
	}

	public function setFactoryClass($factoryClassName)
	{
		$this->factoryClass = $factoryClassName;
		return $this;
	}

	public function getFactoryClass()
	{
		return $this->factoryClass;
	}

	public function setResourceRoute($resourceRoute)
	{
		$this->resourceRoute = $resourceRoute;
		return $this;
	}

	public function getResourceRoute()
	{
		return $this->resourceRoute;
	}

	public function setViewName($viewName)
	{
		$this->viewName = $viewName;
		return $this;
	}

	public function getViewName()
	{
		return $this->viewName;
	}

	public function setResourceId($resourceId)
	{
		$this->resourceId = $resourceId;
		return $this;
	}

	public function getResourceId()
	{
		return $this->resourceId;
	}

	public function setUnresolvedRoute($unresolvedRoute)
	{
		$this->unresolvedRoute = $unresolvedRoute;
		return $this;
	}

	public function getUnresolvedRoute()
	{
		return $this->unresolvedRoute;
	}

	public function setResourceDefinition(Graph\Definition\Resource $resourceDefinition)
	{
		$this->resourceDefinition = $resourceDefinition;
		return $this;
	}

	public function getResourceDefinition()
	{
		return $this->resourceDefinition;
	}

	public function setResourceFactory(Graph\ResourceFactory $resourceFactory)
	{
		$this->resourceFactory = $resourceFactory;
		return $this;
	}

	public function getResourceFactory()
	{
		return $this->resourceFactory;
	}

	public function setView(Graph\Definition\View $view)
	{
		$this->view = $view;
		return $this;
	}

	public function getView()
	{
		return $this->view;
	}
}
