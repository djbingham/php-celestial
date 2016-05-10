<?php
namespace Sloth\Module\Data\TableQuery\QuerySet;

use Sloth\Module\Data\Table\Face\ConstraintInterface;
use Sloth\Module\Data\Table\Face\FieldInterface;
use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\Table\Face\SubJoinInterface;
use Sloth\Module\Data\Table\Face\SubJoinListInterface;
use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkListInterface;

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
			$resourceData = $this->formatDataForTableAndDescendants($rawData, $tableDefinition, $tableDefinition);
			$resourceData = $this->stripLinkFieldsAndTableNamesFromFormattedData($resourceData, $tableDefinition);
			$resourceData = $this->filterResourceData($resourceData, $tableDefinition, $filters);
		} else {
			$resourceData = array();
		}
		return $resourceData;
	}

	private function formatDataForTableAndDescendants(array $rawData, TableInterface $table, TableInterface $primaryTable, JoinInterface $parentJoin = null)
	{
		$formattedData = array();
		$tableAlias = $table->getAlias();
		$primaryTableData = $this->getTableData($table, $primaryTable, $rawData);

		if (!empty($primaryTableData)) {
			if ($parentJoin === null) {
				$tableAliasRegex = sprintf('/^%s\./', $tableAlias);
			} else {
				$tablesToInclude = array($tableAlias);
				foreach ($parentJoin->intermediaryTables as $intermediaryTable) {
					$tablesToInclude[] = $intermediaryTable->getAlias();
				}
				$tableAliasRegex = sprintf('/^(%s)\./', implode('|', $tablesToInclude));
			}

			foreach ($primaryTableData as $dataRow) {
				$formattedRow = array();

				foreach ($dataRow as $fieldAlias => $value) {
					if (preg_match($tableAliasRegex, $fieldAlias)) {
						$formattedRow[$fieldAlias] = $value;
					}
				}

				$formattedData[] = $formattedRow;
			}
		}

		/** @var JoinInterface $join */
		foreach ($table->links as $join) {
			$childTable = $join->getChildTable();
			$childQueryData = $this->getTableData($childTable, $primaryTable, $rawData);

			foreach ($formattedData as &$formattedRow) {
				$formattedRow[$join->name] = array();
			}

			if ($join->type === JoinInterface::MANY_TO_MANY) {
				$descendantData = $this->formatDataForTableAndDescendants($rawData, $childTable, $table, $join);

				foreach ($descendantData as $descendantRow) {
					$linkedParentRowIndices = $this->getIndicesOfLinkedParentRows($descendantRow, $formattedData, $join);

					foreach ($linkedParentRowIndices as $parentRowIndex) {
						$formattedData[$parentRowIndex][$join->name][] = $descendantRow;
					}
				}
			} else {
				$descendantData = $this->formatDataForTableAndDescendants($rawData, $childTable, $primaryTable);

				foreach ($childQueryData as $childRow) {
					$parentRowIndices = $this->getIndicesOfLinkedParentRows($childRow, $primaryTableData, $join);

					if (count($parentRowIndices) > 0) {
						$parentRowIndex = $parentRowIndices[0];

						foreach ($descendantData as $descendantRow) {
							$descendantRowMatchesChildRow = true;

							foreach ($childRow as $childFieldAlias => $childFieldValue) {
								if ($descendantRow[$childFieldAlias] !== $childFieldValue) {
									$descendantRowMatchesChildRow = false;
								}
							}

							if (
								$descendantRowMatchesChildRow &&
								!in_array($descendantRow, $formattedData[$parentRowIndex][$join->name])
							) {
								if ($join->type === JoinInterface::ONE_TO_MANY) {
									$formattedData[$parentRowIndex][$join->name][] = $descendantRow;
								} else {
									$formattedData[$parentRowIndex][$join->name] = $descendantRow;
									break;
								}
							}
						}
					}
				}
			}
		}

		return $formattedData;
	}

	private function stripLinkFieldsAndTableNamesFromFormattedData(array $formattedData, TableInterface $primaryTable)
	{
		$strippedData = array();


		foreach ($formattedData as $rowIndex => $formattedRow) {

			$strippedData[] = $this->stripLinkFieldsAndTableNamesFromFormattedDataRow($formattedRow, $primaryTable);
		}

		return $strippedData;
	}

	private function stripLinkFieldsAndTableNamesFromFormattedDataRow(array $formattedRow, TableInterface $primaryTable)
	{
		$strippedRow = array();

		/** @var FieldInterface $field */
		foreach ($primaryTable->fields as $field) {
			$fieldAlias = $field->getAlias();

			if (array_key_exists($fieldAlias, $formattedRow)) {
				$fieldName = preg_replace('/^.+\./', '', $fieldAlias);
				$strippedRow[$fieldName] = $formattedRow[$fieldAlias];
			}
		}

		/** @var JoinInterface $join */
		foreach ($primaryTable->links as $join) {
			if (array_key_exists($join->name, $formattedRow)) {
				$childTable = $join->getChildTable();
				$joinData = $formattedRow[$join->name];

				if (in_array($join->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
					$strippedRow[$join->name] = $this->stripLinkFieldsAndTableNamesFromFormattedData($joinData, $childTable);
				} else {
					$strippedRow[$join->name] = $this->stripLinkFieldsAndTableNamesFromFormattedDataRow($joinData, $childTable);
				}
			}
		}

		return $strippedRow;
	}

	private function getTableData(TableInterface $targetTable, TableInterface $primaryTable, array $rawData)
	{
		$primaryTableAlias = $primaryTable->getAlias();
		$targetTableAlias = $targetTable->getAlias();
		$targetTableAliasRegex = sprintf('/^%s\./', $targetTableAlias);
		$targetData = array();

		if (array_key_exists($targetTableAlias, $rawData)) {
			foreach ($rawData[$targetTableAlias] as $targetTableRow) {
				$targetData[] = $targetTableRow;
			}
		} elseif (array_key_exists($primaryTableAlias, $rawData)) {
			foreach ($rawData[$primaryTableAlias] as $parentRowIndex => $parentRow) {
				$childRow = array();

				foreach ($parentRow as $parentFieldAlias => $parentValue) {
					if (preg_match($targetTableAliasRegex, $parentFieldAlias, $matches)) {
						$childRow[$parentFieldAlias] = $parentValue;
					}
				}
				$targetData[] = $childRow;
			}
		}

		return $targetData;
	}

	private function getIndicesOfLinkedParentRows(array $childRow, array $parentData, JoinInterface $join)
	{
		$linkedParentRowIndices = array();

		/** @var ConstraintInterface $constraint */
		foreach ($join->getConstraints() as $constraint) {
			$parentFieldAlias = $constraint->parentField->getAlias();
			$childFieldAlias = $constraint->childField->getAlias();

			if (array_key_exists($childFieldAlias, $childRow)) {
				$childValue = $childRow[$childFieldAlias];
				$parentValue = null;

				foreach ($parentData as $parentRowIndex => $parentRow) {
					$isLinked = false;

					if ($constraint->subJoins === null || $constraint->subJoins->length() === 0) {
						$parentValue = $parentRow[$parentFieldAlias];

						if ($parentValue === $childValue) {
							$isLinked = true;
						}
					} else {
						/** @var SubJoinInterface $subJoin */
						foreach ($constraint->subJoins as $subJoin) {
							if ($subJoin->parentTable === $join->parentTable) {
								$subParentFieldAlias = $subJoin->parentField->getAlias();
								$subChildFieldAlias = $subJoin->childField->getAlias();

								if (
									array_key_exists($subChildFieldAlias, $childRow) &&
									$childRow[$subChildFieldAlias] === $parentRow[$subParentFieldAlias]
								) {
									$isLinked = true;
								}
							}
						}
					}

					if ($isLinked) {
						$linkedParentRowIndices[] = $parentRowIndex;
					}
				}
			}
		}

		return array_unique($linkedParentRowIndices);
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
