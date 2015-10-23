<?php
namespace Sloth\Api\Rest;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Resource as ResourceModule;
use Sloth\Module\Render as RenderModule;
use Sloth\Request;

class ParsedRequest extends Request implements Face\ParsedRequestInterface
{
	/**
	 * @var Request
	 */
	protected $originalRequest;

	/**
	 * @var string
	 */
	protected $params;

	/**
	 * @var string
	 */
	protected $resourcePath;

	/**
	 * @var string
	 */
	protected $resourceId;

	/**
	 * @var ResourceModule\ResourceFactory
	 */
	protected $resourceFactory;

	/**
	 * @var ResourceModule\Definition\Resource
	 */
	protected $resourceDefinition;

	/**
	 * @var string
	 */
	protected $extension;

	public function __construct(array $properties)
	{
		foreach ($properties as $key => $value) {
			if (!property_exists($this, $key)) {
				throw new InvalidArgumentException(
					sprintf('Unrecognised property given to ParsedRequest: %s', $key)
				);
			}
			$this->$key = $value;
		}
	}

	public function getOriginalRequest()
	{
		return $this->originalRequest;
	}

	public function getResourceId()
	{
		return $this->resourceId;
	}

	public function getResourceFactory()
	{
		return $this->resourceFactory;
	}

	public function getResourceDefinition()
	{
		return $this->resourceDefinition;
	}

	public function getExtension()
	{
		return $this->extension;
	}
}
