<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Value\Table;

require_once dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/bootstrap.php';

use Database\QueryBuilder\MySql\Value\Constant;
use Database\QueryBuilder\MySql\Value\Table\Data;
use Sloth\Test\Unit\Database\UnitTest;

/**
 * DataTest
 * @package Test\Unit\Database\QueryBuilder\MySql\Value\table
 * @author Favicon, 2013
 */
class DataTest extends UnitTest
{
	/**
	 * @var Data
	 */
	protected $object;

	public function setup()
	{
		$this->object = new Data();
	}

	protected function mockField($tableName, $fieldName)
	{
		$mock = $this->mockBuilder()->queryValue('Table\Field');
		$mock->expects($this->any())
			->method('__toString')
			->will($this->returnValue(sprintf('`%s`.`%s`', $tableName, $fieldName)));
		return $mock;
	}

	protected function mockValue($value)
	{
		$mock = $this->mockBuilder()->queryValue('Table\Field');
		$mock->expects($this->any())
			->method('__toString')
			->will($this->returnValue($value));
		return $mock;
	}

	protected function assertNullValue($value)
	{
		$this->assertInstanceOf('\Database\QueryBuilder\MySql\Value\Constant', $value);
		$this->assertEquals('NULL', (string)$value);
	}

	public function testSetAndGetNullValue()
	{
		$value = new Constant();
		$setterOutput = $this->object->setNullValue($value);
		$this->assertEquals($this->object, $setterOutput);
		$this->assertEquals($value, $this->object->getNullValue());
	}

	public function testBeginRowAndGetCurrentRowIndex()
	{
		$output = $this->object->beginRow();
		$this->assertEquals($this->object, $output);
		// Check the current row index was updated
		$this->assertEquals(0, $this->object->getCurrentRowIndex());
	}

	public function testBeginRowCanBeCalledWithSpecificRowIndex()
	{
		$output = $this->object->beginRow(2);
		$this->assertEquals($this->object, $output);
		// Check the current row index was updated
		$this->assertEquals(2, $this->object->getCurrentRowIndex());
	}

	public function testBeginRowRejectsNonInteger()
	{
		$this->setExpectedException('\Exception');
		$this->object->beginRow(1.2);
	}

	public function testSetOneFieldAndGetRow()
	{
		$field = $this->mockField('TableName', 'FieldName');
		$value = $this->mockValue('"value string"');
		$setOutput = $this->object->set($field, $value);
		$this->assertEquals($this->object, $setOutput);
		$getterOutput = $this->object->getCurrentRow();
		$this->assertEquals(array_combine(array($field), array($value)), $getterOutput);
	}

	public function testSetTwoFieldsAndGetRow()
	{
		$fields = array(
			$this->mockField('TableName', 'FieldName1'),
			$this->mockField('TableName', 'FieldName2')
		);
		$values = array(
			$this->mockValue('"value string 1"'),
			$this->mockValue('"value string 2"')
		);
		$this->assertEquals($this->object, $this->object->set($fields[0], $values[0]));
		$this->assertEquals($this->object, $this->object->set($fields[1], $values[1]));
		$getterOutput = $this->object->getCurrentRow();
		$this->assertEquals(array_combine($fields, $values), $getterOutput);
	}

	public function testSetWithExistingFieldAssignsValueToCorrectColumnInCurrentRowValues()
	{
		$fields = array(
			$this->mockField('TableName', 'FieldName1'),
			$this->mockField('TableName', 'FieldName2'),
			$this->mockField('TableName', 'FieldName1')
		);
		$values = array(
			$this->mockValue('"value string 1"'),
			$this->mockValue('"value string 2"'),
			$this->mockValue('"value string 3"'),
		);
		// Set two values and check the row
		$this->assertEquals($this->object, $this->object->set($fields[0], $values[0]));
		$this->assertEquals($this->object, $this->object->set($fields[1], $values[1]));
		$expectedRow = array_combine(array_slice($fields, 0, 2), array_slice($values, 0, 2));
		$this->assertEquals($expectedRow, $this->object->getCurrentRow());
		// Set the first field to a new value and check it has overwritten the original value
		$this->assertEquals($this->object, $this->object->set($fields[0], $values[2]));
		$expectedRow = array_combine(array_slice($fields, 0, 2), array($values[0], $values[2]));
		$this->assertEquals($expectedRow, $this->object->getCurrentRow());
	}

	public function testEndRowAndGetFieldsAndRows()
	{
		$fields = array(
			$this->mockField('TableName', 'FieldName1'),
			$this->mockField('TableName', 'FieldName2')
		);
		$values = array(
			$this->mockValue('"value string 1"'),
			$this->mockValue('"value string 2"')
		);
		$this->object->beginRow();
		$this->object->set($fields[0], $values[0]);
		$this->object->set($fields[1], $values[1]);
		$this->object->endRow();
		// Test getFields and getRows return correct field and value instances
		$this->assertEquals($fields, $this->object->getFields());
		$this->assertEquals(array(array_combine($fields, $values)), $this->object->getRows());
	}

