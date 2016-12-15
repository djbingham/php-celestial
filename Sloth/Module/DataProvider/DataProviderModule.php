<?php
namespace Sloth\Module\DataProvider;

use Sloth\Helper\InternalCacheTrait;
use Sloth\Module\DataProvider\Face\DataProviderInterface;

class DataProviderModule
{
	/**
	 * @var array
	 */
	private $providers = array();

	public function setProviders(array $providers)
	{
		$this->providers = $providers;
		return $this;
	}

	public function buildProvider(array $providerManifest)
	{
		$providerEngine = $this->getProviderEngine($providerManifest['engine']);
		$providerOptions = isset($providerManifest['options']) ? $providerManifest['options'] : [];

		$provider = new DataProvider();
		$provider->setEngine($providerEngine);
		$provider->setOptions($providerOptions);

		return $provider;
	}

	/**
	 * @param $providerName
	 * @return DataProviderInterface
	 */
	private function getProviderEngine($providerName)
	{
		return $this->providers[$providerName];
	}
}
