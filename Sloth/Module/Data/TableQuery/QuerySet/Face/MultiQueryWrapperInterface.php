<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Face;

use Sloth\Helper\Face\ObjectListInterface;

interface MultiQueryWrapperInterface extends QueryWrapperInterface, ObjectListInterface
{
	/**
	 * @param QueryWrapperInterface $query
	 * @return $this
	 */
	public function push(QueryWrapperInterface $query);

	/**
	 * @return QueryWrapperInterface
	 */
	public function shift();

	/**
	 * @return int
	 */
	public function length();

	/**
	 * @param $index
	 * @return QueryWrapperInterface
	 */
	public function getByIndex($index);
}