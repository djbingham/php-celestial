<?php
namespace Sloth\Module\Resource;

use Sloth\Module\Resource\Base\ResourceDefinition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;

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
