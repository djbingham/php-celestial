<?php
namespace Sloth\Module\Session;

use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		session_start();
		return new SessionModule();
	}

	protected function validateOptions()
	{

	}
}
