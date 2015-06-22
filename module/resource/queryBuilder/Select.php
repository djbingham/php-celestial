<?php
namespace Sloth\Module\Resource\QueryBuilder;

use Sloth\Module\Resource\Definition\Attribute;
use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\Definition\TableLink;
use Sloth\Module\Resource\Definition\TableList;
use SlothMySql\Abstractory\Value\ATable;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;

class Select
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
     * @param array $constraints
     * @return MySqlQuery
     */
    public function createQuery(TableList $tableList, AttributeList $attributeList, array $constraints = array())
    {
        foreach ($constraints as $tableField => $value) {
            if (!$attributeList->contains($tableField)) {
                list($tableName, $fieldName) = explode('.', $tableField);
                $attributeList->append($tableField, new Attribute(array(
                    'name' => $tableField,
                    'tableName' => $tableName,
                    'fieldName' => $fieldName
                )));
            }
        }
        $fields = $this->createQueryFields($attributeList);
        $table = $this->getDatabaseTable($tableList->getPrimaryTable()->getName());
        $joins = $this->createQueryJoins($tableList);
        $constraints = $this->createQueryConstraints($attributeList, $constraints);
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

    protected function createQueryConstraints(AttributeList $attributeList, array $constraints)
    {
        $queryConstraints = array();
        foreach ($constraints as $attributeName => $value) {
            $attribute = $attributeList->getByName($attributeName);
            $dbTable = $this->getDatabaseTable($attribute->getTableName());
            $constraint = $this->database->query()->constraint()
                ->setSubject($dbTable->field($attribute->getFieldName()));
            if (is_array($value)) {
                foreach ($value as $index => $item) {
                    $value[$index] = $this->database->value()->string($item);
                }
                $constraint->in($this->database->value()->valueList($value));
            } else {
                $constraint->equals($this->database->value()->string($value));
            }
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
