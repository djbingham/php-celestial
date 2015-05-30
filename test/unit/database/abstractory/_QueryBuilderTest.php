<?php

namespace Sloth\Test\Unit\Database\Abstractory;

require_once dirname(dirname(dirname(__DIR__))) . '/bootstrap.php';

use Sloth\Test\Unit\Database\UnitTest;
use Database\_QueryBuilder;

/**
 * _QueryBuilderTest
 * @package Test\Unit\Database\Abstractory
 * @author Favicon, 2013
 */
class _QueryBuilderTest extends UnitTest
{
	/**
	 * @var _QueryBuilder
	 */
	protected $object;

	public function setUp()
	{
		$this->object = $this->mock()->databaseQueryBuilder();
	}

	public function testSetGetAndHasDatabaseWrapper()
	{
		$wrapper = $this->mockBuilder()->databaseWrapper();
		$setterOutput = $this->object->setDatabaseWrapper($wrapper);
		$this->assertEquals($this->object, $setterOutput);
		$getterOutput = $this->object->getDatabaseWrapper($wrapper);
		$this->assertEquals($wrapper, $getterOutput);
		$this->assertTrue($this->object->hasDatabaseWrapper());
	}
}
