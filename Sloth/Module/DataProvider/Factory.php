<?php
namespace Sloth\Module\DataProvider;

use Sloth\Base\AbstractModuleFactory;
use Sloth\Module\DataProvider\Face\DataProviderInterface;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		$module = new DataProviderModule();
		$module->setProviders($this->initialiseProviders($module));
		return $module;
	}

	protected function validateOptions()
	{

	}

	protected function initialiseProviders(DataProviderModule $module)
	{
		$dependencies = $this->getDependencies($module);
		$providers = array();

		foreach ($this->options['providers'] as $providerName => $providerClass) {
			$provider = $this->instantiateProviderEngine($providerClass, $dependencies);
			$providers[$providerName] = $provider;
		}

		return $providers;
	}

	protected function getDependencies(DataProviderModule $module)
	{
		return array(
			'dataProviderModule' => $module,
			'resourceModule' => $this->getResourceModule(),
			'sessionModule' => $this->getSessionModule(),
			'authenticationModule' => $this->getAuthenticationModule()
		);
	}

	protected function getResourceModule()
	{
		return $this->app->module('resource');
	}

	protected function getSessionModule()
	{
		return $this->app->module('session');
	}

	protected function getAuthenticationModule()
	{
		return $this->app->module('authentication');
	}

	/**
	 * @param string $providerClass
	 * @param array $dependencies
	 * @return DataProviderInterface
	 */
	private function instantiateProviderEngine($providerClass, array $dependencies)
	{
		return new $providerClass($dependencies);
	}
}
