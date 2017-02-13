<?php
namespace Celestial\Module\Data\TableQuery;

use Celestial\App;
use PhpMySql\DatabaseWrapper;

class TableQueryModule
{
	/**
	 * @var App
	 */
	private $app;

	/**
	 * @var DatabaseWrapper
	 */
	private $database;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function setDatabase(DatabaseWrapper $database)
	{
		$this->database = $database;
		return $this;
	}

	public function getDatabase()
	{
		return $this->database;
	}

	public function getBy()
	{
		$orchestrator = new QuerySet\Orchestrator();
		$orchestrator->setDatabase($this->database)
				->setFilterParser(new QuerySet\Filter\FilterParser())
				->setDataParser(new QuerySet\DataParser())
				->setComposer(new QuerySet\Composer\GetByComposer())
				->setConductor(new QuerySet\Conductor\GetByConductor());
		return $orchestrator;
	}

	public function search()
	{
		$orchestrator = new QuerySet\Orchestrator();
		$orchestrator->setDatabase($this->database)
				->setFilterParser(new QuerySet\Filter\ComplexFilterParser())
				->setDataParser(new QuerySet\DataParser())
				->setComposer(new QuerySet\Composer\GetByComposer())
				->setConductor(new QuerySet\Conductor\GetByConductor());
		return $orchestrator;
	}

	public function insert()
	{
		$orchestrator = new QuerySet\Orchestrator();
		$orchestrator->setDatabase($this->database)
				->setDataParser(new QuerySet\DataParser())
				->setComposer(new QuerySet\Composer\InsertComposer())
				->setConductor(new QuerySet\Conductor\InsertConductor());
		return $orchestrator;
	}

	public function update()
	{
		$orchestrator = new QuerySet\Orchestrator();
		$orchestrator->setDatabase($this->database)
				->setDataParser(new QuerySet\DataParser())
				->setComposer(new QuerySet\Composer\UpdateComposer())
				->setConductor(new QuerySet\Conductor\UpdateConductor());
		return $orchestrator;
	}

	public function delete()
	{
		$orchestrator = new QuerySet\Orchestrator();
		$orchestrator->setDatabase($this->database)
				->setDataParser(new QuerySet\DataParser())
				->setComposer(new QuerySet\Composer\DeleteComposer())
				->setConductor(new QuerySet\Conductor\DeleteConductor());
		return $orchestrator;
	}
}
