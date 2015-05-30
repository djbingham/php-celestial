<?php
namespace Sloth\Module\Resource\QueryBuilder;

use Sloth\Module\Resource\Base\ResourceDefinition;
use SlothMySql\DatabaseWrapper;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;
use SlothMySql\QueryBuilder\Value\Table;

class DeleteByAttributes
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

        $query = $this->database->query()->delete()
            ->from($primaryTable)
            ->where($this->createQueryConstraint($resourceDefinition, $attributes[$resourceDefinition->autoAttribute()]));

        return $query;
    }

    protected function createQueryConstraint(ResourceDefinition $resourceDefinition, $resourceId)
    {
        $dbTable = $this->database->value()->table($resourceDefinition->primaryTableName());
        return $this->database->query()->constraint()
            ->setSubject($dbTable->field($resourceDefinition->autoAttribute()))
            ->equals($this->database->value()->string($resourceId));
    }
}
