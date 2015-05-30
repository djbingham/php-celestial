<?php
namespace Sloth\Module\Resource\QueryBuilder;

use Sloth\Module\Resource\Base\ResourceDefinition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;
use SlothMySql\QueryBuilder\Value\Table;

class InsertSingle
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
     * @param array $data
     * @return MySqlQuery
     */
    public function createQuery(ResourceDefinition $resourceDefinition, array $data)
    {
        $primaryTable = $this->database->value()->table($resourceDefinition->primaryTableName());

        $query = $this->database->query()->insert()
            ->data($this->createQueryData($primaryTable, $data))
            ->into($primaryTable);

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
}
