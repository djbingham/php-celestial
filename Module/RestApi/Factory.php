<?php
namespace Sloth\Module\RestApi;

use Sloth\App;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Face\ModuleFactoryInterface;
use Sloth\Module\Render\Renderer;
use Sloth\Module\Resource\ModuleCore;

class Factory implements ModuleFactoryInterface
{
	private $app;
	private $resourceModule;
	private $renderModule;

	public function __construct(array $dependencies = array())
	{
		$this->validateDependencies($dependencies);
		$this->app = $dependencies['app'];
		$this->resourceModule = $dependencies['resourceModule'];
		$this->renderModule = $dependencies['renderModule'];
	}

	public function initialise()
	{
		$requestParser = new RequestParser();
		$requestHandler = new RequestHandler();

		$requestParser
			->setResourceModule($this->resourceModule)
			->setRenderModule($this->renderModule);

		$requestHandler
			->setResourceModule($this->resourceModule)
			->setRenderModule($this->renderModule);

		$module = new RestApiModule();
		$module->setRequestHandler($requestHandler)
			->setRequestParser($requestParser);

		return $module;
	}

	private function validateDependencies(array $dependencies)
	{
		$required = array('app', 'resourceModule', 'renderModule');
		$missing = array_diff($required, array_keys($dependencies));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for RestApi module: ' . implode(', ', $missing)
			);
		}
		if (!($dependencies['app'] instanceof App)) {
			throw new InvalidArgumentException('Invalid app given in dependencies for RestApi module');
		}
		if (!($dependencies['resourceModule'] instanceof ModuleCore)) {
			throw new InvalidArgumentException('Invalid resource module given in dependencies for RestApi module');
		}
		if (!($dependencies['renderModule'] instanceof Renderer)) {
			throw new InvalidArgumentException('Invalid render module given in dependencies for RestApi module');
		}
	}
}
