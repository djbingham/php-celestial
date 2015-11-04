<?php
namespace Sloth\Module\Cookie;

use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		return new CookieModule();
	}

	protected function validateOptions()
	{

	}
}
