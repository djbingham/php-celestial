<?php
namespace Celestial\Module\Data\TableQuery;

use Celestial\Helper\InternalCacheTrait;
use Celestial\Base\AbstractModuleFactory;

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
