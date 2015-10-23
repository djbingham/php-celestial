<?php
namespace Sloth\Api\Rest\Controller;

use Sloth\Base\Controller\RestfulController;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Resource\ModuleCore;
use Sloth\Api\Rest\Face\ParsedRequestInterface;
use Sloth\Api\Rest\ParsedRequest;

class IndexController extends RestfulController
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

		$resourceNames = $this->getResourceNames($this->getResourceModule()->getResourceManifestDirectory());

		$view = $renderer->getViewFactory()->build(array(
			'engine' => $extension,
			'path' => 'Default/index.' . $extension,
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
		return $this->module('resourceRender');
	}

	/**
	 * @return ModuleCore
	 */
	private function getResourceModule()
	{
		return $this->module('resource');
	}

	private function getResourceNames($directory)
	{
		$directoryContents = scandir($directory);
		$resourceNames = array();
		foreach ($directoryContents as $fileName) {
			if (!in_array($fileName, array('.', '..')) && preg_match('/.json$/', $fileName)) {
				$resourceNames[] = basename($fileName, '.json');
			}
		}
		return $resourceNames;
	}
}
