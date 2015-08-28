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
	 * @return Graph\RequestParser\RestfulRequestParser
	 */
	abstract protected function getRequestParser();

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

	protected function handleGet(Graph\RequestParser\RestfulParsedRequest $request)
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
				$output = $this->handleGetDefinition($request);
				break;
			case 'create':
				$output = $this->handleGetCreate($request);
				break;
			case 'update':
				$output = $this->handleGetUpdate($request);
				break;
			case 'filter':
				$output = $this->handleGetFilter($request);
				break;
			case 'search':
				$output = $this->handleGetSearchForm($request);
				break;
			case 'searchResult':
				$output = $this->handleGetSearchResult($request);
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

	protected function handleIndex(Graph\RequestParser\RestfulParsedRequest $request)
	{
		$resources = $this->getResourceNames($this->getResourceModule()->getResourceManifestDirectory());
		$view = new \Sloth\Module\Render\View();
		$view->name = 'index';
		$view->path = 'resource/default/index.php';
		$view->engine = 'php';
		$renderer = $this->getRenderer();
		return $renderer->render($view, array(
			'resources' => $resources
		));
	}

	protected function handleGetDefinition(Graph\RequestParser\RestfulParsedRequest $request)
	{
		return $this->getRenderer()->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetCreate(Graph\RequestParser\RestfulParsedRequest $request)
	{
		return $this->getRenderer()->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetUpdate(Graph\RequestParser\RestfulParsedRequest $request)
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

	protected function handleGetFilter(Graph\RequestParser\RestfulParsedRequest $request)
	{
		return $this->getRenderer()->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetSearchForm(Graph\RequestParser\RestfulParsedRequest $request)
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

	protected function handleGetSearchResult(Graph\RequestParser\RestfulParsedRequest $request)
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

	protected function handlePost(Graph\RequestParser\RestfulParsedRequest $request)
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

	protected function handlePut(Graph\RequestParser\RestfulParsedRequest $request)
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

		$resourceFactory->update($filters, $attributes);

		$requestUri = preg_replace(sprintf('/\.%s$/', $uriExtension), '', trim($request->getUri(), '/'));
		$requestUri = preg_replace(sprintf('/\/%s/', $request->getView()->getFunctionName()), '', $requestUri);

		$redirectUrl = $this->app->createUrl(array($requestUri));
		if ($uriExtension !== null) {
			$redirectUrl .= '.' . $uriExtension;
		}

		$this->app->redirect($redirectUrl);
	}

	protected function handleDelete(Graph\RequestParser\RestfulParsedRequest $request)
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

	/**
	 * @return Graph\ModuleCore
	 */
	protected function getResourceModule()
	{
		return $this->module('graph');
	}

	/**
	 * @return \Sloth\Module\Render\Face\RendererInterface
	 */
	protected function getRenderer()
	{
		return $this->module('render');
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