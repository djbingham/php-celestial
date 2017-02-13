<?php
namespace Celestial\Module\DataProvider\Provider;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Authentication\AuthenticationModule;
use Celestial\Module\DataProvider\Base\AbstractDataProvider;
use Celestial\Module\Data\Resource\Face\ResourceInterface;

class AuthenticationDataProvider extends AbstractDataProvider
{
	public function getData(array $options)
	{
		$this->validateOptions($options);

		$data = null;
		$user = $this->authenticationModule->getAuthenticatedUser();

		if ($user instanceof ResourceInterface) {
			if (array_key_exists('item', $options)) {
				$data = $user->getAttribute($options['item']);
			} else {
				$data = $user->getAttributes();
			}
		}

		return $data;
	}

	protected function validateDependencies(array $dependencies)
	{
		$required = array('authenticationModule');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Render module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['authenticationModule'] instanceof AuthenticationModule)) {
			throw new InvalidArgumentException('Invalid resource module given in dependencies for data provider `AuthenticationDataProvider`');
		}
	}

	protected function validateOptions(array $options)
	{
		if (!array_key_exists('item', $options)) {
			throw new InvalidArgumentException('Missing `item` in options for data provider `AuthenticationDataProvider`');
		}
		return $this;
	}
}
