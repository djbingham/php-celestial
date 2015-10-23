<?php
namespace Sloth\Module\Render\Face;

interface RenderEngineInterface
{
	/**
	 * @param string $viewPath
	 * @param array $params
	 * @return string
	 */
	public function render($viewPath, array $params = array());
}
