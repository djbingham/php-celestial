<?php
namespace Sloth\Module\Authentication;

use Sloth\Base\AbstractModuleFactory;
use Sloth\Module\Resource\ResourceModule;
use Sloth\Module\Session\SessionModule;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		$moduleProperties = array(
			'sessionModule' => $this->getSessionModule(),
			'resourceModule' => $this->getResourceModule()
		);

		if (array_key_exists('userResource', $this->options)) {
			$moduleProperties['userResource'] = $this->options['userResource'];
		}
		if (array_key_exists('usernameAttribute', $this->options)) {
			$moduleProperties['usernameAttribute'] = $this->options['usernameAttribute'];
		}
		if (array_key_exists('passwordAttribute', $this->options)) {
			$moduleProperties['passwordAttribute'] = $this->options['passwordAttribute'];
		}
		if (array_key_exists('sessionDataKey', $this->options)) {
			$moduleProperties['sessionDataKey'] = $this->options['sessionDataKey'];
		}

		return new AuthenticationModule($moduleProperties);
	}

	protected function validateOptions()
	{

	}

	/**
	 * @return SessionModule
	 */
	protected function getSessionModule()
	{
		return $this->app->module('session');
	}

	/**
	 * @return ResourceModule
	 */
	protected function getResourceModule()
	{
		return $this->app->module('resource');
	}
}
