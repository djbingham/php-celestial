<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Conductor;

use Sloth\Module\Data\TableQuery\QuerySet\Base;
use Sloth\Module\Data\TableQuery\QuerySet\Face\MultiQueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper\MultiQueryWrapper;
use SlothMySql\QueryBuilder\Query\Insert;
use SlothMySql\QueryBuilder\Query\Update;

class UpdateConductor extends Base\AbstractConductor
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
				$this->executeQueryWithLinks($childQueryWrapper);
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
		/** @var Insert|Update $query */
		$table = $queryWrapper->getTable();
		$query = $queryWrapper->getQuery();
		$data = $queryWrapper->getData();

		$this->database->execute($query);

		$insertedData = array();
		if ($query instanceof Insert) {
			foreach ($data as $rowIndex => $dataRow) {
				foreach ($dataRow as $fieldName => $value) {
					$fieldAlias = $table->fields->getByName($fieldName)->getAlias();
					$insertedData[$rowIndex][$fieldAlias] = $value;
				}
			}
			$this->insertedData[$table->name] = $insertedData;
		} elseif ($query instanceof Update) {
			foreach ($data as $fieldName => $value) {
				$fieldAlias = $table->fields->getByName($fieldName)->getAlias();
				$insertedData[$fieldAlias] = $value;
			}
			$this->insertedData[$table->name][] = $insertedData;
		}

		return $insertedData;
	}
}