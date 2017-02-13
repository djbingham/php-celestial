<?php
namespace Celestial\Api\Authentication;

use Celestial\Base\Controller;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Adapter\AdapterModule;
use Celestial\Module\Render\RenderModule;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Authentication\AuthenticationModule;

class LoginController extends Controller
{
	public function execute(RoutedRequestInterface $request)
	{
		$method = 'handle' . ucfirst($request->getMethod());

		if (!method_exists($this, $method)) {
			throw new InvalidRequestException(
				sprintf('Invalid request method (%s) to LoginController. Allowed: get, post', $method)
			);
		}

		return $this->$method($request);
	}

	protected function handleGet(RoutedRequestInterface $request)
	{
		return $this->getRenderModule()->renderNamedView('authentication/loginForm');
	}

	protected function handlePost(RoutedRequestInterface $request)
	{
		$renderer = $this->getRenderModule();
		$authentication = $this->getAuthenticationModule();
		$adapterModule = $this->getAdapterModule();

		$parameters = $request->getParams()->post();

		$parameters = $adapterModule->getAdapter('stringBoolean')->adapt($parameters);
		$parameters = $adapterModule->getAdapter('stringNull')->adapt($parameters);

		$username = $parameters['username'];
		$password = $parameters['password'];
		$remember = array_key_exists('remember', $parameters) ? $parameters['remember'] : false;

		$previouslyAuthenticated = $authentication->isAuthenticated();
		$newlyAuthenticated = $authentication->authenticateCredentials($username, $password, $remember);

		if ($newlyAuthenticated) {
			$viewName = 'authentication/loginSucceeded';
			$parameters = array(
					'user' => $authentication->getAuthenticatedUser()->getAttributes()
			);
		} elseif ($previouslyAuthenticated) {
			$previousUser = $authentication->getAuthenticatedUser();
			$authentication->unauthenticate();

			$viewName = 'authentication/loginRedundant';
			$parameters = array(
				'username' => $username,
				'previousUsername' => $previousUser->getAttribute('username')
			);
		} else {
			$viewName = 'authentication/loginFailed';
			$parameters = array(
				'username' => $username
			);
		}

		return $renderer->renderNamedView($viewName, $parameters);
	}

	/**
	 * @return AuthenticationModule
	 */
	private function getAuthenticationModule()
	{
		return $this->module('authentication');
	}

	/**
	 * @return RenderModule
	 */
	private function getRenderModule()
	{
		return $this->module('render');
	}

	/**
	 * @return AdapterModule
	 */
	private function getAdapterModule()
	{
		return $this->module('adapter');
	}
}
