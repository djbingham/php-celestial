<?php
namespace Sloth\Module\DataTableQuery\QuerySet;

use Sloth\Module\DataTable\Face\ConstraintInterface;
use Sloth\Module\DataTable\Face\FieldInterface;
use Sloth\Module\DataTable\Face\JoinInterface;
use Sloth\Module\DataTable\Face\SubJoinInterface;
use Sloth\Module\DataTable\Face\SubJoinListInterface;
use Sloth\Module\DataTable\Face\TableInterface;
use Sloth\Module\DataTableQuery\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\DataTableQuery\QuerySet\Face\QueryLinkListInterface;

class DataParser
{
	public function extractLinkListData(QueryLinkListInterface $links, array $data)
	{
		$linkData = array();
		/** @var QueryLinkInterface $link */
		foreach ($links as $link) {
			$join = $link->getJoinDefinition();
			foreach ($this->extractLinkData($join, $data) as $fieldAlias => $value) {
				if ($join->type === JoinInterface::MANY_TO_MANY) {
					/** @var ConstraintInterface $constraint */
					foreach ($join->getConstraints() as $constraint) {
						/** @var SubJoinInterface $subJoin */
						foreach ($constraint->subJoins as $subJoin) {
							$subJoinTableAlias = $subJoin->childTable->getAlias();
							$subJoinFieldAlias = $subJoin->childField->getAlias();
							if ($subJoinFieldAlias === $fieldAlias) {
								$linkData[$subJoinTableAlias][$subJoin->childField->getAlias()] = $value;
							}
						}
					}

				} else {
					$linkData[$join->getChildTable()->getAlias()][$fieldAlias] = $value;
				}
			}
		}
		return $linkData;
	}

	public function extractLinkData(JoinInterface $link, array $data)
	{
		$linkData = array();
		$constraints = $link->constraints;
		if ($constraints !== null) {
			foreach ($link->constraints as $constraint) {
				/** @var ConstraintInterface $constraint */
				if ($constraint->subJoins instanceof SubJoinListInterface && $constraint->subJoins->length() > 0) {
					foreach ($constraint->subJoins as $subJoin) {
						/** @var SubJoinInterface $subJoin */
						$parentFieldAlias = $subJoin->parentField->getAlias();
						$childFieldAlias = $subJoin->childField->getAlias();
						$values = $this->getFieldValues($parentFieldAlias, $data);
						if (!empty($values)) {
							$linkData[$childFieldAlias] = $values;
						}
					}
				} else {
					$parentFieldAlias = $constraint->parentField->getAlias();
					$childFieldAlias = $constraint->childField->getAlias();
					$values = $this->getFieldValues($parentFieldAlias, $data);
					$linkData[$childFieldAlias] = $values;
				}
			}
		}
		return $linkData;
	}

	public function getFieldValues($fieldName, array $data)
	{
		$values = array();
		foreach ($data as $row) {
			if (array_key_exists($fieldName, $row)) {
				$values[] = $row[$fieldName];
			}
		}
		return $values;
	}

	public function formatResourceData(array $rawData, TableInterface $tableDefinition, array $filters = array())
	{
		if (!empty($rawData)) {
			$resourceData = $this->extractResourceData($tableDefinition, $rawData);
			$resourceData = $this->filterResourceData($resourceData, $tableDefinition, $filters);
		} else {
			$resourceData = array();
		}
		return $resourceData;
	}

	private function extractResourceData(TableInterface $tableDefinition, array $rawData, array $linkFilters = array())
	{
		$tableAlias = $tableDefinition->getAlias();
		$fieldData = array();
		if (array_key_exists($tableAlias, $rawData)) {
			foreach ($rawData[$tableAlias] as $rowIndex => $rowData) {
				/** @var FieldInterface $field */
				if ($this->rowMatchesExpectedData($rowData, $linkFilters)) {
					foreach ($tableDefinition->fields as $field) {
						$fieldAlias = $field->getAlias();
						if (array_key_exists($fieldAlias, $rowData)) {
							$fieldData[$rowIndex][$field->name] = $rowData[$fieldAlias];
						}
					}
				}
				/** @var JoinInterface $link */
				foreach ($tableDefinition->links as $link) {
					if (in_array($link->type, array(JoinInterface::ONE_TO_ONE, JoinInterface::MANY_TO_ONE))) {
						foreach ($link->getChildTable()->fields as $field) {
							$fieldAlias = $field->getAlias();
							if (array_key_exists($fieldAlias, $rowData)) {
								$fieldData[$rowIndex][$link->name][$field->name] = $rowData[$fieldAlias];
							}
						}
					} else {
						$childLinkFilters = $this->getLinkData($link, $rowData);
						if ($this->rowMatchesExpectedData($rowData, $linkFilters)) {
							$childData = $this->extractResourceData($link->getChildTable(), $rawData, $childLinkFilters);

							$fieldData[$rowIndex][$link->name] = array();
							foreach ($childData as $childRow) {
								$fieldData[$rowIndex][$link->name][] = $childRow;
							}
						}
					}
				}
			}
		}
		return $fieldData;
	}

