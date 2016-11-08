<?php
namespace Sloth\Module\Render\Engine;

use Sloth\Module\Render\Face\RenderEngineInterface;

class Mustache implements RenderEngineInterface
{
	/**
	 * @var \Mustache_Engine
	 */
	private $mustache;

	public function render($viewPath, array $parameters = array())
	{
		if (!isset($this->mustache)) {
			$this->mustache = new \Mustache_Engine();
		}

		$template = $this->getTemplate($viewPath);

		return $this->mustache->render($template, $parameters);
	}

	protected function getTemplate($viewPath)
	{
		return file_get_contents($viewPath);
	}
}
