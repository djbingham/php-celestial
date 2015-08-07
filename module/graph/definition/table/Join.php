<?php
namespace Sloth\Module\Graph\Definition\Table;

use Sloth\Module\Graph\Definition\Table;
use Sloth\Module\Graph\Definition\Table\Join\Constraint;
use Sloth\Module\Graph\Definition\Table\Join\ConstraintList;
use Sloth\Module\Graph\Definition\Table\Join\SubJoin;
use Sloth\Module\Graph\Definition\Table\Join\SubJoinList;
use Sloth\Module\Graph\Definition\TableList;
use Sloth\Module\Graph\Exception\InvalidTableException;
use Sloth\Module\Graph\DefinitionBuilder\TableDefinitionBuilder;
use Sloth\Exception\InvalidArgumentException;

class Join
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
	 * @var Table
	 */
	public $parentTable;

	/**
	 * @var string
	 */
	public $childTableName;

	/**
	 * @var string
	 */
	public $type = Join::ONE_TO_ONE;

	/**
	 * @var TableList
	 */
	public $intermediaryTables;

	/**
	 * @var array
	 */
	public $joinManifest = array();

	/**
	 * @var ConstraintList
	 */
	public $constraints;

	/**
	 * @var Table
	 */
	protected $childTable;

	/**
	 * @var TableDefinitionBuilder
	 */
	private $tableBuilder;

	public function __construct(TableDefinitionBuilder $tableBuilder)
	{
		$this->tableBuilder = $tableBuilder;
	}

	public function getConstraints()
	{
		if (!isset($this->constraints)) {
			$this->load();
		}
		return $this->constraints;
	}

	public function getChildTable()
	{
		if (!isset($this->childTable)) {
			$this->load();
		}
		return $this->childTable;
	}

	private function load()
	{
		$childTableAlias = $this->buildUniqueTableAlias($this->name, $this->parentTable->getAlias());
		$this->childTable = $this->tableBuilder->buildFromName($this->childTableName, $childTableAlias);
		$this->constraints = $this->buildJoinList();
	}

	private function buildUniqueTableAlias($defaultAlias, $parentAlias = null)
	{
		if ($parentAlias !== null) {
			$defaultAlias = $parentAlias . '_' . $defaultAlias;
		}
		return $defaultAlias;
	}

	private function buildJoinList()
	{
		if (!empty($this->intermediaryTables)) {
			$joins = new ConstraintList();
			$joins->push($this->buildJoinViaIntermediary());
		} else {
			$joins = $this->buildDirectJoins();
		}
		return $joins;
	}

	private function buildJoinViaIntermediary()
	{
		$join = new Constraint();
		$join->link = $this;

		$tables = new TableList();
		$tables->push($this->parentTable);
		$parentAlias = $this->parentTable->getAlias();
		foreach ($this->intermediaryTables as $table) {
			/** @var Table $table */
			$table->alias = $this->buildUniqueTableAlias($table->alias, $parentAlias);
			$tables->push($table);
		}
		$tables->push($this->childTable);

		$join->subJoins = new SubJoinList();
		while ($tables->length() >= 2) {
			$tablePair = $tables->slice(0, 2);
			$tables->shift();

			$subJoin = $this->buildSubJoin($join, $tablePair);
			$join->subJoins->push($subJoin);
		}

		$parentAttribute = $this->getParentAttributeFromSubJoins($join->subJoins);
		$childAttribute = $this->getChildAttributeFromSubJoins($join->subJoins);

		$join->parentAttribute = $this->parentTable->fields->getByName($parentAttribute->name);
		$join->childAttribute = $this->childTable->fields->getByName($childAttribute->name);

		return $join;
	}

	private function getParentAttributeFromSubJoins(SubJoinList $joins)
	{
		$parentAttribute = null;
		/** @var SubJoin $join */
		foreach ($joins as $join) {
			if ($join->parentTable->name === $this->parentTable->name) {
				$parentAttribute = $join->parentAttribute;
				break;
			} elseif ($join->childTable->name === $this->parentTable->name) {
				$parentAttribute = $join->childAttribute;
				break;
			}
		}
		if (is_null($parentAttribute)) {
			throw new InvalidTableException(
				'No parent attribute from table found in sub-joins: ' . json_encode($joins)
			);
		}
		return $parentAttribute;
	}

	private function getChildAttributeFromSubJoins(SubJoinList $joins)
	{
		$childAttribute = null;
		/** @var SubJoin $join */
		foreach ($joins as $join) {
			if ($join->parentTable->name === $this->childTable->name) {
				$childAttribute = $join->parentAttribute;
			} elseif ($join->childTable->name === $this->childTable->name) {
				$childAttribute = $join->childAttribute;
			}
		}
		if (is_null($childAttribute)) {
			throw new InvalidTableException(
				'No child attribute from table found in sub-joins: ' . json_encode($joins)
			);
		}
		return $childAttribute;
	}

	private function buildSubJoin(Constraint $parentJoin, TableList $tables)
	{
		if ($tables->length() !== 2) {
			throw new InvalidArgumentException(
				'Attempted to build a sub-join from an invalid number of tables: ' . json_encode($tables)
			);
		}

		$firstTable = $tables->getByIndex(0);
		$secondTable = $tables->getByIndex(1);

		$join = new SubJoin();
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
				$join->parentAttribute = $this->buildTableAttribute($firstTable, $parentAttributeAlias);
				$join->childTable = $secondTable;
				$join->childAttribute = $this->buildTableAttribute($secondTable, $childAttributeAlias);
				$join->childTable->fields->push($join->childAttribute);
				break;
			} elseif ($childTableAlias === $secondTable->getAlias()) {
				$join->childTable = $secondTable;
				$join->childAttribute = $this->buildTableAttribute($secondTable, $childAttributeAlias);
				$join->parentTable = $firstTable;
				$join->parentAttribute = $this->buildTableAttribute($firstTable, $parentAttributeAlias);
				$join->parentTable->fields->push($join->parentAttribute);
				break;
			}
		}

		return $join;
	}

	private function buildDirectJoins()
	{
		$joins = new ConstraintList();
		foreach ($this->joinManifest as $parentAlias => $childAlias) {
			$parentAttributeName = ltrim(strstr($parentAlias, '.'), '.');
			$parentAttribute = $this->parentTable->fields->getByName($parentAttributeName);

			$childAttributeName = ltrim(strstr($childAlias, '.'), '.');
			$childAttribute = $this->childTable->fields->getByName($childAttributeName);

			$join = new Constraint();
			$join->link = $this;
			$join->parentAttribute = $parentAttribute;
			$join->childAttribute = $childAttribute;
			$joins->push($join);
		}
		return $joins;
	}

	private function buildTableAttribute(Table $table, $attributeName)
	{
		$attribute = new Field();
		$attribute->table = $table;
		$attribute->name = $attributeName;
		$attribute->alias = sprintf('%s.%s', $table->getAlias(), $attributeName);
		return $attribute;
	}

	private function getTableNameFromAlias($alias)
	{
		if ($alias === 'this') {
			$tableName = $this->parentTable->getAlias();
		} elseif ($alias === $this->name) {
			$tableName = $this->childTable->getAlias();
		} else {
			$tableName = $alias;
		}
		return $tableName;
	}
}
