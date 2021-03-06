<?php
namespace Celestial\Api\Rest;

use Celestial\Module\Request\Face\RequestParserInterface;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Data\Resource as ResourceModule;
use Celestial\Module\Render as RenderModule;
use Celestial\Exception;

class RestfulRequestParser implements RequestParserInterface
{
	/**
	 * @var ResourceModule\ResourceModule
	 */
	protected $resourceModule;

	public function setResourceModule(ResourceModule\ResourceModule $resourceModule)
	{
		$this->resourceModule = $resourceModule;
		return $this;
	}

	public function parse(RoutedRequestInterface $request)
	{
		$resourceRoute = str_replace($request->getControllerPath(), '', $request->getPath());
		$resourcePath = trim($resourceRoute, '/');
		$extension = null;
		$extensionStartPos = strpos($resourcePath, '.');
		if ($extensionStartPos !== false) {
			$extension = substr($resourcePath, $extensionStartPos + 1);
			$resourcePath = substr($resourcePath, 0, $extensionStartPos);
		}
		$resourcePathParts = explode('/', $resourcePath);
 		$resourceId = null;

		if (empty($resourcePath)) {
			throw new Exception\InvalidRequestException(
				sprintf('No resource specified in request path: %s', $request->getPath())
			);
		}

		if (!$this->resourceModule->resourceExists($resourcePath)) {
			if (count($resourcePathParts) > 1) {
				$resourceId = array_pop($resourcePathParts);
			}
			$resourcePath = implode('/', $resourcePathParts);
			if (!$this->resourceModule->resourceExists($resourcePath)) {
				throw new Exception\InvalidRequestException(
					'No resource found matching path in request: ' . $resourcePath
				);
			}
		}

		$resourceFactory = $this->resourceModule->getResourceFactory($resourcePath);
		$resourceDefinition = $this->resourceModule->resourceDefinitionBuilder()->buildFromName($resourcePath);

		$requestProperties = $request->toArray();
		$requestProperties['originalRequest'] = $request;
		$requestProperties['params'] = $request->getParams()->toArray();
		$requestProperties['resourcePath'] = $resourcePath;
		$requestProperties['resourceId'] = $resourceId;
		$requestProperties['resourceFactory'] = $resourceFactory;
		$requestProperties['resourceDefinition'] = $resourceDefinition;
		$requestProperties['extension'] = $extension;

		return new RestfulParsedRequest($requestProperties);
	}
}
