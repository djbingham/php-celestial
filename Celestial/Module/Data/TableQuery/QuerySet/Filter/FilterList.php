<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Filter;

use Celestial\Helper\ObjectList;

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
