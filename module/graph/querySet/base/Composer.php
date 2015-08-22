<?php
namespace Sloth\Module\Graph\QuerySet\Base;

use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;

abstract class Composer
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

	abstract public function compose();

	public function setDatabase(DatabaseWrapper $database)
	{
		$this->database = $database;
		return $this;
	}

	public function setResource(Definition\Table $tableDefinition)
	{
		$this->tableDefinition = $tableDefinition;
		return $this;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}
}
