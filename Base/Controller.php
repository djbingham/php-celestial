<?php
namespace Sloth\Base;

use Sloth;

abstract class Controller
{
	protected $app;

	abstract public function execute(Sloth\Face\RequestInterface $request, $route);

	public function __construct(Sloth\App $app)
	{
		$this->app = $app;
	}

    protected function module($name)
    {
        return $this->app->module($name);
    }
}