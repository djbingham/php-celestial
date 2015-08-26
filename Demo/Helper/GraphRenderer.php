<?php
namespace SlothDemo\Helper;

use Sloth\Module\Render\View;
use Sloth\Module\Render\Renderer;

class GraphRenderer extends Renderer
{
	protected function getAbsoluteViewPath(View $view)
	{
		$viewPath = str_replace('/', DIRECTORY_SEPARATOR, $view->path);
		$viewPathParts = array($this->app->rootDirectory(), 'view', 'resource', $viewPath);
		return implode(DIRECTORY_SEPARATOR, $viewPathParts);
	}
}
