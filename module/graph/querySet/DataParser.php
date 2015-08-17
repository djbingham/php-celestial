<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Definition;

class DataParser
{
	public function extractLinkListData(Definition\Table\JoinList $links, array $data)
	{
		$linkData = array();
		foreach ($links as $link) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			foreach ($this->extractLinkData($link, $data) as $fieldName => $value) {
				$linkData[$link->getChildTable()->getAlias()][$fieldName] = $value;
			}
		}
		return $linkData;
	}

	public function extractLinkData(Definition\Table\Join $link, array $data)
	{
		$linkData = array();
		foreach ($link->constraints as $constraint) {
			/** @var \Sloth\Module\Graph\Definition\Table\Join\Constraint $constraint */
			if ($constraint->subJoins instanceof Definition\Table\Join\SubJoinList && $constraint->subJoins->length() > 0) {
				foreach ($constraint->subJoins as $subJoin) {
					/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $subJoin */
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

	public function formatResourceData(array $rawData, Definition\Table $tableDefinition, array $filters = array())
	{
		if (!empty($rawData)) {
			$resourceData = $this->extractResourceData($tableDefinition, $rawData);
			$resourceData = $this->filterResourceData($resourceData, $tableDefinition, $filters);
		} else {
			$resourceData = array();
		}
		return $resourceData;
	}

	private function extractResourceData(Definition\Table $tableDefinition, array $rawData, array $linkFilters = array())
	{
		$fieldData = array();
		foreach ($rawData[$tableDefinition->getAlias()] as $rowIndex => $rowData) {
			/** @var \Sloth\Module\Graph\Definition\Table\Field $field */
			if ($this->rowMatchesExpectedData($rowData, $linkFilters)) {
				foreach ($tableDefinition->fields as $field) {
					$fieldAlias = $field->getAlias();
					if (array_key_exists($fieldAlias, $rowData)) {
						$fieldData[$rowIndex][$field->name] = $rowData[$fieldAlias];
					}
				}
			}
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			foreach ($tableDefinition->links as $link) {
				if (in_array($link->type, array(Definition\Table\Join::ONE_TO_ONE, Definition\Table\Join::MANY_TO_ONE))) {
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

	private function getLinkData(Definition\Table\Join $link, array $parentRowData)
	{
		$linkData = array();
		/** @var \Sloth\Module\Graph\Definition\Table\Join\Constraint $constraint */
		foreach ($link->getConstraints() as $constraint) {
			if ($constraint->subJoins !== null && $constraint->subJoins->length() > 0) {
				/** @var \Sloth\Module\Graph\Definition\Table\Join\SubJoin $subJoin */
				foreach ($constraint->subJoins as $subJoin) {
					$parentAlias = $subJoin->parentField->getAlias();
					$childAlias = $subJoin->childField->getAlias();
					if (array_key_exists($parentAlias, $parentRowData)) {
						$value = $parentRowData[$parentAlias];
						if ($subJoin->parentTable->getAlias() === $link->parentTable->getAlias()) {
							$linkData[$childAlias] = $value;
						}
					}
				}
			} else {
				$parentAlias = $constraint->parentField->getAlias();
				$childAlias = $constraint->childField->getAlias();
				$value = $parentRowData[$parentAlias];
				$linkData[$childAlias] = $value;
			}
		}
		return $linkData;
	}

	private function filterResourceData(array $resourceData, Definition\Table $tableDefinition, array $filters)
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
	 * @param Definition\Table $tableDefinition
	 * @param array $filters
	 * @return bool
	 */
	private function resourceRowContainsRequiredAttributes(array $resourceRow, Definition\Table $tableDefinition, array $filters)
	{
		$filterCount = 0;
		$matchedFilterCount = 0;
		/** @var Definition\Table\Join $link */
		foreach ($tableDefinition->links as $link) {
			if (array_key_exists($link->name, $filters)) {
				$filterCount++;
				if (array_key_exists($link->name, $resourceRow)) {
					if (in_array($link->type, array(Definition\Table\Join::ONE_TO_MANY, Definition\Table\Join::MANY_TO_MANY))) {
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

	private function filterResourceSubRowsByRequiredAttributes(array $rows, Definition\Table $tableDefinition, array $filters)
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
