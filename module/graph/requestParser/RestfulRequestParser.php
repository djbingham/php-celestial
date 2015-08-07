<?php
namespace Sloth\Module\Graph\RequestParser;

use Sloth\App;
use Sloth\Request;
use Sloth\Exception;

class RestfulRequestParser implements RequestParserInterface
{
	protected $app;

	/**
	 * @param string $resourceRoute
	 * @return string
	 */
	protected function getTableManifestFile($resourceRoute)
	{
        $pathParts = array($this->app->rootDirectory(), 'resource', 'graph', 'tableManifest', $resourceRoute);
        return sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $pathParts));
	}

    /**
     * @param string $resourceRoute
     * @return string
     */
    protected function getResourceManifestFile($resourceRoute)
    {
        $pathParts = array($this->app->rootDirectory(), 'resource', 'graph', 'tableManifest', $resourceRoute);
        return sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $pathParts));
    }

	/**
	 * @param string $resourceRoute
	 * @return string
	 */
	protected function getFactoryClass($resourceRoute)
    {
        return sprintf('SlothDemo\\Resource\\%sFactory', $resourceRoute);
    }

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function parse(Request $request, $controllerRoute)
	{
		$resourceRoute = str_replace($controllerRoute, '', $request->path());
		$resourcePath = trim($resourceRoute, '/');

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
		$requestProperties['viewName'] = $parsedResourcePath['viewName'];
		$requestProperties['resourceId'] = $parsedResourcePath['resourceId'];
		$requestProperties['unresolvedRoute'] = $parsedResourcePath['unresolvedRoute'];
		return $this->instantiateParsedRequest($requestProperties);
	}

	protected function parseResourcePath($resourcePath)
	{

		$resourcePathParts = explode('/', $resourcePath);
		$otherPathParts = array();

		foreach ($resourcePathParts as $i => $pathPart) {
			$resourcePathParts[$i] = ucfirst($pathPart);
		}

		$manifestPathParts = $resourcePathParts;
		$lastPathPartIndex = count($manifestPathParts) - 1;
		$lastPathPart = $manifestPathParts[$lastPathPartIndex];
		$extensionStartPos = strrpos($lastPathPart, '.');
		if ($extensionStartPos !== false) {
			$manifestPathParts[$lastPathPartIndex] = substr($lastPathPart, 0, $extensionStartPos);
		}

		$factoryClass = null;
		$manifestFile = null;
		for ($i = count($manifestPathParts); $i > 0; $i--) {
			$manifestFile = $this->getResourceManifestFile(implode(DIRECTORY_SEPARATOR, $manifestPathParts));
			if (file_exists($manifestFile)) {
				$factoryClass = null;
				break;
			} elseif (class_exists($factoryClass)) {
				$manifestFile = null;
				$factoryClass = $this->getFactoryClass(implode('\\', $manifestPathParts));
				break;
			}

			array_unshift($otherPathParts, array_pop($resourcePathParts));
			array_pop($manifestPathParts);
		}

		if (empty($otherPathParts)) {
			if ($extensionStartPos !== false) {
				$otherPathParts[] = substr($lastPathPart, $extensionStartPos);
			}
		}

		if (!is_a($factoryClass, 'Sloth\Module\Resource\ResourceFactory', true) && !empty($factoryClass)) {
			throw new Exception\InvalidRequestException(
				sprintf('Request routed to a class that is not a resource factory: `%s`', $factoryClass)
			);
		}

		$viewName = lcfirst(array_pop($otherPathParts));

		if (count($otherPathParts) > 0) {
			$resourceId = lcfirst(array_pop($otherPathParts));
		} else {
			$resourceId = null;
		}

		$unresolvedRoute = null;
		if (!empty($otherPathParts)) {
			$unresolvedRoute = implode('/', $otherPathParts);
		}

		return array(
			'manifestFile' => $manifestFile,
			'factoryClass' => $factoryClass,
			'resourceRoute' => implode('/', $resourcePathParts),
			'resourceId' => $resourceId,
			'viewName' => $viewName,
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
		return new RestfulParsedRequest($requestProperties);
	}
}
