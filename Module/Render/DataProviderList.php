<?php
namespace Module\Render;

use Module\Render\Face\DataProviderListInterface;
use Sloth\Helper\ObjectListTrait;
use Sloth\Module\Render\Face\DataProviderInterface;

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
