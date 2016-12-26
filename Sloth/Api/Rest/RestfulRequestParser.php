<?php
namespace Sloth\Api\Rest;

use Sloth\Module\Request\Face\RequestParserInterface;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Data\Resource as ResourceModule;
use Sloth\Module\Render as RenderModule;
use Sloth\Exception;

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

		$resourceName = implode('/', $resourcePathParts);
		$resourceFactory = $this->resourceModule->getResourceFactory($resourceName);
		$resourceDefinition = $this->resourceModule->resourceDefinitionBuilder()->buildFromName($resourceName);

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
