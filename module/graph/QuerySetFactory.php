<?php
namespace Sloth\Module\Graph;

use Sloth\Module\Graph\QuerySet;
use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;

class QuerySetFactory
{
	/**
	 * @var DatabaseWrapper
	 */
	private $database;

	public function setDatabase(DatabaseWrapper $database)
	{
		$this->database = $database;
		return $this;
	}

	public function getBy()
	{
		$orchestrator = new QuerySet\Orchestrator();
		$orchestrator->setDatabase($this->database)
			->setFilterParser(new QuerySet\FilterParser())
			->setDataParser(new QuerySet\DataParser())
			->setComposer(new QuerySet\GetBy\Composer())
			->setConductor(new QuerySet\GetBy\Conductor());
		return $orchestrator;
	}

	public function search()
	{
		$orchestrator = new QuerySet\Orchestrator();
		$orchestrator->setDatabase($this->database)
			->setFilterParser(new QuerySet\ComplexFilterParser())
			->setDataParser(new QuerySet\DataParser())
			->setComposer(new QuerySet\GetBy\Composer())
			->setConductor(new QuerySet\GetBy\Conductor());
		return $orchestrator;
	}

	public function insertRecord()
	{
//		return new QuerySet\InsertRecord($this->queryFactory, $this->attributeMapper);
	}
}
