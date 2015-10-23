<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Module\Render\Face\RenderEngineInterface;

class Mustache implements RenderEngineInterface
{
	public function render($viewPath, array $parameters = array())
	{
		ob_start();
		extract($parameters);
		require $viewPath;
		$output = ob_get_contents();
		ob_clean();
		$mustache = new \Mustache_Engine();
		$output = $mustache->render($output, $parameters);
		return $output;
	}
}
