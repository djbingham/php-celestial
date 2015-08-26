<?php

namespace Sloth\Base\Config;
use Sloth\Exception;

class Routes
{
    private $routes = array();

	public function __construct(array $routes)
	{
        foreach ($routes as $requestPath => $controller) {
            $this->routes[$requestPath] = $controller;
        }
	}

    /**
     * @param string $name
     * @return string
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
}