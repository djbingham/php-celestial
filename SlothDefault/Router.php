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
		$requestUri = $request->getUri();

		if ($canCacheRequest) {
			$output = $this->searchCache($requestUri);
		}

		if (is_null($output)) {
			$routeData = $this->searchRoutes($request);
			if (empty($routeData['controller'])) {
				$routeData = $this->searchControllers($request);
			}

			if (!array_key_exists('controller', $routeData) && !array_key_exists('namespace', $routeData)) {
				throw new InvalidRequestException(sprintf('No controller or namespace found for request: %s', $requestUri));
			}

			$route = $routeData['route'];
			$controller = $this->instantiateController($routeData['controller'], $app);
			$output = $controller->execute($request, $route);

			if ($canCacheRequest) {
				$this->cacheOutput($requestUri, $output);
			}
		}

		return $output;
	}

	protected function canCache(Request $request)
	{
		return $request->getMethod() === 'getChild';
	}

	protected function searchCache($uri)
	{
		$output = null;
		if (array_key_exists($uri, $this->cache)) {
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
		$availableRoutes = $this->config->routes();
		$routeString = null;
		$controllerName = null;

		if ($availableRoutes->count() > 0) {
			$routeParts = explode('/', $request->getPath());
			$remainingPathParts = array();

			while (count($routeParts) > 0) {
				$proposedRoute = implode('/', $routeParts);

				if ($availableRoutes->routeExists($proposedRoute)) {
					$routeParams = $availableRoutes->get($proposedRoute);
					$routeString = $routeParams->getRoute();
					$controllerName = $routeParams->getControllerName();

					if ($controllerName === null) {
						$namespace = $routeParams->getNamespace();

						$controllerSearchResult = $this->findController($remainingPathParts, $namespace);
						$controllerName = $controllerSearchResult['controllerName'];
						$routeString .= '/' . $controllerSearchResult['route'];
					}
					break;
				}

				array_unshift($remainingPathParts, array_pop($routeParts));
			}
		}

		return array(
			'route' => $routeString,
			'controller' => $controllerName
		);
	}

	private function findController(array $routeParts, $namespace = null)
	{
		$controller = null;
		$controllerPathParts = array();

		foreach ($routeParts as $routePart) {
			$controllerPathParts[] = ucfirst($routePart);
		}

		while (!empty($controllerPathParts)) {
			$path = implode('\\', $controllerPathParts);
			if ($namespace !== null) {
				$path = $namespace . '\\' . $path . 'Controller';
			}
			if (class_exists($path)) {
				$controller = $path;
				break;
			}
			array_pop($controllerPathParts);
			array_pop($routeParts);
		}

		return array(
			'controllerName' => $controller,
			'route' => implode('/', $routeParts)
		);
	}

	protected function searchControllers(Request $request)
	{
		$requestPathParts = explode('/', rtrim($request->getPath(), '/'));

		$controllerClass = '';
		$route = '';

		if (count($requestPathParts) > 0) {
			$controllerPathParts = array_map('ucFirst', $requestPathParts);
			$controllerNamespace = sprintf('%s\\Controller', $this->config->rootNamespace());

			while (count($requestPathParts) > 0) {
				$proposedClass = $controllerNamespace . '\\' . implode('\\', $controllerPathParts) . 'Controller';

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
			'route' => $route,
			'controller' => $controllerClass
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
