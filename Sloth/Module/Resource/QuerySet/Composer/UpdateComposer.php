<?php
namespace Sloth\Module\Resource\QuerySet\Composer;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Resource\QuerySet\Base;
use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Resource\QuerySet\QueryWrapper\MultiQueryWrapper;
use Sloth\Module\Resource\QuerySet\QueryWrapper\QueryLink;
use Sloth\Module\Resource\QuerySet\QueryWrapper\QueryLinkList;
use Sloth\Module\Resource\QuerySet\QueryWrapper\SingleQueryWrapper;
use SlothMySql\Face\Value\TableInterface;

class UpdateComposer extends Base\AbstractComposer
{
	public function compose()
	{
		$this->validateFiltersAndData($this->filters, $this->data, $this->tableDefinition);
		return $this->buildQueriesForTableAndDescendants($this->tableDefinition, $this->data, $this->filters);
	}

	protected function validateFiltersAndData(array $filters, array $rowData, Definition\Table $tableDefinition, Definition\Table\Join $joinDefinition = null)
	{
		$this->validateFiltersForTable($filters, $tableDefinition, $joinDefinition);
		foreach ($rowData as $fieldName => $value) {
			if ($tableDefinition->links->indexOfName($fieldName) !== -1) {
				$join = $tableDefinition->links->getByName($fieldName);
				$childTable = $join->getChildTable();

				if (array_key_exists($fieldName, $filters)) {
					$joinFilters = $filters[$fieldName];
				} else {
					$joinFilters = array();
				}

				$this->validateJoins($join);
				$this->validateFiltersAndData($joinFilters, $value, $childTable, $join);
			}
		}
		return $this;
	}

	protected function validateFiltersForTable(array $filters, Definition\Table $tableDefinition, Definition\Table\Join $joinDefinition = null)
	{
		$foundFieldFilter = false;
		if ($joinDefinition instanceof Definition\Table\Join && in_array($joinDefinition->type, array(Definition\Table\Join::ONE_TO_MANY, Definition\Table\Join::MANY_TO_MANY))) {
			foreach ($filters as $rowFilters) {
				$this->validateFiltersForTable($rowFilters, $tableDefinition);
				$foundFieldFilter = true;
			}
		} else {
			/** @var Definition\Table\Field $tableField */
			foreach ($tableDefinition->fields as $tableField) {
				if (array_key_exists($tableField->name, $filters)) {
					$foundFieldFilter = true;
					break;
				}
			}
		}
		if (!$foundFieldFilter) {
			throw new InvalidRequestException(
				'No filters given for table: ' . $tableDefinition->getAlias()
			);
		}
		return $this;
	}

	protected function validateJoins(Definition\Table\Join $join)
	{
		$errorMessage = null;
		$joinAlias = $join->getChildTable()->getAlias();
		switch ($join->onUpdate) {
			case Definition\Table\Join::ACTION_INSERT:
				if ($join->type === Definition\Table\Join::MANY_TO_MANY) {
					$errorMessage = 'On update action should not be "%s" for a many-to-many join: %s';
					$errorMessage = sprintf($errorMessage, $join->onUpdate, $joinAlias);
				}
				break;
			case Definition\Table\Join::ACTION_UPDATE:
				// Update is always valid
				break;
			case Definition\Table\Join::ACTION_IGNORE:
				// Ignore is always valid
				break;
			case Definition\Table\Join::ACTION_ASSOCIATE:
				if ($join->type !== Definition\Table\Join::MANY_TO_MANY) {
					$errorMessage = 'On update action should not be "%s" for a join that is not many-to-many: %s';
					$errorMessage = sprintf($errorMessage, $join->onUpdate, $joinAlias);
				}
				break;
			case Definition\Table\Join::ACTION_REJECT:
			default:
				$errorMessage = sprintf('Data to update includes a disallowed subset: %s', $joinAlias);
				break;
		}
		if ($errorMessage !== null) {
			throw new InvalidRequestException($errorMessage);
		}
		return $this;
	}

