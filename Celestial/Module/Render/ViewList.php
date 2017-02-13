<?php
namespace Celestial\Module\Render;

use Celestial\Helper\ObjectList;
use Celestial\Module\Render\Face\ViewInterface;

class ViewList extends ObjectList
{
	public function push(ViewInterface $view)
	{
		$this->items[] = $view;
		return $this;
	}

	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	public function getByProperty($propertyName, $value)
	{
		return parent::getByProperty($propertyName, $value);
	}
}
