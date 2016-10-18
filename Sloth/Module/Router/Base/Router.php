<?php
namespace Sloth\Module\Router\Base;

use Sloth\Helper\InternalCacheTrait;
use Sloth\App;
use Sloth\Base\Config\Routes;
use Sloth\Module\Request\Request;

abstract class Router
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var string
	 */
	protected $rootNamespace;

	/**
	 * @var Routes
	 */
	protected $routes;

	/**
	 * @var string
	 */
	protected $defaultController;

	public function __construct(array $properties)
	{
		$this->app = $properties['app'];
		$this->rootNamespace = $properties['rootNamespace'];
		$this->routes = $properties['routes'];
		$this->defaultController = $properties['defaultController'];
	}

	/**
	 * @param Request $request
	 * @return string
	 */
	abstract public function route(Request $request);
}
