<?php

namespace Sloth\Test\Unit\Database\Abstractory;

require_once dirname(dirname(dirname(__DIR__))) . '/bootstrap.php';

use Sloth\Test\Unit\Database\UnitTest;
use Database\_Value;

/**
 * _ValueTest
 * @package Test\Unit\Database\Abstractory
 * @author Favicon, 2013
 */
class _ValueTest extends UnitTest
{
	/**
	 * @var _Value
	 */
	protected $object;

	public function setUp()
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
		$testString = 'Test string to be escaped';
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
