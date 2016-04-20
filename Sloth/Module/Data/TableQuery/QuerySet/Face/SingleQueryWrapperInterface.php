<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Face;

use SlothMySql;

interface SingleQueryWrapperInterface extends QueryWrapperInterface
{
	/**
	 * @param SlothMySql\Face\QueryInterface $query
	 * @return $this
	 */
	public function setQuery(SlothMySql\Face\QueryInterface $query);

	/**
	 * @return SlothMySql\Face\QueryInterface
	 */
	public function getQuery();

	/**
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data);

	/**
	 * @return array
	 */
	public function getData();

	/**
	 * @param array $filters
	 * @return $this
	 */
	public function setFilters(array $filters);

	/**
	 * @return array
	 */
	public function getFilters();
}