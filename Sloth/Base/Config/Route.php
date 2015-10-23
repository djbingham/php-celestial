<?php
namespace Sloth\Base\Config;

class Route
{
	/**
	 * @var string
	 */
	private $route;

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var string
	 */
	private $controller;

	public function __construct(array $properties)
	{
		if (array_key_exists('route', $properties)) {
			$this->route = $properties['route'];
		}
		if (array_key_exists('controller', $properties)) {
			$this->controller = $properties['controller'];
		}
		if (array_key_exists('namespace', $properties)) {
			$this->namespace = $properties['namespace'];
		}
	}

	public function getRoute()
	{
		return $this->route;
	}

	public function getNamespace()
	{
		return $this->namespace;
	}

	public function getControllerName()
	{
		return $this->controller;
	}
}
