<?php

namespace Celestial\Module\Log\Test;

require_once __DIR__ . '/bootstrap.php';

use Celestial\Module\Data\Table\DefinitionBuilder\LinkListBuilder;
use Celestial\Module\Data\Table\DefinitionBuilder\TableBuilder;
use Celestial\Module\Data\Table\DefinitionBuilder\TableFieldBuilder;
use Celestial\Module\Data\Table\DefinitionBuilder\TableFieldListBuilder;
use Celestial\Module\Data\Table\DefinitionBuilder\ValidatorListBuilder;
use Celestial\Module\Data\Table\TableManifestValidator;
use Celestial\Module\Data\Resource\Test\Mock\Connection;
use Celestial\Module\Data\Resource\Test\Mock\DatabaseWrapper;

abstract class UnitTest extends \PHPUnit_Framework_TestCase
{
	public function rootDir()
	{
		return dirname(__DIR__);
	}
}
