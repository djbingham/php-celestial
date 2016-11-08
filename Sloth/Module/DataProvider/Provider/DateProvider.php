<?php
namespace Sloth\Module\DataProvider;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\DataProvider\Base\AbstractDataProvider;

class DateProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		return date($options['format']);
	}

	protected function validateDependencies(array $dependencies)
	{

	}

	protected function validateOptions(array $options)
	{
		if (!array_key_exists('format', $options)) {
			throw new InvalidArgumentException('Missing format in options set for data provider `DateProvider`');
		}
	}
}
