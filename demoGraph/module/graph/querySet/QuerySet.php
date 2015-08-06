<?php
namespace DemoGraph\Module\Graph\QuerySet;

use DemoGraph\Module\Graph\Helper\ObjectList;
use DemoGraph\Module\Graph\QuerySet\QuerySetItem;
use DemoGraph\Module\Graph\Definition;

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
