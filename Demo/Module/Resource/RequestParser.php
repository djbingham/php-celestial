<?php
namespace SlothDemo\Module\Resource;

class RequestParser extends \Sloth\Module\Resource\Restful\RequestParser
{
    protected function getFactoryClass($resourceRoute)
    {
        return sprintf('SlothDemo\\Resource\\%sFactory', $resourceRoute);
    }

    protected function getManifestFile($resourceRoute)
    {
        $pathParts = array($this->app->rootDirectory(), 'resource', 'manifest', $resourceRoute);
        return sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $pathParts));
    }
}
