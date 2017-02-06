<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Module\Render\Face\RenderEngineInterface;
use Sloth\Module\Render\Face\ViewInterface;

class Php implements RenderEngineInterface
{
	public function __construct(array $options)
	{

	}

	public function render(ViewInterface $view, array $parameters = [])
	{
		ob_start();

		extract($parameters);
		require $view->getPath();

		$output = ob_get_contents();
		ob_clean();

		return $output;
	}
}
