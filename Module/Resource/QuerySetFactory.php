<?php
namespace Sloth\Module\Resource;

class QuerySetFactory
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var AttributeMapper
     */
    private $attributeMapper;

    public function __construct(QueryFactory $queryFactory, AttributeMapper $attributeMapper)
    {
        $this->queryFactory = $queryFactory;
        $this->attributeMapper = $attributeMapper;
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
        return new QuerySet\InsertRecord($this->queryFactory, $this->attributeMapper);
    }
}
