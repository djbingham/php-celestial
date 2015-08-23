<?php
namespace SlothDemo\Parser;

use Sloth\Module\Graph\RequestParser\RestfulRequestParser;

class GraphRequestParser extends RestfulRequestParser
{
	protected function getTableManifestFile($resourceRoute)
	{
		$pathParts = array($this->app->rootDirectory(), 'resource', 'graph', 'tableManifest', $resourceRoute);
		return sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $pathParts));
	}

	protected function getResourceManifestFile($resourceRoute)
	{
		$pathParts = array($this->app->rootDirectory(), 'resource', 'graph', 'resourceManifest', $resourceRoute);
		return sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $pathParts));
	}

	protected function getResourceFactoryClass($resourceRoute)
	{
		return sprintf('SlothDemo\\Resource\\%sFactory', $resourceRoute);
	}
}
