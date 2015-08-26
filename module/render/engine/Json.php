<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Module\Render\Face\RenderEngineInterface;

class Json implements RenderEngineInterface
{
	public function render($viewPath, array $parameters = array())
	{
		$output = json_encode($parameters);
		return $output;
	}
}
