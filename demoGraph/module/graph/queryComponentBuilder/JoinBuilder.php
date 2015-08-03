<?php
namespace DemoGraph\Module\Graph\QueryComponentBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;
use DemoGraph\Module\Graph\QueryComponent;

class JoinBuilder
{
	/**
	 * @var array
	 */
	private $cache = array(
		'queryTable' => array()
	);

	public function buildFromResourceLinkDefinition(ResourceDefinition\Link $linkDefinition)
	{
		$joins = new QueryComponent\TableJoinList();
		foreach ($linkDefinition->getConstraints() as $constraint) {
			$constraintJoins = $this->buildJoinsForConstraint($constraint);
			foreach ($constraintJoins as $join) {
				$joins->push($join);
			}
		}
		return $joins;
	}

	private function buildJoinsForConstraint(ResourceDefinition\LinkConstraint $constraintDefinition)
	{
		$joins = new QueryComponent\TableJoinList();
		if (empty($constraintDefinition->subJoins)) {
			$join = $this->buildDirectJoinForConstraint($constraintDefinition);
			$joins->push($join);
		} else {
			$subJoins = $this->buildJoinsFromDefinitionList($constraintDefinition->subJoins);
			foreach ($subJoins as $join) {
				$joins->push($join);
			}
		}
		return $joins;
	}

	private function buildDirectJoinForConstraint(ResourceDefinition\LinkConstraint $constraintDefinition)
	{
		$parentTable = $this->buildQueryTable($constraintDefinition->parentAttribute->table);
		$childTable = $this->buildQueryTable($constraintDefinition->childAttribute->table);
		$constraintList = new QueryComponent\ConstraintList();
		$constraintList->push($this->buildConstraintFromResourceLinkConstraintDefinition($constraintDefinition));

		$join = new QueryComponent\TableJoin();
		$join->setParentTable($parentTable)
			->setChildTable($childTable)
			->setConstraints($constraintList);
		return $join;
	}

	private function buildJoinsFromDefinitionList(ResourceDefinition\TableJoinList $joinDefinitions)
	{
		$joins = new QueryComponent\TableJoinList();
		foreach ($joinDefinitions as $subJoin) {
			/** @var ResourceDefinition\TableJoin $subJoin */
			$joins->push($this->buildJoinFromDefinition($subJoin));
		}
		return $joins;
	}

	private function buildJoinFromDefinition(ResourceDefinition\TableJoin $joinDefinition)
	{
		$parentTable = $this->buildQueryTable($joinDefinition->parentTable);
		$childTable = $this->buildQueryTable($joinDefinition->childTable);
		$constraintList = new QueryComponent\ConstraintList();
		$constraintList->push($this->buildConstraintFromJoinDefinition($joinDefinition));
		$queryJoin = new QueryComponent\TableJoin();
		$queryJoin->setParentTable($parentTable)->setChildTable($childTable)->setConstraints($constraintList);
		return $queryJoin;
	}

	private function buildConstraintFromJoinDefinition(ResourceDefinition\TableJoin $joinDefinition)
	{
		$constraint = new QueryComponent\Constraint();
		$constraint->setSubject($joinDefinition->parentField)
			->setValue($joinDefinition->childField);
		return $constraint;
	}

	private function buildConstraintFromResourceLinkConstraintDefinition(ResourceDefinition\LinkConstraint $constraintDefinition)
	{
		$constraint = new QueryComponent\Constraint();
		$constraint->setSubject($constraintDefinition->parentAttribute)
			->setValue($constraintDefinition->childAttribute);
		return $constraint;
	}

	/**
	 * @param ResourceDefinition\Table $tableDefinition
	 * @return QueryComponent\Table
	 */
	private function buildQueryTable(ResourceDefinition\Table $tableDefinition)
	{
		if (!array_key_exists($tableDefinition->alias, $this->cache['queryTable'])) {
			$queryTable = new QueryComponent\Table();
			$queryTable->setDefinition($tableDefinition);
			$this->cache['queryTable'][$tableDefinition->alias] = $queryTable;
		}
		return $this->cache['queryTable'][$tableDefinition->alias];
	}
}
