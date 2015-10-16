<?php
namespace Sloth\Module\RestApi\Face;

use Sloth\Face\RequestInterface;
use Sloth\Module\Resource\Definition\Resource as ResourceDefinition;
use Sloth\Module\Resource\ResourceFactoryInterface;

interface ParsedRequestInterface extends RequestInterface
{
	public function __construct(array $properties);

	/**
	 * @return RequestInterface
	 */
	public function getOriginalRequest();

	/**
	 * @return mixed
	 */
	public function getResourceId();

	/**
	 * @return ResourceFactoryInterface
	 */
	public function getResourceFactory();

	/**
	 * @return ResourceDefinition
	 */
	public function getResourceDefinition();

	/**
	 * @return string
	 */
	public function getExtension();
}
