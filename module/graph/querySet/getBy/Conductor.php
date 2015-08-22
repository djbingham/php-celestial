<?php
namespace Sloth\Module\Graph\QuerySet\GetBy;

use Sloth\Module\Graph\QuerySet\Base;
use Sloth\Module\Graph\QuerySet\QuerySet;
use Sloth\Module\Graph\QuerySet\QuerySetItem;
use Sloth\Module\Graph\Definition;
use SlothMySql\QueryBuilder\Query\Constraint;
use SlothMySql\QueryBuilder\Query\Select;

class Conductor extends Base\Conductor
{
	/**
	 * @var QuerySet
	 */
	private $executedQuerySet;

	/**
	 * @var array
	 */
	private $fetchedData = array();

	public function conduct()
	{
		$this->executedQuerySet = new QuerySet();
		while ($this->querySetToExecute->length() > 0) {
			$querySetItem = $this->querySetToExecute->shift();
			$this->executedQuerySet->push($querySetItem);
			$this->executeQuerySetItem($querySetItem);
		}
		return $this->fetchedData;
	}

	private function executeQuerySetItem(QuerySetItem $item)
	{
		/** @var Select $query */
		$query = $item->getQuery();
		$data = $this->database->execute($query)->getData();

		$newConstraint = $this->buildConstraintForReQuery($item, $data);
		if (!empty($newConstraint)) {
			$newQuery = clone $query;
			$newQuery->setConstraint($newConstraint);
			$data = $this->database->execute($newQuery)->getData();
		}

		$this->fetchedData[$item->getTableName()] = $data;

		$linkData = $this->dataParser->extractLinkListData($item->getLinks(), $data);
		$this->applyLinkDataToQueries($linkData);

		return $data;
	}

	private function buildConstraintForReQuery(QuerySetItem $querySetItem, array $data)
	{
		$constraints = array();
		$masterConstraint = null;
		$joinFromParent = $querySetItem->getParentLink();

		if ($joinFromParent !== null) {
			/** @var Definition\Table\Join\Constraint $constraintDefinition */
			foreach ($joinFromParent->getConstraints() as $constraintDefinition) {
				if (!empty($constraintDefinition->subJoins)) {
					$firstSubJoin = $constraintDefinition->subJoins->getByParentTableAlias($joinFromParent->parentTable->getAlias());
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

	private function buildQueryConstraintForValueList(Definition\Table\Field $field, array $values)
	{
		$queryValues = array();
		foreach ($values as $index => $value) {
			$queryValues[$index] = $this->database->value()->guess($value);
		}
		$queryField = $this->database->value()->table($field->table->getAlias())->field($field->name);
		$queryValue = $this->database->value()->guess($queryValues);
		$constraint = $this->database->query()->constraint()
			->setSubject($queryField)
			->in($queryValue);
		return $constraint;
	}

	private function applyLinkDataToQueries(array $linkData)
	{
		foreach ($this->executedQuerySet as $executedQuerySetItem) {
			/** @var QuerySetItem $executedQuerySetItem */
			$links = $executedQuerySetItem->getLinks();

			foreach ($this->querySetToExecute as $targetQuerySetItem) {
				/** @var QuerySetItem $targetQuerySetItem */
				$targetTableName = $targetQuerySetItem->getTableName();
				if (array_key_exists($targetTableName, $linkData)) {
					$targetLinkData = $linkData[$targetTableName];
					$linkToTargetTable = $links->getByChild($targetTableName);
					if ($linkToTargetTable instanceof Definition\Table\Join) {
						$constraint = $this->buildLinkConstraint($linkToTargetTable, $targetLinkData);
						if ($constraint instanceof Constraint) {
							$targetQuerySetItem->getQuery()->where($constraint);
						}
					}
				}
			}
		}
		return $this;
	}

	private function buildLinkConstraint(Definition\Table\Join $link, $linkData)
	{
		/** @var \Sloth\Module\Graph\Definition\Table\Join\Constraint $constraintDefinition */
		foreach ($link->getConstraints() as $constraintDefinition) {
			if ($constraintDefinition->subJoins !== null && $constraintDefinition->subJoins->length() > 0) {
				foreach ($constraintDefinition->subJoins as $subJoin) {
					/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $subJoin */
					$tableName = $subJoin->childTable->getAlias();
					$field = $subJoin->childField;
					$queryField = $this->database->value()
						->table($tableName)
						->field($field->name);
					if (array_key_exists($field->getAlias(), $linkData)) {
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
