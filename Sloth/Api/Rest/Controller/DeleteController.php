<?php
namespace Sloth\Api\Rest\Controller;

use Sloth\Api\Rest\Base\RestfulController;
use Sloth\Api\Rest\Face\RestfulParsedRequestInterface;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Data\Resource\ResourceModule;
use Sloth\Api\Rest\RestfulRequestParser;

class DeleteController extends RestfulController
{
	public function parseRequest(RoutedRequestInterface $request)
	{
		$requestParser = new RestfulRequestParser();
		$requestParser->setResourceModule($this->getResourceModule());
		return $requestParser->parse($request);
	}

	public function handleGet(RestfulParsedRequestInterface $request)
	{
		$this->handleDelete($request);
	}

	public function handlePost(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot post to resource/delete');
	}

	public function handlePut(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot put to resource/create');
	}

	public function handleDelete(RestfulParsedRequestInterface $request)
	{
		$getParams = $request->getParams()->get();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$primaryAttribute = $resourceDefinition->primaryAttribute;
		$resourceId = $request->getResourceId();
		$urlExtension = $request->getExtension();

		$resources = $resourceFactory->getBy($resourceDefinition->attributes, array($primaryAttribute => $resourceId));
		$resources->delete();

		if (array_key_exists('redirect', $getParams)) {
			$redirectUrl = $this->app->createUrl(explode('/', $getParams['redirect']));
		} else {
			$redirectUrl = $this->app->createUrl(array('resource/view', lcfirst($resourceDefinition->name)));
			if ($urlExtension !== null) {
				$redirectUrl .= '.' . $urlExtension;
			}
		}

		$this->app->redirect($redirectUrl);
	}

	/**
	 * @return ResourceModule
	 */
	private function getResourceModule()
	{
		return $this->module('data.resource');
	}
}
