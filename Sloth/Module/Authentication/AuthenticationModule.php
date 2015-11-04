<?php
namespace Sloth\Module\Authentication;

use Sloth\Exception\AuthenticationException;
use Sloth\Exception\InvalidConfigurationException;
use Sloth\Module\Cookie\CookieModule;
use Sloth\Module\Hashing\HashingModule;
use Sloth\Module\Resource\Resource;
use Sloth\Module\Resource\ResourceModule;
use Sloth\Module\Session\SessionModule;

class AuthenticationModule
{
	/**
	 * @var SessionModule
	 */
	private $sessionModule;

	/**
	 * @var CookieModule
	 */
	private $cookieModule;

	/**
	 * @var ResourceModule
	 */
	private $resourceModule;

	/**
	 * @var HashingModule
	 */
	private $hashingModule;

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
	private $sessionKey = 'slothAuthentication';

	/**
	 * @var bool
	 */
	private $rememberUser = false;

	/**
	 * @var string
	 */
	private $cookieName = 'slothAuthentication';

	/**
	 * @var int
	 */
	private $cookieDuration = 84600;

	/**
	 * @var string
	 */
	private $cookieVerificationResource = 'AuthenticationCookie';

	public function __construct(array $properties)
	{
		$this->sessionModule = $properties['sessionModule'];
		$this->resourceModule = $properties['resourceModule'];
		$this->cookieModule = $properties['cookieModule'];
		$this->hashingModule = $properties['hashingModule'];
		$this->cookieVerificationResource = $properties['cookieVerificationResource'];

		$this->rememberUser = $properties['rememberUser'];

		if (array_key_exists('userResource', $properties)) {
			$this->userResource = $properties['userResource'];
		}
		if (array_key_exists('usernameAttribute', $properties)) {
			$this->usernameAttribute = $properties['usernameAttribute'];
		}
		if (array_key_exists('passwordAttribute', $properties)) {
			$this->passwordAttribute = $properties['passwordAttribute'];
		}
		if (array_key_exists('sessionKey', $properties)) {
			$this->sessionKey = $properties['sessionKey'];
		}
		if (array_key_exists('cookieName', $properties)) {
			$this->cookieName = $properties['cookieName'];
		}
	}

	public function isAuthenticated()
	{
		$isAuthenticated = $this->isAuthenticatedBySession();

		if (!$isAuthenticated && $this->rememberUser === true) {
			$isAuthenticated = $this->isAuthenticatedByCookie();
		}

		return $isAuthenticated;
	}

	public function authenticateCredentials($username, $password, $rememberUser = false)
	{
		$authenticatedUser = $this->getUserByCredentials($username, $password);
		$isAuthenticated = ($authenticatedUser instanceof Resource);

		if ($isAuthenticated === true && $this->rememberUser === true) {
			$userId = $authenticatedUser->getAttribute($authenticatedUser->getDefinition()->primaryAttribute);

			if ($rememberUser === true) {
				$cookie = $this->getAuthenticationCookie();

				if ($cookie === null) {
					$cookie = new AuthenticationCookie(array(
						'identifier' => $this->generateToken(),
						'token' => $this->generateToken(),
						'expires' => $this->calculateCookieExpiryDate()
					));

					$this->saveAuthenticationCookie($cookie, $userId);
				} else {
					$cookie->setToken($this->generateToken())
						->setExpires($this->calculateCookieExpiryDate());

					$this->saveAuthenticationCookie($cookie, $userId, $cookie->getIdentifier());
				}

				$this->saveSessionAuthentication($userId, $cookie);
			} else {
				$this->saveSessionAuthentication($userId);
			}
		}

		return $isAuthenticated;
	}

	public function getAuthenticatedUser()
	{
		$userResource = null;

		if ($this->isAuthenticated()) {
			$sessionData = $this->sessionModule->get($this->sessionKey);
			$userId = $sessionData['userId'];

			$userResourceFactory = $this->getUserResourceFactory();
			$userResourceDefinition = $userResourceFactory->getResourceDefinition();

			$matchedUsers = $userResourceFactory->getBy(
				$userResourceDefinition->attributes,
				array(
					$userResourceDefinition->primaryAttribute => $userId
				)
			);

			if ($matchedUsers->count() > 0) {
				$userResource = $matchedUsers->getByIndex(0);
			}
		}

		return $userResource;
	}

	public function unauthenticate()
	{
		$this->sessionModule->remove($this->sessionKey);
		$this->cookieModule->destroy($this->cookieName);
		return $this;
	}

	protected function getUserByCredentials($username, $password)
	{
		$resourceFactory = $this->getUserResourceFactory();
		$resourceDefinition = $resourceFactory->getResourceDefinition();

		$filters = array(
			$this->usernameAttribute => $username,
			$this->passwordAttribute => $password
		);

		$matchedUsers = $resourceFactory->getBy($resourceDefinition->attributes, $filters);

		if ($matchedUsers->count() === 1) {
			$user = $matchedUsers->getByIndex(0);
		} else {
			$user = null;
		}

		return $user;
	}

