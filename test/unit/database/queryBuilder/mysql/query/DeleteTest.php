<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Query;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Database\QueryBuilder\MySql\Query\Delete;
use Sloth\Test\Unit\Database\UnitTest;

/**
 * DeleteTest
 * @package Test\Unit\Database\QueryBuilder\MySql\Query
 * @author Favicon, 2013
 */
class DeleteTest extends UnitTest
{
	/**
	 * @var Delete
	 */
	protected $object;

	public function setup()
	{
		$this->object = new Delete();
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

	public function testFromAcceptsTableAndReturnsDeleteInstance()
	{
		$output = $this->object->from($this->mockTable('`TableName`'));
		$this->assertEquals($this->object, $output);
	}

	public function testFromRejectsString()
	{
		$this->setExpectedException('\Exception');
		$this->object->from('TableName');
	}

	public function testFromRejectsArray()
	{
		$this->setExpectedException('\Exception');
		$this->object->from(array());
	}

	public function testWhereAcceptsConstraintAndReturnsDeleteInstance()
	{
		$output = $this->object->where($this->mockConstraint('constraint string'));
		$this->assertEquals($this->object, $output);
	}

	public function testWhereRejectsString()
	{
		$this->setExpectedException('\Exception');
		$this->object->where('constraint string');
	}

	public function testWhereRejectsArray()
	{
		$this->setExpectedException('\Exception');
		$this->object->where(array());
	}

	public function testToStringReturnsCorrectSqlQuery()
	{
		$this->object->from($this->mockTable('`TableName`'))
			->where($this->mockConstraint('constraint string'));
		$expected = <<<EOT
DELETE FROM `TableName`
WHERE constraint string
EOT;
		$this->assertEquals($expected, (string)$this->object);
	}

	public function testToStringWithoutConditionOmitsWhereClauseFromSqlQuery()
	{
		$this->object->from($this->mockTable('`TableName`'));
		$expected = "DELETE FROM `TableName`";
		$this->assertEquals($expected, (string)$this->object);
	}
}
