<?php
namespace Sloth\Module\Request\Face;

use Sloth\Base\Controller;

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