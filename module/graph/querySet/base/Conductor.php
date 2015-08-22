<?php
namespace Sloth\Module\Graph\QuerySet\Base;

use Sloth\Module\Graph\QuerySet\DataParser;
use Sloth\Module\Graph\QuerySet\QuerySet;
use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;

abstract class Conductor
{
	/**
	 * @var DatabaseWrapper
	 */
	protected $database;

	/**
	 * @var DataParser
	 */
	protected $dataParser;

	/**
	 * @var QuerySet
	 */
	protected $querySetToExecute;

	/**
	 * @var array
	 */
	protected $data = array();

	abstract public function conduct();

	public function setDatabase(DatabaseWrapper $database)
	{
		$this->database = $database;
		return $this;
	}

	public function setDataParser(DataParser $dataParser)
	{
		$this->dataParser = $dataParser;
		return $this;
	}

	public function setQuerySet(QuerySet $querySet)
	{
		$this->querySetToExecute = $querySet;
		return $this;
	}

	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}
}
