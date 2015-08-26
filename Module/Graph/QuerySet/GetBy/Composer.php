<?php
namespace Sloth\Module\Graph\QuerySet\GetBy;

use Sloth\Module\Graph\Exception\InvalidTableException;
use Sloth\Module\Graph\QuerySet\Base;
use Sloth\Module\Graph\QuerySet\Filter;
use Sloth\Module\Graph\QuerySet\QuerySet;
use Sloth\Module\Graph\QuerySet\QuerySetItem;
use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\Abstractory\Value\ATable as QueryTable;

class Composer extends Base\AbstractComposer
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

	public function setResource(Definition\Table $tableDefinition)
	{
		$this->tableDefinition = $tableDefinition;
		return $this;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}

	public function compose()
	{
		return $this->buildQuerySetForResourceAndDescendants($this->tableDefinition, $this->filters);
	}

	private function buildQuerySetForResourceAndDescendants(Definition\Table $tableDefinition, array $filters, Definition\Table\Join $parentLink = null)
	{
		$querySet = new QuerySet();
		$querySetItem = new QuerySetItem();
		$querySetItem->setTableName($tableDefinition->getAlias())
			->setQuery($this->buildQueryForResource($tableDefinition, $filters))
			->setLinks($this->getLinksToManyRowsFromTable($tableDefinition));
		if ($parentLink === null) {
			$querySetItem->setParentLink($parentLink);
		}
		$querySet->push($querySetItem);

		$linksToManyRows = $this->getLinksToManyRowsFromTable($tableDefinition);
		/** @var Definition\Table\Join $link */
		foreach ($linksToManyRows as $link) {
			if (array_key_exists($link->name, $filters)) {
				$descendantFilters = $filters[$link->name];
			} else {
				$descendantFilters = array();
			}
			$descendantQuerySet = $this->buildQuerySetForLinkDescendants($link, $descendantFilters);
			if (!is_null($descendantQuerySet)) {
				foreach ($descendantQuerySet as $descendantQuerySetItem) {
					$querySet->push($descendantQuerySetItem);
				}
			}
		}

		return $querySet;
	}

	private function getLinksToManyRowsFromTable(Definition\Table $tableDefinition)
	{
		$foundLinks = new Definition\Table\JoinList();
		foreach ($tableDefinition->links as $link) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			if (in_array($link->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
				$childLinks = $this->getLinksToManyRowsFromTable($link->getChildTable());
				foreach ($childLinks as $childLink) {
					$foundLinks->push($childLink);
				}
			} else {
				$foundLinks->push($link);
			}
		}
		return $foundLinks;
	}

	private function buildQueryForResource(Definition\Table $tableDefinition, array $filters)
	{
		$query = $this->database->query()->select()
			->setFields($this->buildQueryFieldsFromTable($tableDefinition))
			->from($this->getQueryTable($tableDefinition->name, $tableDefinition->getAlias()))
			->setJoins($this->buildQueryJoinsFromResource($tableDefinition));

		$constraint = $this->buildQueryConstraintFromFilters($filters, $tableDefinition);
		if ($constraint !== null) {
			$query->where($constraint);
		}

		return $query;
	}

	private function buildQueryJoinsFromResource(Definition\Table $resource)
	{
		$joins = array();
		foreach ($resource->links as $link) {
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

				$joins[] = $this->database->query()->join()->inner()
					->table($this->getQueryTable($childTable->name, $childTable->getAlias()))
					->on($firstJoinConstraint);

				$joins = array_merge($joins, $this->buildQueryJoinsFromResource($childTable));
			}
		}
		return $joins;
	}

	/**
	 * @param string $tableName
	 * @param string $tableAlias
	 * @return QueryTable
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
					$resourceLink = $tableDefinition->links->getByName($filterName);
					if (in_array($resourceLink->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
						$childTable = $resourceLink->getChildTable();
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

	private function buildQueryFieldsFromTable(Definition\Table $resource)
	{
		$resourceFields = $this->buildQueryFieldsFromFields($resource->fields);
		$linkFields = $this->buildQueryFieldsFromLinks($resource->links);
		return array_merge($resourceFields, $linkFields);
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

	private function buildQuerySetForLinkDescendants(Definition\Table\Join $link, array $filters)
	{
		$childTable = $link->getChildTable();
		$querySet = null;
		if ($link->type === Definition\Table\Join::MANY_TO_MANY) {
			$querySet = $this->buildQuerySetForSubJoinedResourceAndDescendants($link, $filters);
		} elseif ($link->type === Definition\Table\Join::ONE_TO_MANY) {
			$querySet = $this->buildQuerySetForResourceAndDescendants($childTable, $filters, $link);
		}
		return $querySet;
	}

	private function buildQuerySetForSubJoinedResourceAndDescendants(Definition\Table\Join $link, array $filters)
	{
		$tableDefinition = $link->getChildTable();
		$querySet = new QuerySet();
		$querySetItem = new QuerySetItem();
		$querySetItem->setTableName($tableDefinition->getAlias())
			->setQuery($this->buildQueryForSubJoinsAndResource($link, $filters))
			->setLinks($this->getLinksToManyRowsFromTable($tableDefinition));
		if ($link !== null) {
			$querySetItem->setParentLink($link);
		}
		$querySet->push($querySetItem);

		foreach ($tableDefinition->links as $childLink) {
			if (array_key_exists($childLink->name, $filters)) {
				$descendantFilters = $filters[$childLink->name];
			} else {
				$descendantFilters = array();
			}
			foreach ($this->buildQuerySetForLinkDescendants($childLink, $descendantFilters) as $querySetItem) {
				$querySet->push($querySetItem);
			}
		}

		return $querySet;
	}

	private function buildQueryForSubJoinsAndResource(Definition\Table\Join $link, array $filters)
	{
		$tableDefinition = $link->getChildTable();
		$query = $this->database->query()->select();

		$subJoinGroups = $this->groupSubJoinsByChild($link);

		$queryJoins = array();
		foreach ($subJoinGroups as $childTableAlias => $subJoins) {
			$join = $this->database->query()->join()->inner();
			$join->table($this->getQueryTable($link->getChildTable()->name, $link->getChildTable()->getAlias()));

			if (!isset($firstSubJoin)) {
				/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $firstSubJoin */
				$firstSubJoinName = array_keys($subJoins)[0];
				$firstSubJoin = $subJoins[$firstSubJoinName];
			}

			$joinConstraints = array();
			foreach ($subJoins as $joinDefinition) {
				/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $joinDefinition */
				if ($joinDefinition->parentTable->getAlias() !== $link->parentTable->getAlias()) {
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
				$join->on($constraintCollection);
				$queryJoins[] = $join;
			}
		}
		$queryJoins = array_merge($queryJoins, $this->buildQueryJoinsFromResource($tableDefinition));
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

		$queryConstraint = $this->buildQueryConstraintFromFilters($filters, $link->getChildTable());
		if (!is_null($queryConstraint)) {
			$query->where($queryConstraint);
		}

		return $query;
	}

	private function groupSubJoinsByChild(Definition\Table\Join $resourceLink)
	{
		$groupedJoins = array();
		foreach ($resourceLink->constraints as $constraint) {
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
