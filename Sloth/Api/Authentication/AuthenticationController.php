<?php
namespace Sloth\Api\Authentication;

use Sloth\Base\Controller;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Authentication\AuthenticationModule;
use Sloth\Module\Render\Face\RendererInterface;

class AuthenticationController extends Controller
{
	public function execute(RequestInterface $request, $route)
	{
		$method = 'handle' . ucfirst($request->getMethod());

		if (!method_exists($this, $method)) {
			throw new InvalidRequestException(
				sprintf('Invalid request method (%s) to AuthenticationController. Allowed: get, post', $method)
			);
		}

		return $this->$method($request, $route);
	}

	protected function handleGet(RequestInterface $request, $route)
	{
		$renderer = $this->getRenderModule();

		$viewName = 'authentication/loginForm';
		$view = $renderer->getViewFactory()->getByName($viewName);

		return $renderer->render($view);
	}

	protected function handlePost(RequestInterface $request, $route)
	{
		$renderer = $this->getRenderModule();
		$authentication = $this->getAuthenticationModule();

		$parameters = $request->getParams()->post();
		$username = $parameters['username'];
		$password = $parameters['password'];

		$authentication->authenticateCredentials($username, $password);

		if ($authentication->isAuthenticated()) {
			$viewName = 'authentication/loginResult';
			$parameters = array(
				'username' => $authentication->getAuthenticatedUsername()
			);
		} else {
			$viewName = 'authentication/loginFailed';
			$parameters = array(
				'username' => $authentication->getAuthenticatedUsername()
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
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('render');
	}
}