	public function testEndRowTwiceWithSameFields()
	{
		$fields = array(
			$this->mockField('TableName', 'FieldName1'),
			$this->mockField('TableName', 'FieldName2')
		);
		$values = array(
			array(
				$this->mockValue('"value string 1 1"'),
				$this->mockValue('"value string 1 2"')
			),
			array(
				$this->mockValue('"value string 2 1"'),
				$this->mockValue('"value string 2 2"')
			)
		);
		$this->object->beginRow();
		$this->assertEquals(0, $this->object->getCurrentRowIndex());
		$this->object->set($fields[0], $values[0][0]);
		$this->object->set($fields[1], $values[0][1]);
		$this->object->endRow();
		$this->object->beginRow();
		$this->assertEquals(1, $this->object->getCurrentRowIndex());
		$this->object->set($fields[0], $values[1][0]);
		$this->object->set($fields[1], $values[1][1]);
		$this->object->endRow();
		// Test getFields and getRows return correct field and value instances
		$this->assertEquals($fields, $this->object->getFields());
		$expectedRows = array(
			array_combine($fields, $values[0]),
			array_combine($fields, $values[1])
		);
		$this->assertEquals($expectedRows, $this->object->getRows());
	}

	public function testEndRowTwiceWithDifferentFields()
	{
		$dbWrapper = $this->mockBuilder()->databaseWrapper();
		$dbWrapper->expects($this->any())
			->method('escapeString')
			->will($this->returnValue('NULL'));
		$nullValue = new Constant();
		$nullValue->setDatabaseWrapper($dbWrapper);
		$nullValue->setValue('NULL');
		$this->object->setNullValue($nullValue);
		$fields = array(
			$this->mockField('TableName', 'FieldName1'),
			$this->mockField('TableName', 'FieldName2'),
			$this->mockField('TableName', 'FieldName3')
		);
		$values = array(
			array(
				$this->mockValue('"value string 1 1"'),
				$this->mockValue('"value string 1 2"')
			),
			array(
				$this->mockValue('"value string 2 1"'),
				$this->mockValue('"value string 2 2"')
			)
		);
		$this->object->beginRow();
		$this->assertEquals(0, $this->object->getCurrentRowIndex());
		$this->object->set($fields[0], $values[0][0]);
		$this->object->set($fields[1], $values[0][1]);
		$this->object->endRow();
		$this->object->beginRow();
		$this->assertEquals(1, $this->object->getCurrentRowIndex());
		$this->object->set($fields[0], $values[1][0]);
		$this->object->set($fields[2], $values[1][1]);
		$this->object->endRow();
		// Test getFields and getRows return correct field and value instances
		$this->assertEquals($fields, $this->object->getFields());
		$outputRows = $this->object->getRows();
		$this->assertEquals($values[0][0], $outputRows[0][(string)$fields[0]]);
		$this->assertEquals($values[0][1], $outputRows[0][(string)$fields[1]]);
		$this->assertNullValue($outputRows[0][(string)$fields[2]]);
		$this->assertEquals($values[0][0], $outputRows[0][(string)$fields[0]]);
		$this->assertNullValue($outputRows[1][(string)$fields[1]]);
		$this->assertEquals($values[1][1], $outputRows[1][(string)$fields[2]]);
	}

	public function testCanSetRowValuesForExistingRow()
	{
		$dbWrapper = $this->mockBuilder()->databaseWrapper();
		$dbWrapper->expects($this->any())
			->method('escapeString')
			->will($this->returnValue('NULL'));
		$nullValue = new Constant();
		$nullValue->setDatabaseWrapper($dbWrapper);
		$nullValue->setValue('NULL');
		$this->object->setNullValue($nullValue);
		$fields = array(
			$this->mockField('TableName', 'FieldName1'),
			$this->mockField('TableName', 'FieldName2'),
			$this->mockField('TableName', 'FieldName3')
		);
		$values = array(
			array(
				$this->mockValue('"value string 1 1"'),
				$this->mockValue('"value string 1 2"')
			),
			array(
				$this->mockValue('"value string 2 1"'),
				$this->mockValue('"value string 2 2"')
			),
			array(
				$this->mockValue('"value string 3 1"')
			)
		);
		$this->object->beginRow();
		$this->assertEquals(0, $this->object->getCurrentRowIndex());
		$this->object->set($fields[0], $values[0][0]);
		$this->object->set($fields[1], $values[0][1]);
		$this->object->endRow();
		$this->object->beginRow();
		$this->assertEquals(1, $this->object->getCurrentRowIndex());
		$this->object->set($fields[0], $values[1][0]);
		$this->object->set($fields[2], $values[1][1]);
		$this->object->endRow();
		// Change row zero's values
		$this->object->beginRow(0);
		$this->assertEquals(0, $this->object->getCurrentRowIndex());
		$this->object->set($fields[0], $values[2][0]);
		$this->object->endRow();
		// Test getFields and getRows return correct field and value instances for row zero
		$this->assertEquals($fields, $this->object->getFields());
		$outputRows = $this->object->getRows();
		$this->assertEquals(2, count($outputRows));
		$this->assertEquals($values[2][0], $outputRows[0][(string)$fields[0]]);
		$this->assertEquals($values[0][1], $outputRows[0][(string)$fields[1]]);
		$this->assertNullValue($outputRows[0][(string)$fields[2]]);
	}

	public function testSetAndGetDatabaseWrapper()
	{
		$dbWrapper = $this->mockBuilder()->databaseWrapper();
		$setterOutput = $this->object->setDatabaseWrapper($dbWrapper);
		$this->assertEquals($this->object, $setterOutput);
		$this->assertEquals($dbWrapper, $this->object->getDatabaseWrapper());
	}

	public function testAppendRow()
	{

	}

	public function testAppendRows()
	{

	}
}
