<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Query;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Database\QueryBuilder\MySql\Query\Select;
use Sloth\Test\Unit\Database\UnitTest;

/**
 * SelectTest
 * @package Test\Unit\Database\QueryBuilder\MySql\Query
 * @author Favicon, 2013
 */
class SelectTest extends UnitTest
{
	/**
	 * @var Select
	 */
	protected $object;

	public function setup()
	{
		$this->object = new Select();
	}

	protected function mockTable($tableName)
	{
		$table = $this->mockBuilder()->queryValue('Table');
		$table->expects($this->any())
			->method('__toString')
			->will($this->returnValue($tableName));
		return $table;
	}

	protected function mockField($fieldName)
	{
		$field = $this->mockBuilder()->queryValue('Table\Field');
		$field->expects($this->any())
			->method('__toString')
			->will($this->returnValue($fieldName));
		return $field;
	}

	protected function mockConstraint($string)
	{
		$constraint = $this->mockBuilder()->queryConstraint();
		$constraint->expects($this->any())
			->method('__toString')
			->will($this->returnValue($string));
		return $constraint;
	}

	protected function mockJoin($string)
	{
		$constraint = $this->mockBuilder()->queryJoin();
		$constraint->expects($this->any())
			->method('__toString')
			->will($this->returnValue($string));
		return $constraint;
	}

	public function testFieldAndGetFields()
	{
		$fields = array(
			$this->mockField('field1'),
			$this->mockField('field2')
		);
		$this->assertEquals($this->object, $this->object->field($fields[0]));
		$this->assertEquals(array_slice($fields, 0, 1), $this->object->getFields());
		$this->assertEquals($this->object, $this->object->field($fields[1]));
		$this->assertEquals($fields, $this->object->getFields());
	}

	public function testSetAndGetFields()
	{
		$fieldSet1 = array(
			$this->mockField('field1'),
			$this->mockField('field2')
		);
		$fieldSet2 = array(
			$this->mockField('field3'),
			$this->mockField('field4')
		);
		$this->assertEquals($this->object, $this->object->setFields($fieldSet1));
		$this->assertEquals($fieldSet1, $this->object->getFields());
		$this->assertEquals($this->object, $this->object->setFields($fieldSet2));
		$this->assertEquals($fieldSet2, $this->object->getFields());
	}

	public function testFromAndGetTables()
	{
		$tables = array(
			$this->mockTable('Table1'),
			$this->mockTable('Table2')
		);
		$this->assertEquals($this->object, $this->object->from($tables[0]));
		$this->assertEquals(array_slice($tables, 0, 1), $this->object->getTables());
		$this->assertEquals($this->object, $this->object->from($tables[1]));
		$this->assertEquals($tables, $this->object->getTables());
	}

	public function testSetAndGetTables()
	{
		$tableSet1 = array(
			$this->mockTable('Table1'),
			$this->mockTable('Table2')
		);
		$tableSet2 = array(
			$this->mockTable('Table3'),
			$this->mockTable('Table4')
		);
		$this->assertEquals($this->object, $this->object->setTables($tableSet1));
		$this->assertEquals($tableSet1, $this->object->getTables());
		$this->assertEquals($this->object, $this->object->setTables($tableSet2));
		$this->assertEquals($tableSet2, $this->object->getTables());
	}

	public function testJoinAndGetJoins()
	{
		$joins = array(
			$this->mockJoin('Join 1'),
			$this->mockJoin('Join 2')
		);
		$this->assertEquals($this->object, $this->object->join($joins[0]));
		$this->assertEquals(array_slice($joins, 0, 1), $this->object->getJoins());
		$this->assertEquals($this->object, $this->object->join($joins[1]));
		$this->assertEquals($joins, $this->object->getJoins());
	}

