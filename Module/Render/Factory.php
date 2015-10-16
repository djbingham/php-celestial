<?php
namespace Sloth\Module\Render;

use Sloth\App;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Face\ModuleFactoryInterface;

class Factory implements ModuleFactoryInterface
{
	/**
	 * @var App
	 */
	private $app;

	/**
	 * @var ViewFactory
	 */
	private $viewFactory;

	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->app = $dependencies['app'];
		$this->viewFactory = $dependencies['viewFactory'];
	}

	public function initialise()
	{
		$renderer = new Renderer();
		$renderer->setApp($this->app)
			->setViewFactory($this->viewFactory);
		return $renderer;
	}

	private function validateDependencies(array $dependencies)
	{
		$required = array('app', 'engines', 'dataProviders', 'viewFactory');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Render module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['app'] instanceof App)) {
			throw new InvalidArgumentException('Invalid app given in dependencies for Render module');
		}
		if (!($dependencies['viewFactory'] instanceof ViewFactory)) {
			throw new InvalidArgumentException('Invalid view factory given in dependencies for Render module');
		}
	}
}
