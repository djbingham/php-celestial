<?php
namespace Sloth\Api\Action\Base;

use Sloth\Base\Controller;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Render\View;
use Sloth\Module\Request\Request;

abstract class ActionController extends Controller
{
	abstract protected function handleIndex(RequestInterface $request);

	public function execute(RequestInterface $request, $controllerRoute)
	{
		$escapedControllerRoute = str_replace('/', '\/', $controllerRoute);
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
