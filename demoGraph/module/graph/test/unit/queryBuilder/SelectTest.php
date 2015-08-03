<?php
namespace DemoGraph\Module\Graph\Test\QueryBuilder;

require_once dirname(dirname(__DIR__)) . '/UnitTest.php';

use DemoGraph\Module\Graph\QueryBuilder\Select;
use DemoGraph\Module\Graph\QueryComponent;
use DemoGraph\Module\Graph\QueryComponent\Constraint;
use DemoGraph\Module\Graph\QueryComponent\ConstraintList;
use DemoGraph\Module\Graph\QueryComponent\FieldSort;
use DemoGraph\Module\Graph\QueryComponent\FieldSortList;
use DemoGraph\Module\Graph\QueryComponent\TableJoin;
use DemoGraph\Module\Graph\QueryComponent\TableJoinList;
use DemoGraph\Module\Graph\ResourceDefinition;
use DemoGraph\Module\Graph\ResourceDefinition\Table;
use DemoGraph\Module\Graph\ResourceDefinition\TableField;
use DemoGraph\Module\Graph\ResourceDefinition\TableFieldList;
use DemoGraph\Test\UnitTest;

class SelectTest extends UnitTest
{
    private function getQueryBuilder()
    {
        $database = $this->getDatabaseWrapper();
        $queryBuilder = new Select($database);
        return $queryBuilder;
    }

    public function testBuildSelectQueryFromSingleTable()
    {
        $table = $this->buildResourceDefinitionTable('Fruit', 'fruit');
        $queryTable = $this->buildQueryTable($table);
        $fields = array($this->buildResourceDefinitionField($table, 'id', 'fruit.id'),
            $this->buildResourceDefinitionField($table, 'name', 'fruit.name'),
            $this->buildResourceDefinitionField($table, 'color', 'fruit.color')
        );
        $fieldList = new TableFieldList();
        foreach ($fields as $field) {
            $fieldList->push($field);
        }
        $constraints = $this->buildQueryConstraintList(array($this->buildQueryConstraint($fields[2], 'Orange')));
        $sortOrder = $this->buildQueryFieldSortList(array(array($fields[1], 'ascending')));

        $queryBuilder = $this->getQueryBuilder()
            ->fields($fieldList)
            ->from($queryTable)
            ->where($constraints)
            ->orderBy($sortOrder);
        $query = $queryBuilder->build();

        $expectedQueryString = <<<EOT
SELECT `fruit`.`id`,`fruit`.`name`,`fruit`.`color`
FROM `Fruit` AS `fruit`
WHERE `fruit`.`color` = "Orange"
ORDER BY `fruit`.`name`
EOT;
        $this->assertEquals($expectedQueryString, (string)$query);
    }

    public function testBuildSelectQueryFromTwoTablesWithSimpleJoin()
    {
        $fruitTable = $this->buildResourceDefinitionTable('Fruit', 'fruit');
        $colorTable = $this->buildResourceDefinitionTable('Color', 'color');

        $fruitQueryTable = $this->buildQueryTable($fruitTable);
        $colorQueryTable = $this->buildQueryTable($colorTable);

        $fruitFields = array($this->buildResourceDefinitionField($fruitTable, 'id', 'fruit.id'),
            $this->buildResourceDefinitionField($fruitTable, 'name', 'fruit.name'),
            $this->buildResourceDefinitionField($fruitTable, 'colorId', 'fruit.colorId')
        );
        $colorFields = array($this->buildResourceDefinitionField($colorTable, 'id', 'color.id'),
            $this->buildResourceDefinitionField($colorTable, 'name', 'color.name')
        );
        $fieldList = new TableFieldList();
        foreach (array_merge($fruitFields, $colorFields) as $field) {
            $fieldList->push($field);
        }

        $fruitNameField = $fieldList->getByProperty('alias', 'fruit.name');
        $fruitColorIdField = $fieldList->getByProperty('alias', 'fruit.colorId');
        $colorIdField = $fieldList->getByProperty('alias', 'color.id');
        $colorNameField = $fieldList->getByProperty('alias', 'color.name');

        $join = new TableJoin();
        $joinConstraint = new Constraint();
        $joinConstraint->setSubject($colorIdField)
            ->setValue($fruitColorIdField);
        $joinConstraintList = new ConstraintList();
        $joinConstraintList->push($joinConstraint);
        $join->setParentTable($fruitQueryTable)
            ->setChildTable($colorQueryTable)
            ->setConstraints($joinConstraintList);
        $fruitQueryTable->setJoins($this->buildTableJoinList(array($join)));

        $constraints = $this->buildQueryConstraintList(array(
            $this->buildQueryConstraint($colorNameField, 'Orange')
        ));
        $sortOrder = $this->buildQueryFieldSortList(array(
            array($fruitNameField, 'ascending')
        ));

        $queryBuilder = $this->getQueryBuilder()
            ->fields($fieldList)
            ->from($fruitQueryTable)
            ->where($constraints)
            ->orderBy($sortOrder);
        $query = $queryBuilder->build();

        $expectedQueryString = <<<EOT
SELECT `fruit`.`id`,`fruit`.`name`,`fruit`.`colorId`,`color`.`id`,`color`.`name`
FROM `Fruit` AS `fruit`
INNER JOIN `Color` AS `color` ON (`color`.`id` = `fruit`.`colorId`)
WHERE `color`.`name` = "Orange"
ORDER BY `fruit`.`name`
EOT;
        $this->assertEquals($expectedQueryString, (string)$query);
    }

    private function buildResourceDefinitionField(Table $table, $name, $alias)
    {
        $field = new TableField();
        $field->table = $table;
        $field->name = $name;
        $field->alias = $alias;
        return $field;
    }

    private function buildQueryTable(Table $table)
    {
        $queryTable = new \DemoGraph\Module\Graph\QueryComponent\Table();
        $queryTable->setDefinition($table);
        return $queryTable;
    }

    private function buildResourceDefinitionTable($name, $alias)
    {
        $table = new Table();
        $table->name = $name;
        $table->alias = $alias;
        return $table;
    }

    private function buildQueryConstraintList(array $constraints = array())
    {
        $list = new ConstraintList();
        foreach ($constraints as $constraint) {
            $list->push($constraint);
        }
        return $list;
    }


    private function buildQueryConstraint(TableField $tableField, $value)
    {
        $constraint = new Constraint();
        $constraint->setSubject($tableField)->setValue($value);
        return $constraint;
    }

    private function buildQueryFieldSortList(array $sortParams)
    {
        $list = new FieldSortList();
        foreach ($sortParams as $fieldOrder) {
            list($field, $order) = $fieldOrder;
            $fieldSort = new FieldSort();
            $fieldSort->setField($field)->setOrder($order);
            $list->push($fieldSort);
        }
        return $list;
    }

    private function buildTableJoinList(array $joins)
    {
        $list = new TableJoinList();
        foreach ($joins as $join) {
            $list->push($join);
        }
        return $list;
    }

    private function buildQueryTableJoin(QueryComponent\Table $parent, QueryComponent\Table $child, ResourceDefinition\TableJoinList $rawJoins)
    {
        $join = new TableJoin();
        $join->setParentTable($parent)
            ->setChildTable($child)
            ->setJoins($rawJoins);
        return $join;
    }

    private function buildResourceDefinitionJoinList(array $joins)
    {
        $list = new ResourceDefinition\TableJoinList();
        foreach ($joins as $join) {
            $list->push($join);
        }
        return $list;
    }

    private function buildResourceDefinitionJoin()
    {
        $join = new ResourceDefinition\TableJoin();
    }
}
