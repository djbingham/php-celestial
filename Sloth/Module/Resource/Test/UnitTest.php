<?php

namespace DemoResource\Test;

require_once __DIR__ . '/bootstrap.php';

use Sloth\Module\Resource\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableDefinitionBuilder;
use Sloth\Module\Resource\DefinitionBuilder\TableValidatorListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\ValidatorListBuilder;
use Sloth\Module\Resource\DefinitionBuilder\ViewListBuilder;
use Sloth\Module\Resource\TableManifestValidator;
use Sloth\Module\Resource\Test\Mock\Connection;
use Sloth\Module\Resource\Test\Mock\DatabaseWrapper;

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

	protected function getTableDefinitionBuilder()
	{
		$manifestValidator = new TableManifestValidator();
		$manifestDirectory = __DIR__ . '/sample/tableManifest';
		$tableDefinitionBuilder = new TableDefinitionBuilder($manifestValidator, $manifestDirectory);

		$validatorListBuilder = new TableValidatorListBuilder();
		$attributeBuilder = new TableFieldBuilder($validatorListBuilder);
		$tableDefinitionBuilder->setSubBuilders(array(
			'tableFieldListBuilder' => new TableFieldListBuilder($attributeBuilder),
			'linkListBuilder' => new LinkListBuilder($tableDefinitionBuilder),
			'validatorListBuilder' => $validatorListBuilder
		));

		return $tableDefinitionBuilder;
	}
}
