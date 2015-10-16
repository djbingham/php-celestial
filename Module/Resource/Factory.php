<?php
namespace Sloth\Module\Resource;

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
	 * @var string
	 */
	private $tableDirectory;

	/**
	 * @var string
	 */
	private $resourceDirectory;

	/**
	 * @var TableManifestValidator
	 */
	private $tableValidator;

	/**
	 * @var ResourceManifestValidator
	 */
	private $resourceValidator;

	/**
	 * @var string
	 */
	private $resourceNamespace;

	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->app = $dependencies['app'];
		$this->tableDirectory = $dependencies['tableDirectory'];
		$this->resourceDirectory = $dependencies['resourceDirectory'];
		$this->tableValidator = $dependencies['tableValidator'];
		$this->resourceValidator = $dependencies['resourceValidator'];
		$this->resourceNamespace = $dependencies['resourceNamespace'];
	}

	public function initialise()
	{
		$module = new ModuleCore($this->app);
		$module->setTableManifestDirectory($this->tableDirectory)
			->setResourceManifestDirectory($this->resourceDirectory)
			->setTableManifestValidator($this->tableValidator)
			->setResourceManifestValidator($this->resourceValidator)
			->setResourceNamespace($this->resourceNamespace);
		return $module;
	}

	private function validateDependencies(array $dependencies)
	{
		$required = array(
			'app',
			'tableDirectory',
			'resourceDirectory',
			'tableValidator',
			'resourceValidator',
			'resourceNamespace'
		);

		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Render module: ' . implode(', ', $missing)
			);
		}

		if (!($dependencies['app'] instanceof App)) {
			throw new InvalidArgumentException('Invalid app given in dependencies for Render module');
		}
		if (!is_dir($dependencies['tableDirectory'])) {
			throw new InvalidArgumentException('Invalid table directory given in dependencies for Render module');
		}
		if (!is_dir($dependencies['resourceDirectory'])) {
			throw new InvalidArgumentException('Invalid resource directory given in dependencies for Render module');
		}
		if (!($dependencies['tableValidator'] instanceof TableManifestValidator)) {
			throw new InvalidArgumentException('Invalid table validator given in dependencies for Render module');
		}
		if (!($dependencies['resourceValidator'] instanceof ResourceManifestValidator)) {
			throw new InvalidArgumentException('Invalid resource validator given in dependencies for Render module');
		}
	}
}
