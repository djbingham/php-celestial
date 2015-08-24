<?php
namespace Sloth\Demo\Controller;

use Sloth\Module\Graph;
use SlothDemo\Helper\GraphRenderer;
use SlothDemo\Helper\GraphRequestParser;

class GraphController extends Graph\Controller\ResourceController
{
	protected function getResourceManifestDirectory()
	{
		$directoryParts = array(dirname(dirname(__DIR__)), 'demo', 'resource', 'graph', 'resourceManifest');
		return implode(DIRECTORY_SEPARATOR, $directoryParts);
	}

	protected function getTableManifestDirectory()
	{
		$directoryParts = array(dirname(dirname(__DIR__)), 'demo', 'resource', 'graph', 'tableManifest');
		return implode(DIRECTORY_SEPARATOR, $directoryParts);
	}

	protected function getRequestParser()
	{
		return new GraphRequestParser($this->app, $this->getResourceModule());
	}

	protected function getRenderer()
	{
		return new GraphRenderer($this->app, array(
			'mustache' => new Graph\Renderer\Mustache(),
			'php' => new Graph\Renderer\Php(),
			'json' => new Graph\Renderer\Json()
		));
	}
}
