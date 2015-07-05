<?php
namespace Sloth\Module\Resource;

class QuerySetFactory
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

	public function getBy()
    {
        return new QuerySet\GetBy($this->queryFactory);
    }

    public function search()
    {
        return new QuerySet\Search($this->queryFactory);
    }

    public function insertRecord()
    {
        return new QuerySet\InsertRecord($this->queryFactory);
    }
}
