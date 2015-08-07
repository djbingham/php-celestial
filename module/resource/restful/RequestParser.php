<?php
namespace Sloth\Module\Graph\Restful;

use Sloth\App;
use Sloth\Request;
use Sloth\Exception;
//use Sloth\Module\Resource\Base;

abstract class RequestParser implements Base\RequestParser
{
	protected $app;

    /**
     * @param string $resourceRoute
     * @return string
     */
	abstract protected function getManifestFile($resourceRoute);

    /**
     * @param string $resourceRoute
     * @return string
     */
    abstract protected function getFactoryClass($resourceRoute);

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function parse(Request $request, $controllerRoute)
    {
        $resourceRoute = str_replace($controllerRoute, '', $request->path());
        $pathParts = explode('.', $resourceRoute);
        $resourcePath = trim(array_shift($pathParts), '/');
        $extension = array_pop($pathParts);

        if (!empty($pathParts)) {
            throw new Exception\InvalidRequestException(
                sprintf('Too many period (.) characters in the request path: `%s`', $request->path())
            );
        }
        if (empty($resourcePath)) {
            throw new Exception\InvalidRequestException(
                sprintf('No resource specified in request path: %s', $request->path())
            );
        }
        $parsedResourcePath = $this->parseResourcePath($resourcePath);


        $requestProperties = $request->toArray();
        if (!is_null($parsedResourcePath['manifestFile'])) {
            $requestProperties['manifest'] = $this->parseManifestFile($parsedResourcePath['manifestFile']);
            if (array_key_exists('factoryClass', $requestProperties['manifest'])) {
                $requestProperties['factoryClass'] = $requestProperties['manifest']['factoryClass'];
            } else {
                $requestProperties['factoryClass'] = $this->getDefaultFactoryClass();
            }
        } else {
            $requestProperties['factoryClass'] = $parsedResourcePath['factoryClass'];
        }
        $requestProperties['resourceRoute'] = $parsedResourcePath['resourceRoute'];
        $requestProperties['unresolvedRoute'] = $parsedResourcePath['unresolvedRoute'];
        $requestProperties['format'] = $extension;
        return $this->instantiateParsedRequest($requestProperties);
    }

    protected function parseResourcePath($resourcePath)
    {
        $resourcePathParts = explode('/', $resourcePath);
        $otherPathParts = array();

        foreach ($resourcePathParts as $i => $pathPart) {
            $resourcePathParts[$i] = ucfirst($pathPart);
        }

        $factoryClass = null;
        $manifestFile = null;
        for ($i = count($resourcePathParts); $i > 0; $i--) {
            $manifestFile = $this->getManifestFile(implode(DIRECTORY_SEPARATOR, $resourcePathParts));
            if (file_exists($manifestFile)) {
                $factoryClass = null;
                break;
            } elseif (class_exists($factoryClass)) {
                $manifestFile = null;
                $factoryClass = $this->getFactoryClass(implode('\\', $resourcePathParts));
                break;
            }

            array_unshift($otherPathParts, array_pop($resourcePathParts));
        }

        if (!is_a($factoryClass, 'Sloth\Module\Resource\ResourceFactory', true) && !empty($factoryClass)) {
            throw new Exception\InvalidRequestException(
                sprintf('Request routed to a class that is not a resource factory: `%s`', $factoryClass)
            );
        }

        $unresolvedRoute = null;
        if (!empty($otherPathParts)) {
            $unresolvedRoute = implode('/', $otherPathParts);
        }

        return array(
            'manifestFile' => $manifestFile,
            'factoryClass' => $factoryClass,
            'resourceRoute' => implode('/', $resourcePathParts),
            'unresolvedRoute' => trim($unresolvedRoute, '/')
        );
    }

    /**
     * @param string $filePath
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    protected function parseManifestFile($filePath)
    {
        if (is_null($filePath)) {
            $manifest = array();
        } elseif (is_file($filePath)) {
            $manifest = json_decode(file_get_contents($filePath), true);
        } else {
            throw new Exception\InvalidArgumentException(
                sprintf('Manifest file not found: %s', $filePath)
            );
        }
        return $manifest;
    }

    protected function getDefaultFactoryClass()
    {
        return 'Sloth\\Module\\Resource\\ResourceFactory';
    }

    protected function instantiateParsedRequest($requestProperties)
    {
        return new ParsedRequest($requestProperties);
    }
}
