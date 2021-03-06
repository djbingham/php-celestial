<?php
namespace Celestial\Api\Rest\Controller;

use Celestial\Api\Rest\Base\RestfulController;
use Celestial\Api\Rest\Face\RestfulParsedRequestInterface;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Render\Face\RendererInterface;
use Celestial\Module\Data\Resource\ResourceModule;
use Celestial\Api\Rest\RestfulRequestParser;

class FilterController extends RestfulController
{
	public function parseRequest(RoutedRequestInterface $request)
	{
		$requestParser = new RestfulRequestParser();
		$requestParser->setResourceModule($this->getResourceModule());
		return $requestParser->parse($request);
	}

	public function handleGet(RestfulParsedRequestInterface $request)
	{
		$renderer = $this->getRenderModule();

		$requestParams = $request->getParams()->get();
		$resourceDefinition = $request->getResourceDefinition();
		$extension = $request->getExtension();
		if ($extension === null) {
			$extension = 'php';
		}

		$dataProviders = array(
			'resourceDefinition' => array(
				'engine' => 'static',
				'options' => array(
					'data' => $resourceDefinition
				)
			)
		);

		if (empty($requestParams)) {
			$viewPath = 'Default/filterForm.' . $extension;
		} else {
			$filters = $this->convertRequestParamsToSearchFilters($requestParams);
			$filters = $this->stripUnusedSearchFilters($filters);
			$dataProviders['resources'] = array(
				'engine' => 'resourceList',
				'options' => array(
					'resourceName' => $resourceDefinition->name,
					'filters' => $filters
				)
			);
			$viewPath = 'Default/list.' . $extension;
		}

		$view = $renderer->getViewFactory()->build(array(
			'engine' => $extension,
			'path' => $viewPath,
			'dataProviders' => $dataProviders
		));

		return $renderer->render($view);
	}

	public function handlePost(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot post to resource/view');
	}

	public function handlePut(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot put to resource/view');
	}

	public function handleDelete(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot delete from resource/view');
	}

	/**
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('restRender');
	}

	/**
	 * @return ResourceModule
	 */
	private function getResourceModule()
	{
		return $this->module('restResource');
	}

	private function convertRequestParamsToSearchFilters(array $requestParams)
	{
		$filters = array();
		foreach ($requestParams as $name => $value) {
			$name = str_replace('_', '.', $name);
			$filters[] = array(
				'subject' => $name,
				'comparator' => '=',
				'value' => $value
			);
		}
		return $filters;
	}

	protected function stripUnusedSearchFilters(array $filters)
	{
		foreach ($filters as $index => $filter) {
			if (strlen($filter['value']) === 0) {
				unset($filters[$index]);
			}
		}
		return array_values($filters);
	}
}
