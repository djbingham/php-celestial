<?php
namespace Sloth\Module\Authentication;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Resource\ResourceModule;
use Sloth\Module\Session\SessionModule;

class AuthenticationModule
{
	/**
	 * @var SessionModule
	 */
	private $sessionModule;

	/**
	 * @var ResourceModule
	 */
	private $resourceModule;

	/**
	 * @var string
	 */
	private $userResource = 'user';

	/**
	 * @var string
	 */
	private $usernameAttribute = 'username';

	/**
	 * @var string
	 */
	private $passwordAttribute = 'password';

	/**
	 * @var string
	 */
	private $sessionDataKey = 'slothAuthentication';

	public function __construct(array $properties)
	{
		$this->sessionModule = $properties['sessionModule'];
		$this->resourceModule = $properties['resourceModule'];

		if (array_key_exists('userResource', $properties)) {
			$this->userResource = $properties['userResource'];
		}
		if (array_key_exists('usernameAttribute', $properties)) {
			$this->usernameAttribute = $properties['usernameAttribute'];
		}
		if (array_key_exists('passwordAttribute', $properties)) {
			$this->passwordAttribute = $properties['passwordAttribute'];
		}
		if (array_key_exists('sessionDataKey', $properties)) {
			$this->sessionDataKey = $properties['sessionDataKey'];
		}
	}

	public function authenticateCredentials($username, $password)
	{
		$resourceFactory = $this->resourceModule->getResourceFactory($this->userResource);
		$attributesToFetch = array(
			$this->usernameAttribute => true,
			$this->passwordAttribute => true
		);
		$filters = array(
			$this->usernameAttribute => $username,
			$this->passwordAttribute => $password
		);

		$matchedUsers = $resourceFactory->getBy($attributesToFetch, $filters);
		$authenticated = ($matchedUsers->count() === 1);

		if ($authenticated === true) {
			$token = $this->generateToken();
			if ($this->sessionModule->exists($this->sessionDataKey)) {
				$sessionData = $this->sessionModule->get($this->sessionDataKey);
			} else {
				$sessionData = array();
			}
			$sessionData['token'] = $token;
			$sessionData['username'] = $username;
			$this->sessionModule->set($this->sessionDataKey, $sessionData);
		}

		return $authenticated;
	}

	public function isAuthenticated()
	{
		return $this->sessionModule->exists($this->sessionDataKey);
	}

	public function getAuthenticatedData($key)
	{
		$output = null;
		if ($this->isAuthenticated()) {
			$sessionData = $this->sessionModule->get($this->sessionDataKey);
			if (!array_key_exists($key, $sessionData)) {
				throw new InvalidArgumentException(
					sprintf('Item `%s` not found in session authentication data.', $key)
				);
			}
			$output = $sessionData[$key];
		}
		return $output;
	}

	public function getAuthenticatedUsername()
	{
		return $this->getAuthenticatedData('username');
	}

	public function unauthenticate()
	{
		$this->sessionModule->destroy($this->sessionDataKey);
	}

	protected function generateToken()
	{
		return uniqid();
	}
}
