<?php
namespace Sloth\Module\Data\TableQuery\QuerySet;

use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Base\AbstractComposer;
use Sloth\Module\Data\TableQuery\QuerySet\Base\AbstractConductor;
use Sloth\Module\Data\TableQuery\QuerySet\Face\FilterParserInterface;
use SlothMySql\DatabaseWrapper;

class Orchestrator
{
	/**
	 * @var DatabaseWrapper
	 */
	private $database;

	/**
	 * @var AbstractComposer
	 */
	private $composer;

	/**
	 * @var AbstractConductor
	 */
	private $conductor;

	/**
	 * @var FilterParserInterface
	 */
	private $filterParser;

	/**
	 * @var DataParser
	 */
	private $dataParser;

	public function setDatabase(DatabaseWrapper $database)
	{
		$this->database = $database;
		return $this;
	}

	public function setComposer(AbstractComposer $composer)
	{
		$this->composer = $composer;
		return $this;
	}

	public function setConductor(AbstractConductor $conductor)
	{
		$this->conductor = $conductor;
		return $this;
	}

	public function setFilterParser(FilterParserInterface $filterParser)
	{
		$this->filterParser = $filterParser;
		return $this;
	}

	public function setDataParser(DataParser $dataParser)
	{
		$this->dataParser = $dataParser;
		return $this;
	}

	public function execute(TableInterface $tableDefinition, array $filters = array(), array $data = array())
	{
		if ($this->filterParser !== null) {
			$filters = $this->filterParser->parse($tableDefinition, $filters);
		}

		$querySet = $this->composer
			->setDatabase($this->database)
			->setTable($tableDefinition)
			->setFilters($filters)
			->setData($data)
			->compose();

		$data = $this->conductor
			->setDatabase($this->database)
			->setDataParser($this->dataParser)
			->setQuerySet($querySet)
			->setData($data)
			->conduct();

		$resourceData = $this->dataParser->formatResourceData($data, $tableDefinition, $filters);

		return $resourceData;
	}
}
