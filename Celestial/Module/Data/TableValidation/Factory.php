<?php
namespace Celestial\Module\Data\TableValidation;

use Celestial\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		return new TableValidatorModule($this->getDependencyManager());
	}

	public function validateOptions()
	{

	}

	protected function getDependencyManager()
	{
		return new DependencyManager($this->app);
	}
}
