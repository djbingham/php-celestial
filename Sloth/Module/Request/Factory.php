<?php
namespace Sloth\Module\Request;

use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		return new RequestModule();
	}

	protected function validateOptions()
	{

	}
}
