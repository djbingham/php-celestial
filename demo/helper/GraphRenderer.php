<?php
namespace SlothDemo\Helper;

use Sloth\Module\Graph\Definition\View;
use Sloth\Module\Graph\Renderer;

class GraphRenderer extends Renderer
{
	protected function getAbsoluteViewPath(View $view)
	{
		$viewPath = str_replace('/', DIRECTORY_SEPARATOR, $view->path);
		$viewPathParts = array($this->app->rootDirectory(), 'view', 'resource', $viewPath);
		return implode(DIRECTORY_SEPARATOR, $viewPathParts);
	}
}
