<?php
namespace Sloth\Demo\Controller\Resource;

use Sloth\Controller\RestfulController;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Resource\ModuleCore;
use Sloth\Module\RestApi\Face\ParsedRequestInterface;
use Sloth\Module\RestApi\RequestParser;

class DeleteController extends RestfulController
{
	public function parseRequest(RequestInterface $request, $route)
	{
		$requestParser = new RequestParser();
		$requestParser->setResourceModule($this->getResourceModule());
		return $requestParser->parse($request, $route);
	}

	public function handleGet(ParsedRequestInterface $request, $route)
	{
		$this->handleDelete($request, $route);
	}

	public function handlePost(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot post to resource/delete');
	}

	public function handlePut(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot put to resource/create');
	}

	public function handleDelete(ParsedRequestInterface $request, $route)
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
	 * @return ModuleCore
	 */
	private function getResourceModule()
	{
		return $this->module('resource');
	}
}
