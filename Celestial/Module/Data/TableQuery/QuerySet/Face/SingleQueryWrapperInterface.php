<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Face;

use PhpMySql;

interface SingleQueryWrapperInterface extends QueryWrapperInterface
{
	/**
	 * @param PhpMySql\Face\QueryInterface $query
	 * @return $this
	 */
	public function setQuery(PhpMySql\Face\QueryInterface $query);

	/**
	 * @return PhpMySql\Face\QueryInterface
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