	public function testSetAndGetJoins()
	{
		$joinSet1 = array(
			$this->mockJoin('Join 1'),
			$this->mockJoin('Join 2')
		);
		$joinSet2 = array(
			$this->mockJoin('Join 3'),
			$this->mockJoin('Join 4')
		);
		$this->assertEquals($this->object, $this->object->setJoins($joinSet1));
		$this->assertEquals($joinSet1, $this->object->getJoins());
		$this->assertEquals($this->object, $this->object->setJoins($joinSet2));
		$this->assertEquals($joinSet2, $this->object->getJoins());
	}

	public function testWhereAndGetConstraint()
	{
		$constraints = array(
			$this->mockBuilder()->queryConstraint(),
			$this->mockConstraint('constraint 2')
		);
		$constraints[0]->expects($this->any())
			->method('__toString')
			->will($this->returnValue('constraint 1'));
		$constraints[0]->expects($this->once())
			->method('andWhere')
			->with($constraints[1])
			->will($this->returnValue($constraints[0]));
		$this->assertEquals($this->object, $this->object->where($constraints[0]));
		$this->assertEquals($constraints[0], $this->object->getConstraint());
		$this->assertEquals($this->object, $this->object->where($constraints[1]));
		$this->assertEquals($constraints[0], $this->object->getConstraint());
	}

	public function testAndWhere()
	{
		$constraints = array(
			$this->mockBuilder()->queryConstraint(),
			$this->mockConstraint('constraint 2')
		);
		$constraints[0]->expects($this->any())
			->method('__toString')
			->will($this->returnValue('constraint 1'));
		$constraints[0]->expects($this->once())
			->method('andWhere')
			->with($constraints[1])
			->will($this->returnValue($constraints[0]));
		$this->assertEquals($this->object, $this->object->where($constraints[0]));
		$this->assertEquals($constraints[0], $this->object->getConstraint());
		$this->assertEquals($this->object, $this->object->andWhere($constraints[1]));
		$this->assertEquals($constraints[0], $this->object->getConstraint());
	}

	public function testAndWhereFailsWithNoPreviousConstraint()
	{
		$constraint = $this->mockBuilder()->queryConstraint();
		$constraint->expects($this->any())
			->method('__toString')
			->will($this->returnValue('constraint string'));
		$this->setExpectedException('\Exception');
		$this->object->andWhere($constraint);
	}

	public function testOrWhere()
	{
		$constraints = array(
			$this->mockBuilder()->queryConstraint(),
			$this->mockConstraint('constraint 2')
		);
		$constraints[0]->expects($this->any())
			->method('__toString')
			->will($this->returnValue('constraint 1'));
		$constraints[0]->expects($this->once())
			->method('orWhere')
			->with($constraints[1])
			->will($this->returnValue($constraints[0]));
		$this->assertEquals($this->object, $this->object->where($constraints[0]));
		$this->assertEquals($constraints[0], $this->object->getConstraint());
		$this->assertEquals($this->object, $this->object->orWhere($constraints[1]));
		$this->assertEquals($constraints[0], $this->object->getConstraint());
	}

	public function testOrWhereFailsWithNoPreviousConstraint()
	{
		$constraint = $this->mockBuilder()->queryConstraint();
		$constraint->expects($this->any())
			->method('__toString')
			->will($this->returnValue('constraint string'));
		$this->setExpectedException('\Exception');
		$this->object->orWhere($constraint);
	}

	public function testSetAndGetConstraint()
	{
		$constraints = array(
			$this->mockConstraint('constraint 1'),
			$this->mockConstraint('constraint 2')
		);
		$this->assertEquals($this->object, $this->object->setConstraint($constraints[0]));
		$this->assertEquals($constraints[0], $this->object->getConstraint());
		$this->assertEquals($this->object, $this->object->setConstraint($constraints[1]));
		$this->assertEquals($constraints[1], $this->object->getConstraint());
	}

	public function testOrderByWithPrioritiseFalseAndGetOrders()
	{
		$orders = array(
			$this->mockField('field1'),
			$this->mockField('field2')
		);
		$this->assertEquals($this->object, $this->object->orderBy($orders[0]));
		$this->assertEquals(array_slice($orders, 0, 1), $this->object->getOrders());
		$this->assertEquals($this->object, $this->object->orderBy($orders[1]));
		$this->assertEquals($orders, $this->object->getOrders());
	}

