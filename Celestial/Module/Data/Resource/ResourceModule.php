<?php
namespace Celestial\Module\Data\Resource;

use Celestial\App;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Data\ResourceDataValidator\ResourceDataValidatorModule;
use Celestial\Module\Data\Table\TableModule;
use Celestial\Module\Data\TableQuery\TableQueryModule;
use Celestial\Module\Data\Resource\DefinitionBuilder\AttributeListBuilder;
use Celestial\Module\Data\Resource\DefinitionBuilder\ResourceDefinitionBuilder;
use Celestial\Module\Data\Resource\DefinitionBuilder\ValidatorListBuilder;
use Celestial\Module\Data\Resource\Face\ResourceFactoryInterface;

class ResourceModule
{
	/**
	 * @var App
	 */
	private $app;

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
	private $resourceNamespace;

	/**
	 * @var TableModule
	 */
	private $tableModule;

	/**
	 * @var TableQueryModule
	 */
	private $tableQueryModule;

	/**
	 * @var ResourceDataValidatorModule
	 */
	private $dataValidator;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function setResourceManifestValidator(ResourceManifestValidator $resourceManifestValidator)
	{
		$this->resourceManifestValidator = $resourceManifestValidator;
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

	public function getTableModule()
	{
		return $this->tableModule;
	}

	public function setTableModule($tableModule)
	{
		$this->tableModule = $tableModule;
		return $this;
	}

	public function getTableQueryModule()
	{
		return $this->tableQueryModule;
	}

	public function setTableQueryModule($tableQueryModule)
	{
		$this->tableQueryModule = $tableQueryModule;
		return $this;
	}

	public function setDataValidator(ResourceDataValidatorModule $validator)
	{
		$this->dataValidator = $validator;
		return $this;
	}

	public function resourceDefinitionBuilder()
	{
		$attributeListBuilder = new AttributeListBuilder();
		$validatorListBuilder = new ValidatorListBuilder();

		$resourceBuilder = new ResourceDefinitionBuilder();
		$resourceBuilder
			->setManifestDirectory($this->resourceManifestDirectory)
			->setManifestValidator($this->resourceManifestValidator)
			->setTableModule($this->tableModule)
			->setSubBuilders(array(
				'attributeListBuilder' => $attributeListBuilder,
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
			$exists = is_a($factoryClass, 'Celestial\Module\Data\Resource\Face\ResourceFactoryInterface', true);
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
			$factoryClass = 'Celestial\Module\Data\Resource\ResourceFactory';
		} elseif (!is_a($factoryClass, 'Celestial\Module\Data\Resource\Face\ResourceFactoryInterface', true)) {
			throw new InvalidRequestException(
				sprintf('Resource class is not an instance of ResourceFactory: `%s`', $factoryClass)
			);
		}

		if (is_file($manifestFilePath)) {
			$resourceDefinition = $this->resourceDefinitionBuilder()->buildFromFile($manifestFilePath);
		} else {
			$resourceDefinition = null;
		}

		$factory = new $factoryClass(
			$this->app,
			$resourceDefinition,
			$this->tableQueryModule,
			$this->dataValidator
		);

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
}
