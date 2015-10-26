<?php
namespace Sloth\Api\Rest\Controller;

use Sloth\Api\Rest\Base\RestfulController;
use Sloth\Api\Rest\Face\RestfulParsedRequestInterface;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Resource\ResourceModule;
use Sloth\Api\Rest\RestfulRequestParser;

class DeleteController extends RestfulController
{
	public function parseRequest(RequestInterface $request, $route)
	{
		$requestParser = new RestfulRequestParser();
		$requestParser->setResourceModule($this->getResourceModule());
		return $requestParser->parse($request, $route);
	}

	public function handleGet(RestfulParsedRequestInterface $request, $route)
	{
		$this->handleDelete($request, $route);
	}

	public function handlePost(RestfulParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot post to resource/delete');
	}

	public function handlePut(RestfulParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot put to resource/create');
	}

	public function handleDelete(RestfulParsedRequestInterface $request, $route)
	{
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$primaryAttribute = $resourceDefinition->primaryAttribute;
		$resourceId = $request->getResourceId();
		$urlExtension = $request->getExtension();

		$resource = $resourceFactory->getBy($resourceDefinition->attributes, array($primaryAttribute => $resourceId));
		$resource->delete();

		$redirectUrl = $this->app->createUrl(array('resource/view', $resourceDefinition->name, $resourceId));
		if ($urlExtension !== null) {
			$redirectUrl .= '.' . $urlExtension;
		}

		$this->app->redirect($redirectUrl);
	}

	/**
	 * @return ResourceModule
	 */
	private function getResourceModule()
	{
		return $this->module('restRender');
	}
}
