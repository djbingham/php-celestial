<?php
namespace Celestial\Module\DataProvider\Provider;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\DataProvider\Base\AbstractDataProvider;
use Celestial\Module\Session\SessionModule;

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
