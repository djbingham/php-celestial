<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Helper\ObjectList;

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
