<?php
namespace Sloth\Module\Graph\Definition;

use Sloth\Helper\ObjectList;

class TableList extends ObjectList
{
	public function push(Table $table)
	{
		$this->items[] = $table;
		return $this;
	}

	/**
	 * @param string $index
	 * @return Table
	 */
	public function getByIndex($index)
	{
		return parent::getByIndex($index);
	}

	/**
	 * @return Table
	 */
	public function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * @param int $start
	 * @param int $quantity
	 * @return TableList
	 */
	public function slice($start, $quantity)
	{
		$tables = array_slice($this->items, $start, $quantity);
		$tableList = new self();
		foreach ($tables as $table) {
			$tableList->push($table);
		}
		return $tableList;
	}

	/**
	 * @param string $alias
	 * @return bool
	 */
	public function containsTableAlias($alias)
	{
		$found = false;
		foreach ($this->items as $table) {
			/** @var Table $table */
			if ($table->getAlias() === $alias) {
				$found = true;
			}
		}
		return $found;
	}
}
