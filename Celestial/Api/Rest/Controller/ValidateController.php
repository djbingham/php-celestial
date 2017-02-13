<?php

namespace Celestial\Api\Rest\Controller;

use Celestial\Api\Action\Base\ActionController;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Data\Resource\ResourceModule;
use Celestial\Module\Data\Table\TableModule;
use Celestial\Module\Data\TableValidation\TableValidatorModule;
use Celestial\Module\Render\Face\RendererInterface;
use Celestial\Module\Request\Face\RoutedRequestInterface;
use Celestial\Module\Validation\Face\ValidationResultInterface;

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

		$tableName = $this->getRequestedTableName($request);

		if (empty($tableName)) {
			$tableDirectory = $this->getTableModule()->getTableManifestDirectory();
			$tableNames = $this->getTableNames($tableDirectory);

			$validationResults = array();

			foreach ($tableNames as $tableName) {
				$validationResult = $this->getTableValidationModule()->validateNamedTable($tableName);
				$validationResults[$tableName] = $validationResult;
			}

			$output = $this->renderTableListValidationResults($validationResults);
		} else {
			$validationResult = $this->getTableValidationModule()->validateNamedTable($tableName);

			if ($this->getTableModule()->exists($tableName)) {
				$tableManifestPath = $this->getResourceModule()->getTableModule()->getManifestPath($tableName);
				$tableJson = file_get_contents($tableManifestPath);
				$tableDefinition = json_decode($tableJson);
			} else {
				$tableDefinition = null;
			}

			$output = $this->renderTableValidationResult($tableName, $tableDefinition, $validationResult);
		}

		return $output;
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

	private function getRequestedTableName(RoutedRequestInterface $request)
	{
		$path = $request->getControllerPath() . '/table';
		$escapedPath = preg_replace('/\//', '\\/', $path);
		$pathRegex = sprintf('/^%s\/?/', $escapedPath);

		return preg_replace($pathRegex, '', $request->getPath());
	}

	private function getTableNames($directory)
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

	private function renderTableListValidationResults(array $resultList)
	{
		$renderer = $this->getRenderModule();

		$view = $renderer->getViewFactory()->build(array(
			'engine' => 'php',
			'path' => 'Default/ValidationResult/tables.php',
			'dataProviders' => array(
				'validationResults' => array(
					'engine' => 'static',
					'options' => array(
						'data' => $resultList
					)
				)
			)
		));

		return $renderer->render($view);
	}

	private function renderTableValidationResult(
		$tableName,
		$tableDefinition,
		ValidationResultInterface $validationResult
	) {
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

	private function getResourceDefinition(RoutedRequestInterface $request)
	{
		return $this->getResourceModule()->resourceDefinitionBuilder()->buildFromName($request->getPath());
	}

	/**
	 * @return ResourceModule
	 */
	private function getResourceModule()
	{
		return $this->module('restResource');
	}

	/**
	 * @return TableModule
	 */
	private function getTableModule()
	{
		return $this->module('data.table');
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
}