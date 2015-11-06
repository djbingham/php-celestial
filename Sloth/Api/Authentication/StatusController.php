<?php
namespace Sloth\Api\Authentication;

use Sloth\Base\Controller;
use Sloth\Module\Authentication\AuthenticationModule;
use Sloth\Module\Render\RenderModule;
use Sloth\Module\Request\Face\RoutedRequestInterface;

class StatusController extends Controller
{
	public function execute(RoutedRequestInterface $request)
	{
		$authentication = $this->getAuthenticationModule();

		$isAuthenticated = $authentication->isAuthenticated();
		$user = $authentication->getAuthenticatedUser();

		$parameters = array(
			'authenticated' => $isAuthenticated
		);
		if ($isAuthenticated) {
			$parameters['user'] = $user->getAttributes();
		}

		return $this->getRenderModule()->renderNamedView('authentication/status', $parameters);
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
}
