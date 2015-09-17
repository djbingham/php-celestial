<?php
namespace Sloth\Module\Graph\QuerySet\QueryWrapper;

use Sloth\Helper\ObjectListTrait;
use Sloth\Module\Graph\QuerySet\Base\AbstractQueryWrapper;
use Sloth\Module\Graph\QuerySet\Face\MultiQueryWrapperInterface;
use Sloth\Module\Graph\QuerySet\Face\QueryWrapperInterface;

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