<?php
namespace DemoGraph\Module\Graph\QuerySet;

use DemoGraph\Module\Graph\ResourceDefinition\Attribute;
use DemoGraph\Module\Graph\ResourceDefinition\AttributeList;
use DemoGraph\Module\Graph\ResourceDefinition\TableLink;
use DemoGraph\Module\Graph\ResourceDefinition\TableList;
use DemoGraph\Module\Graph\QueryFactory;
use DemoGraph\Module\Graph\ResourceDefinition\Resource as ResourceDefinition;

class Search
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
    private $filters;

    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public function setResourceDefinition(ResourceDefinition $definition)
    {
        $this->resourceDefinition = $definition;
        return $this;
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Search for resources by any number of attribute comparisons, using any number of filter definitions.
     * Filter definitions are set via the setFilters method
     *
     * Example functionality:
     * ----------------
     * A resource for recipes is defined with the following attributes:
     *  Recipe: {
     *      "id": int,
     *      "name": string,
     *      "description": string,
     *      "ingredients": [{
     *          "id": int,
     *          "name": string,
     *          "description": string"
     *      }]
     *
     * To find recipes whose name includes "Spaghetti", set the following filter:
     *      [{
     *          "subject": "name",
     *          "comparator": "LIKE",
     *          "value": "%Spaghetti%"
     *      }]
     *
     * To find recipes containing ingredients named "Tomato" or "Red Pepper", set the following filter:
     *      [{
     *          "subject": "ingredients.name",
     *          "comparator": "IN",
     *          "value": ["Tomato", "Red Pepper"]
     *      }]
     *
     * @return array
     */
	public function execute()
    {
        $this->filters = $this->consolidateFiltersToPrimaryTableFields($this->filters);

        if (is_null($this->filters)) {
            $resourceData = array();
        } else {
            $tableNames = $this->resourceDefinition->attributeList()->getTables();
            $primaryData = $this->fetchDataFromTables($tableNames);
            $resourceData = $primaryData;

            if (!empty($primaryData)) {
                $rawData = array(
                    $this->resourceDefinition->primaryTable()->getName() => $primaryData
                );

                foreach ($this->resourceDefinition->attributeList()->getAll() as $alias => $attribute) {
                    if ($attribute instanceof AttributeList) {
                        $rawData[$alias] = $this->fetchDataFromTables($attribute->getTables(), $rawData);
                        $tableList = new TableList($this->mapTableNamesToTables($attribute->getTables()));
                        $resourceData = $this->mergeLinkedDataIntoResourceData($resourceData, $rawData[$alias], $tableList, $alias);
                    }
                }
            }
        }

        return $resourceData;
    }

    private function consolidateFiltersToPrimaryTableFields(array $filters)
    {
        $primaryTableName = $this->resourceDefinition->primaryTable()->getName();
        $filterGroups = $this->groupFiltersByAttributeSet($filters);
        if (!empty($filterGroups['filters'][$primaryTableName])) {
            $finalQueryFilters = $filterGroups['filters'][$primaryTableName];
        } else {
            $finalQueryFilters = array();
        }

        foreach ($filterGroups['filters'] as $alias => $filters) {
            $attributeList = $filterGroups['attributeLists'][$alias];
            $tableList = $filterGroups['tableLists'][$alias];
            $tableLinks = $filterGroups['links'][$alias];

            if (!empty($tableLinks)) {
                foreach ($tableLinks as $table => $links) {
                    foreach ($links as $link) {
                        $this->appendLinkAttributeToList($attributeList, $link);
                    }
                }
                $recordsMatchingFilters = $this->fetchData($tableList, $attributeList, $filters);
                $linkValues = $this->getManyLinkValuesFromChildData($tableLinks, $recordsMatchingFilters);

                // Filter data from each tableList by allowed link values,
                // then iterate filter process until no links are left
                while (count($linkValues) > 1) {
                    foreach ($linkValues as $tableName => $linkData) {
                        if ($tableName !== $primaryTableName) {
                            foreach ($linkData as $fieldName => $values) {
                                echo sprintf("<p>$tableName.$fieldName = %s</p>", implode(', ', $values));
                            }
                            unset($linkValues[$tableName]);
                        }
                    }
                }

                if (empty($linkValues[$primaryTableName])) {
                    // No resources matched this filter. Force no results to be found with final query filters
                    $finalQueryFilters = null;
                    break;
                } else {
                    foreach ($linkValues[$primaryTableName] as $field => $values) {
                        $finalQueryFilters[] = array(
                            'subject' => $field,
                            'comparator' => 'IN',
                            'value' => $values
                        );
                    }
                }
            }
        }
        return $finalQueryFilters;
    }

    private function groupFiltersByAttributeSet(array $filters)
    {
        $groupedFilters = array();
        $attributeLists = array();
        $tableLists = array();
        $tableLinks = array();

        foreach ($filters as $filterParams) {
            $filterSubjectParts = explode('.', $filterParams['subject']);
            $attributeList = $this->resourceDefinition->attributeList();
            $attributePathParts = array();
            while (count($filterSubjectParts) > 1) {
                $parentAttribute = array_shift($filterSubjectParts);
                $attributeList = $attributeList->getByName($parentAttribute);
                $attributePathParts[] = $parentAttribute;
            }

            if (empty($attributePathParts)) {
                $attributeListAlias = $this->resourceDefinition->primaryTable()->getName();
            } else {
                $attributeListAlias = implode('.', $attributePathParts);
            }

            $tableList = new TableList($this->mapTableNamesToTables($attributeList->getTables()));
            $filterParams['subject'] = $filterSubjectParts[0];
            $groupedFilters[$attributeListAlias][] = $filterParams;
            $attributeLists[$attributeListAlias] = $attributeList;
            $tableLists[$attributeListAlias] = $tableList;
            $tableLinks[$attributeListAlias] = $tableList->getLinksToOtherTables();
        }
        return array(
            'filters' => $groupedFilters,
            'attributeLists' => $attributeLists,
            'tableLists' => $tableLists,
            'links' => $tableLinks
        );
    }

    private function getManyLinkValuesFromChildData(array $tableLinks, array $childData)
    {
        $linkValues = array();
        foreach ($tableLinks as $table => $links) {
            foreach ($links as $link) {
                $linkValues = array_merge_recursive($linkValues, $this->getLinkValuesFromChildData($link, $childData));
            }
        }
        return $linkValues;
    }

    private function getLinkValuesFromChildData(TableLink $link, array $childData)
    {
        $parentTable = $link->getParentTable();
        $parentField = $link->getParentField();
        $childField = $link->getChildField();
        $linkValues = array();
        foreach ($childData as $row) {
            $linkValues[$parentTable][$parentField][] = $row[$childField];
        }
        return $linkValues;
    }

    private function fetchData(TableList $tableList, AttributeList $attributeList, array $filters)
    {
        $constraints = $this->createConstraintsFromFilters($filters, $tableList, $attributeList);
        return $this->selectFromTableList($tableList, $attributeList, $constraints);
    }

    private function fetchDataFromTables(array $tableNames, $constraintData = array())
    {
        $tableList = new TableList($this->mapTableNamesToTables($tableNames));
        $attributes = $this->filterAttributesByTableList($this->resourceDefinition->attributeList(), $tableList);
        $attributeList = new AttributeList($attributes);
        $joinConstraints = $this->createConstraintsFromJoinData($tableList, $constraintData);
        foreach ($tableList->getLinksToOtherTables() as $table => $links) {
            foreach ($links as $link) {
                $this->appendLinkAttributeToList($attributeList, $link);
            }
        }
        $attributeConstraints = $this->createConstraintsFromFilters($this->filters, $tableList, $this->resourceDefinition->attributeList());
        $constraints = array_merge($attributeConstraints, $joinConstraints);
        return $this->selectFromTableList($tableList, $attributeList, $constraints);
    }

    private function appendLinkAttributeToList(AttributeList $attributeList, TableLink $link)
    {
        $attribute = new Attribute(array(
            'name' => $link->getChildField(),
            'tableName' => $link->getChildTable(),
            'fieldName' => $link->getChildField()
        ));
        $attributeList->append($link->getChildField(), $attribute);
        return $attributeList;
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
        foreach ($attributeList->getAll() as $alias => $attribute) {
            if ($attribute instanceof AttributeList) {
                $attributes = array_merge($attributes, $this->filterAttributesByTableList($attribute, $tableList));
            } elseif ($this->attributeBelongsToTableList($attribute, $tableList)) {
                $attributes[$alias] = $attribute;
            }
        }
        return $attributes;
    }

    private function attributeBelongsToTableList(Attribute $attribute, TableList $tableList)
    {
        return $tableList->contains($attribute->getTableName());
    }

    private function createConstraintsFromFilters(array $filters, TableList $tableList, AttributeList $attributeList)
    {
        $constraints = array();
        foreach ($filters as $filterParams) {
            $attributeName = $filterParams['subject'];
            $comparator = $filterParams['comparator'];
            $attributeValue = $filterParams['value'];
            $attribute = $attributeList->getByName($attributeName);
            if ($attribute->belongsToTableList($tableList)) {
                $constraints[] = array(
                    'subject' => $attributeName,
                    'comparator' => $comparator,
                    'value' => $attributeValue
                );
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
        $constraints[] = array(
            'subject' => $tableLink->getChildField(),
            'comparator' => 'IN',
            'value' => $parentValues
        );
        return $constraints;
    }

    private function selectFromTableList(TableList $tableList, AttributeList $attributeList, array $constraints)
    {
        if (!($tableList instanceof TableList)) {
            $tableList = new TableList(array($tableList));
        }

        $database = $this->queryFactory->getDatabase();
        $query = $this->queryFactory->search($tableList, $attributeList, $constraints);
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
