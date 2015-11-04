<?php
namespace Sloth\Module\DataProvider\Provider;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\DataProvider\Base\AbstractDataProvider;
use Sloth\Module\DataProvider\DataProviderModule;
use Sloth\Module\Resource\ResourceModule;

class ResourceProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		$this->validateOptions($options);
		$options = $this->processOptions($options);

		$resourceFactory = $this->resourceModule->getResourceFactory($options['resourceName']);
		$resourceDefinition = $resourceFactory->getResourceDefinition();
		$resources = $resourceFactory->search($resourceDefinition->attributes, $options['filters']);

		$data = array();
		if ($resources->count() > 0) {
			$data = $resources->getByIndex(0)->getAttributes();
		}

		return $data;
	}

	protected function validateDependencies(array $dependencies)
	{
		$required = array('resourceModule', 'dataProviderModule');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for data provider `ResourceProvider`: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['resourceModule'] instanceof ResourceModule)) {
			throw new InvalidArgumentException('Invalid resource module given in dependencies for data provider `ResourceProvider`');
		}
		if (!($dependencies['dataProviderModule'] instanceof DataProviderModule)) {
			throw new InvalidArgumentException('Invalid data provider module given in dependencies for data provider `ResourceProvider`');
		}
	}

	protected function validateOptions(array $options)
	{
		if (!array_key_exists('resourceName', $options)) {
			throw new InvalidArgumentException('Missing resource name in options set for data provider `ResourceProvider`');
		}
		return $this;
	}

	private function processOptions(array $options)
	{
		if (!array_key_exists('filters', $options)) {
			$options['filters'] = array();
		} else {
			$options['filters'] = $this->processFilters($options['filters']);
		}
		return $options;
	}

	private function processFilters(array $filters)
	{
		$processedFilters = array();
		foreach ($filters as $filter) {
			$processedFilters[] = $this->processFilter($filter);
		}
		return $processedFilters;
	}

	private function processFilter(array $filterProperties)
	{
		if (array_key_exists('value', $filterProperties)) {
			$filterProperties['source'] = array(
				'engine' => 'static',
				'options' => array(
					'data' => $filterProperties['value']
				)
			);
		}

		if (!array_key_exists('source', $filterProperties)) {
			$filterProperties['source'] = array();
		}

		if (!array_key_exists('engine', $filterProperties['source'])) {
			$filterProperties['source']['engine'] = 'static';
		}

		if (!array_key_exists('options', $filterProperties['source'])) {
			$filterProperties['source']['options'] = array();
		}

		$filterSource = $filterProperties['source'];
		$filterDataProvider = $this->dataProviderModule->buildProvider($filterSource);

		$filterData = $filterDataProvider->getData($filterProperties['source']['options']);
		$filterProperties['value'] = $filterData;

		return $filterProperties;
	}
}
