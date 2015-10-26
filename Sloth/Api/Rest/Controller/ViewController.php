<?php
namespace Sloth\Api\Rest\Controller;

use Sloth\Api\Rest\Base\RestfulController;
use Sloth\Api\Rest\Face\RestfulParsedRequestInterface;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Resource\ResourceModule;
use Sloth\Api\Rest\RestfulRequestParser;

class ViewController extends RestfulController
{
	public function parseRequest(RequestInterface $request, $route)
	{
		$requestParser = new RestfulRequestParser();
		$requestParser->setResourceModule($this->getResourceModule());
		return $requestParser->parse($request, $route);
	}

	public function handleGet(RestfulParsedRequestInterface $request, $route)
	{
		$renderer = $this->getRenderModule();

		$resourceDefinition = $request->getResourceDefinition();
		$resourceId = $request->getResourceId();
		$extension = $request->getExtension();
		if ($extension === null) {
			$extension = 'php';
		}

		$dataProviders = array(
			'resourceDefinition' => array(
				'engine' => 'static',
				'options' => array(
					'data' => $resourceDefinition
				)
			)
		);
		if (isset($resourceId)) {
			$filters = array(
				array(
					'subject' => $resourceDefinition->primaryAttribute,
					'comparator' => '=',
					'value' => $resourceId
				)
			);
			$dataProviders['resource'] = array(
				'engine' => 'resource',
				'options' => array(
					'resourceName' => $resourceDefinition->name,
					'filters' => $filters
				)
			);
			$viewPath = 'Default/item.' . $extension;
		} else {
			$dataProviders['resources'] = array(
				'engine' => 'resourceList',
				'options' => array(
					'resourceName' => $resourceDefinition->name,
					'filters' => array()
				)
			);
			$viewPath = 'Default/list.' . $extension;
		}

		$view = $renderer->getViewFactory()->build(array(
			'engine' => $extension,
			'path' => $viewPath,
			'dataProviders' => $dataProviders
		));

		return $renderer->render($view);
	}

	public function handlePost(RestfulParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot post to resource/view');
	}

	public function handlePut(RestfulParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot put to resource/view');
	}

	public function handleDelete(RestfulParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot delete from resource/view');
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
