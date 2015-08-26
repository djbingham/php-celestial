<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Module\Render\Face\RenderEngineInterface;

class Php implements RenderEngineInterface
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
