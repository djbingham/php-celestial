<?php
namespace Sloth\Module\DataTable\Definition;

use Sloth\Module\DataTable\Face\TableInterface;
use Sloth\Module\Render\ViewList;

class Table implements TableInterface
{
	/**
	 * @var string
	 */
	public $alias;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var Table\FieldList
	 */
	public $fields;

	/**
	 * @var Table\JoinList
	 */
	public $links;

	/**
	 * @var ViewList
	 */
	public $views;

	/**
	 * @var Table\ValidatorList
	 */
	public $validators;

	public function setAlias($alias)
	{
		$this->alias = $alias;
		return $this;
	}

	public function getAlias()
	{
		if ($this->alias !== null) {
			$alias = $this->alias;
		} else {
			$alias = $this->name;
		}
		return $alias;
	}

	public function listDescendants(Table\JoinList $linksToInclude)
	{
		$tableList = new TableList();
		$tableLinks = $linksToInclude->getByParent($this->getAlias());
		foreach ($tableLinks as $link) {
			/** @var Table\Join $link */
			$childTable = $link->getChildTable();
			if ($tableLinks->containsChild($childTable->getAlias())) {
				$tableList->push($childTable);
				foreach ($childTable->listDescendants($linksToInclude) as $grandchildTable) {
					$tableList->push($grandchildTable);
				}
			}
		}
		return $tableList;
	}
}
