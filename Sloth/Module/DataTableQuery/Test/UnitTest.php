<?php

namespace Sloth\Module\DataTableQuery\Test;

require_once __DIR__ . '/bootstrap.php';

use Sloth\Module\DataTable\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\DataTable\DefinitionBuilder\TableBuilder;
use Sloth\Module\DataTable\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\DataTable\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\DataTable\DefinitionBuilder\ValidatorListBuilder;
use Sloth\Module\DataTable\TableManifestValidator;
use Sloth\Module\DataTableQuery\Test\Mock\Connection;
use Sloth\Module\DataTableQuery\Test\Mock\DatabaseWrapper;

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
		$tableDefinitionBuilder = new TableBuilder($manifestValidator, $manifestDirectory);

		$validatorListBuilder = new ValidatorListBuilder();
		$attributeBuilder = new TableFieldBuilder($validatorListBuilder);
		$tableDefinitionBuilder->setSubBuilders(array(
			'tableFieldListBuilder' => new TableFieldListBuilder($attributeBuilder),
			'linkListBuilder' => new LinkListBuilder($tableDefinitionBuilder),
			'validatorListBuilder' => $validatorListBuilder
		));

		return $tableDefinitionBuilder;
	}
}
