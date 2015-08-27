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
	 * @var array
	 */
	private $engines = array();

	/**
	 * @var string
	 */
	private $directory;

	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->app = $dependencies['app'];
		$this->engines = $dependencies['engines'];
		$this->directory = $dependencies['directory'];
	}

	public function initialise()
	{
		return new Renderer($this->app, $this->engines, $this->directory);
	}

	private function validateDependencies(array $dependencies)
	{
		$required = array('app', 'engines', 'directory');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Render module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['app'] instanceof App)) {
			throw new InvalidArgumentException('Invalid app given in dependencies for Render module');
		}
		if (!is_array($dependencies['engines'])) {
			throw new InvalidArgumentException('Invalid engines given in dependencies for Render module');
		}
		if (!is_dir($dependencies['directory'])) {
			throw new InvalidArgumentException('Invalid directory given in dependencies for Render module');
		}
	}
}
