<?php
namespace SlothDefault;

use Sloth\Base;
use Sloth\App;
use Sloth\Request;
use Sloth\Exception\InvalidRequestException;

class Router extends Base\Router
{
	public function route(App $app, Request $request)
	{
		$output = null;
		$canCacheRequest = $this->canCache($request);
		$requestUri = $request->uri();

		if ($canCacheRequest) {
			$output = $this->searchCache($requestUri);
		}

		if (is_null($output)) {
			$routeData = $this->searchRoutes($request);
			if (empty($routeData['controller'])) {
				$routeData = $this->searchControllers($request);
			}

			if (!array_key_exists('controller', $routeData)) {
				throw new InvalidRequestException(sprintf('No controller found for request: %s', $requestUri));
			}
			if (!array_key_exists('route', $routeData)) {
				$routeData['route'] = '';
			}

			$controller = $this->instantiateController($routeData['controller'], $app);
			$route = $routeData['route'];

			$output = $controller->execute($request, $route);

			if ($canCacheRequest) {
				$this->cacheOutput($requestUri, $output);
			}
		}

		return $output;
	}

	protected function canCache(Request $request)
	{
		return $request->method() === 'get';
	}

	protected function searchCache($uri)
	{
		$output = null;
		if (array_key_exists($this->cache, $uri)) {
			$output = $this->cache[$uri];
		}
		return $output;
	}

	protected function cacheOutput($requestUri, $output)
	{
		$this->cache[$requestUri] = $output;
		return $this;
	}

	protected function searchRoutes(Request $request)
	{
		$requestPathParts = explode('/', $request->path());
		$controller = null;
		$route = '';

		while (count($requestPathParts) > 0) {
			$proposedRoute = implode('/', $requestPathParts);

			if ($this->config->routes()->routeExists($proposedRoute)) {
				$controller = $this->config->routes()[$proposedRoute];
				$route = $proposedRoute;
				break;
			}

			array_pop($requestPathParts);
		}

		return array(
			'controller' => $controller,
			'route' => $route
		);
	}

	protected function searchControllers(Request $request)
	{
		$requestPathParts = explode('/', rtrim($request->path(), '/'));

		$controllerClass = '';
		$route = '';

		if (count($requestPathParts) > 0) {
			$controllerPathParts = array_map('ucFirst', $requestPathParts);
			$controllerNamespace = sprintf('%s\\Controller', $this->config->rootNamespace());

			while (count($requestPathParts) > 0) {
				$proposedClass = $controllerNamespace . implode('\\', $controllerPathParts) . 'Controller';

				if (class_exists($proposedClass)) {
					$controllerClass = $proposedClass;
					$route = implode('/', $requestPathParts);
					break;
				}

				array_pop($requestPathParts);
				array_pop($controllerPathParts);
			}
		}

		if (empty($controllerClass)) {
			$controllerClass = $this->config->defaultController();
			$route = '';
		}

		return array(
			'controller' => $controllerClass,
			'route' => $route
		);
	}

	/**
	 * @param string $class
	 * @param App $app
	 * @return Base\Controller
	 * @throws InvalidRequestException if class does not match an existing class
	 */
	protected function instantiateController($class, App $app)
	{
		if (!class_exists($class)) {
			throw new InvalidRequestException(sprintf('Request routed to an unknown class name: %s', $class));
		}

		try {
			$controller = new $class($app);
		} catch (\Exception $e) {
			throw new InvalidRequestException(sprintf('Failed to instantiate controller class: %s', $class));
		}

		if (!($controller instanceof Base\Controller)) {
			throw new InvalidRequestException(sprintf('Request routed to a non-controller class: %s', $class));
		}

		return $controller;
	}
}
