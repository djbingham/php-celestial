<?php
namespace Sloth\Api\Rest\Face;

use Sloth\Face\ParsedRequestInterface;
use Sloth\Module\Resource\Definition\Resource as ResourceDefinition;
use Sloth\Module\Resource\ResourceFactoryInterface;

interface RestfulParsedRequestInterface extends ParsedRequestInterface
{
	/**
	 * @return number|string
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