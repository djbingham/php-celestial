<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Query;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Database\QueryBuilder\MySql\Query\Insert;
use Sloth\Test\Unit\Database\UnitTest;

/**
 * InsertTest
 * @author Favicon, 2013
 */
class InsertTest extends UnitTest
{
	/**
	 * @var Insert
	 */
	protected $object;

	public function setup()
	{
		$this->object = new Insert();
	}

	protected function mockTable($tableName)
	{
		$table = $this->mockBuilder()->queryValue('Table');
		$table->expects($this->any())
			->method('__toString')
			->will($this->returnValue($tableName));
		return $table;
	}

	protected function mockData(array $fields, array $values)
	{
		$data = $this->mockBuilder()->queryValue('Table\\Data');
		$data->expects($this->any())
			->method('getFields')
			->will($this->returnValue($fields));
		$data->expects($this->any())
			->method('getRows')
			->will($this->returnValue($values));
		return $data;
	}

	public function testIntoReturnsInsertInstance()
	{
		$table = $this->mockTable('`TableName`');
		$output = $this->object->into($table);
		$this->assertEquals($this->object, $output);
	}

	public function testDataReturnsInsertInstance()
	{
		$data = $this->mockData(array('`TableName`.`field1`'), array(array('"value1"')));
		$output = $this->object->data($data);
		$this->assertEquals($this->object, $output);
	}

	public function testToString()
	{
		$table = $this->mockTable('`TableName`');
		$fields = array('`TableName`.`field1`', '`TableName`.`field2`', '`TableName`.`field3`');
		$values = array(
			array('"value11"', '"value12"', 'NULL'),
			array('"value21"', 'NULL', '"value23"')
		);
		$mockData = $this->mockData($fields, $values);
		$this->object->into($table)
			->data($mockData);
		$expectedString = <<<EOT
INSERT INTO `TableName`
(`field1`,`field2`,`field3`)
VALUES
("value11","value12",NULL),
("value21",NULL,"value23")
EOT;
		$this->assertInstanceOf('\Database\QueryBuilder\MySql\Query\Insert', $this->object);
		$this->assertEquals($expectedString, (string)$this->object);
	}
}
