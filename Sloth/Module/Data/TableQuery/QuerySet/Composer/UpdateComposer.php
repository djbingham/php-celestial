<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Composer;

use Sloth\Module\Data\Table\Face\ConstraintInterface;
use Sloth\Module\Data\Table\Face\FieldInterface;
use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\Table\Face\SubJoinInterface;
use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Data\TableQuery\QuerySet\Base;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLink;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper;
use SlothMySql\Face\Value\TableInterface as QueryTableInterface;

class UpdateComposer extends Base\AbstractComposer
{
	public function compose()
	{
		$this->validateFiltersAndData($this->filters, $this->data, $this->tableDefinition);
		return $this->buildQueriesForTableAndDescendants($this->tableDefinition, $this->data, $this->filters);
	}

	protected function validateFiltersAndData(array $filters, array $rowData, TableInterface $tableDefinition, JoinInterface $joinDefinition = null)
	{
		$this->validateFiltersForTable($filters, $tableDefinition, $joinDefinition);
		foreach ($rowData as $fieldName => $fieldValue) {
			if ($tableDefinition->links->indexOfName($fieldName) !== -1) {
				$join = $tableDefinition->links->getByName($fieldName);
				$childTable = $join->getChildTable();

				if (array_key_exists($fieldName, $filters)) {
					$joinFilters = $filters[$fieldName];
				} else {
					$joinFilters = array();
				}

				$this->validateJoins($join);
				$this->validateFiltersAndData($joinFilters, $fieldValue, $childTable, $join);
			}
		}
		return $this;
	}

