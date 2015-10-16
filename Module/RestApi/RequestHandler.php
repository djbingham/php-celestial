<?php
namespace Sloth\Module\RestApi;

use Sloth\Module\RestApi\Face\ParsedRequestInterface;
use Sloth\Module\RestApi\Face\RequestHandlerInterface;
use Sloth\Request;
use Sloth\Exception;
use Sloth\Module\Resource as ResourceModule;
use Sloth\Module\Render as RenderModule;

class RequestHandler implements RequestHandlerInterface
{
	/**
	 * @var ResourceModule\ModuleCore
	 */
	private $resourceModule;

	/**
	 * @var RenderModule\Renderer
	 */
	private $renderModule;

	public function setResourceModule(ResourceModule\ModuleCore $resourceModule)
	{
		$this->resourceModule = $resourceModule;
		return $this;
	}

	public function setRenderModule(RenderModule\Renderer $renderModule)
	{
		$this->renderModule = $renderModule;
		return $this;
	}

	public function handle(ParsedRequestInterface $parsedRequest, $route)
	{
		$handler = 'handle' . ucfirst($parsedRequest->getMethod());
		return $this->$handler($parsedRequest);
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

	protected function handleGet(ParsedRequest $request)
	{
		$renderer = $this->renderModule;

		$requestParams = $request->getParams()->get();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceId = $request->getResourceId();
		$extension = $request->getExtension();
		if ($extension === null) {
			$extension = 'json';
		}

		switch (null) {
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
//				$filters = $this->convertRequestParamsToSimpleSearchFilters($requestParams);
//				if (isset($resourceId)) {
//					$filters[$resourceDefinition->primaryAttribute] = $resourceId;
//				}
//
//				$resourceList = $resourceFactory->getBy($resourceDefinition->attributes, $filters);
//
//				if (isset($resourceId)) {
//					$viewData = $resourceList->current();
//				} elseif ($resourceList->length() > 0) {
//					$viewData = $resourceList;
//				} else {
//					$viewData = null;
//				}
//
//				$view = $this->renderModule->getViewFactory()->build(array(
//					'engine' => $extension
//				));
//				$output = $renderer->render($view, $viewData->getAttributes());

				$filters = $this->convertRequestParamsToSearchFilters($requestParams);
				if (isset($resourceId)) {
					$filters[] = array(
						'subject' => $resourceDefinition->primaryAttribute,
						'comparator' => '=',
						'value' => $resourceId
					);
				}

				$view = $this->renderModule->getViewFactory()->build(array(
					'engine' => $extension,
					'path' => 'resource/Default/list.' . $extension,
					'dataProviders' => array(
						'resourceName' => array(
							'engine' => 'static',
							'options' => array(
								'data' => $resourceDefinition->name
							)
						),
						'resources' => array(
							'engine' => 'resource',
							'options' => array(
								'resourceName' => $resourceDefinition->name,
								'filters' => $filters
							)
						)
					)
				));
				$output = $renderer->render($view);

				break;
		}

		return $output;
	}

	protected function handleIndex(ParsedRequest $request)
	{
		$resources = $this->getResourceNames($this->resourceModule->getResourceManifestDirectory());
		$view = new \Sloth\Module\Render\View();
		$view->name = 'index';
		$view->path = 'resource/default/index.php';
		$view->engine = 'php';
		return $this->renderModule->render($view, array(
			'resources' => $resources
		));
	}

	protected function handleGetDefinition(ParsedRequest $request)
	{
		return $this->renderModule->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetCreate(ParsedRequest $request)
	{
		return $this->renderModule->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetUpdate(ParsedRequest $request)
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
		$resource = $request->getResourceFactory()->getBy($resourceDefinition->attributes, $filters)->getByIndex(0);

		return $this->renderModule->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition(),
			'resource' => $resource
		));
	}

