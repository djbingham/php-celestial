<?php
namespace Sloth\Module\Render;

use Helper\InternalCacheTrait;
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
		$renderer = new RenderModule();
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
				'dataProviderModule' => $this->getDataProviderModule()
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

	protected function getDataProviderModule()
	{
		return $this->app->module('dataProvider');
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
