<?php

namespace Sloth\Test\Unit\Database;

use Sloth;

class MockBuilder extends \PHPUnit_Framework_TestCase
{
	public function databaseWrapper()
	{
		return $this->getMockBuilder('Database\DatabaseWrapper')
			->disableOriginalConstructor()
			->getMock();
	}

    public function connectionWrapper()
	{
		return $this->getMockBuilder('Database\_ConnectionWrapper')
			->disableOriginalConstructor()
			->getMock();
	}

	public function connectionOptions()
	{
		return $this->getMockBuilder('Database\Connector\ConnectionOptions')
			->disableOriginalConstructor()
			->getMock();
	}

	public function mySqlQuery($type)
	{
		return $this->getMockBuilder(sprintf('Database\QueryBuilder\MySql\Query\%s', $type))
			->disableOriginalConstructor()
			->getMock();
	}

	public function queryConstraint()
	{
		return $this->getMockBuilder('Database\QueryBuilder\MySql\Query\Constraint')
			->disableOriginalConstructor()
			->getMock();
	}

	public function queryJoin()
	{
		return $this->getMockBuilder('Database\QueryBuilder\MySql\Query\Join')
			->disableOriginalConstructor()
			->getMock();
	}

	public function queryValue($type = null)
	{
		$class = sprintf('Database\QueryBuilder\MySql\_MySqlValue');
		if (!is_null($type)) {
			$class = sprintf('Database\QueryBuilder\MySql\Value\%s', $type);
		}
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @return Sloth\Factory\Database\MySqli
	 */
	public function mySqliFactory()
	{
		return $this->getMockBuilder('Sloth\Factory\Database\MySqli')
			->disableOriginalConstructor()
			->getMock();
	}

	public function mySqlValueFactory()
	{
		return $this->getMockBuilder('Sloth\Factory\Database\MySqli\QueryBuilder\Value')
			->disableOriginalConstructor()
			->getMock();
	}

	public function mySqlQueryBuilderFactory()
	{
		return $this->getMockBuilder('Sloth\Factory\Database\MySqli\QueryBuilder')
			->disableOriginalConstructor()
			->getMock();
	}

	public function mySqlQueryFactory()
	{
		return $this->getMockBuilder('Sloth\Factory\Database\MySqli\QueryBuilder\Query')
			->disableOriginalConstructor()
			->getMock();
	}
}
