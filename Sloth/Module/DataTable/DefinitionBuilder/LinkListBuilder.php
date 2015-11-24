<?php
namespace Sloth\Module\DataTable\DefinitionBuilder;

use Sloth\Module\DataTable\Definition;

class LinkListBuilder
{
	/**
	 * @var TableBuilder
	 */
	private $tableBuilder;

	public function __construct(TableBuilder $tableBuilder)
	{
		$this->tableBuilder = $tableBuilder;
	}

	public function build(Definition\Table $table, \stdClass $linksManifest)
	{
		$links = new Definition\Table\JoinList();
		foreach ($linksManifest as $name => $linkManifest) {
			$link = new Definition\Table\Join($this->tableBuilder);
			$link->name = $name;
			if (property_exists($linkManifest, 'via')) {
				$link->intermediaryTables = $this->buildIntermediaryTables($linkManifest->via);
			}
			$link->type = $linkManifest->type;
			if (property_exists($linkManifest, 'onInsert')) {
				$link->onInsert = $linkManifest->onInsert;
			}
			if (property_exists($linkManifest, 'onUpdate')) {
				$link->onUpdate = $linkManifest->onUpdate;
			}
			if (property_exists($linkManifest, 'onDelete')) {
				$link->onDelete = $linkManifest->onDelete;
			}
			$link->parentTable = $table;
			$link->childTableName = $linkManifest->table;
			$link->joinManifest = $linkManifest->joins;
			$links->push($link);
		}
		return $links;
	}

	private function buildIntermediaryTables(\stdClass $manifest)
	{
		$tables = new Definition\TableList();
		foreach ($manifest as $alias => $tableName) {
			$tableManifest = (object)array(
				'name' => $tableName
			);
			$table = $this->tableBuilder->buildFromManifest($tableManifest, $alias);
			$tables->push($table);
		}
		return $tables;
	}
}
