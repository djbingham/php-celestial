<?php
namespace Sloth\Module\Graph;

use Sloth\App;
use Sloth\Module\Graph\Definition\View;

interface RendererInterface
{
	/**
	 * @param App $app
	 * @param array $engines
	 */
	public function __construct(App $app, array $engines);

	/**
	 * @param View $view
	 * @param array $params
	 * @return string
	 */
	public function render(View $view, array $params = array());
}
