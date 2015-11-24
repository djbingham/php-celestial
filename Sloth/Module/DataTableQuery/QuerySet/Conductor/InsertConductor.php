<?php
namespace Sloth\Module\DataTableQuery\QuerySet\Conductor;

use Sloth\Module\DataTableQuery\QuerySet\Base;
use Sloth\Module\DataTableQuery\QuerySet\Face\MultiQueryWrapperInterface;
use Sloth\Module\DataTableQuery\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\DataTableQuery\QuerySet\Face\QueryWrapperInterface;
use Sloth\Module\DataTableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Sloth\Module\DataTableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use SlothMySql\QueryBuilder\Query\Insert;

class InsertConductor extends Base\AbstractConductor
{
	/**
	 * @var MultiQueryWrapperInterface
	 */
	private $executedQuerySet;

	/**
	 * @var array
	 */
	private $insertedData = array();

	public function conduct()
	{
		$this->executedQuerySet = new MultiQueryWrapper();

		while ($this->querySetToExecute->length() > 0) {
			$queryWrapper = $this->querySetToExecute->shift();
			$this->executeQueryWithLinks($queryWrapper);
			$this->executedQuerySet->push($queryWrapper);
		}

		return $this->insertedData;
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
				if ($childQueryWrapper !== null) {
					$this->executeQueryWithLinks($childQueryWrapper);
				}
			}
		}
	}

	private function executeQuerySet(MultiQueryWrapperInterface $multiQueryWrapper)
	{
		foreach ($multiQueryWrapper as $queryWrapper) {
			$this->executeQueryWithLinks($queryWrapper);
		}
	}

	private function executeQuery(SingleQueryWrapperInterface $queryWrapper)
	{
		/** @var Insert $query */
		$table = $queryWrapper->getTable();
		$query = $queryWrapper->getQuery();
		$data = $queryWrapper->getData();
		$autoFields = $table->fields->findByPropertyValue('autoIncrement', true);

		$insertedId = $this->database->execute($query)->getInsertId();

		$insertedData = array();
		if ($autoFields->length() > 0) {
			$autoField = $autoFields->getByIndex(0);
			$insertedData[$autoField->getAlias()] = $insertedId;
		}
		if (!empty($data)) {
			foreach ($data as $fieldName => $value) {
				$fieldAlias = $table->fields->getByName($fieldName)->getAlias();
				$insertedData[$fieldAlias] = $value;
			}
		}

		$this->insertedData[$table->name][] = $insertedData;
		$linkData = $this->dataParser->extractLinkListData($queryWrapper->getChildLinks(), array($insertedData));

		$this->applyLinkDataToChildQueries($linkData, $queryWrapper);

		return $insertedData;
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
			/** @var Insert $query */
			$query = $queryWrapper->getQuery();
			$queryData = $query->getData();
			$targetLinkData = $linkData[$targetTable->getAlias()];

			foreach ($targetLinkData as $fieldAlias => $values) {
				$field = $targetTable->fields->getByAlias($fieldAlias);
				foreach ($values as $rowIndex => $value) {
					$queryField = $query->getTable()->field($field->name);
					$queryValue = $this->database->value()->guess($value);

					$queryData
						->beginRow($rowIndex)
						->set($queryField, $queryValue)
						->endRow();

					$targetData = $queryWrapper->getData();
					$targetData[$field->name] = $value;
					$queryWrapper->setData($targetData);
				}
			}
		}
	}
}
