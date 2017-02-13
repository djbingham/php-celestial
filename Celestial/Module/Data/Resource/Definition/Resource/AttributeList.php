<?php
namespace Celestial\Module\Data\Resource\Definition\Resource;

use Celestial\Helper\Face\ObjectListInterface;
use Celestial\Helper\ObjectListTrait;

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
