<?php
namespace Celestial\Module\Data\Table\Definition\Table;

use Celestial\Module\Data\Table\Definition\Table;
use Celestial\Module\Data\Table\Definition\Table\Join\Constraint;
use Celestial\Module\Data\Table\Definition\Table\Join\ConstraintList;
use Celestial\Module\Data\Table\Definition\Table\Join\SubJoin;
use Celestial\Module\Data\Table\Definition\Table\Join\SubJoinList;
use Celestial\Module\Data\Table\Definition\TableList;
use Celestial\Module\Data\Table\Exception\InvalidTableException;
use Celestial\Module\Data\Table\DefinitionBuilder\TableBuilder;
use Celestial\Exception\InvalidArgumentException;
use Celestial\Module\Data\Table\Face\JoinInterface;

class Join implements JoinInterface
{
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
	public $type = JoinInterface::ONE_TO_ONE;

	/**
	 * @var string
	 */
	public $onInsert = JoinInterface::ACTION_INSERT;

	/**
	 * @var string
	 */
	public $onUpdate = JoinInterface::ACTION_UPDATE;

	/**
	 * @var string
	 */
	public $onDelete = JoinInterface::ACTION_DELETE;

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
	 * @var TableBuilder
	 */
	private $tableBuilder;

	public function __construct(TableBuilder $tableBuilder)
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

	public function getLinkedFields()
	{
		$linkedFields = array();
		/** @var Constraint $constraint */
		foreach ($this->getConstraints() as $constraint) {
			/** @var SubJoin $subJoin */
			if ($this->type === JoinInterface::MANY_TO_MANY) {
				foreach ($constraint->subJoins as $subJoin) {
					if ($subJoin->parentTable === $this->parentTable) {
						$linkedFields['parent'] = $subJoin->parentField;
						$linkedFields['parentLink'] = $subJoin->childField;
					} elseif ($subJoin->childTable === $this->childTable) {
						$linkedFields['child'] = $subJoin->childField;
						$linkedFields['childLink'] = $subJoin->parentField;
					}
				}
			} else {
				$linkedFields['parent'] = $constraint->parentField;
				$linkedFields['child'] = $constraint->childField;
			}
		}
		return $linkedFields;
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

		$parentField = $this->getParentFieldFromSubJoins($join->subJoins);
		$childField = $this->getChildFieldFromSubJoins($join->subJoins);

		$join->parentField = $this->parentTable->fields->getByName($parentField->name);
		$join->childField = $this->childTable->fields->getByName($childField->name);

		return $join;
	}

	private function getParentFieldFromSubJoins(SubJoinList $joins)
	{
		$parentField = null;
		/** @var SubJoin $join */
		foreach ($joins as $join) {
			if ($join->parentTable->name === $this->parentTable->name) {
				$parentField = $join->parentField;
				break;
			} elseif ($join->childTable->name === $this->parentTable->name) {
				$parentField = $join->childField;
				break;
			}
		}
		if (is_null($parentField)) {
			throw new InvalidTableException(
				'No parent field from table found in sub-joins: ' . json_encode($joins)
			);
		}
		return $parentField;
	}

	private function getChildFieldFromSubJoins(SubJoinList $joins)
	{
		$childField = null;
		/** @var SubJoin $join */
		foreach ($joins as $join) {
			if ($join->parentTable->name === $this->childTable->name) {
				$childField = $join->parentField;
			} elseif ($join->childTable->name === $this->childTable->name) {
				$childField = $join->childField;
			}
		}
		if (is_null($childField)) {
			throw new InvalidTableException(
				'No child field from table found in sub-joins: ' . json_encode($joins)
			);
		}
		return $childField;
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
			$parentFieldAlias = ltrim(strstr($parentAlias, '.'), '.');

			$childTableAlias = rtrim(strstr($childAlias, '.', true), '.');
			$childTableAlias = $this->getTableNameFromAlias($childTableAlias);
			$childFieldAlias = ltrim(strstr($childAlias, '.'), '.');

			if ($parentTableAlias === $firstTable->getAlias()) {
				$join->parentTable = $firstTable;
				$join->parentField = $this->buildTableField($firstTable, $parentFieldAlias);
				$join->childTable = $secondTable;
				$join->childField = $this->buildTableField($secondTable, $childFieldAlias);
				$join->childTable->fields->push($join->childField);
				break;
			} elseif ($childTableAlias === $secondTable->getAlias()) {
				$join->childTable = $secondTable;
				$join->childField = $this->buildTableField($secondTable, $childFieldAlias);
				$join->parentTable = $firstTable;
				$join->parentField = $this->buildTableField($firstTable, $parentFieldAlias);
				$join->parentTable->fields->push($join->parentField);
				break;
			}
		}

		return $join;
	}

	private function buildDirectJoins()
	{
		$joins = new ConstraintList();
		foreach ($this->joinManifest as $parentAlias => $childAlias) {
			$parentFieldName = ltrim(strstr($parentAlias, '.'), '.');
			$parentField = $this->parentTable->fields->getByName($parentFieldName);

			$childFieldName = ltrim(strstr($childAlias, '.'), '.');
			$childField = $this->childTable->fields->getByName($childFieldName);

			$join = new Constraint();
			$join->link = $this;
			$join->parentField = $parentField;
			$join->childField = $childField;
			$joins->push($join);
		}
		return $joins;
	}

	private function buildTableField(Table $table, $fieldName)
	{
		$field = new Field();
		$field->table = $table;
		$field->name = $fieldName;
		$field->alias = sprintf('%s.%s', $table->getAlias(), $fieldName);
		return $field;
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
