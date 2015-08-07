<?php
namespace Sloth\Module\Graph\QuerySet\GetBy;

use Sloth\Module\Graph\QuerySet\Filter;
use Sloth\Module\Graph\QuerySet\QuerySet;
use Sloth\Module\Graph\QuerySet\QuerySetItem;
use Sloth\Module\Graph\Definition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\Abstractory\Value\ATable as QueryTable;

class Composer
{
	/**
	 * @var DatabaseWrapper
	 */
	private $database;

	/**
	 * @var Definition\Table
	 */
	private $resourceDefinition;

	/**
	 * @var array
	 */
	private $filters = array();

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

	public function setResource(Definition\Table $resourceDefinition)
	{
		$this->resourceDefinition = $resourceDefinition;
		return $this;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}

	public function compose()
	{
		return $this->buildQuerySetForResourceAndDescendants($this->resourceDefinition, $this->filters);
	}

	private function buildQuerySetForResourceAndDescendants(Definition\Table $resourceDefinition, array $filters)
	{
		$querySet = new QuerySet();
		$querySetItem = new QuerySetItem();
		$querySetItem->setResourceName($resourceDefinition->getAlias())
			->setQuery($this->buildQueryForResource($resourceDefinition, $filters))
			->setLinks($this->getLinksToManyRowsFromResourceSet($resourceDefinition));
		$querySet->push($querySetItem);

		$linksToManyRows = $this->getLinksToManyRowsFromResourceSet($resourceDefinition);
		foreach ($linksToManyRows as $link) {
			if (array_key_exists($link->name, $filters)) {
				$descendantFilters = $filters[$link->name];
			} else {
				$descendantFilters = array();
			}
			$descendantQuerySet = $this->buildQuerySetForLinkDescendants($link, $descendantFilters);
			if (!is_null($descendantQuerySet)) {
				foreach ($descendantQuerySet as $querySetItem) {
					$querySet->push($querySetItem);
				}
			}
		}

		return $querySet;
	}

	private function getLinksToManyRowsFromResourceSet(Definition\Table $resourceDefinition)
	{
		$foundLinks = new Definition\Table\JoinList();
		foreach ($resourceDefinition->links as $link) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			if (in_array($link->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
				$childLinks = $this->getLinksToManyRowsFromResourceSet($link->getChildTable());
				foreach ($childLinks as $childLink) {
					$foundLinks->push($childLink);
				}
			} else {
				$foundLinks->push($link);
			}
		}
		return $foundLinks;
	}

