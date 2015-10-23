<?php
namespace Sloth\Module\Resource\QuerySet\Base;

use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Resource\QuerySet\Face\QueryLinkListInterface;
use Sloth\Module\Resource\QuerySet\Face\QueryWrapperInterface;

abstract class AbstractQueryWrapper implements QueryWrapperInterface
{
	/**
	 * @var Table
	 */
	private $table;

	/**
	 * @var QueryLinkInterface
	 */
	private $parentLink;

	/**
	 * @var QueryLinkListInterface
	 */
	private $childLinks;

	/**
	 * @var
	 */
	private $wrappedQuery;

	public function setTable(Table $table)
	{
		$this->table = $table;
		return $this;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function setParentLink(QueryLinkInterface $parentLink)
	{
		$this->parentLink = $parentLink;
		return $this;
	}

	public function getParentLink()
	{
		return $this->parentLink;
	}

	public function setChildLinks(QueryLinkListInterface $childLinks)
	{
		$this->childLinks = $childLinks;
		return $this;
	}

	public function getChildLinks()
	{
		return $this->childLinks;
	}

	public function setWrappedQuery(QueryWrapperInterface $wrappedQuery)
	{
		$this->wrappedQuery = $wrappedQuery;
		return $this;
	}

	public function getWrappedQuery()
	{
		return $this->wrappedQuery;
	}
}
