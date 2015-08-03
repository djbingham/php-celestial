<?php
namespace Sloth\Demo\Controller;

use Sloth\Module\Resource\AttributeMapper;
use Sloth\Request;
use Sloth\Exception;
use Sloth\Controller\RestfulController;
use Sloth\Module\Resource\QuerySetFactory;
use Sloth\Module\Resource\QueryFactory;
use Sloth\Module\Resource\Base;
use Sloth\Module\Resource\ResourceDefinition as DefaultDefinition;
use Sloth\Module\Resource\ResourceFactory as DefaultFactory;
use SlothDemo\Module\Resource;
use DemoGraph\Module\Graph;

class GraphController extends RestfulController
{
	public function execute(Request $request, $route)
	{
		$requestPath = $request->path();
		$lastPathPartPos = strrpos($requestPath, '/') + 1;
		$function = strtolower(substr($requestPath, $lastPathPartPos));
		if (empty($function)) {
			$function = 'index';
		}
		if (in_array($function, array('getChild', 'post', 'put', 'delete', 'index'))) {
			$requestProperties = $request->toArray();
			$requestProperties['method'] = $function;
			$requestProperties['uri'] = substr($request->uri(), 0, $lastPathPartPos);
			$requestProperties['path'] = substr($request->path(), 0, $lastPathPartPos);
			$request = Request::fromArray($requestProperties);
		}
		return parent::execute($request, $route);
	}

	protected function index()
	{
		$manifestDirectory = implode(DIRECTORY_SEPARATOR, array($this->app->rootDirectory(), 'resource', 'manifest'));
		$resources = $this->getResourceNames($manifestDirectory);
		return $this->render('resource/index.html', array(
			'resources' => $resources
		));
	}

	protected function getResourceNames($directory)
	{
		$directoryContents = scandir($directory);
		$resourceNames = array();
		foreach ($directoryContents as $fileName) {
			if (!in_array($fileName, array('.', '..')) && preg_match('/.json$/', $fileName)) {
				$resourceNames[] = basename($fileName, '.json');
			}
		}
		return $resourceNames;
	}

	protected function get(Request $request, $route)
	{
		$resourceModule = $this->getGraphFactory();
		$requestParser = new Graph\RequestParser\RestfulRequestParser($this->app);
		$parsedRequest = $requestParser->parse($request, $route);
		$unresolvedRoute = $parsedRequest->getUnresolvedRoute();
		$outputFormat = $parsedRequest->getFormat();
		$requestParams = $request->params()->get();

		if (preg_match('/\//', $unresolvedRoute)) {
			list($resourceId, $function) = explode('/', $unresolvedRoute);
			$function = lcfirst($function);
		} else {
			$function = lcfirst($unresolvedRoute);
			$resourceId = $unresolvedRoute;
		}

		switch ($function) {
//			case 'create':
//				$output = $resourceModule->renderer()->renderCreateForm($resourceFactory, $outputFormat);
//				break;
//			case 'update':
//				if (strlen($resourceId) === 0) {
//					throw new Exception\InvalidRequestException(
//						'Update form cannot be produced for more than one resource'
//					);
//				}
//				$resource = $this->getById($resourceFactory, $resourceId);
//				$output = $resourceModule->renderer()->renderUpdateForm($resourceFactory, $resource, $outputFormat);
//				break;
//			case 'simpleSearch':
//				if (!empty($requestParams)) {
//					$attributes = $this->convertRequestParamsToSimpleSearchFilters($requestParams);
//					$resourceList = $resourceFactory->getBy($attributes);
//					$output = $resourceModule->renderer()->renderResourceList($resourceFactory, $resourceList, $outputFormat);
//				} else {
//					$output = $resourceModule->renderer()->renderSimpleSearchForm($resourceFactory, $outputFormat);
//				}
//				break;
//			case 'search':
//				if (array_key_exists('filters', $requestParams)) {
//					$filters = $this->stripUnusedFilters($requestParams['filters']);
//					$resourceList = $resourceFactory->search($filters);
//					$output = $resourceModule->renderer()->renderResourceList($resourceFactory, $resourceList, $outputFormat);
//				} else {
//					$output = $resourceModule->renderer()->renderSearchForm($resourceFactory, $outputFormat);
//				}
//				break;
//			case 'definition':
//				$output = $resourceModule->renderer()->renderDefinition($resourceFactory, $outputFormat);
//				break;
			default:
				$resourceName = $parsedRequest->getResourceRoute();
				$manifestValidator = new Graph\ResourceManifestValidator();
				$tableManifestDirectory = dirname(dirname(__DIR__)) . '/demo/resource/graph/tableManifest';
				$resourceDefinitionBuilder = $resourceModule->resourceDefinitionBuilder($manifestValidator, $tableManifestDirectory);
				$resourceDefinition = $resourceDefinitionBuilder->buildFromName($resourceName);
				$resourceFactory = $resourceModule->resourceFactory($resourceDefinition);

				$resourceManifest = $this->getResourceManifest($resourceName);
				if (strlen($resourceId) > 0) {
					$filters = array(
						'id' => $resourceId
					);
				} else {
					$filters = $this->convertRequestParamsToSimpleSearchFilters($requestParams);
				}
				$resourceList = $resourceFactory->getBy($resourceManifest['attributes'], $filters);
				$renderer = new Graph\Renderer($this->app);
				$renderer->setResourceManifest($resourceManifest);
				$output = $renderer->renderResourceList($resourceFactory, $resourceList);
				var_dump($output);
				break;
		}

		return $output;
	}

