<?php
namespace Sloth\Module\Graph\Definition;

use Sloth\Module\Graph\Helper\ObjectList;

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
}
