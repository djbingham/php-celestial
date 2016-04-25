<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Module\Render\Face\RenderEngineInterface;
use LightnCandy\LightnCandy as LightNCandyEngine;

class LightNCandy implements RenderEngineInterface
{
	public function render($viewPath, array $parameters = array())
	{
		$template = $this->getTemplate($viewPath);
		$options = $this->getCompilerOptions();

		$php = LightNCandyEngine::compile($template, $options);

		$renderer = eval($php);

		return $renderer($parameters);
	}

	protected function getTemplate($viewPath)
	{
		ob_start();
		require $viewPath;
		$template = ob_get_contents();
		ob_clean();

		return $template;
	}

	protected function getCompilerOptions()
	{
		return array(
			'flags' => $this->getFlags(),
			'helpers' => $this->getHelpers()
		);
	}

	protected function getFlags()
	{
		return LightNCandyEngine::FLAG_PARENT;
	}

	protected function getHelpers()
	{
		return array(
			'isEqual' => function($a, $b, $positiveOutput = true, $negativeOutput = false) {
				return ($a === $b) ? $positiveOutput : $negativeOutput;
			}
		);
	}
}
