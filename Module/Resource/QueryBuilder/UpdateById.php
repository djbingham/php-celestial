<?php
namespace Sloth\Module\Resource\QueryBuilder;

use Sloth\Module\Resource\Base\ResourceDefinition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;
use SlothMySql\QueryBuilder\Value\Table;

class UpdateById
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
     * @param array $attributes
     * @return MySqlQuery
     */
    public function createQuery(ResourceDefinition $resourceDefinition, array $attributes)
    {
        $primaryTable = $this->database->value()->table($resourceDefinition->primaryTableName());

        $query = $this->database->query()->update()
            ->table($primaryTable)
            ->data($this->createQueryData($primaryTable, $attributes))
            ->where($this->createQueryConstraint($resourceDefinition, $attributes[$resourceDefinition->autoAttribute()]));

        return $query;
    }

    protected function createQueryData(Table $primaryTable, array $attributes)
    {
        $queryData = $this->database->value()->tableData()->beginRow();
        foreach ($attributes as $field => $value) {
            $queryData->set($primaryTable->field($field), $this->database->value()->string($value));
        }
        $queryData->endRow();

        return $queryData;
    }

    protected function createQueryConstraint(ResourceDefinition $resourceDefinition, $resourceId)
    {
        $dbTable = $this->database->value()->table($resourceDefinition->primaryTableName());
        return $this->database->query()->constraint()
            ->setSubject($dbTable->field($resourceDefinition->autoAttribute()))
            ->equals($this->database->value()->string($resourceId));
    }
}
