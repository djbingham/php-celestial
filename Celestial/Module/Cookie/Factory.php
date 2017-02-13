<?php
namespace Celestial\Module\Cookie;

use Celestial\Base\AbstractModuleFactory;

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
