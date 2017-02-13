<?php
namespace Celestial\Api\Authentication;

use Celestial\Base\Controller;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Authentication\AuthenticationModule;
use Celestial\Module\Render\Face\RendererInterface;

class LogoutController extends Controller
{
	public function execute(RoutedRequestInterface $request)
	{
		$renderer = $this->getRenderModule();
		$authentication = $this->getAuthenticationModule();

		$authentication->unauthenticate();
		$viewName = 'authentication/logoutResult';
		$view = $renderer->getViewFactory()->getByName($viewName);

		return $renderer->render($view, array(
			'sessionString' => json_encode($_SESSION)
		));
	}

	/**
	 * @return AuthenticationModule
	 */
	protected function getAuthenticationModule()
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
