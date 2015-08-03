<?php

namespace DemoGraph\Test;

require_once __DIR__ . '/bootstrap.php';

use DemoGraph\Module\Graph\DefinitionBuilder\AttributeBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\AttributeListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\LinkListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ResourceDefinitionBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\TableBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ValidatorListBuilder;
use DemoGraph\Module\Graph\DefinitionBuilder\ViewListBuilder;
use DemoGraph\Module\Graph\ResourceManifestValidator;
use DemoGraph\Module\Graph\Test\Mock\Connection;
use DemoGraph\Module\Graph\Test\Mock\DatabaseWrapper;

abstract class UnitTest extends \PHPUnit_Framework_TestCase
{
	public function rootDir()
	{
		return dirname(__DIR__);
	}

	public function tmpDir()
	{
		$directory = implode(DIRECTORY_SEPARATOR, array($this->rootDir(), 'tmp'));
		if (!file_exists($directory)) {
			mkdir($directory);
		}
		return $directory;
	}

    protected function getDatabaseWrapper(Connection $connection = null)
    {
		if (is_null($connection)) {
			$connection = new Connection();
		}
        $queryBuilderFactory = new \SlothMySql\QueryBuilder\Wrapper($connection);
        return new DatabaseWrapper($connection, $queryBuilderFactory);
    }

	protected function getResourceDefinitionBuilder()
	{
		$manifestValidator = new ResourceManifestValidator();
		$manifestDirectory = __DIR__ . '/sample/resourceManifest';
		$resourceDefinitionBuilder = new ResourceDefinitionBuilder($manifestValidator, $manifestDirectory);

		$validatorListBuilder = new ValidatorListBuilder();
		$attributeBuilder = new AttributeBuilder($validatorListBuilder);
		$resourceDefinitionBuilder->setSubBuilders(array(
			'attributeListBuilder' => new AttributeListBuilder($attributeBuilder),
			'linkListBuilder' => new LinkListBuilder($resourceDefinitionBuilder),
			'tableBuilder' => new TableBuilder(),
			'validatorListBuilder' => $validatorListBuilder,
			'viewListBuilder' => new ViewListBuilder()
		));

		return $resourceDefinitionBuilder;
	}
}
