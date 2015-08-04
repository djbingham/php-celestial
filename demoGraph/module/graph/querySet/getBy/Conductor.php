<?php
namespace DemoGraph\Module\Graph\QuerySet\GetBy;

use DemoGraph\Module\Graph\QuerySet\DataParser;
use DemoGraph\Module\Graph\QuerySet\QuerySet;
use DemoGraph\Module\Graph\QuerySet\QuerySetItem;
use DemoGraph\Module\Graph\ResourceDefinition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Query\Constraint;

class Conductor
{
	/**
	 * @var DatabaseWrapper
	 */
	private $database;

	/**
	 * @var DataParser
	 */
	private $dataParser;

	/**
	 * @var QuerySet
	 */
	private $querySetToExecute;

	/**
	 * @var QuerySet
	 */
	private $executedQuerySet;

	/**
	 * @var array
	 */
	private $fetchedData = array();

	public function setDatabase(DatabaseWrapper $database)
	{
		$this->database = $database;
		return $this;
	}

	public function setDataParser(DataParser $dataParser)
	{
		$this->dataParser = $dataParser;
		return $this;
	}

	public function setQuerySet(QuerySet $querySet)
	{
		$this->querySetToExecute = $querySet;
		return $this;
	}

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
		$data = $this->database->execute($item->getQuery())->getData();
		$linkData = $this->dataParser->extractLinkListData($item->getLinks(), $data);

		$this->fetchedData[$item->getResourceName()] = $data;
		$this->applyLinkDataToQueries($linkData);

		return $data;
	}

	private function applyLinkDataToQueries(array $linkData)
	{
		foreach ($this->executedQuerySet as $executedQuerySetItem) {
			/** @var QuerySetItem $executedQuerySetItem */
			$links = $executedQuerySetItem->getLinks();

			foreach ($this->querySetToExecute as $targetQuerySetItem) {
				/** @var QuerySetItem $targetQuerySetItem */
				$targetResourceName = $targetQuerySetItem->getResourceName();
				if (array_key_exists($targetResourceName, $linkData)) {
					$targetLinkData = $linkData[$targetResourceName];
					$linkToTargetResource = $links->getByChild($targetResourceName);
					if ($linkToTargetResource instanceof ResourceDefinition\Link) {
						$constraint = $this->buildLinkConstraint($linkToTargetResource, $targetLinkData);
						if ($constraint instanceof Constraint) {
							$targetQuerySetItem->getQuery()->where($constraint);
						}
					}
				}
			}
		}
		return $this;
	}

	private function buildLinkConstraint(ResourceDefinition\Link $link, $linkData)
	{
		/** @var ResourceDefinition\LinkConstraint $constraintDefinition */
		foreach ($link->getConstraints() as $constraintDefinition) {
			if ($constraintDefinition->subJoins !== null && $constraintDefinition->subJoins->length() > 0) {
				foreach ($constraintDefinition->subJoins as $subJoin) {
					/** @var ResourceDefinition\LinkSubJoin $subJoin */
					$tableName = $subJoin->childResource->getAlias();
					$field = $subJoin->childAttribute;
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
				$tableName = $constraintDefinition->childAttribute->resource->getAlias();
				$attribute = $constraintDefinition->childAttribute;
				$queryField = $this->database->value()
					->table($tableName)
					->field($attribute->name);
				$attributeValues = $linkData[$attribute->getAlias()];

				$queryConstraint = $this->database->query()->constraint()->setSubject($queryField);
				$queryValues = array();
				foreach (array_unique($attributeValues) as $value) {
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
