<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\QuerySet\Base\AbstractComposer;
use Sloth\Module\Graph\QuerySet\Base\AbstractConductor;
use Sloth\Module\Graph\Definition;
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
	 * @var FilterParser
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

	public function execute(Definition\Table $tableDefinition, array $filters = array(), array $data = array())
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
