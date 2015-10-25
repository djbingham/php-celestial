<?php
namespace Sloth\Module\Router;

use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		return new RouterModule(array(
			'app' => $this->app,
			'routes' => $this->options['routes'],
			'rootNamespace' => $this->options['rootNamespace'],
			'defaultController' => $this->options['defaultController']
		));
	}

	protected function validateOptions()
	{

	}
}
