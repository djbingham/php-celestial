<?php
namespace DemoGraph\Module\Graph\QueryBuilder;

use DemoGraph\Module\Graph\QueryComponent;
use DemoGraph\Module\Graph\QueryComponent\Constraint;
use DemoGraph\Module\Graph\QueryComponent\ConstraintList;
use DemoGraph\Module\Graph\QueryComponent\FieldSort;
use DemoGraph\Module\Graph\QueryComponent\FieldSortList;
use DemoGraph\Module\Graph\ResourceDefinition\Table;
use DemoGraph\Module\Graph\ResourceDefinition\TableField;
use DemoGraph\Module\Graph\ResourceDefinition\TableFieldList;
use Sloth\Exception\InvalidRequestException;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Value\ValueList;

class Select
{
	/**
	 * @var DatabaseWrapper
	 */
	private $database;

	/**
	 * @var TableFieldList
	 */
	private $tableFieldList;

	/**
	 * @var QueryComponent\Table
	 */
	private $table;

	/**
	 * @var ConstraintList
	 */
	private $constraintList;

	/**
	 * @var FieldSortList
	 */
	private $fieldSortList;

	public function __construct(DatabaseWrapper $database)
	{
		$this->database = $database;
	}

	public function fields(TableFieldList $fields)
	{
		$this->tableFieldList = $fields;
		return $this;
	}

	public function from(QueryComponent\Table $table)
	{
		$this->table = $table;
		return $this;
	}

	public function where(ConstraintList $constraintList)
	{
		$this->constraintList = $constraintList;
		return $this;
	}

	public function orderBy(FieldSortList $fieldSortList)
	{
		$this->fieldSortList = $fieldSortList;
		return $this;
	}

	public function build()
	{
		$this->padProperties();

		$queryFields = $this->buildQueryFields();
		$queryTable = $this->buildQueryTable($this->table->getDefinition());
		$joins = $this->buildQueryJoinList();
		$query = $this->database->query()->select()
			->setFields($queryFields)
			->from($queryTable)
			->setJoins($joins);

		if ($this->constraintList->length() > 0) {
			$query->where($this->buildQueryConstraint($this->constraintList->shift()));
			foreach ($this->constraintList as $constraint) {
				$query->andWhere($constraint);
			}
		}

		foreach ($this->fieldSortList as $fieldSort) {
			/** @var FieldSort $fieldSort */
			$query->orderBy($queryTable->field($fieldSort->getField()->name));
		}

		return $query;
	}

	private function padProperties()
	{
		if (!isset($this->tableFieldList)) {
			throw new InvalidRequestException('Select query builder requires fields to be set.');
		}
		if (!isset($this->table)) {
			throw new InvalidRequestException('Select query builder requires table to be set.');
		}
		if (!isset($this->constraintList)) {
			$this->constraintList = new ConstraintList();
		}
		if (!isset($this->fieldSortList)) {
			$this->fieldSortList = new FieldSortList();
		}
	}

	private function buildQueryTable(Table $table)
	{
		// todo: cache query tables by name/alias
		$queryTable = $this->database->value()->table($table->name);
		if ($table->alias !== null) {
			$queryTable->setAlias($table->alias);
		}
		return $queryTable;
	}

	private function buildQueryFields()
	{
		$fields = $this->getTableFields();
		$queryFields = array();
		foreach ($fields as $fieldDefinition) {
			$queryFields[] = $this->buildQueryFieldFromDefinition($fieldDefinition);
		}
		return $queryFields;
	}

	private function getTableFields()
	{
		$queryFields = array();
		foreach ($this->tableFieldList as $tableField) {
			$queryFields[] = $tableField;
		}
		foreach ($this->constraintList as $constraint) {
			/** @var Constraint $constraint */
			$tableField = $constraint->getSubject();
			if (!$this->tableFieldInArray($tableField, $queryFields)) {
				$queryFields[] = $tableField;
			}
		}
		return $queryFields;
	}

	private function tableFieldInArray(TableField $tableField, array $queryFields)
	{
		$found = false;
		foreach ($queryFields as $queryField) {
			/** @var TableField $queryField */
			if ($queryField->alias === $tableField->alias) {
				$found = true;
				break;
			}
		}
		return $found;
	}

	private function buildQueryFieldFromDefinition(TableField $fieldDefinition)
	{
		$queryTable = $this->buildQueryTable($fieldDefinition->table);
		return $queryTable->field($fieldDefinition->name);
	}

	private function buildQueryConstraint(Constraint $constraintDefinition)
	{
		$subject = $constraintDefinition->getSubject();
		$value = $constraintDefinition->getValue();

		$querySubject = $this->buildQueryFieldFromDefinition($subject);
		if (is_array($value)) {
			$queryValues = array();
			foreach ($value as $item) {
				$queryValues[] = $this->database->value()->guess($item);
			}
			$queryValue = $this->database->value()->valueList($queryValues);
		} else {
			$queryValue = $this->database->value()->string($value);
		}

		$queryConstraint = new \SlothMySql\QueryBuilder\Query\Constraint();
		$queryConstraint->setSubject($querySubject);
		if ($queryValue instanceof ValueList) {
			$queryConstraint->setComparator('IN');
		} else {
			$queryConstraint->setComparator('=');
		}
		$queryConstraint->setValue($queryValue);
		return $queryConstraint;
	}

	private function buildQueryJoinList()
	{
		$joins = $this->getJoinsFromTable($this->table);
		$queryJoins = array();
		foreach ($joins as $joinDefinition) {
			$queryJoins[] = $this->buildQueryJoin($joinDefinition);
		}
		return $queryJoins;
	}

	private function getJoinsFromTable(QueryComponent\Table $table)
	{
		$joins = $table->getJoins();
		if (is_null($joins)) {
			$joins = new QueryComponent\TableJoinList();
		}
		foreach ($joins as $joinDefinition) {
			/** @var QueryComponent\TableJoin $joinDefinition */
			$childTable = $joinDefinition->getChildTable();
			foreach ($this->getJoinsFromTable($childTable) as $childJoin) {
				$joins->push($childJoin);
			}
		}
		return $joins;
	}

	private function buildQueryJoin(QueryComponent\TableJoin $joinDefinition)
	{
		$childTableDefinition = $joinDefinition->getChildTable()->getDefinition();
		$childTable = $this->database->value()->table($childTableDefinition->name);
		$join = $this->database->query()->join()->inner();
		$join->table($childTable)
			->on($this->buildQueryConstraintFromTableJoins($joinDefinition->getConstraints()));

		if ($childTableDefinition->alias !== null) {
			$join->withAlias($childTableDefinition->alias);
		}

		return $join;
	}

	private function buildQueryConstraintFromTableJoins(ConstraintList $constraints)
	{
		$queryConstraint = $this->database->query()->constraint();
		foreach ($constraints as $constraintDefinition) {
			/** @var Constraint $constraintDefinition */
			$parentField = $constraintDefinition->getSubject();
			$childField = $constraintDefinition->getValue();
			$parent = $this->buildQueryTable($parentField->table)->field($parentField->name);
			$child = $this->buildQueryTable($childField->table)->field($childField->name);
			$queryConstraint->setSubject($parent)->equals($child);
		}
		return $queryConstraint;
	}
}
