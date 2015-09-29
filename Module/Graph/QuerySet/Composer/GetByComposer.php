<?php
namespace Sloth\Module\Graph\QuerySet\Composer;

use Sloth\Module\Graph\Exception\InvalidTableException;
use Sloth\Module\Graph\QuerySet\Base;
use Sloth\Module\Graph\QuerySet\Filter\Filter;
use Sloth\Module\Graph\QuerySet\QueryWrapper\MultiQueryWrapper;
use Sloth\Module\Graph\QuerySet\QueryWrapper\QueryLink;
use Sloth\Module\Graph\QuerySet\QueryWrapper\QueryLinkList;
use Sloth\Module\Graph\QuerySet\QueryWrapper\SingleQueryWrapper;
use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\Face\Value\TableInterface;

class GetByComposer extends Base\AbstractComposer
{
	/**
	 * @var array
	 */
	private $cache = array(
		'queryTable' => array()
	);

	public function setDatabase(DatabaseWrapper $database)
	{
		$this->database = $database;
		return $this;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}

	public function compose()
	{
		return $this->buildQuerySetForTableAndDescendants($this->tableDefinition, $this->filters);
	}

	private function buildQuerySetForTableAndDescendants(Definition\Table $tableDefinition, array $filters, QueryLink $parentLink = null)
	{
		$querySet = new MultiQueryWrapper();
		$queryWrapper = new SingleQueryWrapper();

		$query = $this->buildQueryForTable($tableDefinition, $filters);
		$linksToManyRows = $this->getLinksToManyRowsFromTable($tableDefinition, $queryWrapper);

		$queryWrapper->setTable($tableDefinition)
			->setQuery($query)
			->setChildLinks($linksToManyRows);
		if ($parentLink !== null) {
			$queryWrapper->setParentLink($parentLink);
		}

		$querySet->push($queryWrapper);

		/** @var QueryLink $link */
		foreach ($linksToManyRows as $link) {
			$join = $link->getJoinDefinition();
			if (array_key_exists($join->name, $filters)) {
				$descendantFilters = $filters[$join->name];
			} else {
				$descendantFilters = array();
			}
			$descendantQuerySet = $this->buildQuerySetForLinkDescendants($link, $descendantFilters);
			if (!is_null($descendantQuerySet)) {
				foreach ($descendantQuerySet as $descendantSingleQueryWrapper) {
					$querySet->push($descendantSingleQueryWrapper);
				}
			}
		}

		return $querySet;
	}

	private function getLinksToManyRowsFromTable(Definition\Table $tableDefinition, SingleQueryWrapper $parentQueryWrapper)
	{
		$foundLinks = new QueryLinkList();
		foreach ($tableDefinition->links as $join) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join $join */
			if (in_array($join->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
				$childLinks = $this->getLinksToManyRowsFromTable($join->getChildTable(), $parentQueryWrapper);
				foreach ($childLinks as $childLink) {
					$foundLinks->push($childLink);
				}
			} else {
				$link = new QueryLink();
				$link->setParentQueryWrapper($parentQueryWrapper)
					->setJoinDefinition($join);
				$foundLinks->push($link);
			}
		}
		return $foundLinks;
	}

	private function buildQueryForTable(Definition\Table $tableDefinition, array $filters)
	{
		$query = $this->database->query()->select();
		$query->setFields($this->buildQueryFieldsFromTable($tableDefinition))
			->from($this->getQueryTable($tableDefinition->name, $tableDefinition->getAlias()))
			->setJoins($this->buildQueryJoinsFromTable($tableDefinition));

		$constraint = $this->buildQueryConstraintFromFilters($filters, $tableDefinition);
		if ($constraint !== null) {
			$query->where($constraint);
		}

		return $query;
	}

	private function buildQueryJoinsFromTable(Definition\Table $table)
	{
		$joins = array();
		foreach ($table->links as $link) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			if (in_array($link->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
				$childTable = $link->getChildTable();

				foreach ($link->getConstraints() as $constraint) {
					/** @var \Sloth\Module\Graph\Definition\Table\Join\Constraint $constraint */
					$joinConstraints[] = $this->buildJoinConstraint($constraint->parentField, $constraint->childField);
				}
				$firstJoinConstraint = array_shift($joinConstraints);
				foreach ($joinConstraints as $constraint) {
					$firstJoinConstraint->andOn($constraint);
				}

				$joins[] = $this->database->query()->join()->left()
					->table($this->getQueryTable($childTable->name, $childTable->getAlias()))
					->on($firstJoinConstraint);

				$joins = array_merge($joins, $this->buildQueryJoinsFromTable($childTable));
			}
		}
		return $joins;
	}

