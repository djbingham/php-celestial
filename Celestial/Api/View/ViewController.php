<?php
namespace Celestial\Api\View;

use Celestial\Base\Controller;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Render\Face\RendererInterface;

class ViewController extends Controller
{
	public function execute(RoutedRequestInterface $request)
	{
		if ($request->getMethod() !== 'get') {
			throw new InvalidRequestException(
				sprintf('Invalid request method used: `%s`. Allowed methods: get', $request->getMethod())
			);
		}

		$renderer = $this->getRenderModule();

		$viewName = $this->getViewName($request);
		$view = $renderer->getViewFactory()->getByName($viewName);

		return $renderer->render($view);
	}

	/**
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('render');
	}

	private function getViewName(RoutedRequestInterface $request)
	{
		$requestPath = $request->getPath();
		$controllerPath = $request->getControllerPath();
		$viewPath = preg_replace("/^{$controllerPath}/", '', $requestPath);
		return trim($viewPath, '/');
	}
}