	protected function getResourceManifest($resourceRoute)
	{
		$manifestDirectory = dirname(dirname(__DIR__)) . '/demo/resource/graph/resourceManifest';
		$manifestFile = $manifestDirectory . DIRECTORY_SEPARATOR . $resourceRoute . '.json';
		$manifest = json_decode(file_get_contents($manifestFile), true);
		return $manifest;
	}

	protected function convertRequestParamsToSimpleSearchFilters(array $requestParams)
	{
		$attributes = array();
		foreach ($requestParams as $name => $value) {
			if ((is_string($value) && strlen($value) > 0) || (is_array($value) && count($value) > 0)) {
				$attributes[$name] = $value;
			}
		}
		return $attributes;
	}

	protected function stripUnusedFilters(array $filters)
	{
		foreach ($filters as $index => $filter) {
			if ($filter['comparator'] === '') {
				unset($filters[$index]);
			}
		}
		return $filters;
	}

	protected function post(Request $request, $route)
	{
		$resourceModule = $this->getGraphFactory();
		$parsedRequest = $resourceModule->parser()->parse($request, $route);
		$resourceFactory = $this->instantiateResourceFactory($parsedRequest);
		$outputFormat = $parsedRequest->getFormat();
		$attributes = $request->params()->post();

		if (empty($attributes)) {
			throw new Exception\InvalidRequestException('POST request received with no parameters');
		}

		$resource = $resourceFactory->create($attributes);

		$primaryAttribute = $resourceFactory->getDefinition()->primaryAttribute();
		$resourceId = $resource->getAttribute($primaryAttribute);
		$redirectUri = $this->createRedirectUri($request->uri(), null, $resourceId, $outputFormat);

		echo "<hr>";
		var_dump($redirectUri);
		exit;

		$this->app->redirectUrl($this->app->createUrl(array($redirectUri)));
	}

	protected function put(Request $request, $route)
	{
		$resourceModule = $this->getGraphFactory();
		$parsedRequest = $resourceModule->parser()->parse($request, $route);
		$resourceFactory = $this->instantiateResourceFactory($parsedRequest);
		$resourceId = $parsedRequest->getUnresolvedRoute();
		$outputFormat = $parsedRequest->getFormat();
		$attributes = $request->params()->post();

		if (empty($attributes)) {
			throw new Exception\InvalidRequestException('PUT request received with no parameters');
		}

		$resource = $this->getById($resourceFactory, $resourceId);
		$resource->setAttributes($attributes);
		$updatedResource = $resourceFactory->update($resource);

		$primaryAttribute = $resourceFactory->getDefinition()->primaryAttribute();
		$updatedResourceId = $updatedResource->getAttribute($primaryAttribute);
		$redirectUri = $this->createRedirectUri($request->uri(), $resourceId, $updatedResourceId, $outputFormat);

		$this->app->redirectUrl($this->app->createUrl(array($redirectUri)));
	}

	protected function createRedirectUri($originalUri, $originalResourceId, $updatedResourceId, $outputFormat)
	{
		$redirectUri = trim($originalUri, '/');
		$redirectUri = preg_replace(sprintf('/\/%s$/', $outputFormat), '', $redirectUri);
		if (!is_null($originalResourceId)) {
			$redirectUri = preg_replace(sprintf('/\/%s$/', $originalResourceId), '', $redirectUri);
		}
		if (!is_null($updatedResourceId)) {
			$redirectUri .= '/' . $updatedResourceId;
		}
		if (!is_null($outputFormat)) {
			$redirectUri .= '.' . $outputFormat;
		}
		return $redirectUri;
	}

	protected function delete(Request $request, $route)
	{
		$resourceModule = $this->getGraphFactory();
		$parsedRequest = $resourceModule->parser()->parse($request, $route);
		$resourceFactory = $this->instantiateResourceFactory($parsedRequest);
		$resourceId = $parsedRequest->getUnresolvedRoute();
		$outputFormat = $parsedRequest->getFormat();

		$resource = $this->getById($resourceFactory, $resourceId);
		$resource->delete();

		$output = $resourceModule->renderer()->renderDeletedResource($resourceFactory, $resource, $outputFormat);

		return $output;
	}

	/**
	 * @return Graph\Factory
	 */
	protected function getGraphFactory()
	{
		return $this->module('graph');
	}

	/**
	 * @param Graph\RequestParser\RestfulParsedRequest $parsedRequest
	 * @return DefaultFactory
	 */
	protected function instantiateResourceFactory(Graph\RequestParser\RestfulParsedRequest $parsedRequest)
	{
		$definition = new DefaultDefinition($parsedRequest->getManifest());
		$factoryClass = $parsedRequest->getFactoryClass();
		if (is_null($factoryClass)) {
			$factoryClass = $definition->factoryClass();
		}
		$queryFactory = new QueryFactory($this->app->database());
		$attributeMapper = new AttributeMapper($definition);
		$querySetFactory = new QuerySetFactory($queryFactory, $attributeMapper);
		return new $factoryClass($definition, $querySetFactory);
	}

	protected function getById(Base\ResourceFactory $resourceFactory, $resourceId)
	{
		if (strlen($resourceId) === 0) {
			throw new Exception\InvalidRequestException('Cannot find resource with empty ID');
		}

		$attributes = array(
			$resourceFactory->getDefinition()->primaryAttribute() => str_replace('_', ' ', $resourceId)
		);

		$resourceList = $resourceFactory->getBy($attributes);
		if ($resourceList->count() !== 1) {
			throw new Exception\NotFoundException(
				sprintf(
					'Request to getChild by resource ID did not lead to exactly one resource. Resources found: %s',
					$resourceList->count()
				)
			);
		}
		return $resourceList->get(0);
	}
}
