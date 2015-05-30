<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Query;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Sloth\Test\Unit\Database\UnitTest;
use Database\QueryBuilder\MySql\Query\Constraint;

/**
 * ConstraintTest
 * @package Test\Unit\Database\QueryBuilder\MySql\Query
 * @author Favicon, 2013
 */
class ConstraintTest extends UnitTest
{
	/**
	 * @var Constraint
	 */
	protected $object;

	public function setup()
	{
		$this->object = new Constraint();
	}

	protected function mockConstraint($string)
	{
		$constraint = $this->mockBuilder()->queryConstraint();
		$constraint->expects($this->any())
			->method('__toString')
			->will($this->returnValue($string));
		return $constraint;
	}

	protected function mockField($string)
	{
		$field = $this->mockBuilder()->queryValue('String');
		$field->expects($this->any())
			->method('__toString')
			->will($this->returnValue($string));
		return $field;
	}

	protected function mockList(array $strings)
	{
		$list = $this->mockBuilder()->queryValue('ValueList');
		$list->expects($this->any())
			->method('__toString')
			->will($this->returnValue('(' . implode(',', $strings) . ')'));
		return $list;
	}

	public function testWrapAndIsWrapped()
	{
		$this->assertEquals($this->object, $this->object->wrap());
		$this->assertEquals(true, $this->object->isWrapped());
	}

	public function testSetAndGetSubject()
	{
		$subject = $this->mockField('fieldName');
		$this->assertEquals($this->object, $this->object->setSubject($subject));
		$this->assertEquals($subject, $this->object->getSubject());
	}

