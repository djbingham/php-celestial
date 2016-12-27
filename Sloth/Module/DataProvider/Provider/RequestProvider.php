<?php
namespace Sloth\Module\DataProvider\Provider;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\DataProvider\Base\AbstractDataProvider;
use Sloth\Module\Request\RequestModule;

class RequestProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		$this->validateOptions($options);

		$value = $this->requestModule->fromServerVars()->toArray();

		if (isset($options['item'])) {
			$value = $value[$options['item']];
		}

		return $value;
	}

	protected function validateDependencies(array $dependencies)
	{
		$required = array('requestModule');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for data provider `RequestProvider`: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['requestModule'] instanceof RequestModule)) {
			throw new InvalidArgumentException('Invalid resource module given in dependencies for data provider `ResourceProvider`');
		}
	}

	protected function validateOptions(array $options)
	{

	}
}
