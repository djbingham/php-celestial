<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Definition;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;

class QuerySetItem
{
	/**
	 * @var string
	 */
	private $tableName;

	/**
	 * @var MySqlQuery
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

	public function getLinks()
	{
		return $this->links;
	}

	public function setLinks($links)
	{
		$this->links = $links;
		return $this;
	}

	public function getParentLink()
	{
		return $this->parentLink;
	}

	public function setParentLink($parentLink)
	{
		$this->parentLink = $parentLink;
		return $this;
	}
}