	/**
	 * @param string $tableName
	 * @param string $tableAlias
	 * @return TableInterface
	 */
	private function getQueryTable($tableName, $tableAlias)
	{
		if (!array_key_exists($tableAlias, $this->cache['queryTable'])) {
			$table = $this->database->value()->table($tableName);
			if ($tableName !== $tableAlias) {
				$table->setAlias($tableAlias);
			}
			$this->cache['queryTable'][$tableAlias] = $table;
		}
		return $this->cache['queryTable'][$tableAlias];
	}

	private function buildJoinConstraint(Definition\Table\Field $parentField, Definition\Table\Field $childField)
	{
		$parentTable = $this->getQueryTable($parentField->table->name, $parentField->table->getAlias());
		$childTable = $this->getQueryTable($childField->table->name, $childField->table->getAlias());
		$joinConstraint = $this->database->query()->constraint()
			->setSubject($parentTable->field($parentField->name))
			->equals($childTable->field($childField->name));
		return $joinConstraint;
	}

	private function buildQueryConstraintFromFilters(array $filters, Definition\Table $tableDefinition)
	{
		$constraints = $this->buildQueryConstraintListFromFilters($filters, $tableDefinition);

		$firstConstraint = null;
		if (count($constraints) > 0) {
			$firstConstraint = array_shift($constraints);
			foreach ($constraints as $constraint) {
				$firstConstraint->andWhere($constraint);
			}
		}
		return $firstConstraint;
	}

