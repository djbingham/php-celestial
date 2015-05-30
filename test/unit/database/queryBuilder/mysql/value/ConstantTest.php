<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Value;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Database\QueryBuilder\MySql\Value\Constant;
use Sloth\Test\Unit\Database\UnitTest;

/**
 * SqlFunctionTest
 * @package Test\Unit\Database\QueryBuilder\MySql\Value
 * @author Favicon, 2013
 */
class ConstantTest extends UnitTest
{
	/**
	 * @var Number
	 */
	protected $object;

	public function setup()
	{
		$this->object = new Constant();
	}

	public function testSetValue()
	{
		$this->object->setValue('NULL');
		$this->assertEquals('NULL', (string)$this->object);
		$this->object->setValue('CURRENT_TIMESTAMP');
		$this->assertEquals('CURRENT_TIMESTAMP', (string)$this->object);
	}

	public function testSetValueFailsIfValueIsInvalidString()
	{
		$value = 'Not a constant';
		$this->setExpectedException('\Exception');
		$this->object->setValue($value);
	}

	public function testSetValueFailsIfValueIsNumber()
	{
		$value = 1;
		$this->setExpectedException('\Exception');
		$this->object->setValue($value);
	}

	public function testSetValueFailsIfValueIsArray()
	{
		$value = array();
		$this->setExpectedException('\Exception');
		$this->object->setValue($value);
	}

	public function testSetValueFailsIfValueIsObject()
	{
		$value = $this;
		$this->setExpectedException('\Exception');
		$this->object->setValue($value);
	}
}
