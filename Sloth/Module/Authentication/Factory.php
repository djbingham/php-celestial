<?php
namespace Sloth\Module\Authentication;

use Sloth\Base\AbstractModuleFactory;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Cookie\CookieModule;
use Sloth\Module\Hashing\HashingModule;
use Sloth\Module\Data\Resource\ResourceModule;
use Sloth\Module\Session\SessionModule;

class Factory extends AbstractModuleFactory
{
	public function initialise()
	{
		$moduleProperties = array(
			'sessionModule' => $this->getSessionModule(),
			'resourceModule' => $this->getResourceModule(),
			'cookieModule' => $this->getCookieModule(),
			'hashingModule' => $this->getHashingModule()
		);

		if (array_key_exists('userResource', $this->options)) {
			$moduleProperties['userResource'] = $this->options['userResource'];
		}
		if (array_key_exists('usernameAttribute', $this->options)) {
			$moduleProperties['usernameAttribute'] = $this->options['usernameAttribute'];
		}
		if (array_key_exists('passwordAttribute', $this->options)) {
			$moduleProperties['passwordAttribute'] = $this->options['passwordAttribute'];
		}
		if (array_key_exists('sessionKey', $this->options)) {
			$moduleProperties['sessionKey'] = $this->options['sessionKey'];
		}
		if (array_key_exists('cookieName', $this->options)) {
			$moduleProperties['cookieName'] = $this->options['cookieName'];
		}
		if (array_key_exists('rememberUser', $this->options)) {
			$moduleProperties['rememberUser'] = $this->options['rememberUser'];
		}
		if (array_key_exists('cookieVerificationResource', $this->options)) {
			$moduleProperties['cookieVerificationResource'] = $this->options['cookieVerificationResource'];
		}

		return new AuthenticationModule($moduleProperties);
	}

	protected function validateOptions()
	{
		$required = array('cookieVerificationResource');

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Authentication module: ' . implode(', ', $missing)
			);
		}

		if ($this->options['rememberUser'] === true && !array_key_exists('cookieVerificationResource', $this->options)) {
			throw new InvalidArgumentException(
				'Cookie table resource is required to remember user in Authentication module'
			);
		}
	}

	/**
	 * @return SessionModule
	 */
	protected function getSessionModule()
	{
		return $this->app->module('session');
	}

	/**
	 * @return ResourceModule
	 */
	protected function getResourceModule()
	{
		return $this->app->module('data.resource');
	}

	/**
	 * @return CookieModule
	 */
	protected function getCookieModule()
	{
		return $this->app->module('cookie');
	}

	/**
	 * @return HashingModule
	 */
	protected function getHashingModule()
	{
		return $this->app->module('hashing');
	}
}
