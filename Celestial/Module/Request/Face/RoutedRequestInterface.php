<?php
namespace Celestial\Module\Request\Face;

use Celestial\Base\Controller;

interface RoutedRequestInterface extends RequestInterface
{
	/**
	 * @return Controller
	 */
	public function getController();

	/**
	 * @return string
	 */
	public function getControllerPath();
}