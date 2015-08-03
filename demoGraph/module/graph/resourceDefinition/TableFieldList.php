<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Helper\ObjectList;
use Sloth\Exception\InvalidArgumentException;

class TableFieldList extends ObjectList
{
	public function push(TableField $table)
	{
		$this->items[] = $table;
		return $this;
	}

	/**
	 * @param string $index
	 * @return TableField
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	/**
	 * @param string $alias
	 * @return bool
	 */
	public function containsName($alias)
	{
		try {
			$foundField = $this->getByProperty('name', $alias);
		} catch (InvalidArgumentException $e) {
            $foundField = false;
		}
		return ($foundField instanceof TableField);
	}
}
