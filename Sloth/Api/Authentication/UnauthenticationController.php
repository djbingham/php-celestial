<?php
namespace Sloth\Api\Authentication;

use Sloth\Base\Controller;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Authentication\AuthenticationModule;
use Sloth\Module\Render\Face\RendererInterface;

class UnauthenticationController extends Controller
{
	public function execute(RoutedRequestInterface $request)
	{
		$renderer = $this->getRenderModule();;
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