	protected function handleGetFilter(ParsedRequest $request)
	{
		return $this->renderModule->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition()
		));
	}

	protected function handleGetSearchForm(ParsedRequest $request)
	{
		$resourceDefinition = $request->getResourceDefinition();
		$requestParams = $request->getParams()->get();
		$extension = $request->getView()->getPathExtension();
		if (array_key_exists('filters', $requestParams)) {
			$filters = $this->stripUnusedSearchFilters($requestParams['filters']);
			$resourceList = $request->getResourceFactory()->search($resourceDefinition->attributes, $filters);
			$output = $this->renderModule->render($request->getView(), array(
				'resourceName' => $request->getResourceRoute(),
				'resourceDefinition' => $resourceDefinition,
				'resources' => $extension === 'php' ? $resourceList : $resourceList->getAttributes()
			));
		} else {
			$output = $this->renderModule->render($request->getView(), array(
				'resourceName' => $request->getResourceRoute(),
				'resourceDefinition' => $resourceDefinition
			));
		}
		return $output;
	}

	protected function handleGetSearchResult(ParsedRequest $request)
	{
		$requestParams = $request->getParams()->get();
		$extension = $request->getView()->getPathExtension();
		if (array_key_exists('filters', $requestParams)) {
			$filters = $this->stripUnusedSearchFilters($requestParams['filters']);
		} else {
			$filters = array();
		}
		$resourceList = $request->getResourceFactory()->search($request->getResourceDefinition()->attributes, $filters);
		return $this->renderModule->render($request->getView(), array(
			'resourceName' => $request->getResourceRoute(),
			'resourceDefinition' => $request->getResourceDefinition(),
			'resources' => $extension === 'php' ? $resourceList : $resourceList->getAttributes()
		));
	}

	protected function handlePost(ParsedRequest $request)
	{
		$attributes = $request->getParams()->post();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$uriExtension = $request->getView()->getNameExtension();
		$requestUri = preg_replace(sprintf('/\.%s$/', $uriExtension), '', trim($request->getUri(), '/'));

		if (empty($attributes)) {
			throw new Exception\InvalidRequestException('POST request received with no parameters');
		}

		$resource = $resourceFactory->create($attributes);
		$resourceId = $resource->getAttribute($resourceDefinition->primaryAttribute);

		$redirectUrl = $this->app->createUrl(array($requestUri, $resourceId));
		if ($uriExtension !== null) {
			$redirectUrl .= '.' . $uriExtension;
		}

		$this->app->redirect($redirectUrl);
	}

	protected function handlePut(ParsedRequest $request)
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

		$resource = $resourceFactory->getBy($resourceDefinition->attributes, $filters)->current();
		$updateFilters = $this->getUpdateFiltersFromResource($resource->getAttributes(), $resourceDefinition);

		$resourceFactory->update($updateFilters, $attributes);

		$requestUri = preg_replace(sprintf('/\.%s$/', $uriExtension), '', trim($request->getUri(), '/'));
		$requestUri = preg_replace(sprintf('/\/%s/', $request->getView()->getFunctionName()), '', $requestUri);

		$redirectUrl = $this->app->createUrl(array($requestUri));
		if ($uriExtension !== null) {
			$redirectUrl .= '.' . $uriExtension;
		}
		
		$this->app->redirect($redirectUrl);
	}

	protected function handleDelete(ParsedRequest $request)
	{
		$resourceModule = $this->getResourceFactory();
		$resourceFactory = $this->instantiateResourceFactory($request);
		$resourceId = $request->getUnresolvedRoute();
		$outputFormat = $request->getFormat();

		$resource = $this->getById($resourceFactory, $resourceId);
		$resource->delete();

		$output = $resourceModule->renderer()->renderDeletedResource($resourceFactory, $resource, $outputFormat);

		return $output;
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

	protected function convertRequestParamsToSearchFilters(array $requestParams, $prefix = null)
	{
		$filters = array();
		foreach ($requestParams as $name => $value) {
			if ((is_string($value) && strlen($value) > 0)) {
				$filters[] = array(
					'subject' => $name,
					'comparator' => '=',
					'value' => $value
				);
			} elseif ((is_array($value) && count($value) > 0)) {
				$nestedFilters = $this->convertRequestParamsToSearchFilters($value, $prefix . '.' . $name);
				if (!empty($nestedFilters)) {
					$filters = array_merge($filters, $nestedFilters);
				}
			}
		}
		return $filters;
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

	protected function getUpdateFiltersFromResource(array $attributes, Resource\Definition\Resource $resourceDefinition)
	{
		$primaryTableField = $resourceDefinition->table->fields->getByName($resourceDefinition->primaryAttribute);
		$filters = array(
			$primaryTableField->name => $attributes[$primaryTableField->name]
		);
		$filters = array_merge($filters, $this->getLinkDataFromTableDefinitionTree($attributes, $resourceDefinition->table));
		return $filters;
	}

	protected function getLinkDataFromTableDefinitionTree(array $data, Resource\Definition\Table $tableDefinition)
	{
		$linkData = array();
		/** @var Resource\Definition\Table\Join $join */
		foreach ($tableDefinition->links as $join) {
			$linkedFields = $join->getLinkedFields();
			$childField = $linkedFields['child'];
			if (in_array($join->type, array(Resource\Definition\Table\Join::ONE_TO_MANY, Resource\Definition\Table\Join::MANY_TO_MANY))) {
				foreach ($data[$join->name] as $rowIndex => $rowData) {
					$linkData[$join->name][$rowIndex][$childField->name] = $rowData[$childField->name];
				}
			} else {
				$linkData[$join->name][$childField->name] = $data[$join->name][$childField->name];
			}
		}
		return $linkData;
	}
}
