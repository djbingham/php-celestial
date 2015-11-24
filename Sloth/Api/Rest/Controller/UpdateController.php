<?php
namespace Sloth\Api\Rest\Controller;

use Sloth\Api\Rest\Base\RestfulController;
use Sloth\Api\Rest\Face\RestfulParsedRequestInterface;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\DataTable\Face\JoinInterface;
use Sloth\Module\DataTable\Face\TableInterface;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Resource as ResourceModule;
use Sloth\Api\Rest\RestfulRequestParser;

class UpdateController extends RestfulController
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
			'path' => 'Default/updateForm.php',
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

	public function handlePost(RestfulParsedRequestInterface $request)
	{
		$this->handlePut($request);
	}

	public function handlePut(RestfulParsedRequestInterface $request)
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

	public function handleDelete(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot delete from resource/create');
	}

	/**
	 * @return RendererInterface
	 */
	private function getRenderModule()
	{
		return $this->module('restRender');
	}

	/**
	 * @return ResourceModule\ResourceModule
	 */
	private function getResourceModule()
	{
		return $this->module('restResource');
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

	protected function getLinkDataFromTableDefinitionTree(array $data, TableInterface $tableDefinition)
	{
		$linkData = array();
		/** @var JoinInterface $join */
		foreach ($tableDefinition->links as $join) {
			$linkedFields = $join->getLinkedFields();
			$childField = $linkedFields['child'];
			if (in_array($join->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
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
