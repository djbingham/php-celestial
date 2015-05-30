<?php
namespace Sloth\Module\Resource\QueryBuilder;

use Sloth\Module\Resource\Base\ResourceDefinition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;

class Search
{
    /**
     * @var DatabaseWrapper
     */
    private $database;

    public function __construct(DatabaseWrapper $database)
    {
        $this->database = $database;
    }

    /**
     * @param ResourceDefinition $resourceDefinition
     * @param array $filters
     * @return MySqlQuery
     */
    public function createQuery(ResourceDefinition $resourceDefinition, array $filters)
    {
        $primaryTable = $resourceDefinition->primaryTableName();
        $constraints = $this->createQueryConstraints($primaryTable, $filters);

        $query = $this->database->query()->select()
            ->setFields($this->createQueryFields($resourceDefinition))
            ->from($this->database->value()->table($primaryTable));

        if (count($constraints) > 0) {
            $query->where(array_shift($constraints));
            foreach ($constraints as $constraint) {
                $query->andWhere($constraint);
            }
        }

        return $query;
    }

    protected function createQueryFields(ResourceDefinition $resourceDefinition)
    {
        $fields = array();
        foreach ($resourceDefinition->attributes() as $attributeName => $tableFieldString) {
            $fieldParts = explode('.', $tableFieldString);
            $tableName = '';
            if (count($fieldParts) > 1) {
                $tableName = array_shift($fieldParts);
            }
            $fieldName = array_shift($fieldParts);
            $fields[] = $this->database->value()->tableField($tableName, $fieldName);
        }
        return $fields;
    }

    protected function createQueryConstraints($tableName, array $filters)
    {
        $dbTable = $this->database->value()->table($tableName);
        $queryConstraints = array();
        foreach ($filters as $filter) {
            $queryConstraints[] = $this->database->query()->constraint()
                ->setSubject($dbTable->field($filter['subject']))
                ->setComparator($filter['comparator'])
                ->setValue($this->database->value()->string($filter['value']));
        }
        return $queryConstraints;
    }
}
