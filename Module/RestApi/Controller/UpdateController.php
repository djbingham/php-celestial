<?php
namespace Sloth\Module\RestApi\Controller;

use Sloth\Controller\RestfulController;
use Sloth\Exception\InvalidRequestException;
use Sloth\Face\RequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Resource as ResourceModule;
use Sloth\Module\RestApi\Face\ParsedRequestInterface;
use Sloth\Module\RestApi\RequestParser;

class UpdateController extends RestfulController
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

		$resourceDefinition = $request->getResourceDefinition();
		$primaryAttribute = $resourceDefinition->primaryAttribute;
		$resourceId = $request->getResourceId();

		if (strlen($resourceId) === 0) {
			throw new InvalidRequestException(
				'Update form cannot be produced without specifying a resource ID'
			);
		}

		$view = $renderer->getViewFactory()->build(array(
			'engine' => 'php',
			'path' => 'Resource/Default/updateForm.php',
			'dataProviders' => array(
				'resourceDefinition' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $resourceDefinition
					)
				),
				'resource' => array(
					'engine' => 'resource',
					'options' => array(
						'resourceName' => $resourceDefinition->name,
						'filters' => array(
							array(
								'subject' => $primaryAttribute,
								'comparator' => '=',
								'value' => $resourceId
							)
						)
					)
				)
			)
		));

		return $renderer->render($view);
	}

	public function handlePost(ParsedRequestInterface $request, $route)
	{
		$this->handlePut($request, $route);
	}

	public function handlePut(ParsedRequestInterface $request, $route)
	{
		$attributes = $request->getParams()->post();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$urlExtension = $request->getExtension();
		$primaryAttributeName = $resourceDefinition->primaryAttribute;
		$primaryAttributeValue = $attributes[$primaryAttributeName];

		if (empty($attributes)) {
			throw new InvalidRequestException('PUT request received with no parameters');
		} elseif (!array_key_exists($primaryAttributeName, $attributes)) {
			throw new InvalidRequestException(
				sprintf('PUT request received with no value for primary attribute `%s`', $primaryAttributeName)
			);
		}

		$filters = array(
			$primaryAttributeName => $primaryAttributeValue
		);

		$resource = $resourceFactory->getBy($resourceDefinition->attributes, $filters)->current();
		$updateFilters = $this->getUpdateFiltersFromResource($resource->getAttributes(), $resourceDefinition);

		$resourceFactory->update($updateFilters, $attributes);

		$redirectUrl = $this->app->createUrl(array('resource/view', lcfirst($resourceDefinition->name), $primaryAttributeValue));
		if ($urlExtension !== null) {
			$redirectUrl .= '.' . $urlExtension;
		}

		$this->app->redirect($redirectUrl);
	}

	public function handleDelete(ParsedRequestInterface $request, $route)
	{
		throw new InvalidRequestException('Cannot delete from resource/create');
	}

	/**
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('render');
	}

	/**
	 * @return ResourceModule\ModuleCore
	 */
	private function getResourceModule()
	{
		return $this->module('resource');
	}

	private function getUpdateFiltersFromResource(array $attributes, ResourceModule\Definition\Resource $resourceDefinition)
	{
		$primaryTableField = $resourceDefinition->table->fields->getByName($resourceDefinition->primaryAttribute);
		$filters = array(
			$primaryTableField->name => $attributes[$primaryTableField->name]
		);
		$filters = array_merge($filters, $this->getLinkDataFromTableDefinitionTree($attributes, $resourceDefinition->table));
		return $filters;
	}

	protected function getLinkDataFromTableDefinitionTree(array $data, ResourceModule\Definition\Table $tableDefinition)
	{
		$linkData = array();
		/** @var ResourceModule\Definition\Table\Join $join */
		foreach ($tableDefinition->links as $join) {
			$linkedFields = $join->getLinkedFields();
			$childField = $linkedFields['child'];
			if (in_array($join->type, array(ResourceModule\Definition\Table\Join::ONE_TO_MANY, ResourceModule\Definition\Table\Join::MANY_TO_MANY))) {
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
