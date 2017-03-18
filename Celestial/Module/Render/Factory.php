<?php
namespace Celestial\Module\Render;

use Celestial\Helper\InternalCacheTrait;
use Celestial\Exception\InvalidArgumentException;
use Celestial\Base\AbstractModuleFactory;

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
				'engineManager' => $this->getEngineManager(),
				'dataProviderModule' => $this->getDataProviderModule()
			)));
		}
		return $this->getCached('viewFactory');
	}

	protected function getEngineManager()
	{
		if (!$this->isCached('engineManager')) {
			$engineManager = new EngineManager();

			$engines = $this->buildEngines();
			foreach ($engines as $engineName => $engine) {
				$engineManager->registerEngine($engineName, $engine);
			}

			$this->setCached('engineManager', $engineManager);
		}
		return $this->getCached('engineManager');
	}

	protected function buildEngines()
	{
		$instances = array();

		if (array_key_exists('engines', $this->options)) {
			$engines = $this->options['engines'];
		} else {
			$engines = array(
				'handlebars' => 'Celestial\\Module\\Render\\Engine\\LightNCandy',
				'json' => 'Celestial\\Module\\Render\\Engine\\Json',
				'mustache' => 'Celestial\\Module\\Render\\Engine\\Mustache',
				'php' => 'Celestial\\Module\\Render\\Engine\\Php'
			);
		}

		foreach ($engines as $engineName => $engineConfig) {
			if (is_array($engineConfig)) {
				$engineClass = $engineConfig['class'];
				$engineOptions = $engineConfig['options'];
			} else {
				$engineClass = $engineConfig;
				$engineOptions = [];
			}
			$instances[$engineName] = new $engineClass($engineOptions);
		}

		return $instances;
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

		if (!is_dir($this->options['viewDirectory'])) {
			throw new InvalidArgumentException('Invalid view directory given in options for Render module.');
		}
		if (array_key_exists('viewManifestDirectory', $this->options) && !is_dir($this->options['viewManifestDirectory'])) {
			throw new InvalidArgumentException('Invalid view manifest directory given in options for Render module.');
		}
		if (array_key_exists('engines', $this->options)) {
			if (!is_array($this->options['engines'])) {
				throw new InvalidArgumentException('Invalid engines option given to Render module. Must be an array.');
			}

			foreach ($this->options['engines'] as $engineName => $engineConfig) {
				if (is_array($engineConfig)) {
					$engineClass = $engineConfig['class'];
					$engineOptions = $engineConfig['options'];
				} else {
					$engineClass = $engineConfig;
					$engineOptions = [];
				}

				if (!is_a($engineClass, 'Celestial\\Module\\Render\\Face\\RenderEngineInterface', true)) {
					throw new InvalidArgumentException(
						sprintf(
							'Invalid class specified for render engine `%s` - must implement RenderEngineInterface.',
							$engineName
						)
					);
				}

				if (!is_array($engineOptions)) {
					throw new InvalidArgumentException(
						sprintf('Invalid options provided for render engine `%s` - must be an array.', $engineOptions)
					);
				}
			}
		}
	}
}
