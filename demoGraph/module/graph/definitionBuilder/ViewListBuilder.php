<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;

class ViewListBuilder
{
	public function build(ResourceDefinition\Resource $resource, array $manifestViews)
	{
		$views = new ResourceDefinition\ViewList();
		foreach ($manifestViews as $viewName => $filePath) {
			$view = new ResourceDefinition\View();
			$view->name = $viewName;
            $view->path = $filePath;
			$views->push($view);
		}
        return $views;
	}
}
