<?php
namespace Celestial\Module\Request;

use Celestial\Base\Controller;
use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Request\Face\RoutedRequestInterface;

class RoutedRequest extends Request implements RoutedRequestInterface
{
	/**
	 * @var Controller
	 */
	protected $controller;

	/**
	 * @var string
	 */
	protected $controllerPath;

	public function getController()
	{
		return $this->controller;
	}

	public function getControllerPath()
	{
		return $this->controllerPath;
	}

	protected function validateProperties(array $properties)
	{
		parent::validateProperties($properties);

		$required = array('controller', 'controllerPath');
		$missing = array_diff($required, array_keys($properties));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required properties for RoutedRequest instance: ' . implode(', ', $missing)
			);
		}

		foreach ($properties as $propertyName => $propertyValue) {
			if (!property_exists($this, $propertyName)) {
				throw new InvalidArgumentException(
					sprintf('Unrecognised property given to RoutedRequest instance: %s', $propertyName)
				);
			}
		}

		if (!is_string($properties['controllerPath'])) {
			throw new InvalidArgumentException('Invalid controller path given to RoutedRequest instance');
		}

		if (!($properties['controller'] instanceof Controller)) {
			throw new InvalidArgumentException('Invalid controller given to RoutedRequest instance');
		}
	}
}
