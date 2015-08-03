<?php
namespace DemoGraph\Module\Graph\QuerySet\GetBy;

use DemoGraph\Module\Graph\QuerySet\Filter;
use DemoGraph\Module\Graph\QuerySet\QuerySet;
use DemoGraph\Module\Graph\QuerySet\QuerySetItem;
use DemoGraph\Module\Graph\ResourceDefinition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\Abstractory\Value\ATable as QueryTable;

class Composer
{
	/**
	 * @var DatabaseWrapper
	 */
	private $database;

	/**
	 * @var ResourceDefinition\Resource
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

	public function setResource(ResourceDefinition\Resource $resourceDefinition)
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

	private function buildQuerySetForResourceAndDescendants(ResourceDefinition\Resource $resourceDefinition, array $filters)
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

	private function getLinksToManyRowsFromResourceSet(ResourceDefinition\Resource $resourceDefinition)
	{
		$foundLinks = new ResourceDefinition\LinkList();
		foreach ($resourceDefinition->links as $link) {
			/** @var ResourceDefinition\Link $link */
			if (in_array($link->type, array(ResourceDefinition\Link::ONE_TO_ONE, ResourceDefinition\Link::MANY_TO_ONE))) {
				$childLinks = $this->getLinksToManyRowsFromResourceSet($link->getChildResource());
				foreach ($childLinks as $childLink) {
					$foundLinks->push($childLink);
				}
			} else {
				$foundLinks->push($link);
			}
		}
		return $foundLinks;
	}

	private function buildQueryForResource(ResourceDefinition\Resource $resourceDefinition, array $filters)
	{
		$query = $this->database->query()->select()
			->setFields($this->buildQueryFieldsFromResource($resourceDefinition))
			->from($this->getQueryTable($resourceDefinition->table->name, $resourceDefinition->table->getAlias()))
			->setJoins($this->buildQueryJoinsFromResource($resourceDefinition));

		$constraint = $this->buildQueryConstraintFromFilters($filters, $resourceDefinition, $resourceDefinition->getAlias());
		if ($constraint !== null) {
			$query->where($constraint);
		}

		return $query;
	}

	private function buildQueryJoinsFromResource(ResourceDefinition\Resource $resource)
	{
		$joins = array();
		foreach ($resource->links as $link) {
			/** @var ResourceDefinition\Link $link */
			if (in_array($link->type, array(ResourceDefinition\Link::ONE_TO_ONE, ResourceDefinition\Link::MANY_TO_ONE))) {
				$childResource = $link->getChildResource();

				foreach ($link->getConstraints() as $constraint) {
					/** @var ResourceDefinition\LinkConstraint $constraint */
					$joinConstraints[] = $this->buildJoinConstraint($constraint->parentAttribute->field, $constraint->childAttribute->field);
				}
				$firstJoinConstraint = array_shift($joinConstraints);
				foreach ($joinConstraints as $constraint) {
					$firstJoinConstraint->andOn($constraint);
				}

				$joins[] = $this->database->query()->join()->inner()
					->table($this->getQueryTable($childResource->table->name, $childResource->table->getAlias()))
					->on($firstJoinConstraint);

				$joins = array_merge($joins, $this->buildQueryJoinsFromResource($childResource));
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

	private function buildJoinConstraint(ResourceDefinition\TableField $parentField, ResourceDefinition\TableField $childField)
	{
		$parentTable = $this->getQueryTable($parentField->table->name, $parentField->table->getAlias());
		$childTable = $this->getQueryTable($childField->table->name, $childField->table->getAlias());
		$joinConstraint = $this->database->query()->constraint()
			->setSubject($parentTable->field($parentField->name))
			->equals($childTable->field($childField->name));
		return $joinConstraint;
	}

	private function buildQueryConstraintFromFilters(array $filters, ResourceDefinition\Resource $resourceDefinition, $parentAlias)
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

	private function buildQueryConstraintListFromFilters(array $filters, ResourceDefinition\Resource $resourceDefinition, $parentAlias)
	{
		$constraints = array();
		foreach ($filters as $filterName => $filter) {
			if ($filter instanceof Filter) {
				$attribute = $filter->attribute;
				$field = $this->buildQueryFieldFromAlias($attribute->field->getAlias(), $parentAlias);
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
					if (in_array($resourceLink->type, array(ResourceDefinition\Link::ONE_TO_ONE, ResourceDefinition\Link::MANY_TO_ONE))) {
						$childResource = $resourceLink->getChildResource();
						$constraints = array_merge($constraints, $this->buildQueryConstraintListFromFilters($filter, $childResource, $filterName));
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

	private function buildQueryFieldsFromResource(ResourceDefinition\Resource $resource)
	{
		$resourceFields = $this->buildQueryFieldsFromAttributes($resource->attributes);
		$linkFields = $this->buildQueryFieldsFromLinks($resource->links);
		return array_merge($resourceFields, $linkFields);
	}

	private function buildQueryFieldsFromAttributes(ResourceDefinition\AttributeList $attributes)
	{
		$queryFields = array();
		foreach ($attributes as $attribute) {
			/** @var ResourceDefinition\Attribute $attribute */
			$tableName = $attribute->table->name;
			$tableAlias = $attribute->table->getAlias();
			$fieldName = $attribute->field->name;
			$queryField = $this->getQueryTable($tableName, $tableAlias)->field($fieldName)->setAlias($attribute->field->getAlias());
			$queryFields[] = $queryField;
		}
		return $queryFields;
	}

	private function buildQueryFieldsFromLinks(ResourceDefinition\LinkList $links)
	{
		$queryFields = array();
		foreach ($links as $link) {
			/** @var ResourceDefinition\Link $link */
			if (in_array($link->type, array(ResourceDefinition\Link::ONE_TO_ONE, ResourceDefinition\Link::MANY_TO_ONE))) {
				$childResource = $link->getChildResource();
				$childFields = $this->buildQueryFieldsFromResource($childResource);
				$queryFields = array_merge($queryFields, $childFields);
			}
		}
		return $queryFields;
	}

	private function buildQuerySetForLinkDescendants(ResourceDefinition\Link $link, array $filters)
	{
		$childResource = $link->getChildResource();
		$querySet = null;
		if ($link->type === ResourceDefinition\Link::MANY_TO_MANY) {
			$querySet = $this->buildQuerySetForSubJoinedResourceAndDescendants($link, $filters);
		} elseif ($link->type === ResourceDefinition\Link::ONE_TO_MANY) {
			$querySet = $this->buildQuerySetForResourceAndDescendants($childResource, $filters);
		}
		return $querySet;
	}

	private function buildQuerySetForSubJoinedResourceAndDescendants(ResourceDefinition\Link $link, array $filters)
	{
		$resourceDefinition = $link->getChildResource();
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

	private function buildQueryForSubJoinsAndResource(ResourceDefinition\Link $link, array $filters)
	{
		$resourceDefinition = $link->getChildResource();
		$query = $this->database->query()->select();

		$subJoinGroups = $this->groupSubJoinsByChild($link);

		$queryJoins = array();
		foreach ($subJoinGroups as $childTableAlias => $subJoins) {
			$join = $this->database->query()->join()->inner();
			$join->table($this->getQueryTable($link->getChildResource()->table->name, $link->getChildResource()->table->getAlias()));

			if (!isset($firstSubJoin)) {
				$firstSubJoinName = array_keys($subJoins)[0];
				$firstSubJoin = $subJoins[$firstSubJoinName];
			}

			$joinConstraints = array();
			foreach ($subJoins as $joinDefinition) {
				/** @var ResourceDefinition\TableJoin $joinDefinition */
				if ($joinDefinition->parentTable->getAlias() !== $link->parentResource->getAlias()) {
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
		$queryJoins = array_merge($queryJoins, $this->buildQueryJoinsFromResource($resourceDefinition));
		$query->setJoins($queryJoins);

		$firstTableAlias = $firstSubJoin->childTable->getAlias();
		$linkField = $this->getQueryTable($firstTableAlias, $firstTableAlias)->field($firstSubJoin->childField->name);

		$queryFields = $this->buildQueryFieldsFromResource($resourceDefinition);
		$queryFields[] = $linkField;

		$table = $firstSubJoin->childTable;
		$query->setFields($queryFields)
			->from($this->getQueryTable($table->name, $table->getAlias()));

		$queryConstraint = $this->buildQueryConstraintFromFilters($filters, $link->getChildResource(), $link->name);
		if (!is_null($queryConstraint)) {
			$query->where($queryConstraint);
		}

		return $query;
	}

	private function groupSubJoinsByChild(ResourceDefinition\Link $resourceLink)
	{
		$groupedJoins = array();
		foreach ($resourceLink->constraints as $constraint) {
			/** @var ResourceDefinition\LinkConstraint $constraint */
			foreach ($constraint->subJoins as $subJoin) {
				/** @var ResourceDefinition\TableJoin $subJoin */
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
