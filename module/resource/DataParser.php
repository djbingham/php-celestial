<?php

namespace Sloth\Module\Resource;

use SlothMySql\QueryBuilder\Wrapper as MySqlQueryBuilder;

class DataParser
{
	/**
	 * @var MySqlQueryBuilder
	 */
	protected $sqlFactory;

	/**
	 * @var ResourceDefinition
	 */
	protected $resourceDefinition;

	/**
	 * @var array Multi-table data sorted by row
	 */
	protected $rawData;

	/**
	 * @var array Data sorted by table, then by row
	 */
	protected $tabularData = array();

	/**
	 * @param MySqlQueryBuilder $sqlFactory
	 */
	public function __construct(MySqlQueryBuilder $sqlFactory)
	{
		$this->sqlFactory = $sqlFactory;
	}

	/**
	 * @return MySqlQueryBuilder
	 */
	public function getSqlFactory()
	{
		return $this->sqlFactory;
	}

	/**
	 * @param ResourceDefinition $resourceDefinition
	 * @return DataParser $this
	 */
	public function setResourceDefinition(ResourceDefinition $resourceDefinition)
	{
		$this->resourceDefinition = $resourceDefinition;
		return $this;
	}

	/**
	 * @return ResourceDefinition
	 */
	public function getResourceDefinition()
	{
		return $this->resourceDefinition;
	}

	/**
	 * @param array $rawData
	 * @return array
	 */
	public function parse(array $rawData)
	{
		return $this->tabulateDataSet($rawData);
	}

	/**
	 * Sort data into a Data\Table object for each table represented in the data set
	 * @param array $data
	 * @return array
	 */
	protected function tabulateDataSet(array $data)
	{
		$groupedData = $this->groupDataByTable($data);
		$groupedData = $this->propagateLinkData($groupedData, $this->resourceDefinition);

		$tabularData = array();
		foreach ($this->resourceDefinition->tableList() as $tableManifest) {
			$tableName = $tableManifest['name'];
			if (array_key_exists($tableName, $groupedData)) {
				$tabularData[$tableName] = $this->createDataTable($tableManifest, $groupedData[$tableName]);
			}
		}
		return $tabularData;
	}

	/**
	 * @param array $data Multi-table data sorted by row. Field keys are "$tableName.$fieldName".
	 * @return array Data sorted by table, then by row
	 */
	protected function groupDataByTable(array $data)
	{
		$groupedData = array();
		foreach ($data as $rowNum => $rowData) {
			foreach ($rowData as $key => $value) {
				list($tableName, $fieldName) = explode('.', $key);
				$groupedData[$tableName][$rowNum][$fieldName] = $value;
			}
		}
		return $groupedData;
	}

	protected function propagateLinkData(array $tabularData, $tableManifest)
	{
		if ($tableManifest['type'] === 'simple') {
			foreach ($this->resourceDefinition->tableList() as $table) {
				$links = $table['links'];
				if (array_key_exists('links', $tableManifest) && !empty($links)) {
					foreach ($links as $link) {
						$childField = $link->getChildField();
						$parentField = $link->getParentField();
						$parentTableName = $parentField->getTable()->getName();
						if (array_key_exists($parentTableName, $tabularData)) {
							$parentData = $tabularData[$parentTableName];
							$childData = $tabularData[$table->getName()];
							foreach ($childData as $i => $childRow) {
								$parentRow = $parentData[$i];
								$value = NULL;
								if (array_key_exists($parentField->getName(), $parentRow)) {
									$value = $parentData[$i][$parentField->getName()];
								}
								$childRow[$childField->getName()] = $value;
								$childData[$i] = $childRow;
							}
							$tabularData[$table->getName()] = $childData;
						}
					}
				}
				$tabularData = $this->propagateLinkData($tabularData, $table);
			}
		}
		return $tabularData;
	}

	/**
	 * @param array $tableManifest
	 * @param array $rawData
	 * @return Data\Table
	 */
	protected function createDataTable(array $tableManifest, array $rawData)
	{
		if ($tableManifest['type'] === 'list') {
			return $this->createDataListTable($tableManifest, $rawData);
		} else {
			$dataTable = new Data\Table();
			$dataTable->setSqlFactory($this->getSqlFactory());

			foreach ($rawData as $i => $rowData) {
				$row = $dataTable->appendRow()->setLabel($i);
				foreach ($rowData as $fieldName => $value) {
					$row->set($fieldName, $value);
				}
			}
		}
		return $dataTable;
	}

	/**
	 * @param array $tableManifest
	 * @param array $rawData
	 * @return Data\MetaTable
	 */
	protected function createDataListTable(array $tableManifest, array $rawData)
	{
		$indexField = $tableManifest['primaryAttribute'];
		$nameField = $tableManifest['nameAttribute'];
		$valueField = $tableManifest['valueAttribute'];

		$dataTable = new Data\MetaTable();
		$dataTable->setSqlFactory($this->getSqlFactory());

		foreach ($rawData as $rowNum => $rowData) {
			$indexValue = null;
			if (array_key_exists($indexField, $rowData) && !empty($rowData[$indexField])) {
				$indexValue = $rowData[$indexField];
			}
			foreach ($rowData as $field => $value) {
				if ($field !== $indexField) {
					$dataTable->appendRow()->setLabel($rowNum)
						->set($indexField, $indexValue)
						->set($nameField, $field)
						->set($valueField, $value);
				}
			}
		}
		return $dataTable;
	}
}
