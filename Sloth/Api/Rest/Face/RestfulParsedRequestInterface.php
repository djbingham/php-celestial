<?php
namespace Sloth\Api\Rest\Face;

use Sloth\Module\Request\Face\ParsedRequestInterface;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Resource\Definition\Resource as ResourceDefinition;
use Sloth\Module\Resource\Face\ResourceFactoryInterface;

interface RestfulParsedRequestInterface extends ParsedRequestInterface
{
	/**
	 * @return RoutedRequestInterface
	 */
	public function getOriginalRequest();

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