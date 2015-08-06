<?php
namespace DemoGraph\Module\Graph\Renderer;

class Mustache
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
