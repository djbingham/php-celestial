<?php
namespace Celestial\Api\Action\Base;

use Celestial\Base\Controller;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Render\Face\RendererInterface;
use Celestial\Module\Render\View;

abstract class ActionController extends Controller
{
	abstract protected function actionIndex(RoutedRequestInterface $request);

	public function execute(RoutedRequestInterface $request)
	{
		$escapedControllerRoute = str_replace('/', '\/', $request->getControllerPath());
		$controllerRouteRegex = sprintf('/^%s/', $escapedControllerRoute);
		$actionRoute = preg_replace($controllerRouteRegex, '', $request->getPath());
		$actionRoute = trim($actionRoute, '/');
		$actionRouteParts = explode('/', $actionRoute);
		$action = array_shift($actionRouteParts);
		if (strlen($action) === 0) {
			$action = 'index';
		}
		$actionMethod = 'action' . ucFirst($action);

		if (!method_exists($this, $actionMethod)) {
			throw new InvalidRequestException(sprintf('Action method not found in controller: %s', $actionMethod));
		}

		return $this->$actionMethod($request);
	}

	protected function render($viewPath, array $parameters = array(), $engine = 'mustache')
	{
		$view = new View();
		$view->name = $viewPath;
		$view->path = $viewPath;
		$view->engine = $engine;
		return $this->getRenderModule()->render($view, $parameters);
	}

	/**
	 * @return RendererInterface
	 */
	protected function getRenderModule()
	{
		return $this->module('render');
	}
}
