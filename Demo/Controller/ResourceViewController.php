<?php
namespace Sloth\Demo\Controller;

use Sloth\Controller\RestfulController;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Resource\ModuleCore;
use Sloth\Module\RestApi\Face\ParsedRequestInterface;

class ResourceViewController extends RestfulController
{
	public function parseRequest(RequestInterface $request, $route)
	{
		$requestProperties = $request->toArray();
		return new ParsedRequest($requestProperties);
	}

	public function handleGet(ParsedRequestInterface $request, $route)
	{
		$renderer = $this->getRenderModule();

		$extension = $request->getExtension();
		if ($extension === null) {
			$extension = 'php';
		}

		$view = $renderer->getViewFactory()->build(array(
			'engine' => $extension,
			'path' => 'Resource/Default/index.' . $extension,
			'dataProviders' => array(
				'resourceNames' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $resourceNames
					)
				)
			)
		));

		return $renderer->render($view);
	}

	public function handlePost(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot post to resource/index');
	}

	public function handlePut(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot put to resource/index');
	}

	public function handleDelete(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot delete from resource/index');
	}

	/**
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('render');
	}

	/**
	 * @return ModuleCore
	 */
	private function getResourceModule()
	{
		return $this->module('resource');
	}
}
