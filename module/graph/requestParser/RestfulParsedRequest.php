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

	public function getOriginalRequest()
	{
		return $this->originalRequest;
	}

	public function getManifest()
	{
		return $this->manifest;
	}

	public function getFactoryClass()
	{
		return $this->factoryClass;
	}

	public function getResourceRoute()
	{
		return $this->resourceRoute;
	}

	public function getViewName()
	{
		return $this->viewName;
	}

	public function getResourceId()
	{
		return $this->resourceId;
	}

	public function getUnresolvedRoute()
	{
		return $this->unresolvedRoute;
	}

	public function getResourceDefinition()
	{
		return $this->resourceDefinition;
	}

	public function getResourceFactory()
	{
		return $this->resourceFactory;
	}

	public function getView()
	{
		return $this->view;
	}
}
