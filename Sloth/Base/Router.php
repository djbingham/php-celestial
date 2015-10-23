<?php
namespace Sloth\Base;

use Sloth\App;
use Sloth\Request;

abstract class Router
{
	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $cache = array();

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * @param App $app
	 * @param Request $request
	 * @return string
	 */
	abstract public function route(App $app, Request $request);
}
