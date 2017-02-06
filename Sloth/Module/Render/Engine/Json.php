<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Module\Render\Face\RenderEngineInterface;
use Sloth\Module\Render\Face\ViewInterface;

class Json implements RenderEngineInterface
{
	public function __construct(array $options)
	{

	}

	public function render(ViewInterface $view, array $parameters = [])
	{
		$output = json_encode($parameters['data']);
		return $output;
	}
}
