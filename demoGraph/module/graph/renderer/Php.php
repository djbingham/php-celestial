<?php
namespace DemoGraph\Module\Graph\Renderer;

class Php
{
	public function render($viewPath, array $parameters = array())
	{
		ob_start();
		extract($parameters);
		require $viewPath;
		$output = ob_get_contents();
		ob_clean();
		return $output;
	}
}
