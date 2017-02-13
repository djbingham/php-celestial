<?php
namespace Celestial\Api\Rest\Face;

use Celestial\Module\Request\Face\ParsedRequestInterface;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Data\Resource\Definition\Resource as ResourceDefinition;
use Celestial\Module\Data\Resource\Face\ResourceFactoryInterface;

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