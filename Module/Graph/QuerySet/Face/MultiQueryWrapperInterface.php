<?php
namespace Sloth\Module\Graph\QuerySet\Face;

use Helper\Face\ObjectListInterface;

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