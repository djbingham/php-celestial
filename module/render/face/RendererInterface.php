<?php
namespace Sloth\Module\Render\Face;

use Sloth\App;

interface RendererInterface
{
	/**
	 * @param App $app
	 * @param array $engines
	 * @param string $viewDirectory
	 */
	public function __construct(App $app, array $engines, $viewDirectory);

	/**
	 * @param ViewInterface $view
	 * @param array $params
	 * @return string
	 */
	public function render(ViewInterface $view, array $params = array());
}
