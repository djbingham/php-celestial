<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Conductor;

use Sloth\Module\Data\TableQuery\QuerySet\Base;
use Sloth\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use PhpMySql\QueryBuilder\Query\Insert;
use PhpMySql\QueryBuilder\Query\Update;

class UpdateConductor extends Base\AbstractConductor
{
	/**
	 * @var MultiQueryWrapperInterface
	 */
	private $executedQuerySet;

	/**
	 * @var array
	 */
	private $updatedData = array();

	public function conduct()
	{
		$this->executedQuerySet = new MultiQueryWrapper();
		while ($this->querySetToExecute->length() > 0) {
			$queryWrapper = $this->querySetToExecute->shift();
			$this->executeQueryWithLinks($queryWrapper);
			$this->executedQuerySet->push($queryWrapper);
		}
		return $this->updatedData;
	}

	private function executeQueryWithLinks(QueryWrapperInterface $queryWrapper)
	{
		if ($queryWrapper instanceof SingleQueryWrapperInterface) {
			$this->executeQuery($queryWrapper);
		} elseif ($queryWrapper instanceof MultiQueryWrapperInterface) {
			$this->executeQuerySet($queryWrapper);
		}

		$childLinks = $queryWrapper->getChildLinks();
		if ($childLinks !== null) {
			/** @var QueryLinkInterface $childLink */
			foreach ($childLinks as $childLink) {
				$childQueryWrapper = $childLink->getChildQueryWrapper();
				$this->executeQueryWithLinks($childQueryWrapper);
			}
		}
	}

	private function executeQuerySet(MultiQueryWrapperInterface $multiQueryWrapper)
	{
		/** @var QueryWrapperInterface $queryWrapper */
		foreach ($multiQueryWrapper as $queryWrapper) {
			$this->executeQueryWithLinks($queryWrapper);
		}
	}

	private function executeQuery(SingleQueryWrapperInterface $queryWrapper)
	{
		/** @var Insert|Update $query */
		$table = $queryWrapper->getTable();
		$query = $queryWrapper->getQuery();
		$data = $queryWrapper->getData();
		$filters = $queryWrapper->getFilters();

		$filterData = array();
		if ($filters !== null) {
			foreach ($filters as $filterName => $filterValue) {
				$filterAlias = $table->fields->getByName($filterName)->getAlias();
				$filterData[$filterAlias] = $filterValue;
			}
		}

		if ($query !== null) {
			$this->database->execute($query);
		}

		$updatedData = array();
		if ($query instanceof Insert) {
			foreach ($data as $rowIndex => $dataRow) {
				$updatedData[$rowIndex] = $filterData;
				if (!is_array($dataRow)) {
					exit;
				}
				foreach ($dataRow as $fieldName => $value) {
					$fieldAlias = $table->fields->getByName($fieldName)->getAlias();
					$updatedData[$rowIndex][$fieldAlias] = $value;
				}
			}
			$this->updatedData[$table->name] = $updatedData;
		} elseif ($query instanceof Update) {
			$updatedData = $filterData;
			foreach ($data as $fieldName => $value) {
				$fieldAlias = $table->fields->getByName($fieldName)->getAlias();
				$updatedData[$fieldAlias] = $value;
			}
			$this->updatedData[$table->name][] = $updatedData;
		}

		$linkData = $this->dataParser->extractLinkListData($queryWrapper->getChildLinks(), array($updatedData));

		$this->applyLinkDataToChildQueries($linkData, $queryWrapper);

		return $updatedData;
	}

	private function applyLinkDataToChildQueries(array $linkData, QueryWrapperInterface $parentQueryWrapper)
	{
		/** @var QueryLinkInterface $link */
		foreach ($parentQueryWrapper->getChildLinks() as $link) {
			$targetQueryWrapper = $link->getChildQueryWrapper();
			if ($targetQueryWrapper !== null) {
				$this->applyLinkDataToQueryWrapper($linkData, $targetQueryWrapper);
			}
		}
		return $this;
	}

	private function applyLinkDataToQueryWrapper(array $linkData, QueryWrapperInterface $querySetWrapper)
	{
		if ($querySetWrapper instanceof SingleQueryWrapperInterface) {
			$this->applyLinkDataToSingleQuery($linkData, $querySetWrapper);
		} else {
			foreach ($querySetWrapper as $queryWrapper) {
				$this->applyLinkDataToQueryWrapper($linkData, $queryWrapper);
			}
		}
	}

	private function applyLinkDataToSingleQuery(array $linkData, SingleQueryWrapperInterface $queryWrapper)
	{
		$targetTable = $queryWrapper->getTable();

		if (array_key_exists($targetTable->getAlias(), $linkData)) {
			/** @var Update $query */
			$query = $queryWrapper->getQuery();
			if ($query instanceof Update || $query instanceof Insert) {
				$queryData = $query->getData();
				$numRows = $queryData->numRows();
				$targetLinkData = $linkData[$targetTable->getAlias()];

				foreach ($targetLinkData as $fieldAlias => $values) {
					$field = $targetTable->fields->getByAlias($fieldAlias);
					foreach ($values as $rowIndex => $value) {
						if ($rowIndex <= $numRows) {
							$queryField = $query->getTable()->field($field->name);
							$queryValue = $this->database->value()->guess($value);

							$queryData
								->beginRow($rowIndex)
								->set($queryField, $queryValue)
								->endRow();
						}
					}
				}

				$query->data($queryData);
			}
		}
	}
}
