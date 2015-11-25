<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\QueryWrapper;

use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryWrapperInterface;
use Sloth\Module\Data\TableQuery\QuerySet\Face\QueryLinkInterface;

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
	 * @var JoinInterface
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

	public function setJoinDefinition(JoinInterface $joinDefinition)
	{
		$this->joinDefinition = $joinDefinition;
		return $this;
	}

	public function getJoinDefinition()
	{
		return $this->joinDefinition;
	}
}
