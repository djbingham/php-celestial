<?php

namespace Sloth\Module\Data\Table\Test;

require_once __DIR__ . '/bootstrap.php';

use Sloth\Module\Data\Table\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\ValidatorListBuilder;
use Sloth\Module\Data\Table\TableManifestValidator;

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

	protected function getTableDefinitionBuilder()
	{
		$manifestDirectory = __DIR__ . '/sample/tableManifest';
		$tableDefinitionBuilder = new TableBuilder($manifestDirectory);

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
