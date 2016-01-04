<?php
namespace Sloth\Module\Data\TableValidation;

use Sloth\Base\AbstractModuleFactory;

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
