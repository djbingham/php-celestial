<?php
namespace Sloth\Module\Render;

use Module\Render\Face\DataProviderFactoryInterface;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Resource\ModuleCore as ResourceModuleCore;

class DataProviderFactory implements DataProviderFactoryInterface
{
	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->resourceModule = $dependencies['resourceModule'];
	}

	public function buildProviders(array $providersManifest)
	{
		$providers = new DataProviderList();
		foreach ($providersManifest as $providerName => $providerManifest) {
			$options = $providerManifest['options'];
			$engine = $providerManifest['engine'];
			switch ($engine) {
				case 'static':
					$provider = new DataProvider\StaticDataProvider();
					break;
				case 'resource':
					$provider = new DataProvider\ResourceProvider(array(
						'resourceModule' => $this->resourceModule
					));
					break;
				case 'resourceList':
					$provider = new DataProvider\ResourceListProvider(array(
						'resourceModule' => $this->resourceModule
					));
					break;
				case 'phpResource':
					$provider = new DataProvider\PhpResourceProvider(array(
						'resourceModule' => $this->resourceModule
					));
					break;
				default:
					throw new InvalidArgumentException(
						sprintf('Invalid engine given for data provider in Render module: `%s`', $providerName)
					);
					break;
			}
			$provider
				->setName($providerName)
				->setOptions($options);
			$providers->push($provider);
		}
		return $providers;
	}

	protected function validateDependencies(array $dependencies)
	{
		$required = array('resourceModule');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Render module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['resourceModule'] instanceof ResourceModuleCore)) {
			throw new InvalidArgumentException(
				'Invalid resource module given in dependencies for DataProviderFactory in Render module'
			);
		}
	}
}
