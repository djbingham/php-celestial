<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper;

use Sloth\Module\Data\TableQuery\QuerySet\Base\AbstractQueryWrapper;
use Sloth\Module\Data\TableQuery\QuerySet\Face\SingleQueryWrapperInterface;
use SlothMySql;

class SingleQueryWrapper extends AbstractQueryWrapper implements SingleQueryWrapperInterface
{
	/**
	 * @var SlothMySql\Face\QueryInterface
	 */
	private $query;

	/**
	 * @var array $data
	 */
	private $data;

	public function setQuery(SlothMySql\Face\QueryInterface $query)
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
}