<?php
namespace Sloth\Module\Resource\QuerySet;

use Sloth\Module\Resource\Definition\Attribute;
use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\Definition\TableLink;
use Sloth\Module\Resource\Definition\TableList;
use Sloth\Module\Resource\QueryFactory;
use Sloth\Module\Resource\Base\ResourceDefinition;

class InsertRecord
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var ResourceDefinition
     */
    private $resourceDefinition;

    /**
     * @var array
     */
    private $attributeValues;

    /**
     * @var array
     */
    private $tableMap;

    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public function setResourceDefinition(ResourceDefinition $definition)
    {
        $this->resourceDefinition = $definition;
        return $this;
    }

    public function setAttributeValues(array $attributes)
    {
        $this->attributeValues = $attributes;
        return $this;
    }

    public function execute()
    {
        var_dump($this->attributeValues);
        echo "<hr>";

        $coreAttributeList = $this->resourceDefinition->attributeList();
        $this->tableMap = $this->constructTableMap($coreAttributeList);
        $insertedData = $this->insertAttributeList($this->resourceDefinition->attributeList());

        echo "<hr>";
        var_dump($insertedData);

        return $insertedData;
    }

    private function constructTableMap(AttributeList $attributeList, $prefix = null)
    {
        $map = array();
        foreach ($attributeList->getAll() as $attributeName => $attribute) {
            if ($attribute instanceof AttributeList) {
                $map = array_merge_recursive($map, $this->constructTableMap($attribute, $attributeName));
            } else {
                /** @var Attribute $attribute */
                $map[$attribute->getTableName()] = $prefix;
            }
        }
        return $map;
    }

    private function insertAttributeList(AttributeList $attributeList, $prefix = null, array $linkData = array())
    {
        $tableAttributes = $this->groupAttributeListByTable($attributeList);
        $tableAttributeValues = $this->groupAttributeValuesByTable($attributeList, $prefix, false);
        $tableInsertOrder = $this->getTableInsertOrder($attributeList);
        $tableAttributeValues = $this->addLinkDataToTableAttributeValues($tableAttributeValues, $linkData);

        $attributeLists = array();
        $insertedData = array();
        $linkData = array();
        foreach ($tableInsertOrder as $table) {
            /** @var Table $table */

            $tableName = $table->getName();
            $attributeLists[$tableName] = new AttributeList($tableAttributes[$tableName]);
            $autoIncrementFields = $this->getAutoFields($attributeLists[$tableName]);

            $insertedData[$tableName] = $this->insertRowIntoTable($table, $attributeLists[$tableName], $tableAttributeValues[$tableName]);
            $tableAttributes = $this->addLinkFieldsToTableAttributes($tableAttributes, $table, $autoIncrementFields);
            $linkData = $this->addToLinkDataFromInsertedRow($linkData, $insertedData[$tableName], $table, $autoIncrementFields);
        }

        $listsToInsert = $this->filterAttributesToLists($attributeList, $prefix);
        foreach ($listsToInsert as $alias => $list) {
            $insertedData = $this->mergeInsertedDataSets($insertedData, $this->insertAttributeListRows($list, $alias, $linkData));
        }

        return $insertedData;
    }

    private function insertAttributeListRows(AttributeList $attributeList, $prefix, array $linkData = array())
    {
        $tableAttributes = $this->groupAttributeListByTable($attributeList);
        $tableInsertOrder = $this->getTableInsertOrder($attributeList);
        $tableAttributeValues = $this->groupAttributeValuesByTable($attributeList, $prefix, true);
        $tableAttributeValues = $this->addLinkDataToTableAttributeValues($tableAttributeValues, $linkData);
        $insertedData = array();
        foreach ($tableInsertOrder as $table) {
            /** @var Table $table */

            $tableName = $table->getName();
            if (array_key_exists($tableName, $linkData)) {
                $linkFields = array_keys($linkData[$tableName][0]);
            }
            if (!isset($linkFields) || is_null($linkFields)) {
                $linkFields = array();
            }
            $tableAttributes = $this->addLinkFieldsToTableAttributes($tableAttributes, $table, $linkFields);
            $attributeLists[$tableName] = new AttributeList($tableAttributes[$tableName]);
            $autoIncrementFields = $this->getAutoFields($attributeLists[$tableName]);
            foreach ($tableAttributeValues[$tableName] as $rowIndex => $rowAttributeValues) {
                $insertedData[$tableName][$rowIndex] = $this->insertRowIntoTable($table, $attributeLists[$tableName], $rowAttributeValues);
                $linkData = $this->addToLinkDataFromInsertedRow($linkData, $insertedData[$tableName][$rowIndex], $table, $autoIncrementFields, $rowIndex);
                $tableAttributeValues = $this->addLinkDataToTableAttributeValues($tableAttributeValues, $linkData);
            }
        }

        $listsToInsert = $this->filterAttributesToLists($attributeList, $prefix);
        foreach ($listsToInsert as $alias => $list) {
            $insertedData = $this->mergeInsertedDataSets($insertedData, $this->insertAttributeListRows($list, $alias, $linkData));
        }

        return $insertedData;
    }

    private function addLinkFieldsToTableAttributes(array $tableAttributes, Table $table, array $linkFields)
    {
        foreach ($linkFields as $fieldName) {
            $tableAttributes = $this->addParentFieldToTableAttributes($tableAttributes, $table, $fieldName);
            $tableAttributes = $this->addChildFieldToTableAttributes($tableAttributes, $table->getName(), $fieldName);
        }
        return $tableAttributes;
    }

    private function addParentFieldToTableAttributes(array $tableAttributes, Table $table, $fieldName)
    {
        foreach ($table->getLinksToParents() as $parent => $links) {
            foreach ($links as $link) {
                /** @var TableLink $link */
                if ($link->getChildField() === $fieldName) {
                    $tableAttributes[$link->getChildTable()][$link->getChildField()] = new Attribute(array(
                        'name' => $link->getChildField(),
                        'tableName' => $link->getChildTable(),
                        'fieldName' => $link->getChildField()
                    ));
                }
            }
        }

        return $tableAttributes;
    }

    private function addChildFieldToTableAttributes(array $tableAttributes, $parentTableName, $fieldName)
    {
        foreach ($this->resourceDefinition->tableList()->getAll() as $potentialChildTable) {
            /** @var Table $potentialChildTable */
            foreach ($potentialChildTable->getLinksToParents() as $parent => $links) {
                foreach ($links as $link) {
                    /** @var TableLink $link */
                    if ($link->getParentField() === $fieldName && $link->getParentTable() === $parentTableName) {
                        $tableAttributes[$link->getParentTable()][$link->getParentField()] = new Attribute(array(
                            'name' => $link->getParentField(),
                            'tableName' => $link->getParentTable(),
                            'fieldName' => $link->getParentField()
                        ));
                    }
                }
            }
        }
        return $tableAttributes;
    }

    private function addToLinkDataFromInsertedRow(array $linkData, array $insertedRow, Table $table, array $linkFields, $rowIndex = null)
    {
        foreach ($linkFields as $fieldName) {
            $value = $insertedRow[$fieldName];
            if (!is_null($value)) {
                $linkData = $this->addParentFieldDataToLinkData($linkData, $table, $fieldName, $value, $rowIndex);
                $linkData = $this->addChildFieldDataToLinkData($linkData, $table->getName(), $fieldName, $value, $rowIndex);
            }
        }
        return $linkData;
    }

    private function addParentFieldDataToLinkData(array $linkData, Table $table, $fieldName, $value, $rowIndex = null)
    {
        foreach ($table->getLinksToParents() as $parent => $links) {
            foreach ($links as $link) {
                /** @var TableLink $link */
                if ($link->getChildField() === $fieldName) {
                    $parentTableName = $link->getParentTable();
                    $parentFieldName = $link->getParentField();
                    $parentTable = $this->resourceDefinition->tableList()->getByName($parentTableName);
                    if ($parentTable->getType() === 'list') {
                        if (!is_null($rowIndex)) {
                            $linkData[$parentTableName][$rowIndex][$parentFieldName] = $value;
                        } else {
                            $attributeSet = $this->tableMap[$parentTableName];
                            for ($i = 0; $i < count($this->attributeValues[$attributeSet]); $i++) {
                                $linkData[$parentTableName][$i][$parentFieldName] = $value;
                            }
                        }
                    } else {
                        $linkData[$parentTableName][$parentFieldName] = $value;
                    }
                }
            }
        }
        return $linkData;
    }

    private function addChildFieldDataToLinkData(array $linkData, $parentTableName, $fieldName, $value, $rowIndex = null)
    {
        foreach ($this->resourceDefinition->tableList()->getAll() as $potentialChildTable) {
            /** @var Table $potentialChildTable */
            foreach ($potentialChildTable->getLinksToParents() as $parent => $links) {
                /** @var TableLink $link */
                foreach ($links as $link) {
                    if ($link->getParentField() === $fieldName && $link->getParentTable() === $parentTableName) {
                        $childTableName = $link->getChildTable();
                        $childFieldName = $link->getChildField();
                        $table = $this->resourceDefinition->tableList()->getByName($childTableName);
                        if ($table->getType() === 'list') {
                            if (!is_null($rowIndex)) {
                                $linkData[$childTableName][$rowIndex][$childFieldName] = $value;
                            } else {
                                $attributeSet = $this->tableMap[$childTableName];
                                for ($i = 0; $i < count($this->attributeValues[$attributeSet]); $i++) {
                                    $linkData[$childTableName][$i][$childFieldName] = $value;
                                }
                            }
                        } else {
                            $linkData[$childTableName][$childFieldName] = $value;
                        }
                    }
                }
            }
        }

        return $linkData;
    }

    private function groupAttributeListByTable(AttributeList $attributeList)
    {
        $tableAttributes = array();
        foreach ($attributeList->getAll() as $alias => $attribute) {
            if ($attribute instanceof Attribute && !($attribute instanceof AttributeList)) {
                $tableName = $attribute->getTableName();
                $tableAttributes[$tableName][$alias] = $attribute;
            }
        }
        return $tableAttributes;
    }

    private function filterAttributesToLists(AttributeList $attributeList, $aliasPrefix)
    {
        $listsToInsert = array();
        foreach ($attributeList->getAll() as $alias => $attribute) {
            if (!is_null($aliasPrefix)) {
                $prefixedAlias = sprintf('%s.%s', $aliasPrefix, $alias);
            } else {
                $prefixedAlias = $alias;
            }

            if ($attribute instanceof AttributeList) {
                $listsToInsert[$prefixedAlias] = $attribute;
            }
        }
        return $listsToInsert;
    }

    private function groupAttributeValuesByTable(AttributeList $attributeList, $prefix, $multiRow = false)
    {
        $tableAttributeValues = array();
        foreach ($attributeList->getAll() as $alias => $attribute) {
            if (!is_null($prefix)) {
                $prefixedAlias = sprintf('%s.%s', $prefix, $alias);
            } else {
                $prefixedAlias = $alias;
            }

            if ($attribute instanceof Attribute && !($attribute instanceof AttributeList)) {
                $tableName = $attribute->getTableName();
                if ($multiRow) {
                    $tableAttributeValues[$tableName][$alias] = $this->getAttributeValues($prefixedAlias);
                } else {
                    $tableAttributeValues[$tableName][$alias] = $this->getAttributeValue($prefixedAlias);
                }
            }
        }
        if ($multiRow) {
            foreach ($tableAttributeValues as $tableName => $tableValues) {
                $tableAttributeValues[$tableName] = $this->transposeMatrix($tableValues);
            }
        }
        return $tableAttributeValues;
    }

    private function addLinkDataToTableAttributeValues(array $tableAttributeValues, array $linkData)
    {
        foreach ($linkData as $tableName => $tableLinkData) {
            foreach ($tableLinkData as $rowIndex => $rowLinkData) {
                foreach ($rowLinkData as $fieldName => $value) {
                    if (!array_key_exists($tableName, $tableAttributeValues)) {
                        $tableAttributeValues[$tableName] = array();
                    }
                    $tableAttributeValues[$tableName][$rowIndex][$fieldName] = $value;
                }
            }
        }
        return $tableAttributeValues;
    }

    private function mergeInsertedDataSets(array $data1, array $data2)
    {
        $mergedData = $data1;
        foreach ($data2 as $tableName => $tableData) {
            $mergedData[$tableName] = $tableData;
        }
        return $mergedData;
    }

    private function transposeMatrix(array $data)
    {
        $transposedData = array();
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $columnIndex => $cellValue) {
                $transposedData[$columnIndex][$rowIndex] = $cellValue;
            }
        }
        return $transposedData;
    }

    private function getAttributeValue($attributeAlias)
    {
        $aliasParts = explode('.', $attributeAlias);
        $value = $this->attributeValues;
        foreach ($aliasParts as $part) {
            $value = $value[$part];
        }
        return $value;
    }

    private function getAttributeValues($attributeAlias)
    {
        $aliasParts = explode('.', $attributeAlias);
        $lastAliasPart = array_pop($aliasParts);
        $values = $this->attributeValues;

        foreach ($aliasParts as $part) {
            $values = $values[$part];
        }

        $attributeValues = array();
        if (!is_null($values)) {
            foreach ($values as $rowValues) {
                $attributeValues[] = $rowValues[$lastAliasPart];
            }
        }

        return $attributeValues;
    }

    private function getAutoFields(AttributeList $attributeList)
    {
        $autoIncrements = array();
        foreach ($attributeList->getTables() as $tableName) {
            $table = $this->resourceDefinition->tableList()->getByName($tableName);
            $tableAutoIncrement = $table->getAutoIncrement();
            if (!is_null($tableAutoIncrement)) {
                $autoIncrements[] = $tableAutoIncrement;
            }
        }
        return $autoIncrements;
    }

    private function insertRowIntoTable(Table $table, AttributeList $attributeList, array $data)
    {
        $attributeValues = $this->buildTableInsertData($table, $attributeList, $data);
        $query = $this->queryFactory->insertSingle($table, $attributeValues);

        echo "<p>$query</p>";

        $this->queryFactory->getDatabase()->execute($query);

        $autoField = $table->getAutoIncrement();
        if (!is_null($autoField)) {
            $insertedAutoValue = $this->queryFactory->getDatabase()->getInsertId();
            $attributeValues[$autoField] = $insertedAutoValue;
        }

        return $attributeValues;
    }

    private function buildTableInsertData(Table $table, AttributeList $attributeList, array $data)
    {
        $attributeValues = array();
        foreach ($attributeList->getAll() as $attributeAlias => $attribute) {
            /** @var Attribute $attribute */
            if ($attribute->getName() !== $table->getAutoIncrement()) {
                $attributeValues[$attribute->getFieldName()] = $data[$attribute->getName()];
            }
        }
        return $attributeValues;
    }

    private function mapTableNamesToTables(array $tableNames)
    {
        $groups = array();
        foreach ($tableNames as $alias => $tableName) {
            if (is_array($tableName)) {
                $groups[$alias] = new TableList($this->mapTableNamesToTables($tableName));
            } else {
                $groups[$tableName] = $this->resourceDefinition->tableList()->getByName($tableName);
            }
        }
        return $groups;
    }

    private function getTableInsertOrder(AttributeList $attributeList)
    {
        $tableList = new TableList(array());
        foreach ($attributeList->getTables() as $alias => $tableName) {
            $tableList->append($tableName, $this->resourceDefinition->tableList()->getByName($tableName));
        }

        $tableOrder = array();
        foreach ($tableList->getAll() as $table) {
            /** @var Table $table */
            $tableOrder[] = array(
                'tableName' => $table->getName(),
                'table' => $table,
                'linksToParents' => $table->getLinksToParents(),
                'parents' => $this->getTableParentNames($table)
            );
        }

        usort($tableOrder, function(array $a, array $b) use ($tableList, $tableOrder) {
            if (empty($a['parents'])) {
                return 1;
            } elseif (empty($b['parents'])) {
                return -1;
            } else {
                if (in_array($b['tableName'], $a['parents'])) {
                    foreach ($b['linksToParents'] as $parentOfB => $links) {
                        foreach ($links as $link) {
                            /** @var TableLink $link */
                            if ($link->getParentField() === $a['table']->getAutoIncrement()) {
                                return -1;
                            }
                        }
                    }
                    foreach ($a['linksToParents'] as $parentOfA => $links) {
                        foreach ($links as $link) {
                            if ($link->getParentField() === $b['table']->getAutoIncrement()) {
                                return 1;
                            }
                        }
                    }
                    return -1;
                } else {
                    return 1;
                }
            }
        });

        $tableOrder = array_map(function($tableParams) use ($tableList) {
            return $tableList->getByName($tableParams['tableName']);
        }, $tableOrder);

        return $tableOrder;
    }

    private function getTableParentNames(Table $table)
    {
        $parents = array();
        foreach ($table->getLinksToParents() as $parent => $linksToParent) {
            foreach ($linksToParent as $link) {
                $parents[] = $this->getParentTableFromLink($link)->getName();
            }
        }
        return $parents;
    }
    private function getParentTableFromLink(TableLink $link)
    {
        return $this->resourceDefinition->tableList()->getByName($link->getParentTable());
    }
}
