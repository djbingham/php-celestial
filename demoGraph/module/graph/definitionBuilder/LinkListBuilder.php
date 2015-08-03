<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;

class LinkListBuilder
{
	/**
	 * @var ResourceDefinitionBuilder
	 */
	private $resourceBuilder;

	public function __construct(ResourceDefinitionBuilder $resourceBuilder)
	{
		$this->resourceBuilder = $resourceBuilder;
	}

	public function build(ResourceDefinition\Resource $resource, array $linksManifest)
	{
		$links = new ResourceDefinition\LinkList();
		foreach ($linksManifest as $name => $linkManifest) {
			$link = new ResourceDefinition\Link($this->resourceBuilder);
			$link->name = $name;
			if (array_key_exists('via', $linkManifest)) {
				$link->intermediaryTables = $this->buildTableList($linkManifest['via']);
			}
			$link->type = $linkManifest['type'];
			$link->parentResource = $resource;
			$link->childResourceName = $linkManifest['resource'];
			$link->joinManifest = $linkManifest['joins'];
			$links->push($link);
		}
		return $links;
	}

	private function buildTableList(array $manifest)
	{
		$tables = new ResourceDefinition\TableList();
		foreach ($manifest as $alias => $tableName) {
			$table = new ResourceDefinition\Table();
			$table->alias = $alias;
			$table->name = $tableName;
			$tables->push($table);
		}
		return $tables;
	}
}
