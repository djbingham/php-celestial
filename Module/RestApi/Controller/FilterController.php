<?php
namespace Sloth\Module\RestApi\Controller;

use Sloth\Controller\RestfulController;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Resource\ModuleCore;
use Sloth\Module\RestApi\Face\ParsedRequestInterface;
use Sloth\Module\RestApi\RequestParser;

class FilterController extends RestfulController
{
	public function parseRequest(RequestInterface $request, $route)
	{
		$requestParser = new RequestParser();
		$requestParser->setResourceModule($this->getResourceModule());
		return $requestParser->parse($request, $route);
	}

	public function handleGet(ParsedRequestInterface $request, $route)
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

	public function handlePost(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot post to resource/view');
	}

	public function handlePut(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot put to resource/view');
	}

	public function handleDelete(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot delete from resource/view');
	}

	/**
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('resourceRender');
	}

	/**
	 * @return ModuleCore
	 */
	private function getResourceModule()
	{
		return $this->module('resource');
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
