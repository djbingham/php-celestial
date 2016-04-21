<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Composer;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Data\Table\Face\ConstraintInterface;
use Sloth\Module\Data\Table\Face\FieldInterface;
use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\Table\Face\SubJoinInterface;
use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Base;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper;
use SlothMySql\Face\Value\TableInterface as QueryTableInterface;

class DeleteComposer extends Base\AbstractComposer
{
	public function compose()
	{
		$this->validateFilters($this->filters, $this->tableDefinition);
		$sortedFilters = $this->sortFiltersByTable($this->filters, $this->tableDefinition);
		return $this->buildQueries($sortedFilters);
	}

	protected function validateFilters(array $filters, TableInterface $tableDefinition, JoinInterface $joinDefinition = null)
	{
		$this->validateFiltersForTable($filters, $tableDefinition, $joinDefinition);

		foreach ($filters as $fieldName => $filterValue) {
			if ($tableDefinition->links->indexOfName($fieldName) !== -1) {
				$join = $tableDefinition->links->getByName($fieldName);
				$childTable = $join->getChildTable();

				$this->validateJoins($join);
				$this->validateFilters($filterValue, $childTable, $join);
			}
		}

		return $this;
	}

	protected function validateFiltersForTable(array $filters, TableInterface $tableDefinition, JoinInterface $joinDefinition = null)
	{
		$filtersHaveValidFields = true;
		$filtersExistForTables = false;

		if ($joinDefinition instanceof JoinInterface && in_array($joinDefinition->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
			foreach ($filters as $rowFilters) {
				$filtersHaveValidFields = $filtersHaveValidFields && $this->validateFiltersForTable($rowFilters, $tableDefinition);

				/** @var FieldInterface $tableField */
				foreach ($tableDefinition->fields as $tableField) {
					if (array_key_exists($tableField->name, $rowFilters)) {
						$filtersExistForTables = true;
						break;
					}
				}
			}
		} else {
			/** @var FieldInterface $tableField */
			foreach ($filters as $fieldName => $filterValue) {
				if (
					$tableDefinition->fields->indexOfName($fieldName) === -1
					&& $tableDefinition->links->indexOfName($fieldName) === -1
				) {
					$filtersHaveValidFields = false;
					break;
				}
			}

			/** @var FieldInterface $tableField */
			foreach ($tableDefinition->fields as $tableField) {
				if (array_key_exists($tableField->name, $filters)) {
					$filtersExistForTables = true;
					break;
				}
			}
		}

		if (!$filtersHaveValidFields) {
			throw new InvalidRequestException(
				'Invalid filters given for table: ' . $tableDefinition->getAlias()
			);
		} elseif (!$filtersExistForTables) {
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
		switch ($join->onDelete) {
			case JoinInterface::ACTION_INSERT:
				if ($join->type === JoinInterface::MANY_TO_MANY) {
					$errorMessage = 'On delete action should not be "%s" for a many-to-many join: %s';
					$errorMessage = sprintf($errorMessage, $join->onDelete, $joinAlias);
				}
				break;
			case JoinInterface::ACTION_DELETE:
				// Delete is always valid
				break;
			case JoinInterface::ACTION_IGNORE:
				// Ignore is always valid
				break;
			case JoinInterface::ACTION_ASSOCIATE:
				if ($join->type !== JoinInterface::MANY_TO_MANY) {
					$errorMessage = 'On delete action should not be "%s" for a join that is not many-to-many: %s';
					$errorMessage = sprintf($errorMessage, $join->onDelete, $joinAlias);
				}
				break;
			case JoinInterface::ACTION_REJECT:
			default:
				$errorMessage = sprintf('Data to delete includes a disallowed subset: %s', $joinAlias);
				break;
		}
		if ($errorMessage !== null) {
			throw new InvalidRequestException($errorMessage);
		}
		return $this;
	}

	protected function sortFiltersByTable(array $filters, TableInterface $tableDefinition, JoinInterface $parentJoin = null)
	{
		if ($parentJoin === null) {
			$tableName = $tableDefinition->name;
		} else {
			$tableName = $parentJoin->getChildTable()->name;
		}

		$sortedFilters = array();
		$sortedFilters[$tableName]['table'] = $tableDefinition;

		/** @var FieldInterface $field*/
		$tableFilters = array();
		foreach ($tableDefinition->fields as $field) {
			$fieldName = $field->name;
			if (array_key_exists($fieldName, $filters)) {
				$tableFilters[$fieldName] = $filters[$fieldName];
			}
		}
		$sortedFilters[$tableName]['filters'][] = $tableFilters;

		/** @var JoinInterface $join */
		foreach ($tableDefinition->links as $join) {
			if (array_key_exists($join->name, $filters)) {
				if ($join->onDelete === JoinInterface::ACTION_DELETE) {
					if (in_array($join->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
						$childTable = $join->getChildTable();
						$sortedChildFilters = array(
							$childTable->name => array(
								'table' => $childTable,
								'filters' => array()
							)
						);
						foreach ($filters[$join->name] as $rowChildFilters) {
							$sortedRowFilters = $this->sortFiltersByTable($rowChildFilters, $join->getChildTable());

							foreach ($sortedRowFilters as $childTableName => $childFilterData) {
								if (!array_key_exists($childTableName, $sortedChildFilters)) {
									$sortedChildFilters[$childTableName] = array(
										'table' => $childFilterData['table'],
										'filters' => array()
									);
								}
								$childFilters = array_merge_recursive($sortedChildFilters[$childTableName]['filters'], $childFilterData['filters']);
								$sortedChildFilters[$childTableName]['filters'] = $childFilters;
							}
						}
					} else {
						$sortedChildFilters = $this->sortFiltersByTable($filters[$join->name], $join->getChildTable());
					}

					$sortedFilters = array_merge($sortedFilters, $sortedChildFilters);
				} elseif ($join->onDelete === JoinInterface::ACTION_ASSOCIATE) {
					/** @var ConstraintInterface $constraint */
					foreach ($join->getConstraints() as $constraint) {
						/** @var SubJoinInterface $subJoin */
						foreach ($constraint->subJoins as $subJoin) {
							if ($subJoin->parentTable === $tableDefinition) {
								$linkTable = $subJoin->childTable;
								$parentFieldName = $subJoin->parentField->name;
								$childFieldName = $subJoin->childField->name;
								$subFilters[$linkTable->name]['table'] = $linkTable;
								$subFilters[$linkTable->name]['filters'][$childFieldName] = $filters[$parentFieldName];
							}
						}

						foreach ($subFilters as $linkTableName => $subFilterData) {
							$linkTable = $subFilterData['table'];
							$linkFilters = $subFilterData['filters'];
							if (!array_key_exists($linkTableName, $sortedFilters)) {
								$sortedFilters[$linkTableName] = array(
									'table' => $linkTable,
									'filters' => array()
								);
							}
							$sortedFilters[$linkTableName]['filters'][] = $linkFilters;
						}
					}
				}
			}
		}

		return $sortedFilters;
	}

	protected function buildQueries(array $filters)
	{
		$querySet = new MultiQueryWrapper();

		/** @var JoinInterface $join */
		foreach ($filters as $tableName => $tableData) {
			$table = $tableData['table'];
			$filters = $tableData['filters'];

			$query = $this->buildQuery($table, $filters);

			$queryWrapper = new SingleQueryWrapper();
			$childLinks = new QueryLinkList();
			$queryWrapper
				->setTable($table)
				->setQuery($query)
				->setChildLinks($childLinks);

			$querySet->push($queryWrapper);
		}

		return $querySet;
	}

	protected function buildQuery(TableInterface $table, array $filters)
	{
		$queryTable = $this->database->value()->table($table->name);
		$queryConstraint = $this->buildConstraintForTable($queryTable, $filters);

		$query = $this->database->query()->delete();
		$query->from($queryTable)
			->where($queryConstraint);

		return $query;
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

	protected function buildQueryForTable(TableInterface $tableDefinition, array $filters)
	{
		$queryTable = $this->database->value()->table($tableDefinition->name);
		$queryConstraint = $this->buildConstraintForTable($queryTable, $filters);
		$query = $this->database->query()->delete();
		$query->from($queryTable)
			->where($queryConstraint);
		return $query;
	}

	protected function buildConstraintForTable(QueryTableInterface $queryTable, array $filters)
	{
		$constraints = array();

		foreach ($filters as $filterSet) {
			$subConstraints = array();
			foreach ($filterSet as $fieldName => $value) {
				$subConstraint = $this->database->query()->constraint();

				$querySubject = $queryTable->field($fieldName);

				if (is_array($value)) {
					$value = array_map(function ($item) {
						return $this->database->value()->guess($item);
					}, $value);
				}
				$queryValue = $this->database->value()->guess($value);

				$subConstraint->setSubject($querySubject)
					->equals($queryValue);

				$subConstraints[] = $subConstraint;

			}
			$firstSubConstraint = array_shift($subConstraints);
			foreach ($subConstraints as $subConstraint) {
				$firstSubConstraint->andWhere($subConstraint);
			}

			$constraints[] = $firstSubConstraint;
		}

		$firstConstraint = array_shift($constraints);
		foreach ($constraints as $constraint) {
			$firstConstraint->orWhere($constraint);
		}

		return $firstConstraint;
	}

	protected function buildQueriesForLinkTable(JoinInterface $join, array $joinFilters)
	{
		$querySet = new MultiQueryWrapper();

		$parentFilters = $this->extractLinkTableFilters($join, $joinFilters);

		$targetLinkData = array();
		/** @var ConstraintInterface $constraint */
		foreach ($join->getConstraints() as $constraint) {
			$linkTable = $constraint->link->intermediaryTables->getByIndex(0);

			/** @var SubJoinInterface $subJoin */
			foreach ($constraint->subJoins as $subJoin) {
				if ($subJoin->parentTable === $join->parentTable) {
					$linkParentField = $subJoin->childField;
					$parentValue = $parentFilters[$subJoin->childField->name];
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

			$deleteQueryWrapper = new SingleQueryWrapper();
			$deleteQueryWrapper->setQuery($deleteQuery)
				->setTable($linkTable)
				->setData($parentFilters)
				->setChildLinks(new QueryLinkList());

			$querySet->push($deleteQueryWrapper);
		}

		return $querySet;
	}

	protected function extractLinkTableFilters(JoinInterface $joinDefinition, array $filters)
	{
		$tableRow = array();
		foreach ($filters as $fieldName => $value) {
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
