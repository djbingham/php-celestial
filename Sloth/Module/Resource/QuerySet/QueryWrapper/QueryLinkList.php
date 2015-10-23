<?php
namespace Sloth\Module\Resource\QuerySet\QueryWrapper;

use Sloth\Module\Resource\Definition\Table;
use Sloth\Module\Resource\QuerySet\Face\QueryLinkInterface;
use Sloth\Module\Resource\QuerySet\Face\QueryLinkListInterface;
use Sloth\Module\Resource\QuerySet\Face\QueryWrapperInterface;
use Sloth\Helper\ObjectList;

class QueryLinkList extends ObjectList implements QueryLinkListInterface
{
	/**
	 * @param QueryLinkInterface $link
	 * @return $this
	 */
	public function push(QueryLinkInterface $link)
	{
		array_push($this->items, $link);
		return $this;
	}

	/**
	 * @return QueryLinkInterface
	 */
	public function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * @var QueryWrapperInterface
	 */
	private $parentQueryWrapper;

	/**
	 * @var QueryLinkListInterface
	 */
	private $childLinks;

	public function setParentQueryWrapper(QueryWrapperInterface $parentQueryWrapper)
	{
		$this->parentQueryWrapper = $parentQueryWrapper;
		return $this;
	}

	public function getParentQueryWrapper()
	{
		return $this->parentQueryWrapper;
	}

	public function setChildLinks(QueryLinkListInterface $childLinks)
	{
		$this->childLinks = $childLinks;
		return $this;
	}

	public function getChildLinks()
	{
		return $this->childLinks;
	}

	public function getByChildTable(Table $childTable)
	{
		$found = null;
		/** @var QueryLinkInterface $queryLink */
		foreach ($this as $queryLink) {
			$join = $queryLink->getJoinDefinition();
			if ($join->getChildTable()->getAlias() === $childTable->getAlias()) {
				$found = $queryLink;
			}
		}
		return $found;
	}
}