	private function rowMatchesExpectedData(array $rowData, array $expectedValues)
	{
		$matches = 0;
		foreach ($expectedValues as $childFieldAlias => $parentValue) {
			if ($rowData[$childFieldAlias] === $parentValue) {
				$matches++;
			}
		}
		return $matches === count($expectedValues);
	}

	private function getLinkData(JoinInterface $link, array $parentRowData)
	{
		$linkData = array();
		/** @var ConstraintInterface $constraint */
		foreach ($link->getConstraints() as $constraint) {
			if ($constraint->subJoins !== null && $constraint->subJoins->length() > 0) {
				/** @var SubJoinInterface $subJoin */
				foreach ($constraint->subJoins as $subJoin) {
					$parentAlias = $subJoin->parentField->getAlias();
					$childAlias = $subJoin->childField->getAlias();
					if (array_key_exists($parentAlias, $parentRowData)) {
						if ($subJoin->parentTable->getAlias() === $link->parentTable->getAlias()) {
							$linkData[$childAlias] = $parentRowData[$parentAlias];
						}
					}
				}
			} else {
				$parentAlias = $constraint->parentField->getAlias();
				$childAlias = $constraint->childField->getAlias();
				if (array_key_exists($parentAlias, $parentRowData)) {
					$linkData[$childAlias] = $parentRowData[$parentAlias];
				}
			}
		}
		return $linkData;
	}

	private function filterResourceData(array $resourceData, TableInterface $tableDefinition, array $filters)
	{
		$filteredData = array();
		while (!empty($resourceData)) {
			$resourceRow = array_shift($resourceData);
			if ($this->resourceRowContainsRequiredAttributes($resourceRow, $tableDefinition, $filters)) {
				$filteredData[] = $resourceRow;
			}
		}
		return $filteredData;
	}

	/**
	 * Check whether a given resource row contains all of the links required to have matched a set of filters.
	 * Returns false if any linked table had a filter set and has no data in the resource row.
	 * Note that we do not need to check every table field, since all fields of a table are queried together.
	 *
	 * @param array $resourceRow
	 * @param TableInterface $tableDefinition
	 * @param array $filters
	 * @return bool
	 */
	private function resourceRowContainsRequiredAttributes(array $resourceRow, TableInterface $tableDefinition, array $filters)
	{
		$filterCount = 0;
		$matchedFilterCount = 0;
		/** @var JoinInterface $link */
		foreach ($tableDefinition->links as $link) {
			if (array_key_exists($link->name, $filters)) {
				$filterCount++;
				if (array_key_exists($link->name, $resourceRow)) {
					if (in_array($link->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
						$resourceRow[$link->name] = $this->filterResourceSubRowsByRequiredAttributes($resourceRow[$link->name], $link->getChildTable(), $filters[$link->name]);
						if (!empty($resourceRow[$link->name])) {
							$matchedFilterCount++;
							break;
						}
					} elseif ($this->resourceRowContainsRequiredAttributes($resourceRow[$link->name], $link->getChildTable(), $filters[$link->name])) {
						$matchedFilterCount++;
						break;
					}
				}
			}
		}
		return $filterCount === $matchedFilterCount;
	}

	private function filterResourceSubRowsByRequiredAttributes(array $rows, TableInterface $tableDefinition, array $filters)
	{
		$filteredRows = array();
		while (!empty($rows)) {
			$row = array_shift($rows);
			if ($this->resourceRowContainsRequiredAttributes($row, $tableDefinition, $filters)) {
				$filteredRows[] = $row;
			}
		}
		return $filteredRows;
	}
}
