<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Definition;
use SlothMySql\QueryBuilder\Abstractory\MySqlQuery;

class QuerySetItem
{
	/**
	 * @var string
	 */
	private $resourceName;

	/**
	 * @var MySqlQuery
	 */
	private $query;

	/**
	 * @var \Sloth\Module\Graph\Definition\Table\JoinList
	 */
	private $links;

	public function getResourceName()
	{
		return $this->resourceName;
	}

	public function setResourceName($resourceName)
	{
		$this->resourceName = $resourceName;
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
}
