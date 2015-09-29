<?php
namespace Sloth\Module\Resource\DefinitionBuilder;

use Sloth\Module\Resource\Definition;

class LinkListBuilder
{
	/**
	 * @var TableDefinitionBuilder
	 */
	private $tableBuilder;

	public function __construct(TableDefinitionBuilder $tableBuilder)
	{
		$this->tableBuilder = $tableBuilder;
	}

	public function build(Definition\Table $table, array $linksManifest)
	{
		$links = new Definition\Table\JoinList();
		foreach ($linksManifest as $name => $linkManifest) {
			$link = new Definition\Table\Join($this->tableBuilder);
			$link->name = $name;
			if (array_key_exists('via', $linkManifest)) {
				$link->intermediaryTables = $this->buildIntermediaryTables($linkManifest['via']);
			}
			$link->type = $linkManifest['type'];
			if (array_key_exists('onInsert', $linkManifest)) {
				$link->onInsert = $linkManifest['onInsert'];
			}
			if (array_key_exists('onUpdate', $linkManifest)) {
				$link->onUpdate = $linkManifest['onUpdate'];
			}
			if (array_key_exists('onDelete', $linkManifest)) {
				$link->onDelete = $linkManifest['onDelete'];
			}
			$link->parentTable = $table;
			$link->childTableName = $linkManifest['table'];
			$link->joinManifest = $linkManifest['joins'];
			$links->push($link);
		}
		return $links;
	}

	private function buildIntermediaryTables(array $manifest)
	{
		$tables = new Definition\TableList();
		foreach ($manifest as $alias => $tableName) {
			$tableManifest = array(
				'name' => $tableName
			);
			$table = $this->tableBuilder->buildFromManifest($tableManifest, $alias);
			$tables->push($table);
		}
		return $tables;
	}
}
