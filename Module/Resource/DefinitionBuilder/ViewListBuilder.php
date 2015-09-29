<?php
namespace Sloth\Module\Resource\DefinitionBuilder;

use Sloth\Module\Resource\Definition;

class ViewListBuilder
{
	public function build(array $manifestViews)
	{
		$views = new \Sloth\Module\Render\ViewList();
		foreach ($manifestViews as $viewName => $viewManifest) {
			$view = new \Sloth\Module\Render\View();
			$view->name = $viewName;
			$view->path = $viewManifest['path'];
			$view->engine = $viewManifest['engine'];
			$views->push($view);
		}
        return $views;
	}
}
