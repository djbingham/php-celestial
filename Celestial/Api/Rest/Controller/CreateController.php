<?php
namespace Celestial\Api\Rest\Controller;

use Celestial\Api\Rest\Face\RestfulParsedRequestInterface;
use Celestial\Api\Rest\Base\RestfulController;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Data\Resource\Face\Definition\ResourceInterface;
use Celestial\Module\Data\ResourceDataValidator\Result\ExecutedValidatorList;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Render\Face\RendererInterface;
use Celestial\Module\Data\Resource\ResourceModule;
use Celestial\Api\Rest\RestfulRequestParser;

class CreateController extends RestfulController
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

		return $this->renderCreateForm($resourceDefinition);
	}

	public function handlePost(RestfulParsedRequestInterface $request)
	{
		$getParams = $request->getParams()->get();
		$postParams = $request->getParams()->post();
		$resourceDefinition = $request->getResourceDefinition();
		$resourceFactory = $request->getResourceFactory();
		$urlExtension = $request->getExtension();

		if (!array_key_exists('attributes', $postParams)) {
			throw new InvalidRequestException('POST/PUT request received with no parameters');
		}

		$attributes = $postParams['attributes'];

		if (empty($attributes)) {
			throw new InvalidRequestException('POST request to resource/create received with no parameters');
		}

		$validationResult = $resourceFactory->validateCreateData($attributes);
		$failedValidators = $validationResult->getFailedValidators();
		$output = null;
		$redirectUrl = null;

		if ($failedValidators->length() === 0) {
			$resource = $resourceFactory->create($attributes);
			$resourceId = $resource->getAttribute($resourceDefinition->primaryAttribute);

			if (array_key_exists('redirect', $getParams)) {
				$redirectUrl = $this->app->createUrl(explode('/', $getParams['redirect']));
			} elseif (array_key_exists('redirect', $postParams)) {
				$redirectUrl = $this->app->createUrl(explode('/', $postParams['redirect']));
			} else {
				$redirectUrl = $this->app->createUrl(array('resource/view', lcfirst($resourceDefinition->name), $resourceId));
				if ($urlExtension !== null) {
					$redirectUrl .= '.' . $urlExtension;
				}
			}
		} elseif (array_key_exists('errorUrl', $getParams)) {
			$redirectUrl = $this->app->createUrl(explode('/', $getParams['errorUrl']));
		} else {
			$viewParameters = array(
				'attributes' => $attributes,
				'presetData' => $attributes,
				'failedValidators' => $failedValidators
			);

			$output = $this->renderCreateForm($resourceDefinition, $viewParameters);
		}

		if ($redirectUrl !== null) {
			$this->app->redirect($redirectUrl);
		}

		return $output;
	}

	public function handlePut(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot put to resource/create');
	}

	public function handleDelete(RestfulParsedRequestInterface $request)
	{
		throw new InvalidRequestException('Cannot delete from resource/create');
	}

	private function renderCreateForm(ResourceInterface $resourceDefinition, $parameters = array())
	{
		$renderer = $this->getRenderModule();

		if (!array_key_exists('failedValidators', $parameters)) {
			$parameters['failedValidators'] = new ExecutedValidatorList();
		}

		if (!array_key_exists('presetData', $parameters)) {
			$parameters['presetData'] = array();
		}

		$view = $renderer->getViewFactory()->build(array(
			'engine' => 'php',
			'path' => 'Default/createForm.php',
			'dataProviders' => array(
				'resourceDefinition' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $resourceDefinition
					)
				)
			)
		));

		return $renderer->render($view, $parameters);
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
}
