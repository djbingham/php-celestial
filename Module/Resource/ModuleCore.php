<?php
namespace Sloth\Module\Resource;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Resource\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\ResourceDefinitionBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableDefinitionBuilder;
use Sloth\Module\Resource\DefinitionBuilder\ValidatorListBuilder;
use Sloth\App;

class ModuleCore
{
	/**
	 * @var App
	 */
	private $app;

	/**
	 * @var TableManifestValidator
	 */
	private $tableManifestValidator;

	/**
	 * @var ResourceManifestValidator
	 */
	private $resourceManifestValidator;

	/**
	 * @var string
	 */
	private $resourceManifestDirectory;

	/**
	 * @var string
	 */
	private $tableManifestDirectory;

	/**
	 * @var string
	 */
	private $resourceNamespace;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function setResourceManifestValidator(ResourceManifestValidator $resourceManifestValidator)
	{
		$this->resourceManifestValidator = $resourceManifestValidator;
		return $this;
	}

	public function setTableManifestValidator(TableManifestValidator $tableManifestValidator)
	{
		$this->tableManifestValidator = $tableManifestValidator;
		return $this;
	}

	public function setTableManifestDirectory($directory)
	{
		$this->tableManifestDirectory = $directory;
		return $this;
	}

	public function getTableManifestDirectory()
	{
		return $this->tableManifestDirectory;
	}

	public function setResourceManifestDirectory($directory)
	{
		$this->resourceManifestDirectory = $directory;
		return $this;
	}

	public function getResourceManifestDirectory()
	{
		return $this->resourceManifestDirectory;
	}

	public function setResourceNamespace($resourceNamespace)
	{
		$this->resourceNamespace = $resourceNamespace;
		return $this;
	}

	public function getResourceNamespace()
	{
		return $this->resourceNamespace;
	}

	public function resourceDefinitionBuilder()
	{
		$validatorListBuilder = new ValidatorListBuilder();
		$attributeListBuilder = null;
		$tableFieldBuilder = new TableFieldBuilder($validatorListBuilder);

		$tableBuilder = new TableDefinitionBuilder($this->tableManifestValidator, $this->tableManifestDirectory);
		$tableBuilder->setSubBuilders(array(
			'tableFieldListBuilder' => new TableFieldListBuilder($tableFieldBuilder),
			'linkListBuilder' => new LinkListBuilder($tableBuilder),
			'validatorListBuilder' => $validatorListBuilder
		));

		$resourceBuilder = new ResourceDefinitionBuilder();
		$resourceBuilder
			->setManifestDirectory($this->resourceManifestDirectory)
			->setManifestValidator($this->resourceManifestValidator)
			->setSubBuilders(array(
				'tableBuilder' => $tableBuilder,
				'validatorListBuilder' => $validatorListBuilder,
				'attributeListBuilder' => $attributeListBuilder
			));

		return $resourceBuilder;
	}

	public function resourceFactory(Definition\Table $definition)
	{
		return new ResourceFactory($definition, $this->getQuerySetFactory());
	}

	public function resourceExists($resourcePath)
	{
		$manifestFilePath = $this->getManifestPath($resourcePath);

		$exists = is_file($manifestFilePath);
		if (!$exists) {
			$factoryClass = $this->getFactoryClass($resourcePath);
			$exists = is_a($factoryClass, 'Sloth\Module\Resource\ResourceFactoryInterface', true);
		}

		return $exists;
	}

	public function getResourceFactory($resourcePath)
	{
		$manifestFilePath = $this->getManifestPath($resourcePath);
		$factoryClass = $this->getFactoryClass($resourcePath);

		if (empty($factoryClass)) {
			$factoryClass = 'Sloth\Module\Resource\ResourceFactory';
		} elseif (!is_a($factoryClass, 'Sloth\Module\Resource\ResourceFactoryInterface', true)) {
			throw new InvalidRequestException(
				sprintf('Resource class is not an instance of ResourceFactory: `%s`', $factoryClass)
			);
		}

		if (is_file($manifestFilePath)) {
			$tableDefinition = $this->resourceDefinitionBuilder()->buildFromFile($manifestFilePath)->table;
		} else {
			$tableDefinition = null;
		}

		echo $factoryClass;

		$factory = new $factoryClass($tableDefinition, $this->getQuerySetFactory());

		return $factory;
	}

	private function getManifestPath($resourcePath)
	{
		$pathParts = explode('/', $resourcePath);
		foreach ($pathParts as &$pathPart) {
			$pathPart = ucfirst($pathPart);
		}
		return $this->resourceManifestDirectory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $pathParts) . '.json';
	}

	private function getFactoryClass($resourcePath)
	{
		$pathParts = explode('/', $resourcePath);
		foreach ($pathParts as &$pathPart) {
			$pathPart = ucfirst($pathPart);
		}
		return $this->resourceNamespace . '\\' . implode(DIRECTORY_SEPARATOR, $pathParts) . 'Factory';
	}

	private function getQuerySetFactory()
	{
		$querySetFactory = new QuerySetFactory();
		$querySetFactory->setDatabase($this->app->database());
		return $querySetFactory;
	}
}
