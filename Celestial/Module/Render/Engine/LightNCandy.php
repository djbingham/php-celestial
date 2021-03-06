<?php
namespace Celestial\Module\Render\Engine;

use Celestial\Exception\InvalidArgumentException;
use Celestial\Exception\InvalidConfigurationException;
use Celestial\Module\Render\Face\RenderEngineInterface;
use LightnCandy\LightnCandy as LightNCandyEngine;
use Celestial\Module\Render\Face\ViewInterface;

class LightNCandy implements RenderEngineInterface
{
	private $options = [];

	public function __construct(array $options)
	{
		$this->options = $options;
	}

	public function render(ViewInterface $view, array $parameters = [])
	{
		$template = file_get_contents($view->getPath());
		$options = $this->getOptions($view);

		$php = LightNCandyEngine::compile($template, $options);

		$renderer = eval($php);

		return $renderer($parameters);
	}

	private function getOptions(ViewInterface $view)
	{
		$rawOptions = array_merge_recursive($this->options, $view->getOptions());

		$this->validateOptions($rawOptions);

		$options = $this->padOptions($rawOptions);

		$options['flags'] = $this->compileFlags($options['flags']);
		$options['helpers'] = $this->compileHelpers($options['helpers']);
		$options['partials'] = $this->compilePartials($options['partials']);

		return $options;
	}

	private function validateOptions(array $options)
	{
		if (isset($options['partials'])) {
			if (!isset($options['partialsDirectory'])) {
				throw new InvalidArgumentException(
					'Missing `partialsDirectory` option for render engine `LightNCandy`. Required when partials are provided.'
				);
			}

			if (!is_dir($options['partialsDirectory'])) {
				throw new InvalidArgumentException(
					'Invalid `partialsDirectory` option given to render engine `LightNCandy`. Must be a directory.'
				);
			}

			if (!is_array($options['partials'])) {
				throw new InvalidArgumentException(
					'Invalid `partials` option given to render engine `LightNCandy`. Must be an array.'
				);
			}
		}

		if (isset($options['flags']) && !is_array($options['flags'])) {
			throw new InvalidArgumentException(
				'Invalid `flags` option given to render engine `LightNCandy`. Must be an array.'
			);
		}

		if (isset($options['helpers']) && !is_array($options['helpers'])) {
			throw new InvalidArgumentException(
				'Invalid `helpers` option given to render engine `LightNCandy`. Must be an array.'
			);
		}
	}

	private function padOptions(array $options)
	{
		if (!isset($options['flags'])) {
			$options['flags'] = [
				'HANDLEBARSJS',
				'ERROR_EXCEPTION'
			];
		}

		if (!isset($options['helpers'])) {
			$options['helpers'] = [];
		}

		if (!isset($options['partials'])) {
			$options['partials'] = [];
		}

		return $options;
	}

	private function compileFlags(array $flags)
	{
		$compiledFlags = 0;

		foreach ($flags as $flagName) {
			$flagConstant = sprintf('LightnCandy\LightnCandy::FLAG_%s', $flagName);

			$compiledFlags = $compiledFlags | constant($flagConstant);
		}

		return $compiledFlags;
	}

	private function compileHelpers(array $helperFunctions)
	{
		$helpers = [];

		foreach ($helperFunctions as $name => $functionOrClass) {
			if ($this->functionOrMethodExists($functionOrClass)) {
				$helpers[$name] = $functionOrClass;
			} else {
				throw new InvalidConfigurationException(
					sprintf('No function or method found matching configured Handlebars helper `%s`', $functionOrClass)
				);
			}
		}

		return $helpers;
	}

	private function compilePartials(array $partialFilePaths)
	{
		$partials = [];

		foreach ($partialFilePaths as $partialName => $filePath) {
			$fullFilePath = $this->options['partialsDirectory'] . DIRECTORY_SEPARATOR . $filePath;

			if (file_exists($fullFilePath)) {
				$partials[$partialName] = file_get_contents($fullFilePath);
			} else {
				throw new InvalidConfigurationException(
					sprintf(
						'No file found for configured Handlebars partial `%s`. Looking for path: `%s`.',
						$partialName,
						$fullFilePath
					)
				);
			}
		}

		return $partials;
	}

	private function functionOrMethodExists($name)
	{
		$exists = function_exists($name);

		if (!$exists && preg_match('::', $name)) {
			list($class, $method) = explode('::', $name);
			$exists = method_exists($class, $method);
		}

		return $exists;
	}
}
