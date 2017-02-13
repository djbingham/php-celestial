<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Composer;

use Celestial\Module\Data\Table\Face\ConstraintInterface;
use Celestial\Module\Data\Table\Face\JoinInterface;
use Celestial\Module\Data\Table\Face\SubJoinInterface;
use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;
use Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLink;
use Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\QueryLinkList;
use Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper\SingleQueryWrapper;
use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Data\TableQuery\QuerySet\Base;
use PhpMySql\Face\Value\TableInterface as QueryTableInterface;

class InsertComposer extends Base\AbstractComposer
{
	public function compose()
	{
		$this->validateData($this->data, $this->tableDefinition);
		return $this->buildQueriesForTableAndDescendants($this->tableDefinition, $this->data);
	}

	protected function validateData(array $rowData, TableInterface $tableDefinition)
	{
		foreach ($rowData as $fieldName => $value) {
			if ($tableDefinition->links->indexOfName($fieldName) !== -1) {
				$join = $tableDefinition->links->getByName($fieldName);
				$this->validateJoins($join);
				$this->validateData($value, $join->getChildTable());
			}
		}
	}

	protected function validateJoins(JoinInterface $join)
	{
		$errorMessage = null;
		$joinAlias = $join->getChildTable()->getAlias();
		switch ($join->onInsert) {
			case JoinInterface::ACTION_INSERT:
				// Insert is valid as long as the data matches expected fields/joins
				if ($join->type === JoinInterface::MANY_TO_MANY) {
					$errorMessage = 'On insert action should not be "%s" for a many-to-many join: %s';
					$errorMessage = sprintf($errorMessage, $join->onInsert, $joinAlias);
				}
				break;
			case JoinInterface::ACTION_IGNORE:
				// Ignore is always valid
				break;
			case JoinInterface::ACTION_ASSOCIATE:
				if ($join->type !== JoinInterface::MANY_TO_MANY) {
					$errorMessage = 'On insert action should not be "%s" for a join that is not many-to-many: %s';
					$errorMessage = sprintf($errorMessage, $join->onInsert, $joinAlias);
				}
				break;
			case JoinInterface::ACTION_REJECT:
			default:
				$errorMessage = sprintf('Data to insert includes a disallowed subset: %s', $joinAlias);
				break;
		}
		if ($errorMessage !== null) {
			throw new InvalidRequestException($errorMessage);
		}
	}

