<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;

class LinkList extends ObjectList
{
	public function push(Link $connection)
	{
		$this->items[] = $connection;
		return $this;
	}

	/**
	 * @param string $index
	 * @return Link
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	/**
	 * @param string $name
	 * @return Link
	 */
	public function getByName($name)
	{
		return $this->getByProperty('name', $name);
	}

	public function getByType($types)
	{
		if (!is_array($types)) {
			$types = array($types);
		}
		$links = new LinkList();
		foreach ($this as $link) {
			/** @var Link $link */
			if (in_array($link->type, $types)) {
				$links->push($link);
			}
		}
		return $links;
	}

	public function getByParent($parentAlias)
	{
		$links = new LinkList();
		foreach ($this as $link) {
			/** @var Link $link */
			if ($link->parentResource->getAlias() === $parentAlias) {
				$links->push($link);
			}
		}
		return $links;
	}

	public function getByChild($childAlias)
	{
		$foundLink = null;
		foreach ($this as $link) {
			/** @var Link $link */
			if ($link->getChildResource()->getAlias() === $childAlias) {
				$foundLink = $link;
			}
		}
		return $foundLink;
	}

	public function containsChild($childAlias)
	{
		$found = false;
		foreach ($this as $link) {
			/** @var Link $link */
			if ($link->getChildResource()->getAlias() === $childAlias) {
				$found = true;
				break;
			}
		}
		return $found;
	}

	public function indexOfName($linkName)
	{
		$foundIndex = -1;
		foreach ($this as $index => $link) {
			/** @var Link $link */
			if ($link->name === $linkName) {
				$foundIndex = $index;
			}
		}
		return $foundIndex;
	}
}
