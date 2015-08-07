<?php
namespace Sloth\Demo\Controller;

use Sloth\Module\Graph;

class GraphController extends Graph\Controller\ResourceController
{
	protected function getResourceManifestDirectory()
	{
		return dirname(dirname(__DIR__)) . '/demo/resource/graph/resourceManifest';
	}

	protected function getTableManifestDirectory()
	{
		return dirname(dirname(__DIR__)) . '/demo/resource/graph/tableManifest';
	}
}
