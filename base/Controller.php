<?php
namespace Sloth\Base;

use Sloth;

abstract class Controller
{
	protected $app;

	abstract public function execute(Sloth\Request $request, $route);

	public function __construct(Sloth\App $app)
	{
		$this->app = $app;
	}

    protected function module($name)
    {
        return $this->app->module($name);
    }

	protected function render($view, array $parameters = array())
	{
        return $this->app->render()->full($view, $parameters);
	}

    protected function capture($view, array $parameters = array())
    {
        return $this->app->render()->captureFull($view, $parameters);
    }
}