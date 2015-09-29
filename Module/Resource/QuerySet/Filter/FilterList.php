<?php
namespace Sloth\Module\Resource\QuerySet\Filter;

use Sloth\Helper\ObjectList;
use Sloth\Module\Resource\QuerySet\Filter\Filter;

class FilterList extends ObjectList
{
	public function push(Filter $table)
	{
		$this->items[] = $table;
		return $this;
	}

	/**
	 * @param string $index
	 * @return Filter
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}
}
