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

		$viewName = trim(preg_replace(sprintf('/^%s/', $request->getControllerPath()), '', $request->getPath()), '/');
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
}
