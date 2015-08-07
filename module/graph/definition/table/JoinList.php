<?php
namespace Sloth\Module\Graph\Definition\Table;

use Sloth\Module\Graph\Helper\ObjectList;

class JoinList extends ObjectList
{
	public function push(Join $connection)
	{
		$this->items[] = $connection;
		return $this;
	}

	/**
	 * @param string $index
	 * @return Join
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	/**
	 * @param string $name
	 * @return Join
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
		$links = new JoinList();
		foreach ($this as $link) {
			/** @var Join $link */
			if (in_array($link->type, $types)) {
				$links->push($link);
			}
		}
		return $links;
	}

	public function getByParent($parentAlias)
	{
		$links = new JoinList();
		foreach ($this as $link) {
			/** @var Join $link */
			if ($link->parentTable->getAlias() === $parentAlias) {
				$links->push($link);
			}
		}
		return $links;
	}

	public function getByChild($childAlias)
	{
		$foundLink = null;
		foreach ($this as $link) {
			/** @var Join $link */
			if ($link->getChildTable()->getAlias() === $childAlias) {
				$foundLink = $link;
			}
		}
		return $foundLink;
	}

	public function containsChild($childAlias)
	{
		$found = false;
		foreach ($this as $link) {
			/** @var Join $link */
			if ($link->getChildTable()->getAlias() === $childAlias) {
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
			/** @var Join $link */
			if ($link->name === $linkName) {
				$foundIndex = $index;
			}
		}
		return $foundIndex;
	}
}
