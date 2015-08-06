<?php
namespace DemoGraph\Module\Graph\Renderer;

class Json
{
	public function render($viewPath, array $parameters = array())
	{
		$output = json_encode($parameters);
		return $output;
	}
}
