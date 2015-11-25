<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Face;

use Sloth\Module\Data\Table\Face\JoinInterface;

interface QueryLinkInterface
{
	/**
	 * @param QueryWrapperInterface $parentQueryWrapper
	 * @return $this
	 */
	public function setParentQueryWrapper(QueryWrapperInterface $parentQueryWrapper);

	/**
	 * @return QueryWrapperInterface
	 */
	public function getParentQueryWrapper();

	/**
	 * @param QueryWrapperInterface $childQueryWrapper
	 * @return $this
	 */
	public function setChildQueryWrapper(QueryWrapperInterface $childQueryWrapper);

	/**
	 * @return QueryWrapperInterface
	 */
	public function getChildQueryWrapper();

	/**
	 * @param JoinInterface $joinDefinition
	 * @return $this
	 */
	public function setJoinDefinition(JoinInterface $joinDefinition);

	/**
	 * @return JoinInterface
	 */
	public function getJoinDefinition();
}
