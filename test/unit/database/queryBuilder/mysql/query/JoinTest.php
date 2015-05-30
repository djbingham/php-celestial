<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Query;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Database\QueryBuilder\MySql\Query\Join;
use Sloth\Test\Unit\Database\UnitTest;


/**
 * JoinTest
 * @package Test\Unit\Database\QueryBuilder\MySql\Query
 * @author Favicon, 2013
 */
class JoinTest extends UnitTest
{
	/**
	 * @var Join
	 */
	protected $object;

	public function setup()
	{
		$this->object = new Join();
	}

	protected function mockTable($tableName)
	{
		$table = $this->mockBuilder()->queryValue('Table');
		$table->expects($this->any())
			->method('__toString')
			->will($this->returnValue($tableName));
		return $table;
	}

	protected function mockConstraint($string)
	{
		$constraint = $this->mockBuilder()->queryConstraint();
		$constraint->expects($this->any())
			->method('__toString')
			->will($this->returnValue($string));
		return $constraint;
	}

	public function testSetAndGetType()
	{
		$this->assertEquals($this->object, $this->object->setType(Join::TYPE_INNER));
		$this->assertEquals(Join::TYPE_INNER, $this->object->getType());
		$this->assertEquals($this->object, $this->object->setType(Join::TYPE_OUTER));
		$this->assertEquals(Join::TYPE_OUTER, $this->object->getType());
		$this->assertEquals($this->object, $this->object->setType(Join::TYPE_LEFT));
		$this->assertEquals(Join::TYPE_LEFT, $this->object->getType());
		$this->assertEquals($this->object, $this->object->setType(Join::TYPE_RIGHT));
		$this->assertEquals(Join::TYPE_RIGHT, $this->object->getType());
		$this->setExpectedException('\Exception');
		$this->object->setType('Not a join type');
	}

	public function testTableAcceptsTableAndReturnsUpdateInstance()
	{
		$output = $this->object->table($this->mockTable('`TableName`'));
		$this->assertEquals($this->object, $output);
	}

	public function testTableRejectsStringInput()
	{
		$this->setExpectedException('\Exception');
		$this->object->table('TableName');
	}

	public function testTableRejectsArrayInput()
	{
		$this->setExpectedException('\Exception');
		$this->object->table(array());
	}

	public function testOnAcceptsConstraintInstanceAndReturnsUpdateInstance()
	{
		$output = $this->object->on($this->mockConstraint('constraint string'));
		$this->assertEquals($this->object, $output);
	}

	public function testOnRejectsStringInput()
	{
		$this->setExpectedException('\Exception');
		$this->object->on('constraint string');
	}

	public function testOnRejectsArrayInput()
	{
		$this->setExpectedException('\Exception');
		$this->object->on(array());
	}

	public function testToStringReturnsCorrectSqlQuery()
	{
		$this->object->setType(Join::TYPE_INNER)
			->table($this->mockTable('`TableName`'))
			->on($this->mockConstraint('constraint string'));
		$expectedSql = <<<EOT
INNER JOIN `TableName` ON (constraint string)
EOT;
		$this->assertEquals($expectedSql, (string)$this->object);
	}
}
