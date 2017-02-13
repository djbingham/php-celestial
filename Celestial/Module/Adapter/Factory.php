<?php
namespace Celestial\Module\Adapter;

use Celestial\Base\AbstractModuleFactory;
use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Adapter\Face\AdapterInterface;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		$module = new AdapterModule();

		foreach ($this->options['adapters'] as $adapterName => $adapterProperties) {
			$adapter = $this->instantiateAdapter($adapterProperties);
			$module->setAdapter($adapterName, $adapter);
		}

		return $module;
	}

	protected function validateOptions()
	{
		$required = array('adapters');

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Adapter module: ' . implode(', ', $missing)
			);
		}

		if (!is_array($this->options['adapters']) || empty($this->options['adapters'])) {
			throw new InvalidArgumentException('No adapters given in options for Adapter module');
		}

		foreach ($this->options['adapters'] as $adapterName => $adapterProperties) {
			$this->validateAdapterProperties($adapterName, $adapterProperties);
		}
	}

	protected function validateAdapterProperties($adapterName, array $adapterProperties)
	{
		$required = array('class');

		$missing = array_diff($required, array_keys($adapterProperties));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				sprintf('Missing required properties for Adapter `%s`: %s', $adapterName, implode(', ', $missing))
			);
		}

		if (!class_exists($adapterProperties['class'])) {
			throw new InvalidArgumentException(
				sprintf('Invalid class name given for adapter: `%s`', $adapterProperties['class'])
			);
		}
		if (!empty($adapterProperties['options']) && !is_array($adapterProperties['options'])) {
			throw new InvalidArgumentException(
				'Invalid options given in adapter properties: ' . json_encode($adapterProperties['options'])
			);
		}
	}

	/**
	 * @param array $adapterProperties
	 * @return AdapterInterface
	 */
	protected function instantiateAdapter(array $adapterProperties)
	{
		$className = $adapterProperties['class'];
		$options = array();

		if (array_key_exists('options', $adapterProperties)) {
			$options = $adapterProperties['options'];
		}

		return new $className($options);
	}
}
