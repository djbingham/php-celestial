<?php
namespace Sloth\Module\Graph\QuerySet\Face;

use Sloth\Module\Graph\Definition\Table\Join;

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
	 * @param Join $joinDefinition
	 * @return $this
	 */
	public function setJoinDefinition(Join $joinDefinition);

	/**
	 * @return Join
	 */
	public function getJoinDefinition();
}
