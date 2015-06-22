<?php
namespace Sloth\Module\Resource\QuerySet;

use Sloth\Module\Resource\Definition\Attribute;
use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\TableLink;
use Sloth\Module\Resource\Definition\TableList;
use Sloth\Module\Resource\QueryFactory;
use Sloth\Module\Resource\Base\ResourceDefinition;

class GetBy
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
        $tableNames = $this->resourceDefinition->attributeList()->getTables();
        $primaryData = $this->fetchDataFromTables($tableNames, array());

        $rawData = array(
            $this->resourceDefinition->primaryTable()->getName() => $primaryData
        );
        $resourceData = $primaryData;

        foreach ($this->resourceDefinition->attributeList()->getAll() as $alias => $attribute) {
            if ($attribute instanceof AttributeList) {
                $rawData[$alias] = $this->fetchDataFromTables($attribute->getTables(), $rawData);
                $tableList = new TableList($this->mapTableNamesToTables($attribute->getTables()));
                $resourceData = $this->mergeLinkedDataIntoResourceData($resourceData, $rawData[$alias], $tableList, $alias);
            } else {

            }
        }

        return $resourceData;
    }

    private function fetchDataFromTables(array $tableNames, $constraintData)
    {
        $tableList = new TableList($this->mapTableNamesToTables($tableNames));
        $attributes = $this->filterAttributesByTableList($this->resourceDefinition->attributeList(), $tableList);
        $attributeList = new AttributeList($attributes);
        $attributeConstraints = $this->createConstraintsFromAttributeValues($this->attributeValues, $tableList);
        $joinConstraints = $this->createConstraintsFromJoinData($tableList, $constraintData);
        $constraints = array_merge($attributeConstraints, $joinConstraints);

        return $this->selectFromTableList($tableList, $attributeList, $constraints);
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

    private function filterAttributesByTableList(AttributeList $attributeList, TableList $tableList)
    {
        $attributes = array();
        foreach ($attributeList->getAll() as $attribute) {
            if ($attribute instanceof AttributeList) {
                $attributes = array_merge($attributes, $this->filterAttributesByTableList($attribute, $tableList));
            } elseif ($this->attributeBelongsToTableList($attribute, $tableList)) {
                $attributes[] = $attribute;
            }
        }
        return $attributes;
    }

    private function attributeBelongsToTableList(Attribute $attribute, TableList $tableList)
    {
        return $tableList->contains($attribute->getTableName());
    }

    private function createConstraintsFromAttributeValues(array $attributes, TableList $tableList)
    {
        $constraints = array();
        foreach ($attributes as $attributeName => $attributeValue) {
            $attribute = $this->resourceDefinition->attributeList()->getByName($attributeName);
            if ($attribute->belongsToTableList($attribute, $tableList)) {
                $constraints[$attributeName] = $attributeValue;
            }
        }
        return $constraints;
    }

    private function createConstraintsFromJoinData(TableList $tableList, array $rawData)
    {
        $linksToParents = $tableList->getLinksToOtherTables();
        $constraints = array();
        foreach ($linksToParents as $parent => $linksToParent) {
            foreach ($linksToParent as $link) {
                $constraints = array_merge($constraints, $this->createConstraintsFromLinkedData($link, $rawData));
            }
        }
        return $constraints;
    }

    private function createConstraintsFromLinkedData(TableLink $tableLink, array $rawData)
    {
        $parentValues = array();
        foreach ($rawData[$tableLink->getParentTable()] as $parentData) {
            $parentValues[] = $parentData[$tableLink->getParentField()];
        }
        $tableName = $this->resourceDefinition->tableList()->getByName($tableLink->getChildTable())->getName();
        $childTableField = sprintf('%s.%s', $tableName, $tableLink->getChildField());
        $constraints[$childTableField] = $parentValues;
        return $constraints;
    }

    private function selectFromTableList(TableList $tableList, AttributeList $attributeList, array $constraints)
    {
        if (!($tableList instanceof TableList)) {
            $tableList = new TableList(array($tableList));
        }

        $database = $this->queryFactory->getDatabase();
        $query = $this->queryFactory->select($tableList, $attributeList, $constraints);
        $database->execute($query);
        return $database->getData();
    }

    private function mergeLinkedDataIntoResourceData(array $resourceData, array $dataToMerge, TableList $tableList, $alias)
    {
        $linksToParents = $tableList->getLinksToOtherTables();
        foreach ($dataToMerge as $rawRow) {
            if (empty($linksToParents)) {
                $resourceData[] = $rawRow;
            } else {
                foreach ($resourceData as $resourceIndex => $resourceRow) {
                    foreach ($linksToParents as $parentTable => $linksToParent) {
                        foreach ($linksToParent as $parentField => $link) {
                            if ($this->linkAffectsDataRows($link, $resourceRow, $rawRow)) {
                                $resourceData[$resourceIndex] = $this->mergeLinkedRows($resourceRow, $rawRow, $alias);
                            }
                        }
                    }
                }
            }
        }
        return $resourceData;
    }

    private function linkAffectsDataRows(TableLink $tableLink, array $row1, array $row2)
    {
        return $row1[$tableLink->getParentField()] === $row2[$tableLink->getChildField()];
    }

    private function mergeLinkedRows(array $row1, array $row2, $alias)
    {
        $thisData = array();
        foreach ($row2 as $field => $value) {
            $thisData[$field] = $value;
        }
        $row1[$alias][] = $thisData;
        return $row1;
    }
}