	protected function buildQueriesForTableAndDescendants(Definition\Table $tableDefinition, array $data, array $filters, QueryLinkInterface $parentLink = null)
	{
		$querySet = new MultiQueryWrapper();
		$queryWrapper = new SingleQueryWrapper();
		$childLinks = new QueryLinkList();
		$tableData = $this->extractTableData($tableDefinition, $data);
		$tableFilters = $this->extractTableFilters($filters, $tableDefinition);
		$query = $this->buildQueryForTable($tableDefinition, $tableData, $tableFilters);

		$queryWrapper
			->setTable($tableDefinition)
			->setQuery($query)
			->setChildLinks($childLinks)
			->setData($tableData);
		if ($parentLink instanceof QueryLinkInterface && $parentLink->getJoinDefinition() !== null) {
			$queryWrapper->setParentLink($parentLink);
		}

		$querySet->push($queryWrapper);

		/** @var Definition\Table\Join $join */
		foreach ($tableDefinition->links as $join) {
			if ($join->onUpdate === Definition\Table\Join::ACTION_IGNORE) {
				continue;
			}

			if (array_key_exists($join->name, $data)) {
				$childData = $data[$join->name];
			}
			if (array_key_exists($join->name, $filters)) {
				$childFilters = $filters[$join->name];
			}

			if (!empty($childData) && !empty($childFilters)) {
				if ($join->onUpdate === Definition\Table\Join::ACTION_REJECT) {
					throw new InvalidRequestException();
				}

				$queryLink = new QueryLink();
				$queryLink->setParentQueryWrapper($queryWrapper)
					->setJoinDefinition($join);

				switch ($join->type) {
					case Definition\Table\Join::ONE_TO_ONE:
					case Definition\Table\Join::MANY_TO_ONE:
					default:
						if ($join->onUpdate === Definition\Table\Join::ACTION_UPDATE) {
							$childQuerySet = $this->buildQueriesForTableAndDescendants($join->getChildTable(), $childData, $childFilters);
							$queryLink->setChildQueryWrapper($childQuerySet->getByIndex(0));
						} else {
							throw new InvalidRequestException('Invalid update action for one/many-to-one join: ' . $join->onUpdate);
						}
					break;
					case Definition\Table\Join::ONE_TO_MANY:
						if ($join->onUpdate === Definition\Table\Join::ACTION_UPDATE) {
							$childQuerySet = new MultiQueryWrapper();
							foreach ($childData as $rowIndex => $childRow) {
								$childRowQueries = $this->buildQueriesForTableAndDescendants($join->getChildTable(), $childRow, $childFilters[$rowIndex]);
								$childQuerySet->push($childRowQueries);
							}
							$queryLink->setChildQueryWrapper($childQuerySet);
						} else {
							throw new InvalidRequestException('Invalid update action for one-to-one join: ' . $join->onUpdate);
						}
						break;

					case Definition\Table\Join::MANY_TO_MANY:
						if ($join->onUpdate === Definition\Table\Join::ACTION_ASSOCIATE) {
							$childQuerySet = $this->buildQueriesForLinkTable($join, $data, $filters);
							$queryLink->setChildQueryWrapper($childQuerySet);
						} else {
							throw new InvalidRequestException('Invalid update action for one/many-to-one join: ' . $join->onUpdate);
						}
						break;
				}
				$childLinks->push($queryLink);
			}
		}

		return $querySet;
	}

	protected function extractTableData(Definition\Table $tableDefinition, array $data)
	{
		$tableRow = array();
		foreach ($data as $fieldName => $value) {
			$fieldIndex = $tableDefinition->fields->indexOfName($fieldName);
			if ($fieldIndex !== -1) {
				if (!$tableDefinition->fields->getByIndex($fieldIndex)->autoIncrement) {
					$tableRow[$fieldName] = $value;
				}
			}
		}
		return $tableRow;
	}

	protected function extractTableFilters(array $filters, Definition\Table $table)
	{
		$tableFilters = array();
		foreach ($filters as $name => $value) {
			if ($table->fields->indexOfName($name) !== -1) {
				$tableFilters[$name] = $value;
			}
		}
		return $tableFilters;
	}

	protected function buildQueryForTable(Definition\Table $tableDefinition, array $data, array $filters)
	{
		$queryTable = $this->database->value()->table($tableDefinition->name);
		$queryData = $this->buildQueryData($data, $queryTable);
		$queryConstraint = $this->buildConstraintForTable($queryTable, $filters);
		$query = $this->database->query()->update();
		$query->table($queryTable)
			->data($queryData)
			->where($queryConstraint);
		return $query;
	}

	protected function buildConstraintForTable(TableInterface $queryTable, array $filters)
	{
		$constraint = $this->database->query()->constraint();
		foreach ($filters as $fieldName => $value) {
			$querySubject = $queryTable->field($fieldName);
			$queryValue = $this->database->value()->guess($value);
			$constraint->setSubject($querySubject)
				->equals($queryValue);
		}
		return $constraint;
	}

