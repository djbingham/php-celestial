<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Base;

use Sloth\Module\Data\TableQuery\QuerySet\DataParser;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use PhpMySql\DatabaseWrapper;

abstract class AbstractConductor
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
	 * @var MultiQueryWrapper
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

	public function setQuerySet(MultiQueryWrapper $querySet)
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
