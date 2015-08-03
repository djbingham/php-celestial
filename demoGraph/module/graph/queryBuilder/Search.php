<?php
namespace DemoGraph\Module\Graph\QueryBuilder;

use DemoGraph\Module\Graph\ResourceDefinition\Attribute;
use DemoGraph\Module\Graph\ResourceDefinition\AttributeList;
use DemoGraph\Module\Graph\ResourceDefinition\Table;
use DemoGraph\Module\Graph\ResourceDefinition\TableLink;
use DemoGraph\Module\Graph\ResourceDefinition\TableList;
use SlothMySql\Abstractory\Value\ATable;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;

class Search
{
    /**
     * @var DatabaseWrapper
     */
    private $database;

    /**
     * @var array
     */
    private $cachedDatabaseTables = array();

    public function __construct(DatabaseWrapper $database)
    {
        $this->database = $database;
    }

    /**
     * @param TableList $tableList
     * @param AttributeList $attributeList
     * @param array $filters
     * @return MySqlQuery
     */
    public function createQuery(TableList $tableList, AttributeList $attributeList, array $filters = array())
    {
        $fields = $this->createQueryFields($attributeList);
        $table = $this->getDatabaseTable($tableList->getPrimaryTable()->getName());
        $joins = $this->createQueryJoins($tableList);
        $constraints = $this->createQueryConstraints($attributeList, $filters);
        $fetchOrder = $tableList->getFetchOrder();

        $query = $this->database->query()->select()
            ->setFields($fields)
            ->from($table)
            ->setJoins($joins);

        if (count($constraints) > 0) {
            $query->where(array_shift($constraints));
            foreach ($constraints as $constraint) {
                $query->andWhere($constraint);
            }
        }

        if (count($fetchOrder) > 0) {
            foreach ($fetchOrder as $attribute => $order) {
                $query->orderBy($table->field($attribute));
            }
        }

        return $query;
    }

    protected function createQueryFields(AttributeList $attributeList)
    {
        $fields = array();
        foreach ($attributeList->getAll() as $attribute) {
            if ($attribute instanceof AttributeList) {
                $fields = array_merge($fields, $this->createQueryFields($attribute));
            } else {
                $fields[] = $this->createQueryFieldForAttribute($attribute);
            }
        }
        return $fields;
    }

    protected function createQueryFieldForAttribute(Attribute $attribute)
    {
        return $this->database->value()->tableField($attribute->getTableName(), $attribute->getFieldName());
    }

    protected function createQueryJoins(TableList $tableList)
    {
        $joins = array();
        foreach ($tableList->getAll() as $tableDefinition) {
            $tableJoin = $this->createJoin($tableDefinition, $tableList);
            if (!is_null($tableJoin)) {
                $joins[] = $tableJoin;
            }
        }
        return $joins;
    }

    protected function createJoin(Table $tableDefinition, TableList $tableList)
    {
        $join = null;
        $linksToParents = $tableDefinition->getLinksToParents($tableList);
        if (count($linksToParents) > 0) {
            if ($tableDefinition->getType() === 'list') {
                $join = $this->database->query()->join()->left();
            } else {
                $join = $this->database->query()->join()->left();
            }
            $join->table($this->getDatabaseTable($tableDefinition->getName()))
                ->on($this->createJoinConstraints($linksToParents, $tableList));
        }
        return $join;
    }

    protected function createJoinConstraints(array $linksGroupedByTable, TableList $tableList)
    {
        $firstTableLinks = array_shift($linksGroupedByTable);
        $constraint = $this->createConstraintsForTableLinks($firstTableLinks, $tableList);
        foreach ($linksGroupedByTable as $linkedTableName => $tableLinks) {
            $constraint->andOn($this->createConstraintsForTableLinks($tableLinks, $tableList));
        }
        return $constraint;
    }

    protected function createConstraintsForTableLinks(array $tableLinks, TableList $tableList)
    {
        $firstLink = array_shift($tableLinks);
        $constraint = $this->createConstraintForTableLink($firstLink, $tableList);

        foreach ($tableLinks as $tableLink) {
            $constraint->andOn($this->createConstraintForTableLink($tableLink, $tableList));
        }
        array_unshift($tableLinks, $firstLink);
        return $constraint;
    }

    protected function createConstraintForTableLink(TableLink $tableLink, TableList $tableList)
    {
        $parentTable = $this->getDatabaseTable($tableList->getByName($tableLink->getParentTable())->getName());
        $parentField = $parentTable->field($tableLink->getParentField());
        $childTable = $this->getDatabaseTable($tableList->getByName($tableLink->getChildTable())->getName());
        $childField = $childTable->field($tableLink->getChildField());

        return $this->database->query()->constraint()
            ->setSubject($parentField)
            ->equals($childField);
    }

    protected function createQueryConstraints(AttributeList $attributeList, array $filters)
    {
        $queryConstraints = array();
        foreach ($filters as $attributeName => $filter) {
            $attributeName = $filter['subject'];
            $attribute = $attributeList->getByName($attributeName);
            $dbTable = $this->getDatabaseTable($attribute->getTableName());
            if (is_array($filter['value'])) {
                $values = array();
                foreach ($filter['value'] as $index => $item) {
                    $values[$index] = $this->database->value()->string($item);
                }
                $value = $this->database->value()->valueList($values);
            } else {
                $value = $this->database->value()->string($filter['value']);
            }
            $constraint = $this->database->query()->constraint()
                ->setSubject($dbTable->field($attribute->getFieldName()))
                ->setComparator($filter['comparator'])
                ->setValue($value);
            $queryConstraints[] = $constraint;
        }
        return $queryConstraints;
    }

    /**
     * @param $tableName
     * @return ATable
     */
    protected function getDatabaseTable($tableName)
    {
        if (!array_key_exists($tableName, $this->cachedDatabaseTables)) {
            $this->cachedDatabaseTables[$tableName] = $this->database->value()->table($tableName);
        }
        return $this->cachedDatabaseTables[$tableName];
    }
}
