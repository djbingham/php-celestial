<?php
namespace DemoGraph\Module\Graph\QueryComponentBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;
use DemoGraph\Module\Graph\QueryComponent;

class TableBuilder
{
	/**
	 * @var array
	 */
	private $cache = array();

	/**
	 * @param ResourceDefinition\Table $tableDefinition
	 * @return QueryComponent\Table
	 */
	public function getByDefinition(ResourceDefinition\Table $tableDefinition)
	{
		$table = $this->getCachedByAlias($tableDefinition->getAlias());
		if (is_null($table)) {
			$table = new QueryComponent\Table();
			$table->setDefinition($tableDefinition);
			$this->cacheTable($table);
		}
		return $table;
	}

	/**
	 * @param string $alias
	 * @return QueryComponent\Table
	 */
	public function getCachedByAlias($alias)
	{
		if (array_key_exists($alias, $this->cache)) {
			$queryTable = $this->cache[$alias];
		} else {
			$queryTable = null;
		}
		return $queryTable;
	}

	private function cacheTable(QueryComponent\Table $table)
	{
		$this->cache[$table->getDefinition()->getAlias()] = $table;
		return $this;
	}
}
