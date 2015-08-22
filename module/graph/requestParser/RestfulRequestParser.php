<?php
namespace Sloth\Module\Graph\RequestParser;

use Sloth\App;
use Sloth\Module\Graph;
use Sloth\Request;
use Sloth\Exception;

class RestfulRequestParser implements RequestParserInterface
{
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Graph\Factory
	 */
	protected $module;

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
        $pathParts = array($this->app->rootDirectory(), 'resource', 'graph', 'resourceManifest', $resourceRoute);
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

	public function __construct(App $app, Graph\Factory $module)
	{
		$this->app = $app;
		$this->module = $module;
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
		$parsedResourcePath = $this->parseResourcePathToResourceLocation($resourcePath);

		$requestProperties = $request->toArray();
		$requestProperties['originalRequest'] = $request;
		$requestProperties['params'] = new Request\Params($requestProperties['params']);

		if ($parsedResourcePath['manifestFile'] !== null) {
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

		$furtherRequestProperties = $this->parseRequestProperties($requestProperties, $requestProperties['manifest']);
		$requestProperties['viewName'] = $furtherRequestProperties['viewName'];
		$requestProperties['resourceId'] = $furtherRequestProperties['resourceId'];
		$requestProperties['unresolvedRoute'] = $furtherRequestProperties['unresolvedRoute'];

		$resourceDefinitionFactory = $this->module->resourceDefinitionBuilder();
		$resourceDefinition = $resourceDefinitionFactory->buildFromManifest($requestProperties['manifest']);
		$requestProperties['resourceDefinition'] = $resourceDefinition;
		$requestProperties['resourceFactory'] = $this->module->resourceFactory($resourceDefinition->table);
		$requestProperties['view'] = $resourceDefinition->views->getByProperty('name', $requestProperties['viewName']);

		return $this->instantiateParsedRequest($requestProperties);
	}

	protected function parseResourcePathToResourceLocation($resourcePath)
	{
		$resourcePathParts = explode('/', $resourcePath);
		$otherPathParts = array();

		$manifestPathParts = array();
		$extension = null;
		foreach ($resourcePathParts as $index => $pathPart) {
			$extensionStartPos = strrpos($pathPart, '.');
			if ($extensionStartPos !== false && $extension === null) {
				$manifestPathParts[$index] = ucfirst(substr($pathPart, 0, $extensionStartPos));
				$extension = substr($pathPart, $extensionStartPos);
			} else {
				$manifestPathParts[$index] = $pathPart;
			}
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
			} else {
				$manifestFile = null;
				$factoryClass = null;
			}

			array_unshift($otherPathParts, array_pop($resourcePathParts));
			array_pop($manifestPathParts);
		}

		if (empty($otherPathParts) && !empty($extension)) {
			array_push($otherPathParts, $extension);
		}

		if (!is_a($factoryClass, 'Sloth\Module\Resource\ResourceFactory', true) && !empty($factoryClass)) {
			throw new Exception\InvalidRequestException(
				sprintf('Request routed to a class that is not a resource factory: `%s`', $factoryClass)
			);
		}

		return array(
			'manifestFile' => $manifestFile,
			'factoryClass' => $factoryClass,
			'resourceRoute' => implode('/', $manifestPathParts),
			'unresolvedRoute' => trim(implode('/', $otherPathParts), '/')
		);
	}

	protected function parseRequestProperties(array $requestProperties, array $manifest)
	{
		$unresolvedRoute = $requestProperties['unresolvedRoute'];
		$resourceId = null;
		$viewName = '';

		if (!empty($unresolvedRoute)) {
			$pathParts = explode('/', $unresolvedRoute);
			$resourceId = array_shift($pathParts);

			if (array_key_exists(lcfirst($resourceId), $manifest['views'])) {
				$viewName = lcfirst($resourceId);
				if (!empty($pathParts)) {
					$resourceId = array_shift($pathParts);
				} else {
					$resourceId = null;
				}
			} elseif (!empty($pathParts)) {
				$viewName = lcfirst(array_shift($pathParts));
			}
		}

		$extensionStartPos = strpos($resourceId, '.');
		if ($extensionStartPos !== false) {
			$extension = substr($resourceId, $extensionStartPos);
			$resourceId = substr($resourceId, 0, $extensionStartPos);
			$viewName = $viewName . $extension;
		}

		$unresolvedRoute = null;
		if (!empty($pathParts)) {
			$unresolvedRoute = implode('/', $pathParts);
		}

		return array(
			'viewName' => $viewName,
			'resourceId' => $resourceId,
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
			if ($manifest === null) {
				throw new Exception\InvalidArgumentException(
					sprintf('Error decoding manifest file (probably invalid JSON). File path: %s', $filePath)
				);
			}
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