	protected function validateFiltersForTable(array $filters, TableInterface $tableDefinition, JoinInterface $joinDefinition = null)
	{
		$foundFieldFilter = false;
		if ($joinDefinition !== null && in_array($joinDefinition->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
			foreach ($filters as $rowFilters) {
				$this->validateFiltersForTable($rowFilters, $tableDefinition);
				$foundFieldFilter = true;
			}
		} else {
			/** @var FieldInterface $tableField */
			foreach ($tableDefinition->fields as $tableField) {
				if (array_key_exists($tableField->name, $filters)) {
					$foundFieldFilter = true;
					break;
				}
			}
		}
		if (!$foundFieldFilter && ($joinDefinition === null || $joinDefinition->onUpdate === JoinInterface::ACTION_UPDATE)) {
			throw new InvalidRequestException(
				'No filters given for table: ' . $tableDefinition->getAlias()
			);
		}
		return $this;
	}

	protected function validateJoins(JoinInterface $join)
	{
		$errorMessage = null;
		$joinAlias = $join->getChildTable()->getAlias();
		switch ($join->onUpdate) {
			case JoinInterface::ACTION_INSERT:
				if ($join->type === JoinInterface::MANY_TO_MANY) {
					$errorMessage = 'On update action should not be "%s" for a many-to-many join: %s';
					$errorMessage = sprintf($errorMessage, $join->onUpdate, $joinAlias);
				}
				break;
			case JoinInterface::ACTION_UPDATE:
				// Update is always valid
				break;
			case JoinInterface::ACTION_IGNORE:
				// Ignore is always valid
				break;
			case JoinInterface::ACTION_ASSOCIATE:
				if ($join->type !== JoinInterface::MANY_TO_MANY) {
					$errorMessage = 'On update action should not be "%s" for a join that is not many-to-many: %s';
					$errorMessage = sprintf($errorMessage, $join->onUpdate, $joinAlias);
				}
				break;
			case JoinInterface::ACTION_REJECT:
			default:
				$errorMessage = sprintf('Data to update includes a disallowed subset: %s', $joinAlias);
				break;
		}
		if ($errorMessage !== null) {
			throw new InvalidRequestException($errorMessage);
		}
		return $this;
	}

	protected function buildQueriesForTableAndDescendants(TableInterface $tableDefinition, array $data, array $filters, QueryLinkInterface $parentLink = null)
	{
		$querySet = new MultiQueryWrapper();
		$queryWrapper = new SingleQueryWrapper();
		$childLinks = new QueryLinkList();

		$tableData = $this->extractTableData($tableDefinition, $data);

		if (!empty($tableData)) {
			$tableFilters = $this->extractTableFilters($filters, $tableDefinition);
			$query = $this->buildQueryForTable($tableDefinition, $tableData, $tableFilters);

			$queryWrapper
				->setTable($tableDefinition)
				->setQuery($query)
				->setChildLinks($childLinks)
				->setData($tableData)
				->setFilters($tableFilters);

			if ($parentLink instanceof QueryLinkInterface && $parentLink->getJoinDefinition() !== null) {
				$queryWrapper->setParentLink($parentLink);
			}

			$querySet->push($queryWrapper);

			/** @var JoinInterface $join */
			foreach ($tableDefinition->links as $join) {
				if ($join->onUpdate === JoinInterface::ACTION_IGNORE) {
					continue;
				}

				$childData = array();
				$childFilters = array();

				if (array_key_exists($join->name, $data)) {
					$childData = $data[$join->name];
				}
				if (array_key_exists($join->name, $filters)) {
					$childFilters = $filters[$join->name];
				}

				if ($join->onUpdate === JoinInterface::ACTION_REJECT) {
					throw new InvalidRequestException();
				}

				$queryLink = new QueryLink();
				$queryLink->setParentQueryWrapper($queryWrapper)
					->setJoinDefinition($join);

				switch ($join->type) {
					case JoinInterface::ONE_TO_ONE:
					default:
						if ($join->onUpdate === JoinInterface::ACTION_UPDATE) {
							$childQuerySet = $this->buildQueriesForTableAndDescendants($join->getChildTable(), $childData, $childFilters);
							if ($childQuerySet->length() > 0) {
								$queryLink->setChildQueryWrapper($childQuerySet->getByIndex(0));
							}
						} else {
							throw new InvalidRequestException('Invalid update action for *-to-one join: ' . $join->onUpdate);
						}
						break;
					case JoinInterface::MANY_TO_ONE:
						if ($join->onUpdate === JoinInterface::ACTION_UPDATE) {
							$childQuerySet = $this->buildQueriesForTableAndDescendants($join->getChildTable(), $childData, $childFilters);
							if ($childQuerySet->length() > 0) {
								$queryLink->setChildQueryWrapper($childQuerySet->getByIndex(0));
							}
						} elseif (!in_array($join->onUpdate, array(JoinInterface::ACTION_ASSOCIATE, JoinInterface::ACTION_IGNORE))) {
							throw new InvalidRequestException('Invalid update action for *-to-one join: ' . $join->onUpdate);
						}
						break;
					case JoinInterface::ONE_TO_MANY:
						if ($join->onUpdate === JoinInterface::ACTION_UPDATE) {
							$childQuerySet = new MultiQueryWrapper();
							foreach ($childData as $rowIndex => $childRow) {
								$linkedFields = $join->getLinkedFields();

								/** @var FieldInterface $parentField */
								$parentField = $linkedFields['parent'];

								/** @var FieldInterface $childField */
								$childField = $linkedFields['child'];

								$childRow[$childField->name] = $data[$parentField->name];

								$childRowFilters = array();
								if (array_key_exists($rowIndex, $childFilters)) {
									$childRowFilters = $childFilters[$rowIndex];
								}
								$childRowQueries = $this->buildQueriesForTableAndDescendants($join->getChildTable(), $childRow, $childRowFilters);
								if ($childRowQueries->length() > 0) {
									$childQuerySet->push($childRowQueries);
								}
							}
							if ($childQuerySet->length() > 0) {
								$queryLink->setChildQueryWrapper($childQuerySet);
							}
						} else {
							throw new InvalidRequestException('Invalid update action for one-to-one join: ' . $join->onUpdate);
						}
						break;

					case JoinInterface::MANY_TO_MANY:
						if ($join->onUpdate === JoinInterface::ACTION_ASSOCIATE) {
							$childQuerySet = $this->buildQueriesForLinkTable($join, $data, $filters);
							if ($childQuerySet->length() > 0) {
								$queryLink->setChildQueryWrapper($childQuerySet);
							}
						} else {
							throw new InvalidRequestException('Invalid update action for many-to-many join: ' . $join->onUpdate);
						}
						break;
				}

				if ($queryLink->getChildQueryWrapper() !== null) {
					$childLinks->push($queryLink);
				}
			}
		}

		return $querySet;
	}

	protected function extractTableData(TableInterface $tableDefinition, array $data)
	{
		$tableRow = array();
		foreach ($data as $fieldName => $value) {
			$fieldIndex = $tableDefinition->fields->indexOfName($fieldName);
			if ($fieldIndex !== -1) {
				$tableRow[$fieldName] = $value;
			}
		}
		return $tableRow;
	}

	protected function extractTableFilters(array $filters, TableInterface $table)
	{
		$tableFilters = array();
		foreach ($filters as $name => $value) {
			if ($table->fields->indexOfName($name) !== -1) {
				$tableFilters[$name] = $value;
			}
		}
		return $tableFilters;
	}

	protected function buildQueryForTable(TableInterface $tableDefinition, array $data, array $filters)
	{
		$queryTable = $this->database->value()->table($tableDefinition->name);
		$queryData = $this->buildQueryData($data, $queryTable);

		if (empty($filters)) {
			$query = $this->database->query()->insert();
			$query->into($queryTable)
				->data($queryData);
		} else {
			$queryConstraint = $this->buildConstraintForTable($queryTable, $filters);
			$query = $this->database->query()->update();
			$query->table($queryTable)
				->data($queryData)
				->where($queryConstraint);
		}

		return $query;
	}

	protected function buildConstraintForTable(QueryTableInterface $queryTable, array $filters)
	{
		if (empty($filters)) {
			$constraint = $this->database->query()->constraint();
		} else {
			/** @var \SlothMySql\Face\Query\ConstraintInterface $constraint */
			$constraint = null;
			foreach ($filters as $fieldName => $value) {
				if ($constraint === null) {
					$constraint = $this->buildQueryConstraint($queryTable, $fieldName, $value);
				} else {
					$constraint->andWhere($this->buildQueryConstraint($queryTable, $fieldName, $value));
				}
			}
		}

		return $constraint;
	}

	protected function buildQueryConstraint(QueryTableInterface $table, $fieldName, $value)
	{
		$constraint = $this->database->query()->constraint();
		$querySubject = $table->field($fieldName);
		$queryValue = $this->database->value()->guess($value);

		$constraint->setSubject($querySubject)
			->equals($queryValue);

		return $constraint;
	}

	protected function buildQueryData(array $data, QueryTableInterface $queryTable)
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

	protected function buildQueriesForLinkTable(JoinInterface $join, array $data, array $joinFilters)
	{
		$querySet = new MultiQueryWrapper();

		$parentFilters = $this->extractLinkTableData($join, $joinFilters);
		if (array_key_exists($join->name, $data) && !empty($data[$join->name])) {
			$childData = $data[$join->name];

			$targetLinkData = array();
			/** @var ConstraintInterface $constraint */
			foreach ($join->getConstraints() as $constraint) {
				$linkTable = $constraint->link->intermediaryTables->getByIndex(0);

				/** @var SubJoinInterface $subJoin */
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
		}

		return $querySet;
	}

	protected function extractLinkTableData(JoinInterface $joinDefinition, array $data)
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

	private function getSubJoinFieldLinkedToParent($parentFieldName, JoinInterface $joinDefinition) {
		$foundField = null;

		/** @var ConstraintInterface $constraint */
		foreach ($joinDefinition->getConstraints() as $constraint) {
			/** @var SubJoinInterface $subJoin */
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
