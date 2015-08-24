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

	/**
	 * @return string
	 */
	abstract protected function getResourceManifestDirectory();

	/**
	 * @return string
	 */
	abstract protected function getTableManifestDirectory();

	/**
	 * @return Graph\RequestParser\RestfulRequestParser
	 */
	abstract protected function getRequestParser();

	/**
	 * @return Graph\RendererInterface
	 */
	abstract protected function getRenderer();

	public function parseRequest(Request $request, $route, $quit = false)
	{
		$requestParser = $this->getRequestParser();
		$parsedRequest = $requestParser->parse($request, $route);
		if ($parsedRequest->getUnresolvedRoute() === 'index') {
			$parsedRequest->setMethod('index');
		} else {
			$function = $parsedRequest->getView()->getFunctionName();
			if (in_array($function, array('put', 'delete')) && $parsedRequest->getMethod() === 'post') {
				$parsedRequest->setMethod($function);
			}
		}
		return $parsedRequest;
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

		$requestParams = $request->getParams()->get();
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
				$output = $this->handleGetDefinition($request, $route);
				break;
			case 'create':
				$output = $this->handleGetCreate($request, $route);
				break;
			case 'update':
				$output = $this->handleGetUpdate($request, $route);
				break;
			case 'filter':
				$output = $this->handleGetFilter($request, $route);
				break;
			case 'search':
				$output = $this->handleGetSearchForm($request, $route);
				break;
			case 'searchResult':
				$output = $this->handleGetSearchResult($request, $route);
				break;
			default:
				$filters = $this->convertRequestParamsToSimpleSearchFilters($requestParams);
				if (isset($resourceId)) {
					$filters[$resourceDefinition->primaryAttribute] = $resourceId;
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
						'resourceDefinition' => $resourceDefinition,
						'resource' => $pathExtension === 'php' ? $resourceList->get(0) : $resourceList->get(0)->getAttributes()
					));
				} else {
					$output = $renderer->render($view, array(
						'resourceName' => $resourceName,
						'resourceDefinition' => $resourceDefinition,
						'resources' => $pathExtension === 'php' ? $resourceList : $resourceList->getAttributes()
					));
				}
				break;
		}

		return $output;
	}

	protected function handleIndex(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$resources = $this->getResourceNames($this->getResourceManifestDirectory());
		$view = new Graph\Definition\View();
		$view->name = 'index';
		$view->path = 'default/index.php';
		$view->engine = 'php';
		$renderer = $this->getRenderer();
		return $renderer->render($view, array(
			'resources' => $resources
		));
	}

	protected function handleGetDefinition(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		return $this->getRenderer()->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetCreate(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		return $this->getRenderer()->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetUpdate(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$resourceDefinition = $request->getResourceDefinition();
		$resourceId = $request->getResourceId();
		$primaryAttribute = $resourceDefinition->primaryAttribute;

		if (strlen($resourceId) === 0) {
			throw new Exception\InvalidRequestException(
				'Update form cannot be produced without specifying a resource'
			);
		}

		$filters = array(
			$primaryAttribute => $resourceId
		);
		$resource = $request->getResourceFactory()->getBy($resourceDefinition->attributes, $filters)->get(0);

		return $this->getRenderer()->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition(),
			'resource' => $resource
		));
	}

	protected function handleGetFilter(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		return $this->getRenderer()->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetSearchForm(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$resourceDefinition = $request->getResourceDefinition();
		$requestParams = $request->getParams()->get();
		$extension = $request->getView()->getPathExtension();
		if (array_key_exists('filters', $requestParams)) {
			$filters = $this->stripUnusedSearchFilters($requestParams['filters']);
			$resourceList = $request->getResourceFactory()->search($resourceDefinition->attributes, $filters);
			$output = $this->getRenderer()->render($request->getView(), array(
				'resourceName' => $request->getResourceRoute(),
				'resourceDefinition' => $resourceDefinition,
				'resources' => $extension === 'php' ? $resourceList : $resourceList->getAttributes()
			));
		} else {
			$output = $this->getRenderer()->render($request->getView(), array(
				'resourceName' => $request->getResourceRoute(),
				'resourceDefinition' => $resourceDefinition
			));
		}
		return $output;
	}

	protected function handleGetSearchResult(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$requestParams = $request->getParams()->get();
		$extension = $request->getView()->getPathExtension();
		if (array_key_exists('filters', $requestParams)) {
			$filters = $this->stripUnusedSearchFilters($requestParams['filters']);
		} else {
			$filters = array();
		}
		$resourceList = $request->getResourceFactory()->search($request->getResourceDefinition()->attributes, $filters);
		return $this->getRenderer()->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition(),
			'resources' => $extension === 'php' ? $resourceList : $resourceList->getAttributes()
		));
	}

	protected function handlePost(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$attributes = $request->getParams()->post();
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

		$requestUri = preg_replace(sprintf('/\.%s$/', $uriExtension), '', trim($request->getUri(), '/'));

		$redirectUrl = $this->app->createUrl(array($requestUri, $resourceId));
		if ($uriExtension !== null) {
			$redirectUrl .= '.' . $uriExtension;
		}

		$this->app->redirect($redirectUrl);
	}

	protected function handlePut(Graph\RequestParser\RestfulParsedRequest $request, $route)
	{
		$attributes = $request->getParams()->post();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$uriExtension = $request->getView()->getNameExtension();
		$primaryAttributeName = $resourceDefinition->primaryAttribute;

		if (empty($attributes)) {
			throw new Exception\InvalidRequestException('PUT request received with no parameters');
		} elseif (!array_key_exists($primaryAttributeName, $attributes)) {
			throw new Exception\InvalidRequestException(
				sprintf('PUT request received with no value for primary attribute `%s`', $primaryAttributeName)
			);
		}

		$filters = array(
			$primaryAttributeName => $attributes[$primaryAttributeName]
		);

		$resource = $resourceFactory->update($filters, $attributes);

		$requestUri = preg_replace(sprintf('/\.%s$/', $uriExtension), '', trim($request->getUri(), '/'));
		$requestUri = preg_replace(sprintf('/\/%s/', $request->getView()->getFunctionName()), '', $requestUri);

		$redirectUrl = $this->app->createUrl(array($requestUri));
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

	protected function stripUnusedSearchFilters(array $filters)
	{
		foreach ($filters as $index => $filter) {
			if ($filter['comparator'] === '') {
				unset($filters[$index]);
			}
		}
		return array_values($filters);
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
