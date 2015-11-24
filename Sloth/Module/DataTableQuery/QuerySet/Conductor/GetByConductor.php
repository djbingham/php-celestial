<?php
namespace Sloth\Module\DataTableQuery\QuerySet\Conductor;

use Sloth\Module\DataTable\Face\ConstraintInterface;
use Sloth\Module\DataTable\Face\FieldInterface;
use Sloth\Module\DataTable\Face\JoinInterface;
use Sloth\Module\DataTable\Face\SubJoinInterface;
use Sloth\Module\DataTable\Face\TableInterface;
use Sloth\Module\DataTableQuery\QuerySet\Base;
use Sloth\Module\DataTableQuery\QuerySet\Face\MultiQueryWrapperInterface;
use Sloth\Module\DataTableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Sloth\Module\DataTableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use SlothMySql\QueryBuilder\Query\Constraint;
use SlothMySql\QueryBuilder\Query\Select;

class GetByConductor extends Base\AbstractConductor
{
	/**
	 * @var MultiQueryWrapperInterface
	 */
	private $executedQuerySet;

	/**
	 * @var array
	 */
	private $fetchedData = array();

	public function conduct()
	{
		$this->executedQuerySet = new MultiQueryWrapper();
		while ($this->querySetToExecute->length() > 0) {
			$queryWrapper = $this->querySetToExecute->shift();
			$data = $this->executeQuerySetItem($queryWrapper);
			$this->fetchedData[$queryWrapper->getTable()->getAlias()] = $data;
			$this->executedQuerySet->push($queryWrapper);
			if (empty($data)) {
				// If no data found for this query, no point running remaining queries
				break;
			}
		}
		return $this->fetchedData;
	}

	private function executeQuerySetItem(SingleQueryWrapperInterface $queryWrapper)
	{
		/** @var Select $query */
		$query = $queryWrapper->getQuery();
		$data = $this->database->execute($query)->getData();
		$newConstraint = $this->buildConstraintForReQuery($queryWrapper, $data);

		if (!empty($newConstraint)) {
			$newQuery = clone $query;
			$newQuery->setConstraint($newConstraint);
			$data = $this->database->execute($newQuery)->getData();
		}

		$linkData = $this->dataParser->extractLinkListData($queryWrapper->getChildLinks(), $data);
		$this->applyLinkDataToQueries($linkData);

		return $data;
	}

	private function buildConstraintForReQuery(SingleQueryWrapperInterface $queryWrapper, array $data)
	{
		$constraints = array();
		$masterConstraint = null;
		$parentLink = $queryWrapper->getParentLink();

		if ($parentLink !== null) {
			$parentJoin = $parentLink->getJoinDefinition();
			/** @var ConstraintInterface $constraintDefinition */
			foreach ($parentJoin->getConstraints() as $constraintDefinition) {
				$subJoins = $constraintDefinition->subJoins;
				if (!empty($subJoins)) {
					$firstSubJoin = $subJoins->getByParentTableAlias($parentJoin->parentTable->getAlias());
					$joinToParentField = $firstSubJoin->childField;
				} else {
					$joinToParentField = $constraintDefinition->childField;
				}

				$fieldAlias = $joinToParentField->getAlias();
				$reFilterData = array();
				foreach ($data as $rowData) {
					if (array_key_exists($fieldAlias, $rowData)) {
						$reFilterData[] = $rowData[$fieldAlias];
					}
				}
				if (!empty($reFilterData)) {
					$reFilterData = array_unique($reFilterData);
					$constraints[] = $this->buildQueryConstraintForValueList($joinToParentField, $reFilterData);
				}
			}
			$masterConstraint = array_shift($constraints);
			foreach ($constraints as $constraint) {
				$masterConstraint->andWhere($constraint);
			}
		}

		return $masterConstraint;
	}