	public function testOrderByWithPrioritiseTrueAndGetOrders()
	{
		$orders = array(
			$this->mockField('field1'),
			$this->mockField('field2')
		);
		$this->assertEquals($this->object, $this->object->orderBy($orders[0], TRUE));
		$this->assertEquals(array_slice($orders, 0, 1), $this->object->getOrders());
		$this->assertEquals($this->object, $this->object->orderBy($orders[1], TRUE));
		$this->assertEquals(array_reverse($orders), $this->object->getOrders());
	}

	public function testSetAndGetOrders()
	{
		$orders = array(
			$this->mockField('field1'),
			$this->mockField('field2')
		);
		$this->assertEquals($this->object, $this->object->setOrders(array_slice($orders, 0, 1)));
		$this->assertEquals(array_slice($orders, 0, 1), $this->object->getOrders());
		$this->assertEquals($this->object, $this->object->setOrders(array_slice($orders, 0, 1)));
		$this->assertEquals(array_slice($orders, 1, 2), $this->object->getOrders());
	}

	public function testGroupByWithPrioritiseFalseAndGetGroups()
	{
		$groups = array(
			$this->mockField('field1'),
			$this->mockField('field2')
		);
		$this->assertEquals($this->object, $this->object->groupBy($groups[0]));
		$this->assertEquals(array_slice($groups, 0, 1), $this->object->getGroups());
		$this->assertEquals($this->object, $this->object->groupBy($groups[1]));
		$this->assertEquals($groups, $this->object->getGroups());
	}

	public function testGroupByWithPrioritiseTrueAndGetGroups()
	{
		$groups = array(
			$this->mockField('field1'),
			$this->mockField('field2')
		);
		$this->assertEquals($this->object, $this->object->groupBy($groups[0], TRUE));
		$this->assertEquals(array_slice($groups, 0, 1), $this->object->getGroups());
		$this->assertEquals($this->object, $this->object->groupBy($groups[1], TRUE));
		$this->assertEquals(array_reverse($groups), $this->object->getGroups());
	}

	public function testSetAndGetGroups()
	{
		$groups = array(
			$this->mockField('field1'),
			$this->mockField('field2')
		);
		$this->assertEquals($this->object, $this->object->setGroups(array_slice($groups, 0, 1)));
		$this->assertEquals(array_slice($groups, 0, 1), $this->object->getGroups());
		$this->assertEquals($this->object, $this->object->setGroups(array_slice($groups, 0, 1)));
		$this->assertEquals(array_slice($groups, 1, 2), $this->object->getGroups());
	}

	public function testToStringReturnsCorrectQueryString()
	{
		$fields = array(
			$this->mockField('Table1.field1'),
			$this->mockField('Table1.field2'),
			$this->mockField('Table2.field1'),
			$this->mockField('Table2.field2')
		);
		$tables = array(
			$this->mockTable('Table1'),
			$this->mockTable('Table2')
		);
		$joins = array(
			$this->mockJoin('JOIN 1'),
			$this->mockJoin('JOIN 2')
		);
		$constraint = $this->mockConstraint('Constraint string');
		$orders = array(
			$fields[0],
			$fields[2]
		);
		$groups = array(
			$fields[1],
			$fields[3]
		);
		$this->object
			->setFields($fields)
			->setTables($tables)
			->setJoins($joins)
			->setConstraint($constraint)
			->setOrders($orders)
			->setGroups($groups);
		$expectedQuery = <<<EOT
SELECT Table1.field1,Table1.field2,Table2.field1,Table2.field2
FROM Table1,Table2
JOIN 1
JOIN 2
WHERE Constraint string
ORDER BY Table1.field1, Table2.field1
GROUP BY Table1.field2, Table2.field2
EOT;
		$this->assertEquals($expectedQuery, (string)$this->object);
	}
}
