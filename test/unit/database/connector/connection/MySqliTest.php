<?php

namespace Sloth\Test\Unit\Database\Connector\Connection;

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/bootstrap.php';

use Sloth\Test\Unit\Database\UnitTest;
use Database\Connector\Connection\MySqli;

/**
 * MySqliConnectionTest
 * @package Test\Unit\Database\connector
 * @author Favicon, 2013
 */
class MySqliTest extends UnitTest
{
	/**
	 * @var MySqli
	 */
	protected $object;

	public function setUp()
	{
		$this->object = new MySqli;
	}

	public function testSetAndGetEngine()
	{
		$engine = $this->getMockBuilder('MySqli')->getMock();
		$setterOutput = $this->object->setEngine($engine);
		$this->assertEquals($this->object, $setterOutput);
		$getterOutput = $this->object->getEngine();
		$this->assertEquals($engine, $getterOutput);

	}

	public function testSetAndGetOptions()
	{
		$options = $this->mockOptions();
		$setterOutput = $this->object->setOptions($options);
		$this->assertEquals($this->object, $setterOutput);
		$getterOutput = $this->object->getOptions();
		$this->assertEquals($options, $getterOutput);
	}

    public function testConnect()
	{
		$this->object->setEngine($this->getMockBuilder('MySqli')->getMock());
		$this->object->setOptions($this->mockOptions());
		$output = $this->object->connect();
		$this->assertEquals($this->object, $output);
	}

	public function testConnectFailsIfValidatorReturnsErrors()
	{
		$this->object->setEngine($this->getMockBuilder('MySqli')->getMock());
		$this->object->setOptions($this->mockOptions(0, array(\Database\Connector\ConnectionOptions::ERROR_DATABASE_NAME)));
		$this->setExpectedException('\Exception');
		$this->object->connect();
	}

	public function testQueryAndGetDataReturnsSelectedData()
	{
		$engine = $this->getMockBuilder('Mysqli')
			->getMock();
		$query = $this->getMockBuilder('Database\_Query')
			->getMock();
		$result = $this->getMockBuilder('MySqli_Result')
			->disableOriginalConstructor()
			->getMock();

		$queryString = 'SELECT * FROM table';
		$data = array(
			array('a' => '1')
		);

		$engine->expects($this->once())
			->method('query')
			->with($queryString)
			->will($this->returnValue($result));

		$query->expects($this->any())
			->method('__toString')
			->will($this->returnValue($queryString));

		$result->expects($this->once())
			->method('fetch_all')
			->with(MYSQLI_ASSOC)
			->will($this->returnValue($data));
		$result->expects($this->once())
			->method('free');

		$this->object->setEngine($engine);
		$this->assertEquals($this->object, $this->object->query($query));
		$this->assertEquals($data, $this->object->getData());
	}

	public function testQueryAndGetDataWhenQueryHasNoResultSet()
	{
		$engine = $this->getMockBuilder('Mysqli')
			->getMock();
		$query = $this->getMockBuilder('Database\_Query')
			->getMock();

		$queryString = 'TRUNCATE table';

		$engine->expects($this->once())
			->method('query')
			->with($queryString)
			->will($this->returnValue(false));

		$query->expects($this->any())
			->method('__toString')
			->will($this->returnValue($queryString));

		$this->object->setEngine($engine);
		$this->assertEquals($this->object, $this->object->query($query));
		$this->assertEquals(false, $this->object->getData());
	}

	public function testGetQueryLog()
	{
		$engine = $this->getMockBuilder('Mysqli')->getMock();
		$queries = array(
			$this->getMockBuilder('Database\_Query')->getMock(),
			$this->getMockBuilder('Database\_Query')->getMock()
		);

		$queryStrings = array(
			'SELECT * FROM table1',
			'SELECT * FROM table2'
		);

		$engine->expects($this->exactly(2))
			->method('query')
			->will($this->returnValueMap(array(
				array($queryStrings[0], true),
				array($queryStrings[1], true)
			)));

		$queries[0]->expects($this->any())
			->method('__toString')
			->will($this->returnValue($queryStrings[0]));
		$queries[1]->expects($this->any())
			->method('__toString')
			->will($this->returnValue($queryStrings[1]));

		$this->object->setEngine($engine);

		// Test log is initially empty
		$this->assertEquals(array(), $this->object->getQueryLog());

		// Test first query enters log
		$this->object->query($queries[0]);
		$this->assertEquals(array($queryStrings[0]), $this->object->getQueryLog());

		// Test second query is appended to log
		$this->object->query($queries[1]);
		$this->assertEquals($queryStrings, $this->object->getQueryLog());
	}

	public function testBegin()
	{
		$engine = $this->getMock('\Mysqli', array('autocommit'));
		$engine->expects($this->once())
			->method('autocommit')
			->with(false);
		$this->object->setEngine($engine);
		$output = $this->object->begin();
		$this->assertEquals($this->object, $output);
		$log = $this->object->getQueryLog();
		$this->assertEquals(array('BEGIN'), $log);
	}

	public function testCommit()
	{
		$engine = $this->getMock('\Mysqli', array('commit', 'autocommit'));
		$engine->expects($this->once())
			->method('commit');
		$engine->expects($this->once())
			->method('autocommit')
			->with(true);
		$this->object->setEngine($engine);
		$output = $this->object->commit();
		$this->assertEquals($this->object, $output);
		$log = $this->object->getQueryLog();
		$this->assertEquals(array('COMMIT'), $log);
	}

	public function testRollback()
	{
		$engine = $this->getMock('\Mysqli', array('rollback', 'autocommit'));
		$engine->expects($this->once())
			->method('rollback');
		$engine->expects($this->once())
			->method('autocommit')
			->with(true);
		$this->object->setEngine($engine);
		$output = $this->object->rollback();
		$this->assertEquals($this->object, $output);
		$log = $this->object->getQueryLog();
		$this->assertEquals(array('ROLLBACK'), $log);
	}

	public function testEscapeString()
	{
		$string = 'Mock string';
		$escapedString = 'Mock escaped string';
		$engine = $this->getMock('\MySqli', array('real_escape_string'));
		$engine->expects($this->once())
			->method('real_escape_string')
			->with($string)
			->will($this->returnValue($escapedString));
		$this->object->setEngine($engine);
		$output = $this->object->escapeString($string);
		$this->assertEquals($escapedString, $output);
	}

	private function mockOptions($index = 0, $errors = array())
	{
		foreach ($this->mock()->databaseConnectionOptions($index) as $option => $value) {
			$setter = 'set' . ucfirst($option);
			$optionValues[$setter] = $value;
		}
		$options = $this->mockBuilder()->connectionOptions();
		foreach ($optionValues as $setter => $value) {
			$options->expects($this->any())
				->method($setter)
				->will($this->returnValue($value));
		}
		$options->expects($this->any())
			->method('errors')
			->will($this->returnValue($errors));
		return $options;
	}
}
