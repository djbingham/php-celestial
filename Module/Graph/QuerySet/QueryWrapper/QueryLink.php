<?php
namespace Sloth\Module\Graph\QuerySet\QueryWrapper;

use Sloth\Module\Graph\QuerySet\Face\QueryWrapperInterface;
use Sloth\Module\Graph\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Graph\Definition\Table\Join;

class QueryLink implements QueryLinkInterface
{
	/**
	 * @var QueryWrapperInterface
	 */
	private $parentQueryWrapper;

	/**
	 * @var QueryWrapperInterface
	 */
	private $childQueryWrapper;

	/**
	 * @var Join
	 */
	private $joinDefinition;

	public function setParentQueryWrapper(QueryWrapperInterface $parentQueryWrapper)
	{
		$this->parentQueryWrapper = $parentQueryWrapper;
		return $this;
	}

	public function getParentQueryWrapper()
	{
		return $this->parentQueryWrapper;
	}

	public function setChildQueryWrapper(QueryWrapperInterface $childQueryWrapper)
	{
		$this->childQueryWrapper = $childQueryWrapper;
		return $this;
	}

	public function getChildQueryWrapper()
	{
		return $this->childQueryWrapper;
	}

	public function setJoinDefinition(Join $joinDefinition)
	{
		$this->joinDefinition = $joinDefinition;
		return $this;
	}

	public function getJoinDefinition()
	{
		return $this->joinDefinition;
	}
}
