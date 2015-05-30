<?php

namespace Sloth\Test\Unit\Database\QueryBuilder\MySql\Value;

require_once dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/bootstrap.php';

use Database\QueryBuilder\MySql\Value\SqlFunction;
use Sloth\Test\Unit\Database\UnitTest;

/**
 * SqlFunctionTest
 * @package Test\Unit\Database\QueryBuilder\MySql\Value
 * @author Favicon, 2013
 */
class SqlFunctionTest extends UnitTest
{
	/**
	 * @var SqlFunction
	 */
	protected $object;

	public function setup()
	{
		$this->object = new SqlFunction();
	}

	public function testSetParamsFailsIfParamsContainsString()
	{
		$params = array('Not a MySqlValue');
		$this->setExpectedException('\Exception');
		$this->object->setParams($params);
	}

	public function testSetParamsFailsIfParamsContainsNumber()
	{
		$params = array(1);
		$this->setExpectedException('\Exception');
		$this->object->setParams($params);
	}

	public function testSetParamsFailsIfParamsContainsArray()
	{
		$params = array(array());
		$this->setExpectedException('\Exception');
		$this->object->setParams($params);
	}

	public function testSetParamsFailsIfParamsContainsNonMySqlValueObject()
	{
		$params = array($this);
		$this->setExpectedException('\Exception');
		$this->object->setParams($params);
	}
}
