<?php
namespace Sloth\Module\DataProvider\Provider;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\DataProvider\Base\AbstractDataProvider;
use Sloth\Module\Session\SessionModule;

class SessionDataProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		return $this->sessionModule->get($options['item']);
	}

	protected function validateDependencies(array $dependencies)
	{
		$required = array('sessionModule');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for data provider `SessionDataProvider`: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['sessionModule'] instanceof SessionModule)) {
			throw new InvalidArgumentException('Invalid resource module given in dependencies for data provider `SessionDataProvider`');
		}
	}
}
