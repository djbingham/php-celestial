<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Definition;
use SlothMySql\Face\QueryInterface;

class QuerySetItem
{
	/**
	 * @var string
	 */
	private $tableName;

	/**
	 * @var QueryInterface
	 */
	private $query;

	/**
	 * @var Definition\Table\JoinList
	 */
	private $links;

	/**
	 * @var Definition\Table\Join
	 */
	private $parentLink;

	public function getTableName()
	{
		return $this->tableName;
	}

	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
		return $this;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function setQuery($query)
	{
		$this->query = $query;
		return $this;
	}

	public function getChildLinks()
	{
		return $this->links;
	}

	public function setChildLinks(Definition\Table\JoinList $links)
	{
		$this->links = $links;
		return $this;
	}

	public function getParentLink()
	{
		return $this->parentLink;
	}

	public function setParentLink(Definition\Table\Join $parentLink)
	{
		$this->parentLink = $parentLink;
		return $this;
	}
}
