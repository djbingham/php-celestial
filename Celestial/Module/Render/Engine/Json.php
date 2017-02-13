<?php
namespace Celestial\Module\Render\Engine;

use Celestial\Module\Render\Face\RenderEngineInterface;
use Celestial\Module\Render\Face\ViewInterface;

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
