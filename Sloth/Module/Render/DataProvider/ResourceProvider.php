<?php
namespace Sloth\Module\Render\DataProvider;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Render\Face\DataProviderInterface;
use Sloth\Module\Resource as ResourceModule;

class ResourceProvider implements DataProviderInterface
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $options = array();

	/**
	 * @var ResourceModule\ResourceModule
	 */
	private $resourceModule;

	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->resourceModule = $dependencies['resourceModule'];
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setOptions(array $options)
	{
		$this->validateOptions($options);
		$this->options = $this->processOptions($options);
		return $this;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getData()
	{
		$resourceModule = $this->resourceModule;
		$resourceFactory = $resourceModule->getResourceFactory($this->getResourceName());
		$resourceDefinition = $resourceFactory->getResourceDefinition();
		$resources = $resourceFactory->search($resourceDefinition->attributes, $this->getResourceFilters());
		return $resources->getByIndex(0);
	}

	private function validateOptions(array $options)
	{
		if (!array_key_exists('resourceName', $options)) {
			throw new InvalidArgumentException(
				sprintf('Missing resource name in options set for resource data provider with name `%s`', $this->getName())
			);
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
		foreach ($filters as $index => $filter) {
			if (array_key_exists('dynamicValue', $filter)) {
				$filter['value'] = $this->computeDynamicFilterValue($filter['dynamicValue']);
			}
			$processedFilters[$index] = $filter;
		}
		return $processedFilters;
	}

	private function computeDynamicFilterValue($key)
	{
		// todo: Compute the correct dynamic value. e.g. Fetch from another resource, or based on current user data.
		return $key;
	}

	private function validateDependencies(array $dependencies)
	{
		$required = array('resourceModule');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Render module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['resourceModule'] instanceof ResourceModule\ResourceModule)) {
			throw new InvalidArgumentException('Invalid resource module given in dependencies for Render module');
		}
	}

	private function getResourceName()
	{
		return $this->options['resourceName'];
	}

	private function getResourceFilters()
	{
		return $this->options['filters'];
	}
}