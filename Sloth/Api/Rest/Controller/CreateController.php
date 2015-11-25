<?php
namespace Sloth\Api\Rest\Controller;

use Sloth\Api\Rest\Face\RestfulParsedRequestInterface;
use Sloth\Api\Rest\Base\RestfulController;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Data\Resource\ResourceModule;
use Sloth\Api\Rest\RestfulRequestParser;

class CreateController extends RestfulController
{
	public function parseRequest(RoutedRequestInterface $request)
	{
		$requestParser = new RestfulRequestParser();
		$requestParser->setResourceModule($this->getResourceModule());
		return $requestParser->parse($request);
	}

	public function handleGet(RestfulParsedRequestInterface $request)
	{
		$renderer = $this->getRenderModule();

		$resourceDefinition = $request->getResourceDefinition();

		$view = $renderer->getViewFactory()->build(array(
			'engine' => 'php',
			'path' => 'Default/createForm.php',
			'dataProviders' => array(
				'resourceDefinition' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $resourceDefinition
					)
				)
			)
		));

		return $renderer->render($view);
	}

	public function handlePost(RestfulParsedRequestInterface $request)
	{
		$attributes = $request->getParams()->post();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$urlExtension = $request->getExtension();

		if (empty($attributes)) {
			throw new InvalidRequestException('POST request to resource/create received with no parameters');
		}

		$resource = $resourceFactory->create($attributes);
		$resourceId = $resource->getAttribute($resourceDefinition->primaryAttribute);

		$redirectUrl = $this->app->createUrl(array('resource/view', $resourceDefinition->name, $resourceId));
		if ($urlExtension !== null) {
			$redirectUrl .= '.' . $urlExtension;
		}

		$this->app->redirect($redirectUrl);
	}

	public function handlePut(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot put to resource/create');
	}

	public function handleDelete(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot delete from resource/create');
	}

	/**
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('restRender');
	}

	/**
	 * @return ResourceModule
	 */
	private function getResourceModule()
	{
		return $this->module('restResource');
	}
}
