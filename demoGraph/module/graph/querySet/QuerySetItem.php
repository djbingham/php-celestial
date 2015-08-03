<?php
namespace DemoGraph\Module\Graph\QuerySet;

use DemoGraph\Module\Graph\ResourceDefinition;
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
	 * @var ResourceDefinition\LinkList
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
