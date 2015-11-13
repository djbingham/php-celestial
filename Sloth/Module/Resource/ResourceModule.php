<?php
namespace Sloth\Module\Resource;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Resource\DefinitionBuilder\AttributeListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\ResourceDefinitionBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableDefinitionBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableValidatorListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\ValidatorListBuilder;
use Sloth\App;
use SlothMySql\DatabaseWrapper;

class ResourceModule
{
	/**
	 * @var App
	 */
	private $app;

	/**
	 * @var DatabaseWrapper
	 */
	private $databaseWrapper;

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

	/**
	 * @var DataValidator
	 */
	private $dataValidator;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function setDatabaseWrapper(DatabaseWrapper $databaseWrapper)
	{
		$this->databaseWrapper = $databaseWrapper;
		return $this;
	}

	public function getDatabaseWrapper()
	{
		return $this->databaseWrapper;
	}

	public function setResourceManifestValidator(ResourceManifestValidator $resourceManifestValidator)
	{
		$this->resourceManifestValidator = $resourceManifestValidator;
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

	public function setTableManifestValidator(TableManifestValidator $tableManifestValidator)
	{
		$this->tableManifestValidator = $tableManifestValidator;
		return $this;
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

	public function setDataValidator(DataValidator $validator)
	{
		$this->dataValidator = $validator;
		return $this;
	}

	public function resourceDefinitionBuilder()
	{
		$attributeListBuilder = new AttributeListBuilder();
		$validatorListBuilder = new ValidatorListBuilder();
		$tableFieldBuilder = new TableFieldBuilder($validatorListBuilder);
		$tableValidatorListBuilder = new TableValidatorListBuilder();

		$tableBuilder = new TableDefinitionBuilder($this->tableManifestValidator, $this->tableManifestDirectory);
		$tableBuilder->setSubBuilders(array(
			'tableFieldListBuilder' => new TableFieldListBuilder($tableFieldBuilder),
			'linkListBuilder' => new LinkListBuilder($tableBuilder),
			'validatorListBuilder' => $validatorListBuilder,
			'validatorListBuilder' => $tableValidatorListBuilder
		));

		$resourceBuilder = new ResourceDefinitionBuilder();
		$resourceBuilder
			->setManifestDirectory($this->resourceManifestDirectory)
			->setManifestValidator($this->resourceManifestValidator)
			->setSubBuilders(array(
				'attributeListBuilder' => $attributeListBuilder,
				'tableBuilder' => $tableBuilder,
				'validatorListBuilder' => $validatorListBuilder
			));

		return $resourceBuilder;
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

	/**
	 * @param string $resourcePath
	 * @return ResourceFactoryInterface
	 * @throws InvalidRequestException
	 */
	public function getResourceFactory($resourcePath)
	{
		$manifestFilePath = $this->getManifestPath($resourcePath);
		$factoryClass = $this->getFactoryClass($resourcePath);

		if (!class_exists($factoryClass)) {
			$factoryClass = 'Sloth\Module\Resource\ResourceFactory';
		} elseif (!is_a($factoryClass, 'Sloth\Module\Resource\ResourceFactoryInterface', true)) {
			throw new InvalidRequestException(
				sprintf('Resource class is not an instance of ResourceFactory: `%s`', $factoryClass)
			);
		}

		if (is_file($manifestFilePath)) {
			$resourceDefinition = $this->resourceDefinitionBuilder()->buildFromFile($manifestFilePath);
		} else {
			$resourceDefinition = null;
		}

		$factory = new $factoryClass($resourceDefinition, $this->getQuerySetFactory(), $this->dataValidator);

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
		$querySetFactory->setDatabase($this->getDatabaseWrapper());
		return $querySetFactory;
	}
}
