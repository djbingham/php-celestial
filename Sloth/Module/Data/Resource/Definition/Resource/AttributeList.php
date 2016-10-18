<?php
namespace Sloth\Module\Data\Resource\Definition\Resource;

use Sloth\Helper\Face\ObjectListInterface;
use Sloth\Helper\ObjectListTrait;

class AttributeList extends Attribute implements ObjectListInterface
{
	use ObjectListTrait;

	public $name;
	public $resource;

	public function push(Attribute $attribute)
	{
		$this->items[] = $attribute;
		return $this;
	}
}
