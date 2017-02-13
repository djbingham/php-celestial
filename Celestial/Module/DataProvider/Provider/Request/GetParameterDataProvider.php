<?php
namespace Celestial\Module\DataProvider\Provider\Request;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\DataProvider\Base\AbstractDataProvider;

class GetParameterDataProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		$this->validateOptions($options);

		$value = null;

		if (array_key_exists('property', $options)) {
			if (array_key_exists($options['property'], $_GET)) {
				$value = $_GET[$options['property']];
			}
		} else {
			$value = $_GET;
		}

		return $value;
	}

	protected function validateDependencies(array $dependencies)
	{

	}

	protected function validateOptions(array $options)
	{
		if (!array_key_exists('property', $options)) {
			throw new InvalidArgumentException('Missing `property` in options for data provider `Request\\GetParameterDataProvider`');
		}
	}
}
