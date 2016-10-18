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

		$provider = new DataProvider();
		$provider->setEngine($providerEngine)
			->setOptions($providerManifest['options']);

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
