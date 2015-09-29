<?php
namespace SlothDemo\Helper;

use Sloth\Module\Resource\RequestParser\RestfulRequestParser;

class ResourceRequestParser extends RestfulRequestParser
{
	protected function getTableManifestFile($resourceRoute)
	{
		$pathParts = array($this->app->rootDirectory(), 'resource', 'tableManifest', $resourceRoute);
		return sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $pathParts));
	}

	protected function getResourceManifestFile($resourceRoute)
	{
		$pathParts = array($this->app->rootDirectory(), 'resource', 'resourceManifest', $resourceRoute);
		return sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $pathParts));
	}

	protected function getResourceFactoryClass($resourceRoute)
	{
		return sprintf('SlothDemo\\Resource\\%sFactory', $resourceRoute);
	}
}
