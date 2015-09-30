<?php
namespace Sloth\Module\Resource\DefinitionBuilder;

use Module\Render\DataProvider\ResourceDataProvider;
use Module\Render\DataProvider\StaticDataProvider;
use Module\Render\DataProviderList;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\ModuleCore;

class ViewListBuilder
{
	/**
	 * @var ModuleCore
	 */
	private $module;

	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->module = $dependencies['module'];
	}

	public function build(array $manifestViews)
	{
		$views = new \Sloth\Module\Render\ViewList();
		foreach ($manifestViews as $viewName => $viewManifest) {
			$view = new \Sloth\Module\Render\View();
			$view->name = $viewName;
			$view->path = $viewManifest['path'];
			$view->engine = $viewManifest['engine'];
			$view->dataProviders = $this->buildDataProviders($viewManifest);
			$views->push($view);
		}
        return $views;
	}

	private function buildDataProviders(array $viewManifest)
	{
		$providers = new DataProviderList();
		if (array_key_exists('providers', $viewManifest)) {
			foreach ($viewManifest['providers'] as $providerName => $providerManifest) {
				$options = $providerManifest['options'];
				$engine = $providerManifest['engine'];
				switch ($engine) {
					case 'static':
						$provider = new StaticDataProvider();
						break;
					case 'resource':
						$provider = new ResourceDataProvider(array(
							'resourceModule' => $this->module
						));
						break;
					default:
						throw new InvalidArgumentException(
							sprintf('Invalid engine given for view data provider: `%s`', $providerName)
						);
						break;
				}
				$provider
					->setName($providerName)
					->setOptions($options);
				$providers->push($provider);
			}
		}
		return $providers;
	}

	private function validateDependencies(array $dependencies)
	{
		$required = array('module');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Render module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['module'] instanceof ModuleCore)) {
			throw new InvalidArgumentException('Invalid resource module given in dependencies for ViewListBuilder');
		}
	}
}
