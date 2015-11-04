<?php
namespace Sloth\Api\Authentication;

use Sloth\Base\Controller;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Adapter\AdapterModule;
use Sloth\Module\Render\RenderModule;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Authentication\AuthenticationModule;

class AuthenticationController extends Controller
{
	public function execute(RoutedRequestInterface $request)
	{
		$method = 'handle' . ucfirst($request->getMethod());

		if (!method_exists($this, $method)) {
			throw new InvalidRequestException(
				sprintf('Invalid request method (%s) to AuthenticationController. Allowed: get, post', $method)
			);
		}

		return $this->$method($request);
	}

	protected function handleGet(RoutedRequestInterface $request)
	{
		$renderer = $this->getRenderModule();

		$viewName = 'authentication/loginForm';
		$view = $renderer->getViewFactory()->getByName($viewName);

		return $renderer->render($view);
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

		$authentication->authenticateCredentials($username, $password, $remember);

		if ($authentication->isAuthenticated()) {
			$viewName = 'authentication/loginSucceeded';
			$parameters = array(
				'user' => $authentication->getAuthenticatedUser()->getAttributes()
			);
		} else {
			$viewName = 'authentication/loginFailed';
			$parameters = array(
				'username' => $username
			);
		}

		$view = $renderer->getViewFactory()->getByName($viewName);

		return $renderer->render($view, $parameters);
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