	public function testEqualsAndGetComparatorAndValue()
	{
		$value = $this->mockField('fieldName');
		$this->assertEquals($this->object, $this->object->equals($value));
		$this->assertEquals('=', $this->object->getComparator());
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testNotEqualsAndGetComparatorAndValue()
	{
		$value = $this->mockField('fieldName');
		$this->assertEquals($this->object, $this->object->notEquals($value));
		$this->assertEquals('!=', $this->object->getComparator());
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testGreaterThanAndGetComparatorAndValue()
	{
		$value = $this->mockField('fieldName');
		$this->assertEquals($this->object, $this->object->greaterThan($value));
		$this->assertEquals('>', $this->object->getComparator());
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testGreaterThanOrEqualsAndGetComparatorAndValue()
	{
		$value = $this->mockField('fieldName');
		$this->assertEquals($this->object, $this->object->greaterThanOrEquals($value));
		$this->assertEquals('>=', $this->object->getComparator());
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testLessThanAndGetComparatorAndValue()
	{
		$value = $this->mockField('fieldName');
		$this->assertEquals($this->object, $this->object->lessThan($value));
		$this->assertEquals('<', $this->object->getComparator());
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testLessThanOrEqualsAndGetComparatorAndValue()
	{
		$value = $this->mockField('fieldName');
		$this->assertEquals($this->object, $this->object->lessThanOrEquals($value));
		$this->assertEquals('<=', $this->object->getComparator());
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testInAndGetComparatorAndValue()
	{
		$value = $this->mockList(array('field1', 'field2'));
		$this->assertEquals($this->object, $this->object->in($value));
		$this->assertEquals('IN', $this->object->getComparator());
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testNotInAndGetComparatorAndValue()
	{
		$value = $this->mockList(array('field1', 'field2'));
		$this->assertEquals($this->object, $this->object->notIn($value));
		$this->assertEquals('NOT IN', $this->object->getComparator());
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testSetAndGetValue()
	{
		$value = $this->mockField('field name');
		$this->assertEquals($this->object, $this->object->setValue($value));
		$this->assertEquals($value, $this->object->getValue());
	}

	public function testSetAndGetComparator()
	{
		$this->assertEquals($this->object, $this->object->setComparator('='));
		$this->assertEquals('=', $this->object->getComparator());
		$this->assertEquals($this->object, $this->object->setComparator('!='));
		$this->assertEquals('!=', $this->object->getComparator());
		$this->assertEquals($this->object, $this->object->setComparator('>'));
		$this->assertEquals('>', $this->object->getComparator());
		$this->assertEquals($this->object, $this->object->setComparator('>='));
		$this->assertEquals('>=', $this->object->getComparator());
		$this->assertEquals($this->object, $this->object->setComparator('<'));
		$this->assertEquals('<', $this->object->getComparator());
		$this->assertEquals($this->object, $this->object->setComparator('<='));
		$this->assertEquals('<=', $this->object->getComparator());
		$this->assertEquals($this->object, $this->object->setComparator('IN'));
		$this->assertEquals('IN', $this->object->getComparator());
		$this->assertEquals($this->object, $this->object->setComparator('NOT IN'));
		$this->assertEquals('NOT IN', $this->object->getComparator());
	}

	public function testSetComparatorRejectsNonComparatorString()
	{
		$this->setExpectedException('\Exception');
		$this->assertEquals($this->object, $this->object->setComparator('Not a comparator'));
	}

	public function testAndWhereAndGetSimultaneousConstraints()
	{
		$constraints = array(
			$this->mockConstraint('constraint 1'),
			$this->mockConstraint('constraint 2')
		);
		$this->assertEquals($this->object, $this->object->andWhere($constraints[0]));
		$this->assertEquals(array_slice($constraints, 0, 1), $this->object->getSimultaneousConstraints());
		$this->assertEquals($this->object, $this->object->andWhere($constraints[1]));
		$this->assertEquals($constraints, $this->object->getSimultaneousConstraints());
	}

	public function testAndOnAndGetSimultaneousConstraints()
	{
		$constraints = array(
			$this->mockConstraint('constraint 1'),
			$this->mockConstraint('constraint 2')
		);
		$this->assertEquals($this->object, $this->object->andOn($constraints[0]));
		$this->assertEquals(array_slice($constraints, 0, 1), $this->object->getSimultaneousConstraints());
		$this->assertEquals($this->object, $this->object->andOn($constraints[1]));
		$this->assertEquals($constraints, $this->object->getSimultaneousConstraints());
	}

	public function testOrWhereAndGetAlternativeConstraints()
	{
		$constraints = array(
			$this->mockConstraint('constraint 1'),
			$this->mockConstraint('constraint 2')
		);
		$this->assertEquals($this->object, $this->object->orWhere($constraints[0]));
		$this->assertEquals(array_slice($constraints, 0, 1), $this->object->getAlternativeConstraints());
		$this->assertEquals($this->object, $this->object->orWhere($constraints[1]));
		$this->assertEquals($constraints, $this->object->getAlternativeConstraints());
	}

	public function testOrOnAndGetAlternativeConstraints()
	{
		$constraints = array(
			$this->mockConstraint('constraint 1'),
			$this->mockConstraint('constraint 2')
		);
		$this->assertEquals($this->object, $this->object->orOn($constraints[0]));
		$this->assertEquals(array_slice($constraints, 0, 1), $this->object->getAlternativeConstraints());
		$this->assertEquals($this->object, $this->object->orOn($constraints[1]));
		$this->assertEquals($constraints, $this->object->getAlternativeConstraints());
	}

	public function testSetGetAndHasSimultaneousConstraints()
	{
		$constraints = array(
			$this->mockConstraint('constraint 1'),
			$this->mockConstraint('constraint 2')
		);
		$this->assertEquals(false, $this->object->hasSimultaneousConstraints());
		$this->assertEquals($this->object, $this->object->setSimultaneousConstraints(array_slice($constraints, 0, 1)));
		$this->assertEquals(true, $this->object->hasSimultaneousConstraints());
		$this->assertEquals(array_slice($constraints, 0, 1), $this->object->getSimultaneousConstraints());
		$this->assertEquals($this->object, $this->object->setSimultaneousConstraints(array_slice($constraints, 1, 1)));
		$this->assertEquals(true, $this->object->hasSimultaneousConstraints());
		$this->assertEquals(array_slice($constraints, 1, 1), $this->object->getSimultaneousConstraints());
	}

	public function testSetGetAndHasAlternativeConstraints()
	{
		$constraints = array(
			$this->mockConstraint('constraint 1'),
			$this->mockConstraint('constraint 2')
		);
		$this->assertEquals(false, $this->object->hasAlternativeConstraints());
		$this->assertEquals($this->object, $this->object->setAlternativeConstraints(array_slice($constraints, 0, 1)));
		$this->assertEquals(true, $this->object->hasAlternativeConstraints());
		$this->assertEquals(array_slice($constraints, 0, 1), $this->object->getAlternativeConstraints());
		$this->assertEquals($this->object, $this->object->setAlternativeConstraints(array_slice($constraints, 1, 1)));
		$this->assertEquals(true, $this->object->hasAlternativeConstraints());
		$this->assertEquals(array_slice($constraints, 1, 1), $this->object->getAlternativeConstraints());
	}

	public function testToString()
	{
		$simultaneousConstraints = array(
			$this->mockConstraint('simultaneous constraint 1'),
			$this->mockConstraint('simultaneous constraint 2')
		);
		$alternativeConstraints = array(
			$this->mockConstraint('alternative constraint 1'),
			$this->mockConstraint('alternative constraint 2')
		);
		$this->object->setSubject($this->mockField('field1'));
		$this->object->setComparator('=');
		$this->object->setValue($this->mockField('field2'));
		$this->object->setSimultaneousConstraints($simultaneousConstraints);
		$this->object->setAlternativeConstraints($alternativeConstraints);
		$expectedQuery = <<<EOT
field1 = field2
AND simultaneous constraint 1
AND simultaneous constraint 2
OR alternative constraint 1
OR alternative constraint 2
EOT;
		$this->assertEquals($expectedQuery, (string) $this->object);
		$this->object->wrap();
		$expectedQuery = <<<EOT
(field1 = field2
AND simultaneous constraint 1
AND simultaneous constraint 2
OR alternative constraint 1
OR alternative constraint 2)
EOT;
		$this->assertEquals($expectedQuery, (string) $this->object);
	}
}
