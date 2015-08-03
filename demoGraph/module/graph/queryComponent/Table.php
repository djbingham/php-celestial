<?php
namespace DemoGraph\Module\Graph\QueryComponent;

use DemoGraph\Module\Graph\ResourceDefinition\Table as TableDefinition;

class Table
{
	/**
	 * @var TableDefinition
	 */
	private $tableDefinition;

	/**
	 * @var TableJoinList
	 */
	private $joinList;

	public function setDefinition(TableDefinition $tableDefinition)
	{
		$this->tableDefinition = $tableDefinition;
		return $this;
	}

	public function getDefinition()
	{
		return $this->tableDefinition;
	}

	public function setJoins(TableJoinList $joinList)
	{
		$this->joinList = $joinList;
		return $this;
	}

	public function getJoins()
	{
		return $this->joinList;
	}
}
