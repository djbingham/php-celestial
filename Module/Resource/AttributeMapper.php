<?php
namespace Sloth\Module\Resource;

use Sloth\Module\Resource\Definition\Attribute;
use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\Definition\TableLink;
use Sloth\Module\Resource\Definition\TableList;

class AttributeMapper
{
    /**
     * @var ResourceDefinition
     */
    private $resourceDefinition;

    /**
     * @var array
     */
    private $cache = array();

    public function __construct(ResourceDefinition $resourceDefinition)
    {
        $this->resourceDefinition = $resourceDefinition;
        return $this;
    }

    public function mapTablesToAttributes()
    {
        if (!array_key_exists('mapTablesToAttributes', $this->cache)) {
            $attributeList = $this->resourceDefinition->attributeList();
            $this->cache['mapTablesToAttributes'] = $this->constructMapTablesToAttributes($attributeList);
        }
        return $this->cache['mapTablesToAttributes'];
    }

    public function mapAttributeSubsetByTable(AttributeList $attributeList)
    {
        if (!array_key_exists('mapAttributeSubsetByTable', $this->cache)) {
            $this->cache['mapAttributeSubsetByTable'] = $this->constructMapAttributeSubsetByTable($attributeList);
        }
        return $this->cache['mapAttributeSubsetByTable'];
    }

    public function getTableInsertOrderForAttributeSubset(AttributeList $attributeList)
    {
        if (!array_key_exists('getAttributeSubsetInsertOrder', $this->cache)) {
            $this->cache['getAttributeSubsetInsertOrder'] = $this->computeInsertOrderForAttributeSubset($attributeList);
        }
        return $this->cache['getAttributeSubsetInsertOrder'];
    }

    private function constructMapTablesToAttributes(AttributeList $attributeList, $prefix = null)
    {
        $map = array();
        foreach ($attributeList->getAll() as $attributeName => $attribute) {
            if ($attribute instanceof AttributeList) {
                $map = array_merge_recursive($map, $this->constructMapTablesToAttributes($attribute, $attributeName));
            } else {
                /** @var Attribute $attribute */
                $map[$attribute->getTableName()] = $prefix;
            }
        }
        return $map;
    }

    private function constructMapAttributeSubsetByTable(AttributeList $attributeList)
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

    private function computeInsertOrderForAttributeSubset(AttributeList $attributeList)
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