	protected function isAuthenticatedBySession()
	{
		return $this->sessionModule->exists($this->sessionKey);
	}

	protected function isAuthenticatedByCookie()
	{
		$isAuthenticated = false;

		$cookie = $this->getAuthenticationCookie();

		if ($cookie !== null) {
			if (!$this->resourceModule->resourceExists($this->cookieVerificationResource)) {
				throw new InvalidConfigurationException(
					'Verification resource for authentication cookie not found: ' . $this->cookieVerificationResource
				);
			}

			$resource = $this->getCookieVerificationResource($cookie->getIdentifier());

			if ($resource === null) {
				$isAuthenticated = false;
			} else {
				$token = $cookie->getToken();
				$hashedToken = $resource->getAttribute('token');

				$expiryDate = new \DateTime($resource->getAttribute('expires'));
				$currentDate = new \DateTime();

				if ($expiryDate > $currentDate && $this->hashingModule->verifySecureHash($token, $hashedToken)) {
					$isAuthenticated = true;
					$userId = $resource->getAttribute('userId');
					$cookie->setToken($this->generateToken());

					$this->saveAuthenticationCookie($cookie, $userId, $cookie->getIdentifier());
				}
			}
		}

		return $isAuthenticated;
	}

	protected function getAuthenticationCookie()
	{
		$cookie = null;

		if ($this->cookieModule->exists($this->cookieName)) {
			$cookieString = $this->cookieModule->get($this->cookieName);
			$cookieData = json_decode($cookieString, true);

			$this->validateAuthenticationCookie($cookieData);

			$cookie = new AuthenticationCookie($cookieData);
		}

		return $cookie;
	}

	protected function validateAuthenticationCookie($cookieData)
	{
		if (!is_array($cookieData)) {
			throw new AuthenticationException('Authentication cookie has an invalid data format');
		}
		if (!array_key_exists('identifier', $cookieData)) {
			throw new AuthenticationException('Identifier missing from authentication cookie');
		}
		if (!array_key_exists('token', $cookieData)) {
			throw new AuthenticationException('Token missing from authentication cookie');
		}
	}

	protected function generateToken()
	{
		return uniqid();
	}

	protected function saveSessionAuthentication($userId, AuthenticationCookie $cookie = null)
	{
		$sessionData = array();

		if ($this->sessionModule->exists($this->sessionKey)) {
			$sessionData = $this->sessionModule->get($this->sessionKey);
		}

		$sessionData['userId'] = $userId;

		if ($cookie !== null) {
			$sessionData['cookieToken'] = $cookie->getToken();
		}

		$this->sessionModule->set($this->sessionKey, $sessionData);

		return $this;
	}

	protected function calculateCookieExpiryDate()
	{
		$cookieDurationInterval = new \DateInterval('PT' . $this->cookieDuration . 'S');

		$expiryDate = new \DateTime();
		$expiryDate->add($cookieDurationInterval);

		return $expiryDate;
	}

	protected function saveAuthenticationCookie(AuthenticationCookie $cookie, $userId, $previousIdentifier = null)
	{
		$expiryString = $cookie->getExpires()->format('Y-m-d H:i:s');

		/*
			The cookie data includes an identifier, token and expiry date.
			The identifier is used to lookup the user and a hash of their token in the database.
			The expiry date is held in the database and used to reject old cookies.
			If the hash of an unexpired cookie's token matches the token in the database, the cookie is authenticated.
			The expiry date is included in the cookie so clients can avoid unnecessary API calls to check it.
		*/
		$cookieData = $cookie->toArray();

		/*
			Verification data for each authentication cookie is held in the database.
			This includes the identifier and userId, for identification of which user the cookie belongs to.
			The token is equivalent to a password, so hashed before storage to ensure security.
			The expiry date is used to reject old cookies.
	 	*/
		$verificationData = array(
			'identifier' => $cookie->getIdentifier(),
			'userId' => $userId,
			'token' => $this->hashingModule->secureHash($cookie->getToken()),
			'expires' => $expiryString
		);

		$this->cookieModule->set($this->cookieName, json_encode($cookieData), $cookie->getExpires()->getTimestamp());

		$resourceFactory = $this->getCookieVerificationResourceFactory();

		if ($previousIdentifier === null) {
			$resourceFactory->create($verificationData);
		} else {
			$filters = array('identifier' => $previousIdentifier);
			$resourceFactory->update($filters, $verificationData);
		}
	}

	protected function getCookieVerificationResource($identifier)
	{
		$resourceFactory = $this->getCookieVerificationResourceFactory();
		$matchedResources = $resourceFactory->getBy(
			array(
				'identifier' => true,
				'userId' => true,
				'token' => true,
				'expires' => true
			),
			array('identifier' => $identifier)
		);

		$resource = null;
		if ($matchedResources->count() > 0) {
			$resource = $matchedResources->getByIndex(0);
		}

		return $resource;
	}

	protected function getCookieVerificationResourceFactory()
	{
		return $this->resourceModule->getResourceFactory($this->cookieVerificationResource);
	}

	protected function getUserResourceFactory()
	{
		return $this->resourceModule->getResourceFactory($this->userResource);
	}
}
