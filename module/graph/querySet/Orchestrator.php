<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\QuerySet\Base\Composer;
use Sloth\Module\Graph\QuerySet\Base\Conductor;
use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;

class Orchestrator
{
	/**
	 * @var DatabaseWrapper
	 */
	private $database;

	/**
	 * @var Composer
	 */
	private $composer;

	/**
	 * @var Conductor
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

	public function setComposer(Composer $composer)
	{
		$this->composer = $composer;
		return $this;
	}

	public function setConductor(Conductor $conductor)
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

	public function execute(Definition\Table $resourceDefinition, array $filters = array(), array $data = array())
	{
		if ($this->filterParser !== null) {
			$filters = $this->filterParser->parse($resourceDefinition, $filters);
		}
		$querySet = $this->composer
			->setDatabase($this->database)
			->setResource($resourceDefinition)
			->setFilters($filters)
			->compose();
		$data = $this->conductor
			->setDatabase($this->database)
			->setDataParser($this->dataParser)
			->setQuerySet($querySet)
			->setData($data)
			->conduct();
		$resourceData = $this->dataParser->formatResourceData($data, $resourceDefinition, $filters);
		return $resourceData;
	}
}
