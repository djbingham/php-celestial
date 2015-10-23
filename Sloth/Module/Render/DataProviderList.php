<?php
namespace Sloth\Module\Render;

use Sloth\Helper\ObjectListTrait;
use Sloth\Module\Render\Face\DataProviderInterface;
use Sloth\Module\Render\Face\DataProviderListInterface;

class DataProviderList implements DataProviderListInterface
{
	use ObjectListTrait;

	public function push(DataProviderInterface $provider)
	{
		array_push($this->items, $provider);
		return $this;
	}

	public function unshift(DataProviderInterface $provider)
	{
		array_unshift($this->items, $provider);
		return $this;
	}
}
