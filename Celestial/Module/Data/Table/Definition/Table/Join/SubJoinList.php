<?php
namespace Celestial\Module\Data\Table\Definition\Table\Join;

use Celestial\Helper\ObjectList;
use Celestial\Module\Data\Table\Face\SubJoinListInterface;

class SubJoinList extends ObjectList implements SubJoinListInterface
{
	public function push(SubJoin $view)
	{
		$this->items[] = $view;
		return $this;
	}

	/**
	 * @return SubJoin
	 */
	public function shift()
	{
		$item = array_shift($this->items);
		return $item;
	}

	/**
	 * @param string $index
	 * @return SubJoin
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	public function getByParentTableAlias($parentAlias)
	{
		$foundJoin = null;
		foreach ($this as $join) {
			/** @var SubJoin $join */
			if ($join->parentTable->getAlias() === $parentAlias) {
				$foundJoin = $join;
				break;
			}
		}
		return $foundJoin;
	}
}
