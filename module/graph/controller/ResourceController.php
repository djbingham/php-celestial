<?php
namespace Sloth\Module\Graph\Controller;

use Sloth\Request;
use Sloth\Exception;
use Sloth\Controller\RestfulController;
use Sloth\Module\Resource\Base;
use SlothDemo\Module\Resource;
use Sloth\Module\Graph;

abstract class ResourceController extends RestfulController
{
	/**
	 * @var Graph\Factory
	 */
	private $resourceModule;

	abstract protected function getResourceManifestDirectory();
	abstract protected function getTableManifestDirectory();

	public function parseRequest(Request $request, $route)
	{
		$requestParser = new Graph\RequestParser\RestfulRequestParser($this->app, $this->getResourceModule());
		$parsedRequest = $requestParser->parse($request, $route);

		$function = $parsedRequest->getView()->getFunctionName();
		if (in_array($function, array('put', 'delete', 'index')) && $parsedRequest->method() === 'post') {
			$functionRegex = sprintf('/\/%s\//', $function);
			$requestProperties = $request->toArray();
			$requestProperties['method'] = $function;
			$requestProperties['uri'] = rtrim(preg_replace($functionRegex, '/', $request->uri()), '/');
			$requestProperties['path'] = rtrim(preg_replace($functionRegex, '/', $request->path()), '/');

			$parsedRequest = $this->parseRequest(Request::fromArray($requestProperties), $route);
		}
		return $parsedRequest;
	}

