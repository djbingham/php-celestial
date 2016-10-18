<?php
namespace Sloth\Module\Data\TableQuery;

use Sloth\Helper\InternalCacheTrait;
use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		$module = new TableQueryModule($this->app);

		$module->setDatabase($this->getDatabaseWrapper());

		return $module;
	}

	protected function validateOptions()
	{

	}

	protected function getDatabaseWrapper()
	{
		return $this->app->module('mysql');
	}
}
