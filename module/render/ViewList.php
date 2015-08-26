<?php
namespace Sloth\Module\Render;

use Sloth\Helper\ObjectList;
use Sloth\Module\Render\View;

class ViewList extends ObjectList
{
	public function push(View $view)
	{
		$this->items[] = $view;
		return $this;
	}

	/**
	 * @param string $index
	 * @return View
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	/**
	 * @param $propertyName
	 * @param $value
	 * @return View
	 */
	public function getByProperty($propertyName, $value)
	{
		return parent::getByProperty($propertyName, $value);
	}
}
