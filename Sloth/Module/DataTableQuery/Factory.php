<?php
namespace Sloth\Module\DataTableQuery;

use Helper\InternalCacheTrait;
use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		$module = new DataTableQueryModule($this->app);

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
