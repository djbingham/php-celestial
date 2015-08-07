<?php
namespace Sloth\Module\Graph\Definition;

use Sloth\Module\Graph\Definition\Table\FieldList;
use Sloth\Module\Graph\Definition\Table\Join;
use Sloth\Module\Graph\Definition\Table\JoinList;

class Table
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
	 * @var FieldList
	 */
	public $fields;

	/**
	 * @var JoinList
	 */
	public $links;

	/**
	 * @var ViewList
	 */
	public $views;

	/**
	 * @var ValidatorList
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

	public function listDescendants(JoinList $linksToInclude)
	{
		$tableList = new TableList();
		$tableLinks = $linksToInclude->getByParent($this->getAlias());
		foreach ($tableLinks as $link) {
			/** @var Join $link */
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
