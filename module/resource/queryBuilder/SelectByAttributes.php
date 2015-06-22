<?php
namespace Sloth\Module\Resource\QueryBuilder;

use Sloth\Module\Resource\Base\ResourceDefinition;
use Sloth\Module\Resource\Definition\Attribute;
use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\Definition\TableList;
use SlothMySql\Abstractory\Value\ATable;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;

class SelectByAttributes
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
     * @param ResourceDefinition $resourceDefinition
     * @param array $attributes
     * @return MySqlQuery
     */
    public function createQuery(ResourceDefinition $resourceDefinition, array $attributes)
    {
        $primaryTable = $resourceDefinition->primaryTable();
        $fields = $this->createQueryFields($resourceDefinition->attributeList(), $resourceDefinition);
        $joins = $this->createQueryJoins($resourceDefinition->tableList());
        $constraints = $this->createQueryConstraints($primaryTable, $attributes);

        $query = $this->database->query()->select()
            ->setFields($fields)
            ->from($this->getDatabaseTable($primaryTable->getName()))
            ->setJoins($joins);

        if (count($constraints) > 0) {
            $query->where(array_shift($constraints));
            foreach ($constraints as $constraint) {
                $query->andWhere($constraint);
            }
        }

        return $query;
    }

    protected function createQueryFields(AttributeList $attributeList, ResourceDefinition $resourceDefinition)
    {
        $fields = array();
        foreach ($attributeList->getAll() as $attributeName => $attribute) {
            if ($attribute instanceof AttributeList) {
                $fields = array_merge($fields, $this->createQueryFields($attribute, $resourceDefinition));
            } elseif ($attribute instanceof Attribute) {
                $table = $resourceDefinition->tableList()->getByName($attribute->getTableName());
                $field = $this->database->value()->tableField($table->getName(), $attribute->getFieldName());
                $fields[] = $field->setAlias($attributeName);
            }
        }
        return $fields;
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
        $linksToParent = $tableDefinition->getLinksToParents();
        if (count($linksToParent) > 0) {
            if ($tableDefinition->getType() === 'list') {
                $join = $this->database->query()->join()->left();
            } else {
                $join = $this->database->query()->join()->left();
            }
            $join->table($this->getDatabaseTable($tableDefinition->getName()))
                ->on($this->createJoinConstraints($linksToParent, $tableList));
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
        $firstLinkParent = array_shift(array_keys($tableLinks));
        $firstLinkChild = $tableLinks[$firstLinkParent];
        unset($tableLinks[$firstLinkParent]);
        $constraint = $this->createConstraintForTableLink($firstLinkParent, $firstLinkChild, $tableList);

        foreach ($tableLinks as $parent => $child) {
            $constraint->andOn($this->createConstraintForTableLink($parent, $child, $tableList));
        }
        return $constraint;
    }

    protected function createConstraintForTableLink($parent, $child, TableList $tableList)
    {
        list($parentTableName, $parentFieldName) = explode('.', $parent);
        list($childTableName, $childFieldName) = explode('.', $child);

        $parentField = $this->getDatabaseTable($tableList->getByName($parentTableName)->getName())->field($parentFieldName);
        $childField = $this->getDatabaseTable($tableList->getByName($childTableName)->getName())->field($childFieldName);

        return $this->database->query()->constraint()
            ->setSubject($parentField)
            ->equals($childField);
    }

    protected function createQueryConstraints(Table $table, array $attributeValues)
    {
        $dbTable = $this->getDatabaseTable($table->getName());
        $queryConstraints = array();
        foreach ($attributeValues as $attribute => $value) {
            $queryConstraints[] = $this->database->query()->constraint()
                ->setSubject($dbTable->field($attribute))
                ->equals($this->database->value()->string($value));
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
