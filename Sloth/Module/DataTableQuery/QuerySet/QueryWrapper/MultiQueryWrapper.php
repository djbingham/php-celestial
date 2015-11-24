<?php
namespace Sloth\Module\DataTableQuery\QuerySet\QueryWrapper;

use Sloth\Helper\ObjectListTrait;
use Sloth\Module\DataTableQuery\QuerySet\Base\AbstractQueryWrapper;
use Sloth\Module\DataTableQuery\QuerySet\Face\MultiQueryWrapperInterface;
use Sloth\Module\DataTableQuery\QuerySet\Face\QueryWrapperInterface;

class MultiQueryWrapper extends AbstractQueryWrapper implements MultiQueryWrapperInterface
{
	use ObjectListTrait;

	public function push(QueryWrapperInterface $query)
	{
		array_push($this->items, $query);
		return $this;
	}

	public function pop()
	{
		return array_pop($this->items);
	}

	public function shift()
	{
		return array_shift($this->items);
	}

	public function unshift($item)
	{
		return array_unshift($this->items, $item);
	}
}
