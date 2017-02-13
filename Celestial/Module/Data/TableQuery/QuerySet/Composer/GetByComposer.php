<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Composer;

use Celestial\Module\Data\Table\Exception\InvalidTableException;
use Celestial\Module\Data\Table\Face\ConstraintInterface;
use Celestial\Module\Data\Table\Face\FieldInterface;
use Celestial\Module\Data\Table\Face\FieldListInterface;
use Celestial\Module\Data\Table\Face\JoinInterface;
use Celestial\Module\Data\Table\Face\JoinListInterface;
use Celestial\Module\Data\Table\Face\SubJoinInterface;
use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Base;
use Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Filter\Filter;
use Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLink;
use Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList;
use Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper;
use PhpMySql\DatabaseWrapper;

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

	private function buildQuerySetForTableAndDescendants(TableInterface $tableDefinition, array $filters, QueryLink $parentLink = null)
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
				/** @var SingleQueryWrapperInterface $descendantSingleQueryWrapper */
				foreach ($descendantQuerySet as $descendantSingleQueryWrapper) {
					$querySet->push($descendantSingleQueryWrapper);
				}
				$link->setChildQueryWrapper($descendantQuerySet);
			}
		}

		return $querySet;
	}

	private function getLinksToManyRowsFromTable(TableInterface $tableDefinition, SingleQueryWrapper $parentQueryWrapper)
	{
		$foundLinks = new QueryLinkList();
		/** @var JoinInterface $join */
		foreach ($tableDefinition->links as $join) {
			if (in_array($join->type, array(JoinInterface::ONE_TO_ONE, JoinInterface::MANY_TO_ONE))) {
				// For *-to-one joins, check if any descendant joins are *-to-many
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

	private function buildQueryForTable(TableInterface $tableDefinition, array $filters)
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

	private function buildQueryJoinsFromTable(TableInterface $table)
	{
		$joins = array();
		/** @var JoinInterface $link */
		foreach ($table->links as $link) {
			if (in_array($link->type, array(JoinInterface::ONE_TO_ONE, JoinInterface::MANY_TO_ONE))) {
				$childTable = $link->getChildTable();

				foreach ($link->getConstraints() as $constraint) {
					/** @var ConstraintInterface $constraint */
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
	 * @return \PhpMySql\Face\Value\TableInterface
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

	private function buildJoinConstraint(FieldInterface $parentField, FieldInterface $childField)
	{
		$parentTable = $this->getQueryTable($parentField->table->name, $parentField->table->getAlias());
		$childTable = $this->getQueryTable($childField->table->name, $childField->table->getAlias());
		$joinConstraint = $this->database->query()->constraint()
			->setSubject($parentTable->field($parentField->name))
			->equals($childTable->field($childField->name));
		return $joinConstraint;
	}

	private function buildQueryConstraintFromFilters(array $filters, TableInterface $tableDefinition)
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

	private function buildQueryConstraintListFromFilters(array $filters, TableInterface $tableDefinition)
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
					if (in_array($tableLink->type, array(JoinInterface::ONE_TO_ONE, JoinInterface::MANY_TO_ONE))) {
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

	private function buildQueryFieldsFromTable(TableInterface $table)
	{
		$tableFields = $this->buildQueryFieldsFromFields($table->fields);
		$linkFields = $this->buildQueryFieldsFromLinks($table->links);
		return array_merge($tableFields, $linkFields);
	}

	private function buildQueryFieldsFromFields(FieldListInterface $fields)
	{
		$queryFields = array();
		foreach ($fields as $field) {
			/** @var FieldInterface $field */
			$tableName = $field->table->name;
			$tableAlias = $field->table->getAlias();
			$fieldName = $field->name;
			$queryField = $this->getQueryTable($tableName, $tableAlias)->field($fieldName)->setAlias($field->getAlias());
			$queryFields[] = $queryField;
		}
		return $queryFields;
	}

	private function buildQueryFieldsFromLinks(JoinListInterface $links)
	{
		$queryFields = array();
		foreach ($links as $link) {
			/** @var JoinInterface $link */
			if (in_array($link->type, array(JoinInterface::ONE_TO_ONE, JoinInterface::MANY_TO_ONE))) {
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
		if ($join->type === JoinInterface::MANY_TO_MANY) {
			$querySet = $this->buildQuerySetForSubJoinedTableAndDescendants($link, $filters);
		} elseif ($join->type === JoinInterface::ONE_TO_MANY) {
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

	private function buildQueryForSubJoinsAndTable(JoinInterface $join, array $filters)
	{
		$tableDefinition = $join->getChildTable();
		$query = $this->database->query()->select();

		$subJoinGroups = $this->groupSubJoinsByChild($join);

		$queryJoins = array();
		foreach ($subJoinGroups as $childTableAlias => $subJoins) {
			$queryJoin = $this->database->query()->join()->inner();
			$queryJoin->table($this->getQueryTable($join->getChildTable()->name, $join->getChildTable()->getAlias()));

			if (!isset($firstSubJoin)) {
				/** @var SubJoinInterface $firstSubJoin */
				$firstSubJoinName = array_keys($subJoins)[0];
				$firstSubJoin = $subJoins[$firstSubJoinName];
			}

			$joinConstraints = array();
			foreach ($subJoins as $joinDefinition) {
				/** @var SubJoinInterface $joinDefinition */
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

	private function groupSubJoinsByChild(JoinInterface $tableLink)
	{
		$groupedJoins = array();
		foreach ($tableLink->constraints as $constraint) {
			/** @var ConstraintInterface $constraint */
			foreach ($constraint->subJoins as $subJoin) {
				/** @var SubJoinInterface $subJoin */
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
