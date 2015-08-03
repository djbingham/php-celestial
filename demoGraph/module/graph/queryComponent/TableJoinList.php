<?php
namespace DemoGraph\Module\Graph\QueryComponent;

use DemoGraph\Module\Graph\Helper\ObjectList;

class TableJoinList extends ObjectList
{
	public function push(TableJoin $attribute)
	{
		$this->items[] = $attribute;
		return $this;
	}

	/**
	 * @param string $index
	 * @return TableJoin
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}
}
