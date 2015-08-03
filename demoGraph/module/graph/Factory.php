<?php
namespace DemoGraph\Module\Graph;

use DemoGraph\Module\Graph\DefinitionBuilder\AttributeBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\AttributeListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\LinkListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ResourceDefinitionBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\TableBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ValidatorListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ViewListBuilder;
use Sloth\App;
use SlothMySql\DatabaseWrapper;

class Factory
{
	/**
	 * @var App
	 */
	private $app;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function resourceDefinitionBuilder(ResourceManifestValidator $manifestValidator, $manifestDirectory)
	{
		$validatorListBuilder = new ValidatorListBuilder();
		$attributeBuilder = new AttributeBuilder($validatorListBuilder);

		$resourceBuilder = new ResourceDefinitionBuilder($manifestValidator, $manifestDirectory);
		$resourceBuilder->setSubBuilders(array(
			'attributeListBuilder' => new AttributeListBuilder($attributeBuilder),
			'linkListBuilder' => new LinkListBuilder($resourceBuilder),
			'tableBuilder' => new TableBuilder(),
			'validatorListBuilder' => $validatorListBuilder,
			'viewListBuilder' => new ViewListBuilder()
		));

		return $resourceBuilder;
	}

	public function resourceFactory(ResourceDefinition\Resource $definition)
	{
		$querySetFactory = new QuerySetFactory(new QueryFactory($this->app->database()), new AttributeMapper($definition));
        $querySetFactory->setDatabase($this->app->database());
		return new ResourceFactory($definition, $querySetFactory);
	}
}
