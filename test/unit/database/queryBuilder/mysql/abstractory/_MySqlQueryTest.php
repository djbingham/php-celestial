<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Sloth\Test\Unit\Database\UnitTest;
use Database\QueryBuilder\MySql\_MySqlQuery;

/**
 * _MySqlQueryTest
 * @author Favicon, 2013
 */
class _MySqlQueryTest extends UnitTest
{
	/**
	 * @var _MySqlQuery
	 */
	protected $object;

	public function setup()
	{
		$this->object = $this->mock()->databaseQuery();
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

	public function testEscapeString()
	{
		$testString = 'String to escape';
		$escapedString = 'Escaped string';
		$wrapper = $this->mockBuilder()->databaseWrapper();
		$wrapper->expects($this->once())
			->method('escapeString')
			->with($testString)
			->will($this->returnValue($escapedString));
		$this->object->setDatabaseWrapper($wrapper);
		$output = $this->object->escapeString($testString);
		$this->assertEquals($escapedString, $output);
	}
}
