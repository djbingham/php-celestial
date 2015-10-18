<?php

namespace Sloth\Base\Config;
use Sloth\Exception;

class Routes
{
	private $routes = array();

	public function __construct(array $routes)
	{
		foreach ($routes as $requestPath => $routeConfig) {
			$routeConfig['route'] = $requestPath;
			$this->routes[$requestPath] = new Route($routeConfig);
		}
	}

	/**
	 * @param string $name
	 * @return Route
	 * @throws Exception\InvalidArgumentException
	 */
	public function get($name)
	{
		if (!array_key_exists($name, $this->routes)) {
			throw new Exception\InvalidArgumentException(
				sprintf('Unrecognised route requested: %s', $name)
			);
		}
		return $this->routes[$name];
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function routeExists($name)
	{
		return array_key_exists($name, $this->routes);
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->routes);
	}
}