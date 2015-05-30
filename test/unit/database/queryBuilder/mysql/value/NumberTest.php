<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Value;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Database\QueryBuilder\MySql\Value\Number;
use Sloth\Test\Unit\Database\UnitTest;

/**
 * SqlFunctionTest
 * @package Test\Unit\Database\QueryBuilder\MySql\Value
 * @author Favicon, 2013
 */
class NumberTest extends UnitTest
{
	/**
	 * @var Number
	 */
	protected $object;

	public function setup()
	{
		$this->object = new Number();
	}

	public function testSetValueFailsIfValueIsString()
	{
		$value = 'Not a number';
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
