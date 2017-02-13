<?php
namespace Celestial\Module\DataProvider\Provider;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\DataProvider\Base\AbstractDataProvider;

class StaticDataProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		$this->validateOptions($options);
		return $options['data'];
	}

	protected function validateDependencies(array $dependencies)
	{

	}

	protected function validateOptions(array $options)
	{
		if (!array_key_exists('data', $options)) {
			throw new InvalidArgumentException('Missing data in options set for data provider `StaticDataProvider`');
		}
	}
}
