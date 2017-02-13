<?php
namespace Celestial\Api\Rest\Controller;

use Celestial\Api\Rest\Base\RestfulController;
use Celestial\Api\Rest\Face\RestfulParsedRequestInterface;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Render\Face\RendererInterface;
use Celestial\Module\Data\Resource\ResourceModule;
use Celestial\Api\Rest\RestfulRequestParser;

class DefinitionController extends RestfulController
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
		$extension = $request->getExtension();
		if ($extension === null) {
			$extension = 'php';
		}

		$view = $renderer->getViewFactory()->build(array(
			'engine' => $extension,
			'path' => 'Default/definition.' . $extension,
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
		throw new InvalidRequestException('Cannot post to resource/view');
	}

	public function handlePut(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot put to resource/view');
	}

	public function handleDelete(RestfulParsedRequestInterface $request)
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
