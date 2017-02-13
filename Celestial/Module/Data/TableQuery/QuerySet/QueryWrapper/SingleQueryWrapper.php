<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\QueryWrapper;

use Celestial\Module\Data\TableQuery\QuerySet\Base\AbstractQueryWrapper;
use Celestial\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use PhpMySql;

class SingleQueryWrapper extends AbstractQueryWrapper implements SingleQueryWrapperInterface
{
	/**
	 * @var PhpMySql\Face\QueryInterface
	 */
	private $query;

	/**
	 * @var array $data
	 */
	private $data;

	/**
	 * @var array $filters
	 */
	private $filters;

	public function setQuery(PhpMySql\Face\QueryInterface $query)
	{
		$this->query = $query;
		return $this;
	}

	public function getQuery()
	{
		return $this->query;
	}

	public function setData(array $data)
	{
		$this->data = $data;
		return $this;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;
		return $this;
	}

	public function getFilters()
	{
		return $this->filters;
	}
}
