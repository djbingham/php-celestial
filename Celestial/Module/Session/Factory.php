<?php
namespace Celestial\Module\Session;

use Celestial\Base\AbstractModuleFactory;

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