	protected function buildQueryData(array $data, TableInterface $queryTable)
	{
		$queryData = $this->database->value()->tableData();
		if (!empty($data)) {
			$queryData->beginRow();
			foreach ($data as $fieldName => $value) {
				$queryData->set($queryTable->field($fieldName), $this->database->value()->guess($value));
			}
			$queryData->endRow();
		}
		return $queryData;
	}

	protected function buildQueriesForLinkTable(Definition\Table\Join $join, array $data, array $joinFilters)
	{
		$querySet = new MultiQueryWrapper();

		$parentFilters = $this->extractLinkTableData($join, $joinFilters);
		$childData = $data[$join->name];

		$targetLinkData = array();
		/** @var Definition\Table\Join\Constraint $constraint */
		foreach ($join->getConstraints() as $constraint) {
			$linkTable = $constraint->link->intermediaryTables->getByIndex(0);

			/** @var Definition\Table\Join\SubJoin $subJoin */
			foreach ($constraint->subJoins as $subJoin) {
				$parentFieldAlias = $subJoin->parentField->name;
				$childFieldAlias = $subJoin->childField->name;

				if ($subJoin->parentTable === $join->parentTable) {
					$linkParentField = $subJoin->childField;
					$parentValue = $parentFilters[$subJoin->childField->name];
				}

				foreach ($childData as $rowIndex => $rowChildData) {
					if ($subJoin->parentTable === $join->parentTable) {
						$targetLinkData[$rowIndex][$childFieldAlias] = $parentFilters[$subJoin->childField->name];
					} elseif ($subJoin->childTable === $join->getChildTable()) {
						$targetLinkData[$rowIndex][$parentFieldAlias] = $rowChildData[$subJoin->childField->name];
					}
				}
			}
		}

		if (isset($linkTable) && isset($linkParentField) && isset($parentValue)) {
			$queryTable = $this->database->value()->table($linkTable->name);
			$queryParentField = $queryTable->field($linkParentField->name);
			$queryParentValue = $this->database->value()->guess($parentValue);

			// Delete all existing links
			$deleteQuery = $this->database->query()->delete()
				->from($queryTable)
				->where($this->database->query()->constraint()
					->setSubject($queryParentField)
					->equals($queryParentValue)
				);

			// Insert all new links
			$queryData = $queryTable->data();
			foreach ($targetLinkData as $rowData) {
				$queryData->beginRow();
				foreach ($rowData as $fieldName => $fieldValue) {
					$queryField = $queryTable->field($fieldName);
					$queryValue = $this->database->value()->guess($fieldValue);
					$queryData->set($queryField, $queryValue);
				}
				$queryData->endRow();
			}
			$insertQuery = $this->database->query()->insert()
				->into($queryTable)
				->data($queryData);

			$deleteQueryWrapper = new SingleQueryWrapper();
			$deleteQueryWrapper->setQuery($deleteQuery)
				->setTable($linkTable)
				->setData($parentFilters)
				->setChildLinks(new QueryLinkList());

			$insertQueryWrapper = new SingleQueryWrapper();
			$insertQueryWrapper->setQuery($insertQuery)
				->setTable($linkTable)
				->setData($targetLinkData)
				->setChildLinks(new QueryLinkList());

			$querySet
				->push($deleteQueryWrapper)
				->push($insertQueryWrapper);
		}

		return $querySet;
	}

	protected function extractLinkTableData(Definition\Table\Join $joinDefinition, array $data)
	{
		$tableRow = array();
		foreach ($data as $fieldName => $value) {
			$linkField = $this->getSubJoinFieldLinkedToParent($fieldName, $joinDefinition);
			if ($linkField !== null) {
				$tableRow[$linkField->name] = $value;
			}
		}
		return $tableRow;
	}

	private function getSubJoinFieldLinkedToParent($parentFieldName, Definition\Table\Join $joinDefinition) {
		$foundField = null;

		/** @var Definition\Table\Join\Constraint $constraint */
		foreach ($joinDefinition->getConstraints() as $constraint) {
			/** @var Definition\Table\Join\SubJoin $subJoin */
			foreach ($constraint->subJoins as $subJoin) {
				if ($subJoin->parentTable === $joinDefinition->parentTable) {
					if ($parentFieldName === $subJoin->parentField->name) {
						$foundField = $subJoin->childField;
					}
					break(2);
				}
			}
		}

		return $foundField;
	}
}
