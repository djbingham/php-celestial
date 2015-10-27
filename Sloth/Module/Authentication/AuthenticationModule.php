<?php
namespace Sloth\Module\Authentication;

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
	private $sessionTokenKey = 'slothAuthToken';

	/**
	 * @var string
	 */
	private $sessionUsernameKey = 'slothUsername';

	public function __construct(array $properties)
	{
		$this->sessionModule = $properties['sessionModule'];
		$this->resourceModule = $properties['resourceModule'];
		$this->userResource = $properties['userResource'];

		if (array_key_exists('usernameAttribute', $properties)) {
			$this->usernameAttribute = $properties['usernameAttribute'];
		}
		if (array_key_exists('passwordAttribute', $properties)) {
			$this->passwordAttribute = $properties['passwordAttribute'];
		}
		if (array_key_exists('sessionTokenKey', $properties)) {
			$this->sessionTokenKey = $properties['sessionTokenKey'];
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
			$this->sessionModule->set($this->sessionTokenKey, $token);
			$this->sessionModule->set($this->sessionUsernameKey, $username);
		}

		return $authenticated;
	}

	public function isAuthenticated()
	{
		return $this->sessionModule->exists($this->sessionTokenKey);
	}

	public function getAuthenticatedUsername()
	{
		if ($this->isAuthenticated()) {
			return $this->sessionModule->get($this->sessionUsernameKey);
		}
	}

	public function unauthenticate()
	{
		$this->sessionModule->destroy($this->sessionTokenKey);
		$this->sessionModule->destroy($this->sessionUsernameKey);
	}

	protected function generateToken()
	{
		return uniqid();
	}
}
