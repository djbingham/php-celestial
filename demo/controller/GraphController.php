<?php
namespace Sloth\Demo\Controller;

use Sloth\Module\Graph;

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
}
