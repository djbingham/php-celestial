<?php
namespace Sloth\Base;

use Sloth;

abstract class Controller
{
	protected $app;

	abstract public function execute(Sloth\Module\Request\Face\RoutedRequestInterface $request);

	public function __construct(Sloth\App $app)
	{
		$this->app = $app;
	}

    protected function module($name)
    {
        return $this->app->module($name);
    }
}