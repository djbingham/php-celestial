<?php
namespace Sloth\Api\Rest\Controller;

use Sloth\Base\Controller;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Resource\ResourceModule;

class IndexController extends Controller
{
	public function execute(RequestInterface $request, $route)
	{
		if ($request->getMethod() !== 'get') {
			throw new InvalidRequestException(
				sprintf('Invalid request method used: `%s`. Allowed methods: get', $request->getMethod())
			);
		}

		$renderer = $this->getRenderModule();

		$resourceNames = $this->getResourceNames($this->getResourceModule()->getResourceManifestDirectory());

		$view = $renderer->getViewFactory()->build(array(
			'engine' => 'php',
			'path' => 'Default/index.php',
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