	private function buildQueryConstraintListFromFilters(array $filters, Definition\Table $tableDefinition)
	{
		$constraints = array();
		foreach ($filters as $filterName => $filter) {
			if ($filter instanceof Filter) {
				$field = $filter->field;
				$field = $this->buildQueryFieldFromAlias($field->getAlias());
				$fieldConstraint = $this->database->query()->constraint()
					->setSubject($field);
				if (is_array($filter->value)) {
					$queryValues = array();
					foreach ($filter->value as $value) {
						$queryValues[] = $this->database->value()->string($value);
					}
					$queryValue = $this->database->value()->valueList($queryValues);
				} else {
					$queryValue = $this->database->value()->string($filter->value);
				}
				$fieldConstraint
					->setComparator($filter->comparator)
					->setValue($queryValue);
				$constraints[] = $fieldConstraint;
			} else {
				if ($tableDefinition->links->length() > 0) {
					$tableLink = $tableDefinition->links->getByName($filterName);
					if (in_array($tableLink->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
						$childTable = $tableLink->getChildTable();
						$constraints = array_merge($constraints, $this->buildQueryConstraintListFromFilters($filter, $childTable));
					}
				}
			}
		}
		return $constraints;
	}

	private function buildQueryFieldFromAlias($fieldAlias)
	{
		$nameParts = explode('.', $fieldAlias);
		list($tableName, $fieldName) = $nameParts;
		return $this->getQueryTable($tableName, $tableName)->field($fieldName);
	}

	private function buildQueryFieldsFromTable(Definition\Table $table)
	{
		$tableFields = $this->buildQueryFieldsFromFields($table->fields);
		$linkFields = $this->buildQueryFieldsFromLinks($table->links);
		return array_merge($tableFields, $linkFields);
	}

	private function buildQueryFieldsFromFields(Definition\Table\FieldList $fields)
	{
		$queryFields = array();
		foreach ($fields as $field) {
			/** @var \Sloth\Module\Graph\Definition\Table\Field $field */
			$tableName = $field->table->name;
			$tableAlias = $field->table->getAlias();
			$fieldName = $field->name;
			$queryField = $this->getQueryTable($tableName, $tableAlias)->field($fieldName)->setAlias($field->getAlias());
			$queryFields[] = $queryField;
		}
		return $queryFields;
	}

	private function buildQueryFieldsFromLinks(Definition\Table\JoinList $links)
	{
		$queryFields = array();
		foreach ($links as $link) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			if (in_array($link->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
				$childTable = $link->getChildTable();
				$childFields = $this->buildQueryFieldsFromTable($childTable);
				$queryFields = array_merge($queryFields, $childFields);
			}
		}
		return $queryFields;
	}

	private function buildQuerySetForLinkDescendants(QueryLink $link, array $filters)
	{
		$join = $link->getJoinDefinition();
		$childTable = $join->getChildTable();
		$querySet = null;
		if ($join->type === Definition\Table\Join::MANY_TO_MANY) {
			$querySet = $this->buildQuerySetForSubJoinedTableAndDescendants($link, $filters);
		} elseif ($join->type === Definition\Table\Join::ONE_TO_MANY) {
			$querySet = $this->buildQuerySetForTableAndDescendants($childTable, $filters, $link);
		}
		return $querySet;
	}

	private function buildQuerySetForSubJoinedTableAndDescendants(QueryLink $link, array $filters)
	{
		$join = $link->getJoinDefinition();
		$childTable = $join->getChildTable();

		$querySet = new MultiQueryWrapper();
		$queryWrapper = new SingleQueryWrapper();

		$query = $this->buildQueryForSubJoinsAndTable($join, $filters);
		$childLinks = $this->getLinksToManyRowsFromTable($childTable, $queryWrapper);

		$queryWrapper->setTable($childTable)
			->setQuery($query)
			->setParentLink($link)
			->setChildLinks($childLinks);
		$querySet->push($queryWrapper);

		$childLinks = $this->getLinksToManyRowsFromTable($childTable, $queryWrapper);
		/** @var QueryLink $childLink */
		foreach ($childLinks as $childLink) {
			$childJoin = $childLink->getJoinDefinition();
			if (array_key_exists($childJoin->name, $filters)) {
				$descendantFilters = $filters[$childJoin->name];
			} else {
				$descendantFilters = array();
			}
			foreach ($this->buildQuerySetForLinkDescendants($childLink, $descendantFilters) as $queryWrapper) {
				$querySet->push($queryWrapper);
			}
		}

		return $querySet;
	}

	private function buildQueryForSubJoinsAndTable(Definition\Table\Join $join, array $filters)
	{
		$tableDefinition = $join->getChildTable();
		$query = $this->database->query()->select();

		$subJoinGroups = $this->groupSubJoinsByChild($join);

		$queryJoins = array();
		foreach ($subJoinGroups as $childTableAlias => $subJoins) {
			$queryJoin = $this->database->query()->join()->inner();
			$queryJoin->table($this->getQueryTable($join->getChildTable()->name, $join->getChildTable()->getAlias()));

			if (!isset($firstSubJoin)) {
				/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $firstSubJoin */
				$firstSubJoinName = array_keys($subJoins)[0];
				$firstSubJoin = $subJoins[$firstSubJoinName];
			}

			$joinConstraints = array();
			foreach ($subJoins as $joinDefinition) {
				/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $joinDefinition */
				if ($joinDefinition->parentTable->getAlias() !== $join->parentTable->getAlias()) {
					$parentTable = $this->getQueryTable($joinDefinition->parentTable->name, $joinDefinition->parentTable->getAlias());
					$parentField = $parentTable->field($joinDefinition->parentField->name);
					$childTable = $this->getQueryTable($joinDefinition->childTable->name, $joinDefinition->childTable->getAlias());
					$childField = $childTable->field($joinDefinition->childField->name);
					$joinConstraint = $this->database->query()->constraint()
						->setSubject($parentField)
						->equals($childField);
					$joinConstraints[] = $joinConstraint;
				}
			}

			if (!empty($joinConstraints)) {
				$constraintCollection = array_shift($joinConstraints);
				foreach ($joinConstraints as $joinConstraint) {
					$constraintCollection->andOn($joinConstraint);
				}
				$queryJoin->on($constraintCollection);
				$queryJoins[] = $queryJoin;
			}
		}
		$queryJoins = array_merge($queryJoins, $this->buildQueryJoinsFromTable($tableDefinition));
		$query->setJoins($queryJoins);

		if (!isset($firstSubJoin)) {
			throw new InvalidTableException('Attempting to build query for a sub-join where no sub-join exists');
		}

		$firstTableAlias = $firstSubJoin->childTable->getAlias();
		$linkField = $this->getQueryTable($firstTableAlias, $firstTableAlias)
			->field($firstSubJoin->childField->name)
			->setAlias($firstSubJoin->childField->getAlias());
		$queryFields = $this->buildQueryFieldsFromTable($tableDefinition);
		$queryFields[] = $linkField;

		$table = $firstSubJoin->childTable;
		$query->setFields($queryFields)
			->from($this->getQueryTable($table->name, $table->getAlias()));

		$queryConstraint = $this->buildQueryConstraintFromFilters($filters, $join->getChildTable());
		if (!is_null($queryConstraint)) {
			$query->where($queryConstraint);
		}

		return $query;
	}

	private function groupSubJoinsByChild(Definition\Table\Join $tableLink)
	{
		$groupedJoins = array();
		foreach ($tableLink->constraints as $constraint) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join\Constraint $constraint */
			foreach ($constraint->subJoins as $subJoin) {
				/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $subJoin */
				$childAlias = $subJoin->childTable->getAlias();
				if (!array_key_exists($childAlias, $groupedJoins)) {
					$groupedJoins[$childAlias] = array();
				}
				$groupedJoins[$childAlias][] = $subJoin;
			}
		}
		return $groupedJoins;
	}
}