	protected function index()
	{
		$manifestDirectory = implode(DIRECTORY_SEPARATOR, array($this->app->rootDirectory(), 'resource', 'graph', 'resourceManifest'));
		$resources = $this->getResourceNames($manifestDirectory);
		$view = new Graph\Definition\View();
		$view->name = 'index';
		$view->path = 'default/index.php';
		$view->engine = 'php';
		$renderer = $this->getRenderer();
		return $renderer->render($view, array(
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

	protected function handleGet(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$renderer = $this->getRenderer();

		$requestParams = $request->params()->get();
		$resourceName = $request->getResourceRoute();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$resourceId = $request->getResourceId();
		$view = $request->getView();

		$viewName = $view->name;
		$function = $view->getFunctionName();
		$pathExtension = $view->getPathExtension();
		$nameExtension = $view->getNameExtension();

		switch ($function) {
			case 'definition':
				$output = $renderer->render($view, array(
					'resourceName' => $resourceName,
					'resourceDefinition' => $resourceDefinition
				));
				break;
			case 'create':
				$output = $renderer->render($view, array(
					'resourceName' => $resourceName,
					'resourceDefinition' => $resourceDefinition
				));
				break;
			case 'update':
				if (strlen($resourceId) === 0) {
					throw new Exception\InvalidRequestException(
						'Update form cannot be produced without specifying a resource'
					);
				}
				$primaryAttribute = $resourceDefinition->primaryAttribute;
				$filters = array(
					$primaryAttribute => $resourceId
				);
				$resource = $resourceFactory->getBy($resourceDefinition->attributes, $filters)->get(0);

				$output = $renderer->render($view, array(
					'resourceDefinition' => $resourceDefinition,
					'resource' => $resource
				));
				break;
			case 'filter':
				$output = $renderer->render($view, array(
					'resourceName' => $resourceName,
					'resourceDefinition' => $resourceDefinition
				));
				break;
			case 'search':
				if (array_key_exists('filters', $requestParams)) {
					$filters = $this->stripUnusedFilters($requestParams['filters']);
					$resourceList = $resourceFactory->search($resourceDefinition->attributes, $filters);
					$output = $renderer->render($view, array(
						'resourceName' => $resourceName,
						'resources' => $pathExtension === 'php' ? $resourceList : $resourceList->getAttributes()
					));
				} else {
					$output = $renderer->render($view, array(
						'resourceName' => $resourceName,
						'resourceDefinition' => $resourceDefinition
					));
				}
				break;
			case 'searchResult':
				if (array_key_exists('filters', $requestParams)) {
					$filters = $this->stripUnusedFilters($requestParams['filters']);
				} else {
					$filters = array();
				}
				$resourceList = $resourceFactory->search($resourceDefinition->attributes, $filters);
				$output = $renderer->render($view, array(
					'resourceName' => $resourceName,
					'resources' => $pathExtension === 'php' ? $resourceList : $resourceList->getAttributes()
				));
				break;
			default:
				$filters = $this->convertRequestParamsToSimpleSearchFilters($requestParams);
				if (isset($resourceId)) {
					$filters['id'] = $resourceId;
				}

				$resourceList = $resourceFactory->getBy($resourceDefinition->attributes, $filters);
				if (isset($resourceId) && $viewName === '') {
					$viewName = 'item';
					if (!empty($nameExtension)) {
						$viewName .= $nameExtension;
					}
					$view = $resourceDefinition->views->getByProperty('name', $viewName);
				}

				if ($view->name === 'item') {
					$output = $renderer->render($view, array(
						'resourceName' => $resourceName,
						'resource' => $pathExtension === 'php' ? $resourceList->get(0) : $resourceList->get(0)->getAttributes()
					));
				} else {
					$output = $renderer->render($view, array(
						'resourceName' => $resourceName,
						'resources' => $pathExtension === 'php' ? $resourceList : $resourceList->getAttributes()
					));
				}
				break;
		}

		return $output;
	}

	protected function handlePost(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$attributes = $request->params()->post();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$uriExtension = $request->getView()->getNameExtension();

		if (empty($attributes)) {
			throw new Exception\InvalidRequestException('POST request received with no parameters');
		}

		$resource = $resourceFactory->create($attributes);

		// todo: remove this if block - only used for testing
		if (empty($resource->getAttributes())) {
			$resource->setAttribute($resourceDefinition->primaryAttribute, 1);
		}

		$resourceId = $resource->getAttribute($resourceDefinition->primaryAttribute);

		$requestUri = preg_replace(sprintf('/\.%s$/', $uriExtension), '', trim($request->uri(), '/'));

		$redirectUrl = $this->app->createUrl(array($requestUri, $resourceId));
		if ($uriExtension !== null) {
			$redirectUrl .= '.' . $uriExtension;
		}

		$this->app->redirect($redirectUrl);
	}

	protected function handlePut(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$attributes = $request->params()->post();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$uriExtension = $request->getView()->getNameExtension();
		$primaryAttributeName = $resourceDefinition->primaryAttribute;

		if (empty($attributes)) {
			throw new Exception\InvalidRequestException('POST request received with no parameters');
		} elseif (!array_key_exists($primaryAttributeName, $attributes)) {
			throw new Exception\InvalidRequestException(
				sprintf('PUT request received with no value for primary attribute `%s`', $primaryAttributeName)
			);
		}

		$filters = array(
			$primaryAttributeName => $attributes[$primaryAttributeName]
		);

		$resource = $resourceFactory->update($filters, $attributes);
		$resourceId = $resource->getAttribute($primaryAttributeName);

		$requestUri = preg_replace(sprintf('/\.%s$/', $uriExtension), '', trim($request->uri(), '/'));

		$redirectUrl = $this->app->createUrl(array($requestUri, $resourceId));
		if ($uriExtension !== null) {
			$redirectUrl .= '.' . $uriExtension;
		}

		$this->app->redirect($redirectUrl);
	}

	protected function handleDelete(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$resourceModule = $this->getGraphFactory();
		$resourceFactory = $this->instantiateResourceFactory($request);
		$resourceId = $request->getUnresolvedRoute();
		$outputFormat = $request->getFormat();

		$resource = $this->getById($resourceFactory, $resourceId);
		$resource->delete();

		$output = $resourceModule->renderer()->renderDeletedResource($resourceFactory, $resource, $outputFormat);

		return $output;
	}

	protected function getResourceModule()
	{
		if (!isset($this->resourceModule)) {
			$tableManifestValidator = new Graph\TableManifestValidator();
			$resourceManifestValidator = new Graph\ResourceManifestValidator();
			$this->resourceModule = $this->initialiseResourceModule();
			$this->resourceModule->setResourceManifestDirectory($this->getResourceManifestDirectory())
				->setTableManifestDirectory($this->getTableManifestDirectory())
				->setResourceManifestValidator($resourceManifestValidator)
				->setTableManifestValidator($tableManifestValidator);
		}
		return $this->resourceModule;
	}

	/**
	 * @return Graph\Factory
	 */
	protected function initialiseResourceModule()
	{
		return $this->module('graph');
	}

	protected function convertRequestParamsToSimpleSearchFilters(array $requestParams)
	{
		$attributes = array();
		foreach ($requestParams as $name => $value) {
			if ((is_string($value) && strlen($value) > 0)) {
				$attributes[$name] = $value;
			} elseif ((is_array($value) && count($value) > 0)) {
				$subAttributes = $this->convertRequestParamsToSimpleSearchFilters($value);
				if (!empty($subAttributes)) {
					$attributes[$name] = $subAttributes;
				}
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
		return array_values($filters);
	}

	protected function getRenderer()
	{
		return new Graph\Renderer($this->app, array(
			'moustache' => new Graph\Renderer\Mustache(),
			'php' => new Graph\Renderer\Php(),
			'json' => new Graph\Renderer\Json()
		));
	}

	protected function createRedirectUri($originalUri, $originalResourceId, $updatedResourceId)
	{
		$redirectUri = trim($originalUri, '/');
		if (!is_null($originalResourceId)) {
			$redirectUri = preg_replace(sprintf('/\/%s$/', $originalResourceId), '', $redirectUri);
		}
		if (!is_null($updatedResourceId)) {
			$redirectUri .= '/' . $updatedResourceId;
		}
		return $redirectUri;
	}
}
