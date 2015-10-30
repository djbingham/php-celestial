<?php
namespace Sloth\Api\View;

use Sloth\Base\Controller;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Render\Face\RendererInterface;

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
