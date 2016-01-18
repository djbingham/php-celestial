<?php

namespace Sloth\Api\Rest\Controller;

use Sloth\Api\Action\Base\ActionController;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Data\Resource\ResourceModule;
use Sloth\Module\Data\Table\TableModule;
use Sloth\Module\Data\TableValidation\TableValidatorModule;
use Sloth\Module\Render\Face\RendererInterface;
use Sloth\Module\Request\Face\RoutedRequestInterface;
use Sloth\Module\Request\RoutedRequest;

class ValidateController extends ActionController
{
	public function actionIndex(RoutedRequestInterface $request)
	{
		if ($request->getMethod() !== 'get') {
			throw new InvalidRequestException(
				sprintf('Invalid request method used: `%s`. Allowed methods: get', $request->getMethod())
			);
		}

		$renderer = $this->getRenderModule();

		$resourceNames = $this->getResourceNames($this->getResourceModule()->getResourceManifestDirectory());

		$view = $renderer->getViewFactory()->build(array(
			'engine' => 'php',
			'path' => 'Default/index.php',
			'dataProviders' => array(
				'resourceNames' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $resourceNames
					)
				)
			)
		));

		return $renderer->render($view);
	}

	public function actionTable(RoutedRequestInterface $request)
	{
		if ($request->getMethod() !== 'get') {
			throw new InvalidRequestException(
				sprintf('Invalid request method used: `%s`. Allowed methods: get', $request->getMethod())
			);
		}

		$tableName = $this->getTableName($request);
		$tableManifestPath = $this->getResourceModule()->getTableModule()->getManifestPath($tableName);
		$tableJson = file_get_contents($tableManifestPath);
		$tableDefinition = json_decode($tableJson);

		$validationResult = $this->getTableValidationModule()->validateManifest($tableDefinition);

		$renderer = $this->getRenderModule();

		$view = $renderer->getViewFactory()->build(array(
			'engine' => 'php',
			'path' => 'Default/ValidationResult/table.php',
			'dataProviders' => array(
				'tableName' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $tableName
					)
				),
				'tableDefinition' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $tableDefinition
					)
				),
				'isValid' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $validationResult->isValid()
					)
				),
				'errors' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $validationResult->getErrors()
					)
				)
			)
		));

		return $renderer->render($view);
	}

	public function actionResource(RoutedRequestInterface $request)
	{
		if ($request->getMethod() !== 'get') {
			throw new InvalidRequestException(
				sprintf('Invalid request method used: `%s`. Allowed methods: get', $request->getMethod())
			);
		}

		$resourceDefinition = $this->getResourceDefinition($request);

		$renderer = $this->getRenderModule();

		$view = $renderer->getViewFactory()->build(array(
			'engine' => 'php',
			'path' => 'Default/ValidationResult/resource.php',
			'dataProviders' => array(
				'resourceDefinition' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $resourceDefinition
					)
				),
				'isValid' => $validationResult->isValid(),
				'errors' => $validationResult->getErrors()
			)
		));

		return $renderer->render($view);
	}

	/**
	 * @return ResourceModule
	 */
	private function getResourceModule()
	{
		return $this->module('restResource');
	}

	/**
	 * @return RendererInterface
	 */
	protected function getRenderModule()
	{
		return $this->module('restRender');
	}

	/**
	 * @return TableValidatorModule
	 */
	private function getTableValidationModule()
	{
		return $this->module('data.tableValidation');
	}

	private function getResourceNames($directory)
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

	private function getTableName(RoutedRequestInterface $request)
	{
		$path = $request->getControllerPath() . '/table';
		$escapedPath = preg_replace('/\//', '\\/', $path);
		$pathRegex = sprintf('/^%s\//', $escapedPath);

		return preg_replace($pathRegex, '', $request->getPath());
	}

	private function getResourceDefinition(RoutedRequestInterface $request)
	{
		return $this->getResourceModule()->resourceDefinitionBuilder()->buildFromName($request->getPath());
	}
}