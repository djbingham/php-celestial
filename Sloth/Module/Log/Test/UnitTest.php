<?php

namespace Sloth\Module\Log\Test;

require_once __DIR__ . '/bootstrap.php';

use Sloth\Module\Data\Table\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\ValidatorListBuilder;
use Sloth\Module\Data\Table\TableManifestValidator;
use Sloth\Module\Data\Resource\Test\Mock\Connection;
use Sloth\Module\Data\Resource\Test\Mock\DatabaseWrapper;

abstract class UnitTest extends \PHPUnit_Framework_TestCase
{
	public function rootDir()
	{
		return dirname(__DIR__);
	}
}
