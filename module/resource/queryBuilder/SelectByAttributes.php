<?php
namespace Sloth\Module\Resource\QueryBuilder;

use Sloth\Module\Resource\Base\ResourceDefinition;
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
        $primaryTable = $resourceDefinition->primaryTableName();
        $joins = $this->createQueryJoins($resourceDefinition->tables());
        $constraints = $this->createQueryConstraints($primaryTable, $attributes);

        $query = $this->database->query()->select()
            ->setFields($this->createQueryFields($resourceDefinition->attributes(), $resourceDefinition))
            ->from($this->database->value()->table($primaryTable))
            ->setJoins($joins);

        if (count($constraints) > 0) {
            $query->where(array_shift($constraints));
            foreach ($constraints as $constraint) {
                $query->andWhere($constraint);
            }
        }

        return $query;
    }

    protected function createQueryFields(array $attributes, ResourceDefinition $resourceDefinition)
    {
        $fields = array();
        foreach ($attributes as $attributeName => $tableFieldString) {
            if (is_array($tableFieldString)) {
                $fields = array_merge($fields, $this->createQueryFields($tableFieldString, $resourceDefinition));
            } else {
                list($tableName, $fieldName) = explode('.', $tableFieldString);
                $table = $resourceDefinition->table($tableName);
                $fields[] = $this->database->value()->tableField($table['name'], $fieldName)->setAlias($attributeName);
            }
        }
        return $fields;
    }

    protected function createQueryJoins(array $tables)
    {
        $joins = array();
        foreach ($tables as $tableDefinition) {
            $tableJoin = $this->createJoin($tableDefinition, $tables);
            if (!is_null($tableJoin)) {
                $joins[] = $tableJoin;
            }
        }
        return $joins;
    }

    protected function createJoin(array $tableDefinition, array $allTables)
    {
        $join = null;
        if (array_key_exists('links', $tableDefinition) && count($tableDefinition['links']) > 0) {
            if ($tableDefinition['type'] === 'list') {
                $join = $this->database->query()->join()->left();
            } else {
                $join = $this->database->query()->join()->left();
            }
            $join->table($this->getDatabaseTable($tableDefinition['name']))
                ->on($this->createJoinConstraints($tableDefinition['links'], $allTables));
        }
        return $join;
    }

    protected function createJoinConstraints(array $linksGroupedByTable, array $allTables)
    {
        $firstTableLinks = array_shift($linksGroupedByTable);
        $constraint = $this->createConstraintsForTableLinks($firstTableLinks, $allTables);
        foreach ($linksGroupedByTable as $linkedTableName => $tableLinks) {
            $constraint->andOn($this->createConstraintsForTableLinks($tableLinks, $allTables));
        }
        return $constraint;
    }

    protected function createConstraintsForTableLinks(array $tableLinks, array $allTables)
    {
        $firstLinkParent = array_shift(array_keys($tableLinks));
        $firstLinkChild = $tableLinks[$firstLinkParent];
        unset($tableLinks[$firstLinkParent]);
        $constraint = $this->createConstraintForTableLink($firstLinkParent, $firstLinkChild, $allTables);

        foreach ($tableLinks as $parent => $child) {
            $constraint->andOn($this->createConstraintForTableLink($parent, $child, $allTables));
        }
        return $constraint;
    }

    protected function createConstraintForTableLink($parent, $child, $allTables)
    {
        list($parentTableName, $parentFieldName) = explode('.', $parent);
        list($childTableName, $childFieldName) = explode('.', $child);

        $parentField = $this->getDatabaseTable($allTables[$parentTableName]['name'])->field($parentFieldName);
        $childField = $this->getDatabaseTable($allTables[$childTableName]['name'])->field($childFieldName);

        return $this->database->query()->constraint()
            ->setSubject($parentField)
            ->equals($childField);
    }

    protected function createQueryConstraints($tableName, array $attributeValues)
    {
        $dbTable = $this->database->value()->table($tableName);
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
