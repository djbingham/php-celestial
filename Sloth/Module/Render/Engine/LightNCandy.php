<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Exception\InvalidConfigurationException;
use Sloth\Module\Render\Face\RenderEngineInterface;
use LightnCandy\LightnCandy as LightNCandyEngine;
use Sloth\Module\Render\Face\ViewInterface;

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

		return $options;
	}

	private function validateOptions(array $options)
	{
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
