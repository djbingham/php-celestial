<?php
namespace Celestial\Api\Rest;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Data\Resource as ResourceModule;
use Celestial\Module\Render as RenderModule;
use Celestial\Module\Request\Request;

class RestfulParsedRequest extends Request implements Face\RestfulParsedRequestInterface
{
	/**
	 * @var RoutedRequestInterface
	 */
	protected $originalRequest;

	/**
	 * @var string
	 */
	protected $resourcePath;

	/**
	 * @var string
	 */
	protected $resourceId;

	/**
	 * @var string
	 */
	protected $extension;

	/**
	 * @var \Celestial\Module\Data\Resource\Face\ResourceFactoryInterface
	 */
	protected $resourceFactory;

	/**
	 * @var ResourceModule\Definition\Resource
	 */
	protected $resourceDefinition;

	public function __construct(array $properties)
	{
		parent::__construct($properties);
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

	protected function validateProperties(array $properties)
	{
		parent::validateProperties($properties);

		$required = array(
			'originalRequest', 'resourcePath', 'resourceId', 'extension', 'resourceFactory', 'resourceDefinition'
		);
		$missing = array_diff($required, array_keys($properties));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required properties for RestfulParsedRequest instance: ' . implode(', ', $missing)
			);
		}

		if (!($properties['originalRequest'] instanceof RoutedRequestInterface)) {
			throw new InvalidArgumentException('Invalid original request given to RestfulParsedRequest');
		}

		if (!is_string($properties['resourcePath']) && !is_null($properties['resourcePath'])) {
			throw new InvalidArgumentException('Invalid (non-string) resource path value given to RestfulParsedRequest');
		}

		if (!is_string($properties['resourceId']) && !is_null($properties['resourceId'])) {
			throw new InvalidArgumentException('Invalid (non-string) resource ID value given to RestfulParsedRequest');
		}

		if (!is_string($properties['extension']) && !is_null($properties['extension'])) {
			throw new InvalidArgumentException('Invalid (non-string) extension value given to RestfulParsedRequest');
		}

		if (!($properties['resourceFactory'] instanceof ResourceModule\Face\ResourceFactoryInterface)) {
			throw new InvalidArgumentException('Invalid resource factory given to RestfulParsedRequest');
		}

		if (!($properties['resourceDefinition'] instanceof ResourceModule\Definition\Resource)) {
			throw new InvalidArgumentException('Invalid resource definition given to RestfulParsedRequest');
		}
	}
}
