<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Exception\InvalidResourceException;
use DemoGraph\Module\Graph\ResourceDefinition\Resource as GraphResource;
use DemoGraph\Module\Graph\DefinitionBuilder\ResourceDefinitionBuilder;
use Sloth\Exception\InvalidArgumentException;

class Link
{
	const MANY_TO_MANY = 'manyToMany';
	const MANY_TO_ONE = 'manyToOne';
	const ONE_TO_MANY = 'oneToMany';
	const ONE_TO_ONE = 'oneToOne';

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var GraphResource
	 */
	public $parentResource;

	/**
	 * @var string
	 */
	public $childResourceName;

	/**
	 * @var string
	 */
	public $type = Link::ONE_TO_ONE;

	/**
	 * @var TableList
	 */
	public $intermediaryTables;

	/**
	 * @var array
	 */
	public $joinManifest = array();

	/**
	 * @var LinkConstraintList
	 */
	public $constraints;

	/**
	 * @var GraphResource
	 */
	protected $childResource;

	/**
	 * @var ResourceDefinitionBuilder
	 */
	private $resourceBuilder;

	public function __construct(ResourceDefinitionBuilder $resourceBuilder)
	{
		$this->resourceBuilder = $resourceBuilder;
	}

	public function getConstraints()
	{
		if (!isset($this->constraints)) {
			$this->load();
		}
		return $this->constraints;
	}

	public function getChildResource()
	{
		if (!isset($this->childResource)) {
			$this->load();
		}
		return $this->childResource;
	}

	private function load()
	{
		$childResourceAlias = $this->buildUniqueTableAlias($this->name, $this->parentResource->getAlias());
		$this->childResource = $this->resourceBuilder->buildFromName($this->childResourceName, $childResourceAlias);
		$this->childResource->table->alias = $childResourceAlias;
		$this->constraints = $this->buildJoinList();
	}

	private function buildUniqueTableAlias($tableAlias, $parentAlias = null)
	{
		if ($parentAlias !== null) {
			$tableAlias = $parentAlias . '_' . $tableAlias;
		}
		return $tableAlias;
	}

	private function buildJoinList()
	{
		if (!empty($this->intermediaryTables)) {
			$joins = new LinkConstraintList();
			$joins->push($this->buildJoinViaIntermediary());
		} else {
			$joins = $this->buildDirectJoins();
		}
		return $joins;
	}

	private function buildJoinViaIntermediary()
	{
		$join = new LinkConstraint();
		$join->link = $this;

		$tables = new TableList();
		$tables->push($this->parentResource->table);
		$parentAlias = $this->parentResource->table->getAlias();
		foreach ($this->intermediaryTables as $table) {
			/** @var Table $table */
			$table->alias = $this->buildUniqueTableAlias($table->getAlias(), $parentAlias);
			$tables->push($table);
		}
		$tables->push($this->childResource->table);

		$join->subJoins = new TableJoinList();
		while ($tables->length() >= 2) {
			$firstTable = $tables->shift();
			$secondTable = $tables->getByIndex(0);
			$tablePair = new TableList();
			$tablePair->push($firstTable);
			$tablePair->push($secondTable);
			$join->subJoins->push($this->buildSubJoin($join, $tablePair));
		}

		$parentField = $this->getParentFieldFromSubJoins($join->subJoins);
		$childField = $this->getChildFieldFromSubJoins($join->subJoins);

		$join->parentAttribute = $this->parentResource->getAttributeByFieldName($parentField->name);
		$join->childAttribute = $this->childResource->getAttributeByFieldName($childField->name);

		return $join;
	}

	private function getParentFieldFromSubJoins(TableJoinList $joins)
	{
		$parentField = null;
		foreach ($joins as $join) {
			if ($join->parentTable->name === $this->parentResource->name) {
				$parentField = $join->parentField;
				break;
			} elseif ($join->childTable->name === $this->parentResource->name) {
				$parentField = $join->childField;
				break;
			}
		}
		if (is_null($parentField)) {
			throw new InvalidResourceException(
				'No parent field from resource found in sub-joins: ' . json_encode($joins)
			);
		}
		return $parentField;
	}

	private function getChildFieldFromSubJoins(TableJoinList $joins)
	{
		$childField = null;
		foreach ($joins as $join) {
			if ($join->parentTable->name === $this->childResource->table->name) {
				$childField = $join->parentField;
			} elseif ($join->childTable->name === $this->childResource->table->name) {
				$childField = $join->childField;
			}
		}
		if (is_null($childField)) {
			throw new InvalidResourceException(
				'No child field from resource found in sub-joins: ' . json_encode($joins)
			);
		}
		return $childField;
	}

	private function buildSubJoin(LinkConstraint $parentJoin, TableList $tables)
	{
		if ($tables->length() !== 2) {
			throw new InvalidArgumentException(
				'Attempted to join an invalid number of tables: ' . json_encode($tables)
			);
		}

		$firstTable = $tables->getByIndex(0);
		$secondTable = $tables->getByIndex(1);

		$join = new TableJoin();
		$join->parentJoin = $parentJoin;

		foreach ($this->joinManifest as $parentAlias => $childAlias) {
			$parentTableAlias = rtrim(strstr($parentAlias, '.', true), '.');
			$parentTableAlias = $this->getTableNameFromAlias($parentTableAlias);
			$parentAttributeAlias = ltrim(strstr($parentAlias, '.'), '.');

			$childTableAlias = rtrim(strstr($childAlias, '.', true), '.');
			$childTableAlias = $this->getTableNameFromAlias($childTableAlias);
			$childAttributeAlias = ltrim(strstr($childAlias, '.'), '.');

			if ($parentTableAlias === $firstTable->getAlias()) {
				$join->parentTable = $firstTable;
				$join->parentField = $this->buildTableField($firstTable, $parentAttributeAlias);
				$join->childTable = $secondTable;
				$join->childField = $this->buildTableField($secondTable, $childAttributeAlias);
				break;
			} elseif ($childTableAlias === $secondTable->getAlias()) {
				$join->childTable = $secondTable;
				$join->childField = $this->buildTableField($secondTable, $childAttributeAlias);
				$join->parentTable = $firstTable;
				$join->parentField = $this->buildTableField($firstTable, $parentAttributeAlias);
				break;
			}
		}

		return $join;
	}

	private function buildDirectJoins()
	{
		$joins = new LinkConstraintList();
		foreach ($this->joinManifest as $parentAlias => $childAlias) {
			$parentAttributeName = ltrim(strstr($parentAlias, '.'), '.');
			$parentAttribute = $this->parentResource->attributes->getByName($parentAttributeName);

			$childAttributeName = ltrim(strstr($childAlias, '.'), '.');
			$childAttribute = $this->childResource->attributes->getByName($childAttributeName);

			$join = new LinkConstraint();
			$join->link = $this;
			$join->parentAttribute = $parentAttribute;
			$join->childAttribute = $childAttribute;
			$joins->push($join);
		}
		return $joins;
	}

	private function buildTableField(Table $table, $fieldName)
	{
		$field = new TableField();
		$field->table = $table;
		$field->name = $fieldName;
		$field->alias = sprintf('%s.%s', $table->getAlias(), $fieldName);
		return $field;
	}

	private function getTableNameFromAlias($alias)
	{
		if ($alias === 'this') {
			$tableName = $this->parentResource->table->getAlias();
		} elseif ($alias === $this->name) {
			$tableName = $this->childResource->table->getAlias();
		} else {
			$tableName = $alias;
		}
		return $tableName;
	}
}
