<?php
namespace DemoGraph\Module\Graph;

use DemoGraph\Module\Graph\ResourceDefinition\AttributeList;
use DemoGraph\Module\Graph\ResourceDefinition\Table;
use DemoGraph\Module\Graph\ResourceDefinition\TableList;
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

	public function select()
	{
		return new QueryBuilder\Select($this->database);
	}

	public function search(TableList $tableList, AttributeList $attributeList, array $filters)
	{
		$queryBuilder = new QueryBuilder\Search($this->database);
		return $queryBuilder->createQuery($tableList, $attributeList, $filters);
	}

	public function insertSingle(Table $table, array $attributes)
	{
		$queryBuilder = new QueryBuilder\InsertSingle($this->database);
		return $queryBuilder->createQuery($table, $attributes);
	}

//    public function updateById(ResourceDefinition $definition, array $attributes)
//    {
//        $queryBuilder = new QueryBuilder\UpdateById($this->database);
//        return $queryBuilder->createQuery($definition, $attributes);
//    }
//
//    public function deleteByAttributes(ResourceDefinition $definition, array $attributes)
//    {
//        $queryBuilder = new QueryBuilder\DeleteByAttributes($this->database);
//        return $queryBuilder->createQuery($definition, $attributes);
//    }
}
