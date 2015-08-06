<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\Definition;

class ViewListBuilder
{
	public function build(array $manifestViews)
	{
		$views = new Definition\ViewList();
		foreach ($manifestViews as $viewName => $viewManifest) {
			$view = new Definition\View();
			$view->name = $viewName;
			$view->path = $viewManifest['path'];
			$view->engine = $viewManifest['engine'];
			$views->push($view);
		}
        return $views;
	}
}
