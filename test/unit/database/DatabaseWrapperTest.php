<?php

namespace Sloth\Test\Unit\Database;

require_once dirname(dirname(__DIR__)) . '/bootstrap.php';

use Database\DatabaseWrapper;
use Sloth;

/**
 * DatabaseWrapperTest
 * @package Test\Unit\Database
 * @author Favicon, 2013
 */
class DatabaseWrapperTest extends Sloth\Test\Unit\Database\UnitTest
{
	/**
	 * @var DatabaseWrapper
	 */
	protected $object;

	public function setUp()
	{
		$this->object = new DatabaseWrapper($this->mock()->databaseFactory());
	}

	public function testQueryBuilder()
	{
		$engine = $this->object->queryBuilder();
		$this->assertInstanceOf('Sloth\Factory\Database\MySqli\QueryBuilder', $engine);
		// Test that calling sqlQueryFactory again returns the same instance
		$this->assertSame($engine, $this->object->queryBuilder());
	}

	public function testConnector()
	{
		$output = $this->object->connector();
		$this->assertInstanceOf('Sloth\Factory\Database\MySqli\Connection', $output);
	}

	public function testConnect()
	{
		$connection = $this->mockBuilder()->connectionWrapper();
		$connection->expects($this->once())
			->method('connect');
		$this->assertEquals($this->object, $this->object->connect($connection));
	}

	public function testQuery()
	{
		$connection = $this->mockBuilder()->connectionWrapper();
		$connection->expects($this->once())
			->method('query');
		$query = $this->getMock('\Database\_Query');

		$this->object->connect($connection);

		$output = $this->object->query($query);
		$this->assertEquals($this->object, $output);
	}

	public function testBegin()
	{
		$connection = $this->mockBuilder()->connectionWrapper();
		$this->object->connect($connection);
		$output = $this->object->begin();
		$this->assertEquals($this->object, $output);
	}

	public function testCommit()
	{
		$connection = $this->mockBuilder()->connectionWrapper();
		$this->object->connect($connection);
		$output = $this->object->commit();
		$this->assertEquals($this->object, $output);
	}

	public function testRollback()
	{
		$connection = $this->mockBuilder()->connectionWrapper();
		$this->object->connect($connection);
		$output = $this->object->rollback();
		$this->assertEquals($this->object, $output);
	}

	public function testGetData()
	{
		$data = array('some' => 'data');
		$connection = $this->mockBuilder()->connectionWrapper();
		$connection->expects($this->once())
			->method('getData')
			->will($this->returnValue($data));
		$this->object->connect($connection);
		$this->assertEquals($data, $this->object->getData());
	}

	public function testEscapeString()
	{
		$string = 'Test string to be escaped';
		$escapedString = 'Test escaped string';
		$connection = $this->mockBuilder()->connectionWrapper();
		$connection->expects($this->once())
			->method('escapeString')
			->with($string)
			->will($this->returnValue($escapedString));
		$this->object->connect($connection);

		$output = $this->object->escapeString($string);
		$this->assertEquals($escapedString, $output);
	}
}
