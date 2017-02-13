<?php
namespace Celestial\Base;

use Celestial;

abstract class Controller
{
	protected $app;

	abstract public function execute(Celestial\Module\Request\Face\RoutedRequestInterface $request);

	public function __construct(Celestial\App $app)
	{
		$this->app = $app;
		$this->app->getLogModule()->createLogger(__FILE__)->debug(sprintf('Initialised controller'));
	}

    protected function module($name)
    {
        return $this->app->module($name);
    }
}