	private function buildQueryForResource(Definition\Table $resourceDefinition, array $filters)
	{
		$query = $this->database->query()->select()
			->setFields($this->buildQueryFieldsFromResource($resourceDefinition))
			->from($this->getQueryTable($resourceDefinition->name, $resourceDefinition->getAlias()))
			->setJoins($this->buildQueryJoinsFromResource($resourceDefinition));

		$constraint = $this->buildQueryConstraintFromFilters($filters, $resourceDefinition, $resourceDefinition->getAlias());
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
					$joinConstraints[] = $this->buildJoinConstraint($constraint->parentAttribute, $constraint->childAttribute);
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

	private function buildQueryConstraintFromFilters(array $filters, Definition\Table $resourceDefinition, $parentAlias)
	{
		$constraints = $this->buildQueryConstraintListFromFilters($filters, $resourceDefinition, $parentAlias);

		$firstConstraint = null;
		if (count($constraints) > 0) {
			$firstConstraint = array_shift($constraints);
			foreach ($constraints as $constraint) {
				$firstConstraint->andWhere($constraint);
			}
		}
		return $firstConstraint;
	}

	private function buildQueryConstraintListFromFilters(array $filters, Definition\Table $resourceDefinition, $parentAlias)
	{
		$constraints = array();
		foreach ($filters as $filterName => $filter) {
			if ($filter instanceof Filter) {
				$attribute = $filter->attribute;
				$field = $this->buildQueryFieldFromAlias($attribute->getAlias(), $parentAlias);
				$fieldConstraint = $this->database->query()->constraint()
					->setSubject($field);
				if (is_array($filter->value)) {
					$queryValues = array();
					foreach ($filter->value as $value) {
						$queryValues[] = $this->database->value()->string($value);
					}
					$queryValue = $this->database->value()->valueList($queryValues);
					$fieldConstraint->in($queryValue);
				} else {
					$queryValue = $this->database->value()->string($filter->value);
					$fieldConstraint->equals($queryValue);
				}
				$constraints[] = $fieldConstraint;
			} else {
				if ($resourceDefinition->links->length() > 0) {
					$resourceLink = $resourceDefinition->links->getByName($filterName);
					if (in_array($resourceLink->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
						$childTable = $resourceLink->getChildTable();
						$constraints = array_merge($constraints, $this->buildQueryConstraintListFromFilters($filter, $childTable, $filterName));
					}
				}
			}
		}
		return $constraints;
	}

	private function buildQueryFieldFromAlias($fieldAlias, $parentAlias)
	{
		$nameParts = explode('.', $fieldAlias);
		list($tableName, $fieldName) = $nameParts;
		return $this->getQueryTable($tableName, $tableName)->field($fieldName);
	}

	private function buildQueryFieldsFromResource(Definition\Table $resource)
	{
		$resourceFields = $this->buildQueryFieldsFromAttributes($resource->fields);
		$linkFields = $this->buildQueryFieldsFromLinks($resource->links);
		return array_merge($resourceFields, $linkFields);
	}

	private function buildQueryFieldsFromAttributes(Definition\Table\FieldList $attributes)
	{
		$queryFields = array();
		foreach ($attributes as $attribute) {
			/** @var \Sloth\Module\Graph\Definition\Table\Field $attribute */
			$tableName = $attribute->table->name;
			$tableAlias = $attribute->table->getAlias();
			$fieldName = $attribute->name;
			$queryField = $this->getQueryTable($tableName, $tableAlias)->field($fieldName)->setAlias($attribute->getAlias());
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
				$childFields = $this->buildQueryFieldsFromResource($childTable);
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
			$querySet = $this->buildQuerySetForResourceAndDescendants($childTable, $filters);
		}
		return $querySet;
	}

	private function buildQuerySetForSubJoinedResourceAndDescendants(Definition\Table\Join $link, array $filters)
	{
		$resourceDefinition = $link->getChildTable();
		$querySet = new QuerySet();
		$querySetItem = new QuerySetItem();
		$querySetItem->setResourceName($resourceDefinition->getAlias())
			->setQuery($this->buildQueryForSubJoinsAndResource($link, $filters))
			->setLinks($this->getLinksToManyRowsFromResourceSet($resourceDefinition));
		$querySet->push($querySetItem);

		foreach ($resourceDefinition->links as $link) {
			if (array_key_exists($link->name, $filters)) {
				$descendantFilters = $filters[$link->name];
			} else {
				$descendantFilters = array();
			}
			foreach ($this->buildQuerySetForLinkDescendants($link, $descendantFilters) as $querySetItem) {
				$querySet->push($querySetItem);
			}
		}

		return $querySet;
	}

	private function buildQueryForSubJoinsAndResource(Definition\Table\Join $link, array $filters)
	{
		$resourceDefinition = $link->getChildTable();
		$query = $this->database->query()->select();

		$subJoinGroups = $this->groupSubJoinsByChild($link);

		$queryJoins = array();
		foreach ($subJoinGroups as $childTableAlias => $subJoins) {
			$join = $this->database->query()->join()->inner();
			$join->table($this->getQueryTable($link->getChildTable()->name, $link->getChildTable()->getAlias()));

			if (!isset($firstSubJoin)) {
				$firstSubJoinName = array_keys($subJoins)[0];
				$firstSubJoin = $subJoins[$firstSubJoinName];
			}

			$joinConstraints = array();
			foreach ($subJoins as $joinDefinition) {
				/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $joinDefinition */
				if ($joinDefinition->parentTable->getAlias() !== $link->parentTable->getAlias()) {
					$parentTable = $this->getQueryTable($joinDefinition->parentTable->name, $joinDefinition->parentTable->getAlias());
					$parentField = $parentTable->field($joinDefinition->parentAttribute->name);
					$childTable = $this->getQueryTable($joinDefinition->childTable->name, $joinDefinition->childTable->getAlias());
					$childField = $childTable->field($joinDefinition->childAttribute->name);
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
		$queryJoins = array_merge($queryJoins, $this->buildQueryJoinsFromResource($resourceDefinition));
		$query->setJoins($queryJoins);

		$firstTableAlias = $firstSubJoin->childTable->getAlias();
		$linkField = $this->getQueryTable($firstTableAlias, $firstTableAlias)
			->field($firstSubJoin->childAttribute->name)
			->setAlias($firstSubJoin->childAttribute->getAlias());
		$queryFields = $this->buildQueryFieldsFromResource($resourceDefinition);
		$queryFields[] = $linkField;

		$table = $firstSubJoin->childTable;
		$query->setFields($queryFields)
			->from($this->getQueryTable($table->name, $table->getAlias()));

		$queryConstraint = $this->buildQueryConstraintFromFilters($filters, $link->getChildTable(), $link->name);
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
