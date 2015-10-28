<?php
namespace Sloth\Module\Router;

use Sloth\Base;
use Sloth\Module\Request\Request;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Request\RequestModule;

class RouterModule extends \Sloth\Module\Router\Base\Router
{
	/**
	 * @var RequestModule
	 */
	private $requestModule;

	public function __construct(array $properties)
	{
		parent::__construct($properties);
		$this->requestModule = $properties['requestModule'];
	}

	public function route(Request $request)
	{
		$routeData = $this->searchRoutes($request);
		if (empty($routeData['controller'])) {
			$routeData = $this->searchControllers($request);
		}

		if (!array_key_exists('controller', $routeData) && !array_key_exists('namespace', $routeData)) {
			throw new InvalidRequestException(
				sprintf('No controller or namespace found for request: %s', $request->getUri())
			);
		}

		$requestProperties = $request->toArray();
		$requestProperties['controllerPath'] = $routeData['route'];
		$requestProperties['controller'] = $this->instantiateController($routeData['controller']);

		return $this->requestModule->buildRoutedRequest($requestProperties);
	}

	protected function searchRoutes(Request $request)
	{
		$availableRoutes = $this->routes;
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

		if (empty($controllerPathParts)) {
			$controllerPathParts[] = 'index';
		}

		while (!empty($controllerPathParts)) {
			$path = implode('\\', $controllerPathParts);

			$proposedClass = $path . 'Controller';
			if ($namespace !== null) {
				$proposedClass = $namespace . '\\' . $proposedClass;
			}

			if (class_exists($proposedClass)) {
				$controller = $proposedClass;
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
			$controllerNamespace = sprintf('%s\\Controller', $this->rootNamespace);

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
			$controllerClass = $this->defaultController;
			$route = '';
		}

		return array(
			'route' => $route,
			'controller' => $controllerClass
		);
	}

	/**
	 * @param string $class
	 * @return Base\Controller
	 * @throws InvalidRequestException if class does not match an existing class
	 */
	protected function instantiateController($class)
	{
		if (!class_exists($class)) {
			throw new InvalidRequestException(sprintf('Request routed to an unknown class name: %s', $class));
		}

		try {
			$controller = new $class($this->app);
		} catch (\Exception $e) {
			throw new InvalidRequestException(sprintf('Failed to instantiate controller class: %s', $class));
		}

		if (!($controller instanceof Base\Controller)) {
			throw new InvalidRequestException(sprintf('Request routed to a non-controller class: %s', $class));
		}

		return $controller;
	}
}
