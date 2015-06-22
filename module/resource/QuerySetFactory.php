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
}
