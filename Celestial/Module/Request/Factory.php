<?php
namespace Celestial\Module\Request;

use Celestial\Base\AbstractModuleFactory;

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
