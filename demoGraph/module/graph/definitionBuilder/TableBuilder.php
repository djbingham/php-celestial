<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;

class TableBuilder
{
	/**
	 * @var array
	 */
	private $cache = array();

	public function build(array $tableManifest)
	{
		$table = $this->getCachedTable($tableManifest['alias']);
		if (is_null($table)) {
			$table = new ResourceDefinition\Table();
			$table->name = $tableManifest['name'];
			$table->alias = $tableManifest['alias'];
			$this->cacheTable($table);
		}
		return $table;
	}

	private function cacheTable(ResourceDefinition\Table $table)
	{
		if (array_key_exists('tables', $this->cache)) {
			$this->cache['tables'] = array();
		}
		$this->cache['tables'][$table->getAlias()] = $table;
	}

	/**
	 * @param string $tableAlias
	 * @return ResourceDefinition\Table
	 */
	private function getCachedTable($tableAlias)
	{
		if (!array_key_exists('tables', $this->cache)) {
			return null;
		}
		if (!array_key_exists($tableAlias, $this->cache['tables'])) {
			return null;
		}
		return $this->cache['tables'][$tableAlias];
	}
}
