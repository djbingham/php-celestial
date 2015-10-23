<?php
namespace Sloth\Module\Render;

use Helper\InternalCacheTrait;
use Sloth\App;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Base\AbstractModuleFactory;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	/**
	 * @var ViewFactory
	 */
	protected $viewFactory;

	public function initialise()
	{
		$renderer = new Renderer();
		$renderer->setApp($this->app)
			->setViewFactory($this->getViewFactory());
		return $renderer;
	}

	protected function getViewFactory()
	{
		if (!$this->isCached('viewFactory')) {
			$this->setCached('viewFactory', new ViewFactory(array(
				'viewManifestDirectory' => $this->options['viewManifestDirectory'],
				'viewDirectory' => $this->options['viewDirectory'],
				'renderEngineFactory' => $this->getRenderEngineFactory(),
				'dataProviderFactory' => $this->getDataProviderFactory()
			)));
		}
		return $this->getCached('viewFactory');
	}

	protected function getRenderEngineFactory()
	{
		if (!$this->isCached('renderEngineFactory')) {
			$this->setCached('renderEngineFactory', new RenderEngineFactory(array(

			)));
		}
		return $this->getCached('renderEngineFactory');
	}

	protected function getDataProviderFactory()
	{
		if (!$this->isCached('dataProviderFactory')) {
			$this->setCached('dataProviderFactory', new DataProviderFactory(array(
				'resourceModule' => $this->getResourceModule()
			)));
		}
		return $this->getCached('dataProviderFactory');
	}

	protected function getResourceModule()
	{
		return $this->app->module('resource');
	}

	protected function validateOptions()
	{
		$required = array('viewDirectory');

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required dependencies for Render module: ' . implode(', ', $missing)
			);
		}

		if (!empty($this->options['viewManifestDirectory']) && !is_dir($this->options['viewManifestDirectory'])) {
			throw new InvalidArgumentException('Invalid view manifest directory given in options for Render module');
		}
		if (!is_dir($this->options['viewDirectory'])) {
			throw new InvalidArgumentException('Invalid view directory given in options for Render module');
		}
	}
}
