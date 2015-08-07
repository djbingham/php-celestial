<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Helper\ObjectList;
use Sloth\Module\Graph\QuerySet\QuerySetItem;
use Sloth\Module\Graph\Definition;

class QuerySet extends ObjectList
{
	/**
	 * @var array
	 */
	protected $items;

	public function push(QuerySetItem $item)
	{
		$this->items[] = $item;
		return $this;
	}

	/**
	 * @return QuerySetItem
	 */
	public function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * @param string $index
	 * @return QuerySetItem
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}
}
