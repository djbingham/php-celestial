<?php
namespace Sloth\Api\Rest\Controller;

use Sloth\Api\Rest\Base\RestfulController;
use Sloth\Api\Rest\Face\RestfulParsedRequestInterface;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Data\Resource as ResourceModule;
use Sloth\Module\Data\Resource\Face\Definition\ResourceInterface;
use Sloth\Module\Data\ResourceDataValidator\Result\ExecutedValidatorList;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Render\Face\RendererInterface;
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
		$resourceDefinition = $request->getResourceDefinition();
		$resourceId = $request->getResourceId();

		if (strlen($resourceId) === 0) {
			throw new InvalidRequestException(
				'Update form cannot be produced without specifying a resource ID'
			);
		}

		return $this->renderUpdateForm($resourceDefinition, $resourceId);
	}

	public function handlePost(RestfulParsedRequestInterface $request)
	{
		return $this->handlePut($request);
	}

	public function handlePut(RestfulParsedRequestInterface $request)
	{
		$getParams = $request->getParams()->get();
		$postParams = $request->getParams()->post();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$urlExtension = $request->getExtension();

		if (!array_key_exists('attributes', $postParams)) {
			throw new InvalidRequestException('POST/PUT request received with no attributes to update');
		}

		$attributes = $postParams['attributes'];
		$primaryAttributeName = $resourceDefinition->primaryAttribute;
		$primaryAttributeValue = $attributes[$primaryAttributeName];

		if (empty($attributes)) {
			throw new InvalidRequestException('PUT request received with no attributes to update');
		} elseif (!array_key_exists($primaryAttributeName, $attributes)) {
			throw new InvalidRequestException(
				sprintf('PUT request received with no value for primary attribute `%s`', $primaryAttributeName)
			);
		}

		$validationResult = $resourceFactory->validateUpdateData($attributes);
		$failedValidators = $validationResult->getFailedValidators();
		$output = null;
		$redirectUrl = null;

		if ($failedValidators->length() === 0) {
			$filters = array(
				$primaryAttributeName => $primaryAttributeValue
			);

			$resource = $resourceFactory->getBy($resourceDefinition->attributes, $filters)->current();

			$resourceFactory->update($resource->getAttributes(), $attributes);

			if (array_key_exists('redirect', $getParams)) {
				$redirectUrl = $this->app->createUrl(explode('/', $getParams['redirect']));
			} elseif (array_key_exists('redirect', $postParams)) {
				$redirectUrl = $this->app->createUrl(explode('/', $postParams['redirect']));
			} else {
				$redirectUrl = $this->app->createUrl(array('resource/view', lcfirst($resourceDefinition->name), $primaryAttributeValue));
				if ($urlExtension !== null) {
					$redirectUrl .= '.' . $urlExtension;
				}
			}
		} elseif (array_key_exists('errorUrl', $getParams)) {
			$redirectUrl = $this->app->createUrl(explode('/', $getParams['errorUrl']));
		} elseif (array_key_exists('errorUrl', $postParams)) {
			$redirectUrl = $this->app->createUrl(explode('/', $postParams['errorUrl']));
		} else {
			$viewParameters = array(
				'attributes' => $attributes,
				'failedValidators' => $failedValidators
			);

			$output = $this->renderUpdateForm($resourceDefinition, $primaryAttributeValue, $viewParameters);
		}

		if ($redirectUrl !== null) {
			$this->app->redirect($redirectUrl);
		}

		return $output;
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

	private function renderUpdateForm(ResourceInterface $resourceDefinition, $resourceId, array $parameters = array())
	{
		$renderer = $this->getRenderModule();
		$primaryAttribute = $resourceDefinition->primaryAttribute;

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

		if (!array_key_exists('failedValidators', $parameters)) {
			$parameters['failedValidators'] = new ExecutedValidatorList();
		}

		return $renderer->render($view, $parameters);
	}
}
