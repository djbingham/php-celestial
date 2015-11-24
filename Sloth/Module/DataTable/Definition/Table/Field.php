<?php
namespace Sloth\Module\DataTable\Definition\Table;

use Sloth\Module\DataTable\Definition\Table;
use Sloth\Module\DataTable\Face\FieldInterface;

class Field implements FieldInterface
{
	/**
	 * @var Table
	 */
	public $table;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $alias;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var boolean
	 */
	public $autoIncrement;

	/**
	 * @var ValidatorList
	 */
	public $validators = array();

	public function getAlias()
	{
		$alias = null;
		if ($this->alias !== null) {
			$alias = $this->alias;
		} else {
			$alias = $this->name;
		}
		return $alias;
	}
}