	private function buildQueryConstraintForValueList(FieldInterface $field, array $values)
	{
		$queryValues = array();
		foreach ($values as $index => $value) {
			$queryValues[$index] = $this->database->value()->guess($value);
		}
		$queryField = $this->database->value()->table($field->table->getAlias())->field($field->name);
		$queryValue = $this->database->value()->guess($queryValues);
		$constraint = $this->database->query()->constraint()
			->setSubject($queryField)
			->setComparator('IN')
			->setValue($queryValue);
		return $constraint;
	}

	private function applyLinkDataToQueries(array $linkData)
	{
		/** @var SingleQueryWrapperInterface $targetQueryWrapper */
		foreach ($this->querySetToExecute as $targetQueryWrapper) {
			$targetTables = array($targetQueryWrapper->getTable());

			$joinDefinition = $targetQueryWrapper->getParentLink()->getJoinDefinition();
			$parentTable = $joinDefinition->parentTable;
			if ($joinDefinition->type === JoinInterface::MANY_TO_MANY) {
				$firstConstraint = $joinDefinition->getConstraints()->getByIndex(0);
				$firstSubJoin = $firstConstraint->subJoins->getByParentTableAlias($parentTable->getAlias());
				array_unshift($targetTables, $firstSubJoin->childTable);
			}

			/** @var TableInterface $targetTable */
			foreach ($targetTables as $targetTable) {
				$constraints = array();
				if (array_key_exists($targetTable->getAlias(), $linkData)) {
					$targetLinkData = $linkData[$targetTable->getAlias()];
					$constraints[] = $this->buildLinkConstraint($joinDefinition, $targetLinkData);
				}
				$constraint = array_shift($constraints);
				if ($constraint instanceof Constraint) {
					foreach ($constraints as $nextConstraint) {
						$constraint->andWhere($nextConstraint);
					}
					/** @var Select $query */
					$query = $targetQueryWrapper->getQuery();
					$query->where($constraint);
				}
			}
		}
		return $this;
	}

	private function buildLinkConstraint(JoinInterface $link, $linkData)
	{
		/** @var ConstraintInterface $constraintDefinition */
		foreach ($link->getConstraints() as $constraintDefinition) {
			if ($constraintDefinition->subJoins !== null && $constraintDefinition->subJoins->length() > 0) {
				/** @var SubJoinInterface $subJoin */
				foreach ($constraintDefinition->subJoins as $subJoin) {
					$field = $subJoin->childField;
					if (array_key_exists($field->getAlias(), $linkData)) {
						$tableName = $subJoin->childTable->getAlias();
						$queryField = $this->database->value()
							->table($tableName)
							->field($field->name);
						$fieldValues = $linkData[$field->getAlias()];

						$queryConstraint = $this->database->query()->constraint()->setSubject($queryField);
						$queryValues = array();
						foreach (array_unique($fieldValues) as $value) {
							$queryValues[] = $this->database->value()->guess($value);
						}
						$queryValue = $this->database->value()->valueList($queryValues);
						$queryConstraint->in($queryValue);

						$queryConstraints[] = $queryConstraint;
					}
				}
			} else {
				$tableName = $constraintDefinition->childField->table->getAlias();
				$field = $constraintDefinition->childField;
				$queryField = $this->database->value()
					->table($tableName)
					->field($field->name);
				$fieldValues = $linkData[$field->getAlias()];

				$queryConstraint = $this->database->query()->constraint()->setSubject($queryField);
				$queryValues = array();
				foreach (array_unique($fieldValues) as $value) {
					$queryValues[] = $this->database->value()->guess($value);
				}
				$queryValue = $this->database->value()->valueList($queryValues);
				$queryConstraint->in($queryValue);

				$queryConstraints[] = $queryConstraint;
			}


		}
		$firstQueryConstraint = null;
		if (!empty($queryConstraints)) {
			$firstQueryConstraint = array_shift($queryConstraints);
			foreach ($queryConstraints as $queryConstraint) {
				$firstQueryConstraint->andWhere($queryConstraint);
			}
		}
		return $firstQueryConstraint;
	}
}
