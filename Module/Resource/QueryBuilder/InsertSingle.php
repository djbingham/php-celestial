<?php
namespace Sloth\Module\Resource\QueryBuilder;

use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\Table;
use SlothMySql\Abstractory\AValue;
use SlothMySql\Abstractory\Value\ATable;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;

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
     * @param Table $table
     * @param array $data
     * @return MySqlQuery
     */
    public function createQuery(Table $table, array $data)
    {
        $sqlTable = $this->database->value()->table($table->getName());

        $query = $this->database->query()->insert()
            ->data($this->createQueryData($sqlTable, $data))
            ->into($sqlTable);

        return $query;
    }

    protected function createQueryData(AValue $primaryTable, array $attributes)
    {
        $queryData = $this->database->value()->tableData()->beginRow();
        foreach ($attributes as $field => $value) {
            $queryData->set($primaryTable->field($field), $this->database->value()->string($value));
        }
        $queryData->endRow();

        return $queryData;
    }

    protected function insertIntoPrimaryTable()
    {

    }

    protected function insertIntoTable($table, array $data)
    {

    }
}
