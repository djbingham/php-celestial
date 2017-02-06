<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Module\Render\Face\RenderEngineInterface;
use Sloth\Module\Render\Face\ViewInterface;

class Mustache implements RenderEngineInterface
{
	public function __construct(array $options)
	{

	}

	/**
	 * @var \Mustache_Engine
	 */
	private $mustache;

	public function render(ViewInterface $view, array $parameters = [])
	{
		if (!isset($this->mustache)) {
			$this->mustache = new \Mustache_Engine();
		}

		$template = $this->getTemplate($view->getPath());

		return $this->mustache->render($template, $parameters);
	}

	protected function getTemplate($viewPath)
	{
		return file_get_contents($viewPath);
	}
}
