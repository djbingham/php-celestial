<?php
namespace Sloth\Module\Resource\QuerySet\Base;

use Sloth\Module\Resource\Definition;
use SlothMySql\DatabaseWrapper;

abstract class AbstractComposer
{
	/**
	 * @var DatabaseWrapper
	 */
	protected $database;

	/**
	 * @var Definition\Table
	 */
	protected $tableDefinition;

	/**
	 * @var array
	 */
	protected $filters = array();

	/**
	 * @var array
	 */
	protected $data = array();

	abstract public function compose();

	public function setDatabase(DatabaseWrapper $database)
	{
		$this->database = $database;
		return $this;
	}

	public function setTable(Definition\Table $tableDefinition)
	{
		$this->tableDefinition = $tableDefinition;
		return $this;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}

	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}
}