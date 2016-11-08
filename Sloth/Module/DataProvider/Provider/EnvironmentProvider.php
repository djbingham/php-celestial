<?php
namespace Sloth\Module\DataProvider;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\DataProvider\Base\AbstractDataProvider;

class EnvironmentProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		return $_ENV[$options['parameter']];
	}

	protected function validateDependencies(array $dependencies)
	{

	}

	protected function validateOptions(array $options)
	{
		if (!array_key_exists('parameter', $options)) {
			throw new InvalidArgumentException('Missing parameter in options set for data provider `EnvironmentProvider`');
		}
	}
}