	protected function buildQueriesForTableAndDescendants(TableInterface $tableDefinition, array $data, QueryLink $parentLink = null)
	{
		$querySet = new MultiQueryWrapper();
		$queryWrapper = new SingleQueryWrapper();
		$childLinks = new QueryLinkList();
		$tableData = $this->filterDataByTableFields($data, $tableDefinition);
		$query = $this->buildQueryForTable($tableDefinition, $tableData);
		$queryWrapper
			->setTable($tableDefinition)
			->setQuery($query)
			->setChildLinks($childLinks)
			->setData($tableData);
		if ($parentLink instanceof QueryLinkInterface && $parentLink->getJoinDefinition() !== null) {
			$queryWrapper->setParentLink($parentLink);
		}
		$querySet->push($queryWrapper);

		$linksToInsert = new QueryLinkList();
		$linksToAssociate = new QueryLinkList();
		/** @var JoinInterface $join */
		foreach ($tableDefinition->links as $join) {
			$queryLink = new QueryLink();
			$queryLink
				->setParentQueryWrapper($queryWrapper)
				->setJoinDefinition($join);

			if ($join->onInsert === JoinInterface::ACTION_ASSOCIATE) {
				if ($join->type === JoinInterface::MANY_TO_MANY) {
					$linksToAssociate->push($queryLink);
				} else {
					$linksToInsert->push($queryLink);
				}
			} elseif ($join->onInsert === JoinInterface::ACTION_INSERT) {
				$linksToInsert->push($queryLink);
			}
		}

		foreach ($linksToInsert as $link) {
			$childLinks->push($link);
		}
		foreach ($linksToAssociate as $link) {
			$childLinks->push($link);
		}

		/** @var QueryLinkInterface $link */
		foreach ($linksToInsert as $link) {
			$joinDefinition = $link->getJoinDefinition();
			$childTable = $joinDefinition->getChildTable();

			if (array_key_exists($joinDefinition->name, $data)) {
				$childTableData = $data[$joinDefinition->name];

				if (in_array($joinDefinition->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
					$childQuerySet = new MultiQueryWrapper();
					foreach ($childTableData as $childDataRow) {
						$rowQuerySet = $this->buildQueriesForTableAndDescendants($childTable, $childDataRow, $link);
						$childQuerySet->push($rowQuerySet);
					}
					$link->setChildQueryWrapper($childQuerySet);
				} else {
					$childQuerySet = $this->buildQueriesForTableAndDescendants($childTable, $childTableData, $link);
					$link->setChildQueryWrapper($childQuerySet->getByIndex(0));
				}
			}
		}


		/** @var QueryLinkInterface $link */
		foreach ($linksToAssociate as $link) {
			/** @var JoinInterface $join */
			$join = $link->getJoinDefinition();
			if ($join->type !== JoinInterface::MANY_TO_MANY) {
				continue;
			}

			$joinDefinition = $link->getJoinDefinition();
			if (array_key_exists($joinDefinition->name, $data)) {
				$childTableData = $data[$joinDefinition->name];

				$querySubSet = new MultiQueryWrapper();
				foreach ($childTableData as $childDataRow) {
					$linkData = array();
					$linkTable = null;

					/** @var ConstraintInterface $constraint */
					foreach ($join->getConstraints() as $constraint) {
						/** @var SubJoinInterface $subJoin */
						foreach ($constraint->subJoins as $subJoin) {
							if ($subJoin->parentTable === $tableDefinition) {
								$linkTable = $subJoin->childTable;
							} elseif ($subJoin->childTable === $join->getChildTable()) {
								$linkData[$subJoin->parentField->name] = $childDataRow[$subJoin->childField->name];
							} else {
								throw new InvalidRequestException(
									sprintf(
										'Invalid join `%s` (subjoin `%s` to `%s` references neither parent or child table)',
										$join->getChildTable()->getAlias(),
										$subJoin->parentTable->getAlias(),
										$subJoin->childTable->getAlias()
									)
								);
							}
						}
					}

					if ($linkTable === null) {
						throw new InvalidRequestException(
							sprintf(
								'Invalid join `%s` (no subjoin references the parent table `%s`)',
								$join->getChildTable()->getAlias()
							)
						);
					}

					// Now we have join data to insert
					$childQueryWrapper = $this->buildQueryForLinkTable($linkTable, $linkData, $link);
					$querySubSet->push($childQueryWrapper);
				}
				$link->setChildQueryWrapper($querySubSet);
			}
		}


		return $querySet;
	}

	protected function buildQueryForLinkTable(TableInterface $linkTableDefinition, array $linkData, QueryLink $parentLink = null)
	{
		$queryWrapper = new SingleQueryWrapper();
		$linksToChildren = new QueryLinkList();
		$linkData = $this->filterDataByTableFields($linkData, $linkTableDefinition);
		$query = $this->buildQueryForTable($linkTableDefinition, $linkData);
		$queryWrapper->setTable($linkTableDefinition)
			->setQuery($query)
			->setData($linkData)
			->setChildLinks($linksToChildren);
		if ($parentLink !== null) {
			$queryWrapper->setParentLink($parentLink);
		}
		return $queryWrapper;
	}

	protected function buildQueryForTable(TableInterface $tableDefinition, array $data)
	{
		$queryTable = $this->database->value()->table($tableDefinition->name);
		$queryData = $this->buildQueryData($data, $queryTable);
		$query = $this->database->query()->insert();
		$query->into($queryTable)
			->data($queryData);
		return $query;
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

	protected function filterDataByTableFields(array $data, TableInterface $tableDefinition)
	{
		$tableRow = array();
		foreach ($data as $fieldName => $value) {
			if ($tableDefinition->fields->indexOfName($fieldName) !== -1) {
				$tableRow[$fieldName] = $value;
			}
		}
		return $tableRow;
	}
}
