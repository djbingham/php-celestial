<?php
namespace Sloth\Module\Graph;

use Sloth\Module\Graph\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\Graph\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\Graph\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\Graph\DefinitionBuilder\ResourceDefinitionBuilder;
use Sloth\Module\Graph\DefinitionBuilder\TableDefinitionBuilder;
use Sloth\Module\Graph\DefinitionBuilder\ValidatorListBuilder;
use Sloth\Module\Graph\DefinitionBuilder\ViewListBuilder;
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

	public function setResourceManifestDirectory($directory)
	{
		$this->resourceManifestDirectory = $directory;
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

	public function getResourceManifestDirectory()
	{
		return $this->resourceManifestDirectory;
	}

	public function resourceDefinitionBuilder()
	{
		$validatorListBuilder = new ValidatorListBuilder();
		$attributeListBuilder = null;
		$viewListBuilder = new ViewListBuilder();
		$tableFieldBuilder = new TableFieldBuilder($validatorListBuilder);

		$tableBuilder = new TableDefinitionBuilder($this->tableManifestValidator, $this->tableManifestDirectory);
		$tableBuilder->setSubBuilders(array(
			'tableFieldListBuilder' => new TableFieldListBuilder($tableFieldBuilder),
			'linkListBuilder' => new LinkListBuilder($tableBuilder),
			'validatorListBuilder' => $validatorListBuilder,
			'viewListBuilder' => $viewListBuilder
		));

		$resourceBuilder = new ResourceDefinitionBuilder();
		$resourceBuilder
			->setManifestDirectory($this->resourceManifestDirectory)
			->setManifestValidator($this->resourceManifestValidator)
			->setSubBuilders(array(
				'tableBuilder' => $tableBuilder,
				'validatorListBuilder' => $validatorListBuilder,
				'attributeListBuilder' => $attributeListBuilder,
				'viewListBuilder' => $viewListBuilder
			));

		return $resourceBuilder;
	}

	public function resourceFactory(Definition\Table $definition)
	{
		$querySetFactory = new QuerySetFactory();
        $querySetFactory->setDatabase($this->app->database());
		return new ResourceFactory($definition, $querySetFactory);
	}
}