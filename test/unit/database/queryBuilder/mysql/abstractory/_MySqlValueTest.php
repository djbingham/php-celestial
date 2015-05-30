<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Sloth\Test\Unit\Database\UnitTest;
use Database\QueryBuilder\MySql\_MySqlValue;

/**
 * _MySqlValueTest
 * @author Favicon, 2013
 */
class _MySqlValueTest extends UnitTest
{
	/**
	 * @var _MySqlValue
	 */
	protected $object;

	public function setup()
	{
		$this->object = $this->mock()->databaseValue();
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
