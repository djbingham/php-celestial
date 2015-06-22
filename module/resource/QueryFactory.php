<?php
namespace Sloth\Module\Resource;

use Sloth\Module\Resource\Base\ResourceDefinition;
use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Definition\TableList;
use SlothMySql\DatabaseWrapper;

class QueryFactory
{
    /**
     * @var DatabaseWrapper
     */
    private $database;

	public function __construct(DatabaseWrapper $database)
    {
        $this->database = $database;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function select(TableList $tableList, AttributeList $attributeList, array $attributeValues)
    {
        $queryBuilder = new QueryBuilder\Select($this->database);
        return $queryBuilder->createQuery($tableList, $attributeList, $attributeValues);
    }

    public function selectByAttributes(ResourceDefinition $definition, array $attributes)
    {
        $queryBuilder = new QueryBuilder\SelectByAttributes($this->database);
        return $queryBuilder->createQuery($definition, $attributes);
    }

    public function search(ResourceDefinition $definition, array $filters)
    {
        $queryBuilder = new QueryBuilder\Search($this->database);
        return $queryBuilder->createQuery($definition, $filters);
    }

    public function insertSingle(ResourceDefinition $definition, array $attributes)
    {
        $queryBuilder = new QueryBuilder\InsertSingle($this->database);
        return $queryBuilder->createQuery($definition, $attributes);
    }

    public function updateById(ResourceDefinition $definition, array $attributes)
    {
        $queryBuilder = new QueryBuilder\UpdateById($this->database);
        return $queryBuilder->createQuery($definition, $attributes);
    }

    public function deleteByAttributes(ResourceDefinition $definition, array $attributes)
    {
        $queryBuilder = new QueryBuilder\DeleteByAttributes($this->database);
        return $queryBuilder->createQuery($definition, $attributes);
    }
}
